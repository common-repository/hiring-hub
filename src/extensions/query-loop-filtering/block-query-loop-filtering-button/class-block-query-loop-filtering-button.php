<?php
/**
 * The "Query loop filtering: Button" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Block_Query_Loop_Filtering_Button" class
 */
final class Block_Query_Loop_Filtering_Button extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register block.
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type( $this->container->get_block_path( 'extensions-query-loop-filtering-block-query-loop-filtering-button' ) );
	}
}
