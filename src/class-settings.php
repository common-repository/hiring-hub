<?php
/**
 * Plugin settings
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use WP_Error;

/**
 * The "Settings" class
 *
 * @phpstan-import-type Type_Settings_Fields_Config from Utils\Settings
 */
class Settings extends Utils\Settings {
	/**
	 * Default value for the archive page slug
	 *
	 * @var string
	 */
	const DEFAULT_ARCHIVE_SLUG = 'jobs';

	/**
	 * Default value for the single job post slug
	 *
	 * @var string
	 */
	const DEFAULT_POST_SLUG = 'job';

	/**
	 * Construct the object
	 *
	 * @param Utils\Container                   $container Container instance.
	 * @param array<string,array<string,mixed>> $config    Configuration data; if not provided, data will be loaded from the database.
	 */
	public function __construct( object $container, ?array $config = null ) {
		$this->container     = $container;
		$this->fields_config = $this->get_fields_config();

		/**
		 * Use the parent constructor
		 */
		parent::__construct( $this->container, $this->fields_config, $config );
	}

	/**
	 * Get default settings
	 *
	 * Used only in case if settings were not loaded
	 * from the database:
	 * - not yet configured by the user,
	 * - typically after the plugin installation
	 *
	 * @return array{}|array<string,array<string,mixed>> Empty array if no default settings defined, array of default settings otherwise.
	 */
	protected function get_default(): array {
		return [
			'general' => [
				'archive_slug' => self::DEFAULT_ARCHIVE_SLUG,
				'post_slug'    => self::DEFAULT_POST_SLUG,
			],
		];
	}

	/**
	 * Get the fields configuration
	 *
	 * @return Type_Settings_Fields_Config Fields configuration.
	 */
	protected function get_fields_config(): array {
		$fields_config = [
			'general' => [
				'type'   => Validatable_Fields\Fields_Group::TYPE,
				'config' => [
					/**
					 * The "archive_slug" field defines the archive page slug
					 * - must not be empty
					 * - must be a sanitized slug
					 * - defaults to "jobs" (not translatable)
					 */
					'archive_slug' => Validatable_Fields\Configuration::string_field(
						self::DEFAULT_ARCHIVE_SLUG,

						/**
						 * Restore the field's value if it's incorrect
						 *
						 * @return string
						 */
						function (): string {
							return self::DEFAULT_ARCHIVE_SLUG;
						},

						/**
						 * Sanitize the value before saving it into the
						 * database
						 *
						 * @param mixed $value Value to sanitize.
						 *
						 * @return string Sanitized value.
						 */
						function ( $value ): string {
							return sanitize_title( Utils\Type::ensure_string( $value ) );
						},

						/**
						 * Validate the value before processing further
						 *
						 * @param mixed $value Provided value.
						 *
						 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
						 */
						function ( $value ) {
							if ( ! is_string( $value ) || empty( $value ) ) {
								return new WP_Error(
									'archives_slug_incorrect',
									sprintf(
										'"%s" is not a valid slug.',
										Utils\Type::ensure_string( $value ),
									),
								);
							}

							return true;
						},
					),

					/**
					 * The "post_slug" field defines the single job post slug
					 * - must not be empty
					 * - must be a sanitized slug
					 * - defaults to "job" (not translatable)
					 */
					'post_slug'    => Validatable_Fields\Configuration::string_field(
						self::DEFAULT_POST_SLUG,

						/**
						 * Restore the field's value if it's incorrect
						 *
						 * @return string
						 */
						function (): string {
							return self::DEFAULT_POST_SLUG;
						},

						/**
						 * Sanitize the value before saving it into the
						 * database
						 *
						 * @param mixed $value Value to sanitize.
						 *
						 * @return string Sanitized value.
						 */
						function ( $value ): string {
							return sanitize_title( Utils\Type::ensure_string( $value ) );
						},

						/**
						 * Validate the value before processing further
						 *
						 * @param mixed $value Provided value.
						 *
						 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
						 */
						function ( $value ) {
							if ( ! is_string( $value ) || empty( $value ) ) {
								return new WP_Error(
									'post_slug_incorrect',
									sprintf(
										'"%s" is not a valid slug.',
										Utils\Type::ensure_string( $value ),
									),
								);
							}

							return true;
						},
					),
				],
			],
		];

		/**
		 * Allow other plugins and modules to filter
		 * the settings fields configuration
		 *
		 * @param Type_Settings_Fields_Config $fields_config Configuration of the settings fields.
		 */
		return apply_filters( 'hiring_hub__settings_fields_config', $fields_config );
	}
}
