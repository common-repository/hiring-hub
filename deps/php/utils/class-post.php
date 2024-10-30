<?php
/**
 * Single post class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use WP_Post;

/**
 * The "Post" class
 */
class Post {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Meta fields
	 *
	 * @var array<string,mixed>
	 */
	protected array $meta_fields = [];

	/**
	 * Post object
	 *
	 * @var ?WP_Post
	 */
	protected ?WP_Post $post = null;

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected string $post_type = 'post';

	/**
	 * Capability required to edit the single post
	 *
	 * @var string
	 */
	protected string $edit_capability = 'edit_posts';

	/**
	 * Instance of the Post_Meta class
	 *
	 * @var ?Post_Meta
	 */
	protected static ?object $post_meta = null;

	/**
	 * Constructor
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( object $container ) {
		$this->container = $container;

		if ( null === self::$post_meta ) {
			$this->get_post_meta();
		}
	}

	/**
	 * Set the post object
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function set( WP_Post $post ): void {
		$this->post = $post;
	}

	/**
	 * Get the post object
	 *
	 * @return ?WP_Post $post The post object.
	 */
	public function get(): ?WP_Post {
		return $this->post;
	}

	/**
	 * Get the instance of the Post_Meta class
	 *
	 * @return Post_Meta Instace of Post_Meta class.
	 */
	public function get_post_meta(): object {
		if ( null === self::$post_meta ) {
			// Instantiate the Post_Meta class.
			self::$post_meta = new Post_Meta(
				$this->container,
				$this->post_type,
				$this->edit_capability,
			);
		}

		return self::$post_meta;
	}

	/**
	 * Get the post type
	 *
	 * @return string Post type.
	 */
	public function get_post_type(): string {
		return $this->post_type;
	}

	/**
	 * Get the job meta field value
	 *
	 * @param string $key Meta field key.
	 *
	 * @return mixed Meta field value (mixed), or null if not defined/unknown.
	 */
	public function get_meta( string $key ) {
		$prefixed_key = sprintf( 'hiring_hub__%s', $key );

		if ( ! isset( $this->meta_fields[ $prefixed_key ] ) ) {
			/**
			 * Allow other plugins to filter the job meta field value
			 *
			 * @param mixed    $value        Meta field value; null if not defined/unknown.
			 * @param string   $prefixed_key Meta field key.
			 * @param ?WP_Post $post         The job post object.
			 */
			$this->meta_fields[ $prefixed_key ] = apply_filters(
				sprintf( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
					'hiring_hub__%1$s_meta_%2$s_value',
					$this->get_post_type(),
					$key,
				),
				null,
				$prefixed_key,
				$this->post,
			);
		}

		return $this->meta_fields[ $prefixed_key ];
	}
}
