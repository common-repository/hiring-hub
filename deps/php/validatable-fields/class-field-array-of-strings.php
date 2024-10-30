<?php
/**
 * Field_Array_Of_Strings class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;

use Closure;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use WP_Error;

/**
 * Field_Array_Of_Strings class
 */
final class Field_Array_Of_Strings extends Field {
	/**
	 * Construct the object
	 *
	 * @param string   $key           Key of the field.
	 * @param string[] $default_value Default value of the field.
	 * @param ?Closure $restorer      Additional function for value restore.
	 * @param ?Closure $sanitizer     Additional sanitizer function.
	 * @param ?Closure $validator     Additional validation function.
	 */
	public function __construct( string $key, array $default_value = [], ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ) {
		$this->key           = $key;
		$this->value_type    = 'array';
		$this->default_value = $default_value;
		$this->restorer      = $restorer;
		$this->sanitizer     = $sanitizer;
		$this->validator     = $validator;
	}

	/**
	 * Validate the value
	 *
	 * @param mixed $value Provided value.
	 *
	 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
	 */
	protected function validate_value( $value ) {
		if ( ! is_array( $value ) ) {
			return new WP_Error(
				'non_array_value',
				sprintf(
					'Value of the "%1$s" field must be an array, %2$s given.',
					$this->get_key_camel_case(),
					gettype( $value ),
				),
			);
		}

		if ( array_values( array_filter( $value, 'is_string' ) ) !== $value ) {
			return new WP_Error(
				'non_array_value',
				sprintf(
					'Value of the "%s" field must be an array of strings; non-string values given.',
					$this->get_key_camel_case(),
				),
			);
		}

		return parent::validate_value( $value );
	}

	/**
	 * Sanitize the value
	 *
	 * @param mixed $value Provided value.
	 *
	 * @return string[] Sanitized value.
	 */
	protected function sanitize_value( $value ): array {
		$default_value = Utils\Type::ensure_array_of_strings( $this->default_value );
		$value         = Utils\Type::ensure_array_of_strings( $value, $default_value );

		$value = array_values(
			array_filter(
				array_map(
					/**
					 * Sanitize each of the array values
					 *
					 * @param string $item Single value.
					 *
					 * @return string Sanitized value.
					 */
					function ( string $item ): string {
						return Utils\Strings::trim( sanitize_text_field( $item ) );
					},
					$value,
				),

				/**
				 * Ensure only non-empty values are included
				 *
				 * @param string $item Single value.
				 *
				 * @return bool Whether the value is not empty.
				 */
				function ( string $item ): bool {
					return ! empty( $item );
				},
			),
		);

		$value = parent::sanitize_value( $value );

		/** @var string[] */
		return Utils\Type::is_array_of_strings( $value )
			? $value
			: $default_value;
	}

	/**
	 * Get schema for the REST API
	 *
	 * @return array{type:string,items?:array{type:string}} Schema array.
	 */
	public function get_schema(): array {
		return [
			'type'  => $this->value_type,
			'items' => [
				'type' => 'string',
			],
		];
	}
}
