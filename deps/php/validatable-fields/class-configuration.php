<?php
/**
 * Configuration class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;

use Closure;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use WP_Error;

/**
 * Configuration class
 */
final class Configuration {
	/**
	 * Configure the "array of strings" field
	 *
	 * @param ?string[] $default_value Default value of the field.
	 * @param ?Closure  $restorer      Additional function for value restore.
	 * @param ?Closure  $sanitizer     Additional sanitizer function.
	 * @param ?Closure  $validator     Additional validation function.
	 *
	 * @return array{type:'array_of_strings',default_value:string[],restorer:?Closure,sanitizer:?Closure,validator:?Closure} Field configuration array.
	 */
	public static function array_of_strings_field( ?array $default_value = [], ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ): array {
		if ( null === $default_value ) {
			$default_value = [];
		}

		return [
			'type'          => 'array_of_strings',
			'default_value' => $default_value,
			'restorer'      => $restorer,
			'sanitizer'     => $sanitizer,
			'validator'     => $validator,
		];
	}

	/**
	 * Configure the "boolean" field
	 *
	 * @param ?bool    $default_value Default value of the field.
	 * @param ?Closure $restorer      Additional function for value restore.
	 * @param ?Closure $sanitizer     Additional sanitizer function.
	 * @param ?Closure $validator     Additional validation function.
	 *
	 * @return array{type:'boolean',default_value:bool,restorer:?Closure,sanitizer:?Closure,validator:?Closure} Field configuration array.
	 */
	public static function boolean_field( ?bool $default_value = false, ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ): array {
		if ( null === $default_value ) {
			$default_value = false;
		}

		return [
			'type'          => 'boolean',
			'default_value' => $default_value,
			'restorer'      => $restorer,
			'sanitizer'     => $sanitizer,
			'validator'     => $validator,
		];
	}

	/**
	 * Configure the "dynamic field key" field
	 *
	 * @return array{type:'string',default_value:Closure,validator:?Closure} Field configuration array.
	 */
	public static function dynamic_field_key_field(): array {
		return [
			'type'          => 'string',

			/**
			 * Build default value of this field dynamically
			 *
			 * @return string Field value.
			 */
			'default_value' => static function (): string {
				return sprintf( 'd:%1$d000%2$d', time(), wp_rand( 1000, 9999 ) );
			},

			/**
			 * Additional validation, specific for this field
			 *
			 * @param mixed $value Provided value.
			 *
			 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
			 */
			'validator'     => static function ( $value ) {
				if ( ! is_string( $value ) || ! Utils\Strings::str_starts_with( $value, 'd:' ) || 19 !== strlen( $value ) || 1 !== preg_match( '/^\\d+$/', str_replace( 'd:', '', $value ) ) ) {
					return new WP_Error(
						'field_key_incorrect',
						sprintf(
							'"%s" is not a valid key of the dynamic field.',
							Utils\Type::ensure_string( $value ),
						),
					);
				}

				return true;
			},
		];
	}

	/**
	 * Configure the "exact string" field
	 *
	 * @param string $exact_value Exact value of the field.
	 *
	 * @return array{type:'string',default_value:string,restorer:Closure,validator:Closure} Field configuration array.
	 */
	public static function exact_string_field( string $exact_value ): array {
		return [
			'type'          => 'string',
			'default_value' => $exact_value,

			/**
			 * Restore the value to expected one
			 *
			 * @return string Field value.
			 */
			'restorer'      => static function () use ( $exact_value ): string {
				return $exact_value;
			},

			/**
			 * Ensure this field value always match
			 * the exact value defined
			 *
			 * @param mixed $value Provided value.
			 *
			 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
			 */
			'validator'     => static function ( $value ) use ( $exact_value ) {
				if ( $value !== $exact_value ) {
					return new WP_Error(
						'field_value_incorrect',
						sprintf(
							'Field\'s value must be exactly "%s".',
							$exact_value,
						),
					);
				}

				return true;
			},
		];
	}

	/**
	 * Configure the "integer" field
	 *
	 * @param int      $default_value Default value of the field.
	 * @param int      $min           Minimum valid value.
	 * @param ?int     $max           Maximum valid value.
	 * @param ?Closure $restorer      Additional function for value restore.
	 * @param ?Closure $sanitizer     Additional sanitizer function.
	 * @param ?Closure $validator     Additional validation function.
	 *
	 * @return array{type:'integer',default_value:int,min:int,max:?int,restorer:?Closure,sanitizer:?Closure,validator:?Closure} Field configuration array.
	 */
	public static function integer_field( int $default_value, int $min, ?int $max = null, ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ): array {
		return [
			'type'          => 'integer',
			'default_value' => $default_value,
			'min'           => $min,
			'max'           => $max,
			'restorer'      => $restorer,
			'sanitizer'     => $sanitizer,
			'validator'     => $validator,
		];
	}

	/**
	 * Configure the "number within range" field
	 *
	 * @deprecated This method has been replaced by "integer_field".
	 *
	 * @param int      $default_value Default value of the field.
	 * @param int      $min           Minimum valid value.
	 * @param int      $max           Maximum valid value.
	 * @param ?Closure $restorer      Additional function for value restore.
	 * @param ?Closure $sanitizer     Additional sanitizer function.
	 * @param ?Closure $validator     Additional validation function.
	 *
	 * @return array{type:'integer',default_value:int,min:int,max:?int,restorer:?Closure,sanitizer:?Closure,validator:?Closure} Field configuration array.
	 */
	public static function number_within_range_field( int $default_value, int $min, int $max, ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ): array {
		return self::integer_field( $default_value, $min, $max, $restorer, $sanitizer, $validator );
	}

	/**
	 * Configure the "string of choice" field
	 *
	 * @param ?string  $default_value  Default value of the field.
	 * @param string[] $allowed_values Array of values allowed as this field's value.
	 * @param ?Closure $restorer       Additional function for value restore.
	 * @param ?Closure $sanitizer      Additional sanitizer function.
	 * @param ?Closure $validator      Additional validation function.
	 *
	 * @return array{type:'string_of_choice',default_value:string,allowed_values:string[],restorer:?Closure,sanitizer:?Closure,validator:?Closure} Field configuration array.
	 */
	public static function string_of_choice_field( ?string $default_value = '', ?array $allowed_values = [], ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ): array {
		if ( null === $default_value ) {
			$default_value = '';
		}

		if ( null === $allowed_values ) {
			$allowed_values = [];
		}

		return [
			'type'           => 'string_of_choice',
			'default_value'  => $default_value,
			'allowed_values' => $allowed_values,
			'restorer'       => $restorer,
			'sanitizer'      => $sanitizer,
			'validator'      => $validator,
		];
	}

	/**
	 * Configure the "string" field
	 *
	 * @param ?string  $default_value Default value of the field.
	 * @param ?Closure $restorer      Additional function for value restore.
	 * @param ?Closure $sanitizer     Additional sanitizer function.
	 * @param ?Closure $validator     Additional validation function.
	 *
	 * @return array{type:'string',default_value:string,restorer:?Closure,sanitizer:?Closure,validator:?Closure} Field configuration array.
	 */
	public static function string_field( ?string $default_value = '', ?Closure $restorer = null, ?Closure $sanitizer = null, ?Closure $validator = null ): array {
		if ( null === $default_value ) {
			$default_value = '';
		}

		return [
			'type'          => 'string',
			'default_value' => $default_value,
			'restorer'      => $restorer,
			'sanitizer'     => $sanitizer,
			'validator'     => $validator,
		];
	}
}
