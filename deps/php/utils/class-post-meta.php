<?php
/**
 * Post meta utils class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use WP_Post;

/**
 * The "Post_Meta" class
 */
final class Post_Meta {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected string $post_type;

	/**
	 * Capability required to edit the post meta
	 *
	 * @var string
	 */
	protected string $edit_capability;

	/**
	 * Construct the object
	 *
	 * @param Container $container       Container instance.
	 * @param string    $post_type       Post type.
	 * @param string    $edit_capability Capability required to edit the post meta.
	 */
	public function __construct( object $container, string $post_type, string $edit_capability ) {
		$this->container       = $container;
		$this->post_type       = $post_type;
		$this->edit_capability = $edit_capability;

		// Maybe filter post metadata.
		add_filter( 'get_post_metadata', [ $this, 'trigger_post_meta_hooks' ], 10, 5 );
	}

	/**
	 * Trigger hooks for each single post meta field, first retrieving its current
	 * value from the post meta cache
	 *
	 * @param mixed  $value     The value to return.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key  Metadata key.
	 * @param bool   $single    Whether to return only the first value of the specified meta key.
	 * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user', or any other object type with an associated meta table.
	 *
	 * @return mixed Value of a meta field.
	 */
	public function trigger_post_meta_hooks( $value, int $object_id, string $meta_key, bool $single, string $meta_type ) {
		if ( Strings::str_starts_with( $meta_key, $this->container->get_data_prefix() ) && get_post_type( $object_id ) === $this->post_type ) {
			// Get the meta key without data prefix for use in hooks.
			$key_without_prefix = str_replace(
				sprintf( '%s__', $this->container->get_data_prefix() ),
				'',
				$meta_key,
			);

			/**
			 * Note: this code below duplicates the behavior of the get_metadata_raw core function.
			 *
			 * @see https://developer.wordpress.org/reference/functions/get_metadata_raw/
			 */
			$meta_cache = wp_cache_get( $object_id, sprintf( '%s_meta', $meta_type ) );

			if ( ! $meta_cache ) {
				$meta_cache = update_meta_cache( $meta_type, [ $object_id ] );

				if ( isset( $meta_cache[ $object_id ] ) ) {
					$meta_cache = $meta_cache[ $object_id ];
				} else {
					$meta_cache = null;
				}
			}

			if ( isset( $meta_cache[ $meta_key ] ) ) {
				if ( $single ) {
					$value = maybe_unserialize( $meta_cache[ $meta_key ][0] );
				} else {
					$value = array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
				}
			}

			/**
			 * Allow other plugins and modules to easily hook into the post meta
			 * raw value and modify it as needed
			 *
			 * @param mixed $value  The value to return.
			 * @param bool  $single Whether to return only the first value of the specified meta key.
			 */
			$value = apply_filters(
				sprintf( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
					'hiring_hub__%1$s_meta_%2$s_raw_value',
					$this->post_type,
					$key_without_prefix,
				),
				$value,
				$single,
			);
		}

		return $value;
	}

	/**
	 * Register post meta field
	 *
	 * @param string                   $key   Meta key.
	 * @param Validatable_Fields\Field $field Instance of the field object.
	 *
	 * @return void
	 */
	public function register_post_meta_field( string $key, object $field ): void {
		$args = [
			'show_in_rest'      => 'array' === $field->get_value_type()
				? [ 'schema' => $field->get_schema() ]
				: true,
			'single'            => true,
			'type'              => $field->get_value_type(),
			'object_subtype'    => $this->post_type,
			'default'           => $field->get_default_value(),

			/**
			 * Verify whether current user can modify the
			 * meta key value or not
			 *
			 * @return bool Auth callback check result.
			 */
			'auth_callback'     => function (): bool {
				return current_user_can( $this->edit_capability );
			},

			/**
			 * Ensure the post meta value is properly sanitized
			 *
			 * @param mixed $value Value to sanitize.
			 *
			 * @return mixed Sanitized value.
			 */
			'sanitize_callback' => function ( $value ) use ( $field ) {
				$field->set_value( $value, true );
				return $field->get_value();
			},
		];

		if ( $field instanceof Validatable_Fields\Field_Integer ) {
			$args['minimum'] = $field->get_minimum();
			$max             = $field->get_maximum();

			if ( null !== $max ) {
				$args['maximum'] = $max;
			}
		}

		register_meta(
			'post',
			sprintf( 'hiring_hub__%s', $key ),
			$args,
		);

		add_filter(
			sprintf(
				'hiring_hub__%1$s_meta_%2$s_value',
				$this->post_type,
				$key,
			),

			/**
			 * Filter the post meta field value
			 *
			 * @param mixed   $value        Meta field value; null if not defined/unknown.
			 * @param string  $prefixed_key Meta field key.
			 * @param WP_Post $post         The post post object.
			 *
			 * @return mixed Value of the meta field.
			 */
			function ( $value, string $prefixed_key, WP_Post $post ) use ( $field ) {
				if ( null === $value ) {
					/**
					 * To support arrays correctly, we need to get all the post meta ("single" flag set
					 * to "false", as in default configuration), and then, get the first item from the
					 * array returned - which in our case can contain an subarray of values.
					 *
					 * Switching the "single" flag to "true" will cause an unexpected behavior, where
					 * not only the first meta value is returned, but also the first item of the
					 * subarray.
					 */
					$meta_value = get_post_meta( $post->ID, $prefixed_key );
					$meta_value = is_array( $meta_value ) ? $meta_value[0] : null;

					$field->set_value( $meta_value, true );
					$value = $field->get_value();
				}

				return $value;
			},
			10,
			3,
		);

		add_filter(
			sprintf(
				'hiring_hub__%1$s_meta_%2$s_raw_value',
				$this->post_type,
				$key,
			),

			/**
			 * Filter the post meta field raw value
			 *
			 * @param mixed $value  The value to return.
			 * @param bool  $single Whether to return only the first value of the specified meta key.
			 *
			 * @return mixed Raw value of a meta field.
			 */
			function ( $value, bool $single ) use ( $field ) {
				if ( ! $single ) {
					$value = is_array( $value ) ? ( $value[0] ?? null ) : $value;
				}

				$field->set_value( $value, true );
				$value = $field->get_value();

				// The return value of get_metadata should always be a string for scalar types.
				if ( in_array( $field->get_value_type(), [ 'string', 'number', 'integer', 'boolean' ], true ) ) {
					$value = Type::ensure_string( $value );
				}

				if ( false === $single || 'array' === $field->get_value_type() ) {
					$value = [ $value ];
				}

				return $value;
			},
			10,
			2,
		);
	}
}
