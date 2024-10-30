<?php
/**
 * The "Job Specification Characteristics" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Job_Specification;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;
use Teydea_Studio\Hiring_Hub\Settings;
use WP_Block;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The "Block_Job_Specification_Characteristics" class
 *
 * @phpstan-import-type Type_Job_Specification_Items from Extension
 */
final class Block_Job_Specification_Characteristics extends Utils\Module {
	/**
	 * Cache group
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'job_specification_characteristics_items';

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register block.
		add_action( 'init', [ $this, 'register_block' ] );

		// Register endpoint.
		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );

		// Filter the block rendition to include up-to-date characteristics.
		add_filter( 'render_block_hiring-hub/job-specification-characteristics', [ $this, 'filter_block_rendition' ], 10, 3 );

		// Clean items cache when post's cache is cleaned.
		add_action( 'clean_post_cache', [ $this, 'clean_items_cache' ] );
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type( $this->container->get_block_path( 'extensions-job-specification-block-job-specification-characteristics' ) );
	}

	/**
	 * Register endpoint
	 *
	 * @return void
	 */
	public function register_endpoint(): void {
		register_rest_route(
			sprintf( '%s/v1', $this->container->get_slug() ),
			'/block-job-specification-characteristics',
			[
				'methods'             => 'GET',
				'args'                => [
					'post_id'                          => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
					'show_all_enabled_characteristics' => [
						'required'          => true,
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
					'disallowed_characteristics'       => [
						'required'          => true,
						'type'              => 'array',

						/**
						 * Data sanitization
						 *
						 * @param string|array $value Field value.
						 *
						 * @return string[] Array of strings.
						 */
						'sanitize_callback' => function ( $value ): array {
							if ( is_string( $value ) ) {
								$value = Utils\JSON::decode( $value, [] );
							} elseif ( ! is_array( $value ) ) {
								$value = [];
							}

							/** @var string[] $value */
							return Utils\Type::ensure_array_of_strings( $value );
						},
					],
				],

				/**
				 * Process the REST API request
				 *
				 * @param WP_REST_Request $request REST request.
				 *
				 * @return WP_REST_Response Instance of WP_REST_Response.
				 */
				'callback'            => function ( WP_REST_Request $request ): WP_REST_Response {
					/** @var int $post_id */
					$post_id = $request->get_param( 'post_id' );

					/** @var bool $show_all_enabled_characteristics */
					$show_all_enabled_characteristics = $request->get_param( 'show_all_enabled_characteristics' );

					/** @var string[] $disallowed_characteristics */
					$disallowed_characteristics = $request->get_param( 'disallowed_characteristics' );

					/** @var Job $job */
					$job = $this->container->get_instance_of( 'job' );

					// Collect and return characteristics items.
					return new WP_REST_Response( $this->collect_items( $job, $post_id, $show_all_enabled_characteristics, $disallowed_characteristics ), 200 );
				},

				/**
				 * Ensure that user is logged in and has the required
				 * capability
				 *
				 * @return bool Boolean "true" if user has the permission to process this request, "false" otherwise.
				 */
				'permission_callback' => function (): bool {
					/** @var Utils\User $user */
					$user = $this->container->get_instance_of( 'user' );
					return $user->can( 'edit_posts' );
				},
			],
		);
	}

	/**
	 * Collect items to be rendered in a block by combining the post meta and plugin settings
	 *
	 * @param Job      $job                              The Job class instance.
	 * @param int      $post_id                          Post ID.
	 * @param bool     $show_all_enabled_characteristics Whether to show all enabled characteristics.
	 * @param string[] $disallowed_characteristics       Array of disallowed characteristics.
	 *
	 * @return string[] Characteristics to render.
	 */
	protected function collect_items( object $job, int $post_id, bool $show_all_enabled_characteristics, array $disallowed_characteristics ): array {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return [];
		}

		/** @var Utils\Cache $cache */
		$cache = $this->container->get_instance_of( 'cache' );

		$cache->set_group( self::CACHE_GROUP );
		$cache->set_post( $post );

		/** @var false|string[] $result */
		$result = $cache->read();

		if ( false === $result ) {
			$result = [];
			$job->set( $post );

			/** @var Settings $settings */
			$settings     = $this->container->get_instance_of( 'settings' );
			$fields_group = $settings->get_fields_group( Extension::SETTINGS_KEY );

			/** @var Type_Job_Specification_Items|WP_Error $items */
			$items = null !== $fields_group ? $fields_group->get_value() : [];

			if ( is_array( $items ) ) {
				foreach ( $items as $item_key => $item ) {
					// We only render "characteristics" in this block.
					if ( 'boolean' !== $item['type'] ) {
						continue;
					}

					// Ensure this characteristic is not disallowed.
					if ( false === $show_all_enabled_characteristics && in_array( $item_key, $disallowed_characteristics, true ) ) {
						continue;
					}

					// Get the value of the characteristic.
					$value = $job->get_meta( sprintf( '%s__%s', Extension::SETTINGS_KEY, $item_key ) );

					// Only render "enabled" characteristics.
					if ( true !== $value ) {
						continue;
					}

					// Add to the characteristics list.
					$result[] = $item['name'];
				}

				// Cache results.
				$cache->write( $result );
			}
		}

		return $result;
	}

	/**
	 * Filter the block rendition to include up-to-date characteristics
	 *
	 * @param string                                                                                                        $block_content The block content.
	 * @param array{blockName:string,attrs:array<string,mixed>,innerBlocks:string[],innerHTML:string,innerContent:string[]} $block         The full block, including name and attributes.
	 * @param WP_Block                                                                                                      $instance      The block instance.
	 *
	 * @return string Updated content of the block.
	 */
	public function filter_block_rendition( string $block_content, array $block, WP_Block $instance ): string {
		/** @var array{showAllEnabledCharacteristics:bool,disallowedCharacteristics:string[]} $block_attributes */
		$block_attributes = wp_parse_args(
			$block['attrs'],
			[
				'showAllEnabledCharacteristics' => true,
				'disallowedCharacteristics'     => [],
			],
		);

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		if ( $job->get_post_type() === ( $instance->context['postType'] ?? null ) && isset( $instance->context['postId'] ) ) {
			// Determine the characteristics items to render.
			$characteristics = $this->collect_items( $job, $instance->context['postId'], $block_attributes['showAllEnabledCharacteristics'], $block_attributes['disallowedCharacteristics'] );

			if ( ! empty( $characteristics ) ) {
				$inner_html = '';

				foreach ( $characteristics as $characteristic ) {
					$inner_html .= sprintf(
						'<li class="wp-block-hiring-hub-job-specification-characteristics__characteristic wp-block-hiring-hub-job-specification__characteristic--%1$s">%2$s</li>',
						esc_attr( sanitize_title( $characteristic ) ),
						esc_html( $characteristic ),
					);
				}

				$block_content = str_replace( '></ul>', sprintf( '>%s</ul>', $inner_html ), $block_content );
			}
		}

		return $block_content;
	}

	/**
	 * Clean items cache when post's cache is cleaned
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function clean_items_cache( int $post_id ): void {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		/** @var Utils\Cache $cache */
		$cache = $this->container->get_instance_of( 'cache' );

		$cache->set_group( self::CACHE_GROUP );
		$cache->set_post( $post );
		$cache->delete();
	}
}
