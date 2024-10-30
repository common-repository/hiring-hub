<?php
/**
 * Block Editor class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Block_Editor" class
 */
final class Module_Block_Editor extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register a category for custom blocks.
		add_filter( 'block_categories_all', [ $this, 'register_blocks_category' ] );
	}

	/**
	 * Register a category for custom blocks
	 *
	 * @param array<int,array{slug:string,title:string}> $block_categories Array of block categories.
	 *
	 * @return array<int,array{slug:string,title:string}> Updated array of block categories.
	 */
	public function register_blocks_category( array $block_categories ): array {
		return array_merge(
			[
				[
					'slug'  => 'hiring-hub',
					'title' => __( 'Hiring Hub', 'hiring-hub' ),
				],
			],
			$block_categories,
		);
	}
}
