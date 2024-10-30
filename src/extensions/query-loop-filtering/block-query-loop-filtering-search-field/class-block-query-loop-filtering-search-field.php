<?php
/**
 * The "Query loop filtering: Search Field" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use WP_HTML_Tag_Processor;

/**
 * The "Block_Query_Loop_Filtering_Search_Field" class
 */
final class Block_Query_Loop_Filtering_Search_Field extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register block.
		add_action( 'init', [ $this, 'register_block' ] );

		// Modify the block markup by appending the "value" attribute.
		add_filter( 'render_block_hiring-hub/query-loop-filtering-search-field', [ $this, 'render_block' ], 10, 2 );
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type( $this->container->get_block_path( 'extensions-query-loop-filtering-block-query-loop-filtering-search-field' ) );
	}

	/**
	 * Modify the block markup by appending the "value" attribute
	 *
	 * @param string                                                                                                        $block_content The block content.
	 * @param array{blockName:string,attrs:array<string,mixed>,innerBlocks:string[],innerHTML:string,innerContent:string[]} $block         The full block, including name and attributes.
	 *
	 * @return string Updated content of the block.
	 */
	public function render_block( string $block_content, array $block ): string {
		$phrase = isset( $_GET[ Extension::QUERY_ARG_KEY ]['s'] ) ? sanitize_text_field( wp_unslash( $_GET[ Extension::QUERY_ARG_KEY ]['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( '' !== $phrase ) {
			/**
			 * Use the core's html tag processor to inject the
			 * value attribute with the current search phrase
			 * as a value to the input field
			 */
			$processor = new WP_HTML_Tag_Processor( $block_content );

			if ( $processor->next_tag( [ 'tag_name' => 'INPUT' ] ) ) {
				$processor->set_attribute( 'value', esc_attr( $phrase ) );
			}

			$block_content = $processor->get_updated_html();
		}

		return $block_content;
	}
}
