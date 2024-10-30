<?php
/**
 * The "Job Specification List" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Job_Specification;

use NumberFormatter;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;
use Teydea_Studio\Hiring_Hub\Settings;
use WP_Block;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The "Block_Job_Specification_List" class
 *
 * @phpstan-import-type Type_Job_Specification_Items from Extension
 */
final class Block_Job_Specification_List extends Utils\Module {
	/**
	 * Cache group
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'job_specification_list_items';

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

		// Filter the block rendition to include up-to-date items list.
		add_filter( 'render_block_hiring-hub/job-specification-list', [ $this, 'filter_block_rendition' ], 10, 3 );

		// Clean items cache when post's cache is cleaned.
		add_action( 'clean_post_cache', [ $this, 'clean_items_cache' ] );
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type( $this->container->get_block_path( 'extensions-job-specification-block-job-specification-list' ) );

		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'job-specification-list' ] );
		$asset->add_inline_script( 'editor-script', [ 'locale' => get_bloginfo( 'language' ) ] );
	}

	/**
	 * Register endpoint
	 *
	 * @return void
	 */
	public function register_endpoint(): void {
		register_rest_route(
			sprintf( '%s/v1', $this->container->get_slug() ),
			'/block-job-specification-list',
			[
				'methods'             => 'GET',
				'args'                => [
					'post_id'          => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
					'show_all_items'   => [
						'required'          => true,
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
					'disallowed_items' => [
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

					/** @var bool $show_all_items */
					$show_all_items = $request->get_param( 'show_all_items' );

					/** @var string[] $disallowed_items */
					$disallowed_items = $request->get_param( 'disallowed_items' );

					/** @var Job $job */
					$job = $this->container->get_instance_of( 'job' );

					// Collect and return list items.
					return new WP_REST_Response( $this->collect_items( $job, $post_id, $show_all_items, $disallowed_items ), 200 );
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
	 * @param Job      $job                    The Job class instance.
	 * @param int      $post_id                Post ID.
	 * @param bool     $show_all_items Whether to show all items.
	 * @param string[] $disallowed_items       Array of disallowed items.
	 *
	 * @return array<int,array{label:string,type:string,values:string[]}> Items to render.
	 */
	protected function collect_items( object $job, int $post_id, bool $show_all_items, array $disallowed_items ): array {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return [];
		}

		/** @var Utils\Cache $cache */
		$cache = $this->container->get_instance_of( 'cache' );

		$cache->set_group( self::CACHE_GROUP );
		$cache->set_post( $post );

		/** @var false|array<int,array{label:string,type:string,values:string[]}> $result */
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
					// Ensure this item is not disallowed.
					if ( false === $show_all_items && in_array( $item_key, $disallowed_items, true ) ) {
						continue;
					}

					// Get the item values.
					$values = $job->get_meta( sprintf( '%s__%s', Extension::SETTINGS_KEY, $item_key ) );

					if ( 'boolean' === $item['type'] ) {
						$values = [ true === $values ? __( 'Yes', 'hiring-hub' ) : __( 'No', 'hiring-hub' ) ];
					} elseif ( 'integer' === $item['type'] ) {
						if ( is_int( $values ) ) {
							$values = [ strval( $values ) ];
						} else {
							continue;
						}
					} elseif ( in_array( $item['type'], [ 'date', 'text', 'url' ], true ) ) {
						if ( ! empty( $values ) ) {
							$values = [ $values ];
						} else {
							continue;
						}
					} elseif ( 'salary' === $item['type'] ) {
						/** @var ?array{isDefined?:bool,currency:string,min:int,max:int,unit:string} $salary */
						$salary = Utils\JSON::decode( is_string( $values ) ? $values : '' );

						if ( null === $salary || ! isset( $salary['isDefined'] ) || true !== $salary['isDefined'] ) {
							continue;
						}

						if ( class_exists( 'NumberFormatter' ) ) {
							$formatter = new NumberFormatter( get_locale(), NumberFormatter::CURRENCY );
							$formatter->setTextAttribute( NumberFormatter::CURRENCY_CODE, $salary['currency'] );
							$formatter->setAttribute( NumberFormatter::FRACTION_DIGITS, 0 );

							$formatted_value = $salary['min'] === $salary['max']
								? $formatter->formatCurrency( $salary['min'], $salary['currency'] )
								: sprintf(
									'%1$s - %2$s',
									$formatter->formatCurrency( $salary['min'], $salary['currency'] ),
									$formatter->formatCurrency( $salary['max'], $salary['currency'] ),
								);
						} else {
							$formatted_value = $salary['min'] === $salary['max']
								? sprintf( '%1$s %2$d', $salary['currency'], $salary['min'] )
								: sprintf(
									'%1$s %2$d - %3$d',
									$salary['currency'],
									$salary['min'],
									$salary['max'],
								);
						}

						$values = [ sprintf( '%1$s / %2$s', $formatted_value, $salary['unit'] ) ];
					} elseif ( ! is_array( $values ) ) {
						$values = [];
					}

					if ( empty( $values ) ) {
						continue;
					}

					// Add to the results list.
					$result[] = [
						'label'  => $item['name'],
						'type'   => $item['type'],

						/** @var string[] $values */
						'values' => $values,
					];
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
		/** @var array{showAllItems:bool,disallowedItems:string[]} $block_attributes */
		$block_attributes = wp_parse_args(
			$block['attrs'],
			[
				'showAllItems'    => true,
				'disallowedItems' => [],
			],
		);

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		if ( $job->get_post_type() === ( $instance->context['postType'] ?? null ) && isset( $instance->context['postId'] ) ) {
			// Determine the list items to render.
			$items = $this->collect_items( $job, $instance->context['postId'], $block_attributes['showAllItems'], $block_attributes['disallowedItems'] );

			if ( ! empty( $items ) ) {
				$inner_html = '';

				// Generate markup for each item.
				foreach ( $items as $item ) {
					$inner = '';

					foreach ( $item['values'] as $value ) {
						$inner .= sprintf(
							'<span class="wp-block-hiring-hub-job-specification-list__item-value wp-block-hiring-hub-job-specification-list__item-value--%1$s">%2$s</span>',
							esc_attr( sanitize_title( $value ) ),
							$this->format_value( $value, $item['type'] ),
						);
					}

					$inner_html .= sprintf(
						'<div class="wp-block-hiring-hub-job-specification-items__item wp-block-hiring-hub-job-specification__item--%1$s"><p class="wp-block-hiring-hub-job-specification-list__item-label">%2$s</p><p class="wp-block-hiring-hub-job-specification-list__item-values">%3$s</p></div>',
						esc_attr( sanitize_title( $item['label'] ) ),
						esc_html( $item['label'] ),
						wp_kses(
							$inner,
							[
								'span' => [ 'class' => true ],
								'a'    => [
									'href'   => true,
									'target' => true,
									'rel'    => true,
								],
							],
						),
					);
				}

				$block_content = str_replace( '></div>', sprintf( '>%s</div>', $inner_html ), $block_content );
			}
		}

		return $block_content;
	}

	/**
	 * Format the single value
	 *
	 * @param string $value Value to format.
	 * @param string $type  Item type.
	 *
	 * @return string Formatted value.
	 */
	protected function format_value( string $value, string $type ): string {
		if ( 'url' === $type ) {
			return sprintf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url( $value ),
				esc_html( $value ),
			);
		}

		return esc_html( $value );
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
