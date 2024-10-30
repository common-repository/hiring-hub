<?php
/**
 * The "Query loop filtering: Container" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;
use WP_HTML_Tag_Processor;

/**
 * The "Block_Query_Loop_Filtering_Container" class
 */
final class Block_Query_Loop_Filtering_Container extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register block.
		add_action( 'init', [ $this, 'register_block' ] );

		// Modify the block markup by appending the "action" attribute.
		add_filter( 'render_block_hiring-hub/query-loop-filtering-container', [ $this, 'render_block' ], 10, 2 );
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type( $this->container->get_block_path( 'extensions-query-loop-filtering-block-query-loop-filtering-container' ) );
	}

	/**
	 * Modify the block markup by appending the "action" attribute
	 *
	 * @param string                                                                                                        $block_content The block content.
	 * @param array{blockName:string,attrs:array<string,mixed>,innerBlocks:string[],innerHTML:string,innerContent:string[]} $block         The full block, including name and attributes.
	 *
	 * @return string Updated content of the block.
	 */
	public function render_block( string $block_content, array $block ): string {
		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Get the permalink for a "job" post type archive.
		$archive_url = get_post_type_archive_link( $job->get_post_type() );

		if ( is_string( $archive_url ) ) {
			/**
			 * Use the core's html tag processor to inject the
			 * action attribute with the archive URL as a value
			 * to the form container
			 */
			$processor = new WP_HTML_Tag_Processor( $block_content );

			if ( $processor->next_tag( [ 'tag_name' => 'FORM' ] ) ) {
				$processor->set_attribute( 'action', esc_url( $archive_url ) );
			}

			$block_content = $processor->get_updated_html();
		}

		return $block_content;
	}
}
