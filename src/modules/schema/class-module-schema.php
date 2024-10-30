<?php
/**
 * Support for the schema.org markup
 *
 * @see https://schema.org/
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use Teydea_Studio\Hiring_Hub\Job;
use Teydea_Studio\Hiring_Hub\Settings;
use WP_Post;

/**
 * The "Module_Schema" class
 *
 * @phpstan-import-type Type_Settings_Fields_Config from Utils\Settings
 */
final class Module_Schema extends Utils\Module {
	/**
	 * Settings key
	 *
	 * @var string
	 */
	const SETTINGS_KEY = 'schema';

	/**
	 * Settings fields, representing schema.org properties
	 * of the JobPosting type
	 *
	 * @var string[]
	 */
	const PROPERTIES = [
		'base_salary',
		'education_requirements',
		'eligibility_to_work_requirement',
		'employer_overview',
		'employment_type',
		'estimated_salary',
		'experience_in_place_of_education',
		'experience_requirements',
		'incentive_compensation',
		'industry',
		'job_benefits',
		'job_immediate_start',
		'job_location_type',
		'job_start_date',
		'occupational_category',
		'physical_requirement',
		'qualifications',
		'responsibilities',
		'security_clearance_requirement',
		'sensory_requirement',
		'skills',
		'special_commitments',
		'total_job_openings',
		'valid_through',
		'work_hours',
	];

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Enqueue required scripts and styles.
		add_action( 'hiring_hub__enqueue_settings_page_scripts', [ $this, 'enqueue_scripts' ] );

		// Filter the configuration of the settings fields.
		add_filter( 'hiring_hub__settings_fields_config', [ $this, 'filter_settings_fields_config' ] );

		// Render schema markup.
		add_action( 'wp_head', [ $this, 'render_markup' ] );
	}

	/**
	 * Enqueue scripts for a settings page
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'schema' ] );
		$asset->enqueue_script();
	}

	/**
	 * Filter the configuration of the settings fields
	 *
	 * @param Type_Settings_Fields_Config $fields_config Configuration of the settings fields.
	 *
	 * @return Type_Settings_Fields_Config Updated configuration of the settings fields.
	 */
	public function filter_settings_fields_config( array $fields_config ): array { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
		$config = [];

		foreach ( self::PROPERTIES as $property ) {
			/**
			 * Allow other plugins and modules to filter the default
			 * value of the specific schema.org element
			 *
			 * @param string $default_value Default value.
			 * @param string $property      Schema element property.
			 */
			$default_value       = apply_filters( 'hiring_hub__schema_element_default_value', '', $property );
			$config[ $property ] = Validatable_Fields\Configuration::string_field( $default_value );
		}

		$fields_config[ self::SETTINGS_KEY ] = [
			'type'   => Validatable_Fields\Fields_Group::TYPE,
			'config' => $config,
		];

		return $fields_config;
	}

	/**
	 * Render schema markup
	 *
	 * @return void
	 */
	public function render_markup(): void {
		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		/** @var Settings $settings */
		$settings = $this->container->get_instance_of( 'settings' );

		/**
		 * Ensure we proceed on job post type only
		 */
		if ( ! is_singular( $job->get_post_type() ) ) {
			return;
		}

		$post_id = get_the_ID();

		if ( false === $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || $post->post_type !== $job->get_post_type() ) {
			return;
		}

		// Assign post to a Job object.
		$job->set( $post );

		/**
		 * Build the initial set of values
		 */
		$elements = [
			'@context'    => 'https://schema.org/',
			'@type'       => 'JobPosting',
			// Limit the length of the "description" value to 300 characters.
			'description' => Utils\Strings::length_limited( Utils\Strings::trim( html_entity_decode( get_the_excerpt( $post ) ) ), 300 ),
			'title'       => Utils\Strings::trim( html_entity_decode( get_the_title( $post ) ) ),
		];

		/**
		 * Get the date when the job was posted, in an ISO-8601 format
		 */
		$date_posted = get_the_date( 'c', $post );

		if ( is_string( $date_posted ) ) {
			$elements['datePosted'] = $date_posted;
		}

		/**
		 * Get the permalink
		 */
		$permalink = get_permalink( $post );

		if ( is_string( $permalink ) ) {
			$elements['url'] = $permalink;
		}

		/**
		 * Get the schema.org property elements configured
		 * in the plugin's settings
		 */
		$fields_group = $settings->get_fields_group( self::SETTINGS_KEY );

		if ( null !== $fields_group ) {
			foreach ( self::PROPERTIES as $property ) {
				$field = $fields_group->get_field( $property );

				if ( null === $field ) {
					continue;
				}

				$key   = $field->get_key_camel_case();
				$value = $field->get_value();

				/**
				 * Allow other plugins and modules to filter
				 * the schema element value
				 *
				 * @param mixed  $value Schema element value.
				 * @param string $key   Schema element key (property).
				 * @param Job    $job   Job object.
				 */
				$value = apply_filters( 'hiring_hub__schema_element_value', $value, $field->get_key(), $job );

				if ( '' !== $value ) {
					$elements[ $key ] = $value;
				}
			}
		}

		/**
		 * Allow other plugins and modules to filter
		 * the schema markup elements
		 *
		 * @param array<string,mixed> $elements Schema markup elements.
		 * @param Job                 $job      Job object.
		 */
		$elements = apply_filters( 'hiring_hub__schema_elements', $elements, $job );

		?>
		<script type="application/ld+json"><?php echo Utils\JSON::encode( [ (object) $elements ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
		<?php
	}
}
