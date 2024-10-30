<?php
/**
 * Template class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use WP_Post;
use WP_Query;

/**
 * The "Template" class
 */
class Template {
	/**
	 * Post type
	 *
	 * @var string
	 */
	const POST_TYPE = 'wp_template';

	/**
	 * Post status
	 *
	 * @var string
	 */
	const POST_STATUS = 'publish';

	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Template slug
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Post object of the template
	 *
	 * @var ?WP_Post
	 */
	protected ?WP_Post $post = null;

	/**
	 * Constructor
	 *
	 * @param Container $container Container instance.
	 * @param string    $slug      Template slug.
	 */
	public function __construct( object $container, string $slug ) {
		$this->container = $container;
		$this->slug      = $slug;
	}

	/**
	 * Check if template with a given slug exists
	 *
	 * @return bool Whether the template exists or not.
	 */
	public function exists(): bool {
		$templates = new WP_Query(
			[
				'post_type'              => self::POST_TYPE,
				'post_name'              => $this->slug,
				'post_status'            => self::POST_STATUS,
				'posts_per_page'         => 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'tax_query'              => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => 'wp_theme',
						'field'    => 'slug',
						'terms'    => get_stylesheet(),
					],
				],
				'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'     => sprintf( '%s__template_version', $this->container->get_data_prefix() ),
						'compare' => 'EXISTS',
					],
				],
			],
		);

		if ( $templates->have_posts() ) {
			$posts = $templates->get_posts();

			if ( isset( $posts[0] ) && $posts[0] instanceof WP_Post ) {
				$this->post = $posts[0];
				return true;
			}
		}

		return false;
	}

	/**
	 * Load the template contents from a file
	 *
	 * @param string $path Path to the template file.
	 *
	 * @return string Template contents.
	 */
	public function load_from_file( string $path ): string {
		// Start the output buffering.
		ob_start();

		// Include template file.
		include $path;

		// Get the contents of the active output buffer and turn it off.
		$output = ob_get_clean();

		// Return the file contents.
		return false === $output ? '' : $output;
	}

	/**
	 * Insert the template
	 *
	 * @param string $title   Template title.
	 * @param string $excerpt Template excerpt (description).
	 * @param string $path    Path to the template file.
	 *
	 * @return bool Whether the template was inserted successfully.
	 */
	public function insert( string $title, string $excerpt, string $path ): bool {
		$content = $this->load_from_file( $path );
		$post_id = wp_insert_post(
			[
				'post_type'      => self::POST_TYPE,
				'post_title'     => $title,
				'post_name'      => $this->slug,
				'post_excerpt'   => $excerpt,
				'post_content'   => $content,
				'post_status'    => self::POST_STATUS,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'meta_input'     => [
					'is_wp_suggestion' => '',
					sprintf( '%s__template_version', $this->container->get_data_prefix() ) => $this->container->get_version(),
				],
				'tax_input'      => [
					'wp_theme' => get_stylesheet(),
				],
			],
		);

		if ( 0 === $post_id ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		$this->post = $post;
		return true;
	}
}
