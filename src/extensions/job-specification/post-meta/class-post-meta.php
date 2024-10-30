<?php
/**
 * The "Job Specification" extension post-meta settings
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Job_Specification;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use Teydea_Studio\Hiring_Hub\Job;
use Teydea_Studio\Hiring_Hub\Settings;

/**
 * The "Post_Meta" class
 */
final class Post_Meta extends Utils\Module {
	/**
	 * Dynamic fields group object
	 *
	 * @var ?Validatable_Fields\Fields_Group
	 */
	private ?object $fields_group = null;

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the post meta fields for the REST API.
		add_action( 'init', [ $this, 'register_post_meta_fields' ] );

		// Engueue block editor assets.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
	}

	/**
	 * Get the dynamic fields group object
	 *
	 * @return ?Validatable_Fields\Fields_Group Dynamic fields group object.
	 */
	protected function get_dynamic_fields_group(): ?object {
		if ( null === $this->fields_group ) {
			/** @var Settings $settings */
			$settings = $this->container->get_instance_of( 'settings' );

			if ( ! $settings->has_validation_errors() ) {
				/** @var ?Validatable_Fields\Dynamic_Fields_Group $fields_group */
				$fields_group = $settings->get_fields_group( Extension::SETTINGS_KEY );

				if ( null !== $fields_group ) {
					$this->fields_group = $fields_group->get_dynamic_fields( sprintf( '%s_fields', Extension::SETTINGS_KEY ) );
				}
			}
		}

		return $this->fields_group;
	}

	/**
	 * Register the post meta fields
	 *
	 * @return void
	 */
	public function register_post_meta_fields(): void {
		$fields_group = $this->get_dynamic_fields_group();

		if ( null !== $fields_group ) {
			/** @var Job $job */
			$job = $this->container->get_instance_of( 'job' );

			/** @var Validatable_Fields\Field $field */
			foreach ( $fields_group->get_fields() as $field ) {
				$job->get_post_meta()->register_post_meta_field(
					sprintf(
						'%1$s__%2$s',
						Extension::SETTINGS_KEY,
						$field->get_key(),
					),
					$field,
				);
			}
		}
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		/** @var Utils\Screen $screen */
		$screen = $this->container->get_instance_of( 'screen' );

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Ensure we load custom assets for custom post type only.
		if ( ! $screen->is_block_editor_and_post_type( $job->get_post_type() ) ) {
			return;
		}

		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'extensions-job-specification-post-meta' ] );

		$asset->enqueue_style();
		$asset->enqueue_script();
	}
}
