<?php
/**
 * The "Apply On Link" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Apply_On_Link;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;
use WP_Block;
use WP_HTML_Tag_Processor;
use WP_Post;

/**
 * The "Block_Apply_On_Link" class
 */
final class Block_Apply_On_Link extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register block.
		add_action( 'init', [ $this, 'register_block' ] );

		// Filter the block rendition to include "apply to" link.
		add_filter( 'render_block_hiring-hub/apply-on-link', [ $this, 'filter_block_rendition' ], 10, 3 );
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type( $this->container->get_block_path( 'extensions-apply-on-link-block-apply-on-link' ) );
	}

	/**
	 * Filter the block rendition to include "apply to" link
	 *
	 * @param string                                                                                                        $block_content The block content.
	 * @param array{blockName:string,attrs:array<string,mixed>,innerBlocks:string[],innerHTML:string,innerContent:string[]} $block         The full block, including name and attributes.
	 * @param WP_Block                                                                                                      $instance      The block instance.
	 *
	 * @return string Updated content of the block.
	 */
	public function filter_block_rendition( string $block_content, array $block, WP_Block $instance ): string {
		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Ensure this block only renders on the "job" post type context.
		if ( $job->get_post_type() === ( $instance->context['postType'] ?? null ) && isset( $instance->context['postId'] ) ) {
			$post = get_post( $instance->context['postId'] );

			if ( $post instanceof WP_Post ) {
				$job->set( $post );

				$apply_on_link = $job->get_meta( Post_Meta::FIELD_KEY );

				if ( is_string( $apply_on_link ) && '' !== $apply_on_link ) {
					$processor = new WP_HTML_Tag_Processor( $block_content );
					$processor->next_tag( [ 'tag_name' => 'a' ] );

					if ( is_email( $apply_on_link ) ) {
						$apply_on_link = sprintf( 'mailto:%s', $apply_on_link );
					} else {
						$processor->set_attribute( 'target', '_blank' );
						$processor->set_attribute( 'rel', 'noopener noreferrer' );
					}

					$processor->set_attribute( 'href', $apply_on_link );
					return $processor->get_updated_html();
				}
			}
		}

		// Block should not be rendered - return empty string.
		return '';
	}
}
