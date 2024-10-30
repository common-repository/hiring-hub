<?php
/**
 * Block Patterns class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Block_Patterns" class
 */
final class Module_Block_Patterns extends Utils\Module {
	/**
	 * Pattern category
	 *
	 * @var string
	 */
	const PATTERN_CATEGORY = 'hiring-hub-patterns';

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register block pattern category.
		add_action( 'init', [ $this, 'register_block_pattern_category' ] );

		// Register block patterns.
		add_action( 'init', [ $this, 'register_block_patterns' ] );
	}

	/**
	 * Register block pattern category
	 *
	 * @return void
	 */
	public function register_block_pattern_category(): void {
		register_block_pattern_category(
			self::PATTERN_CATEGORY,
			[ 'label' => __( 'Hiring Hub', 'hiring-hub' ) ],
		);
	}

	/**
	 * Register block patterns
	 *
	 * @return void
	 */
	public function register_block_patterns(): void {
		$patterns = [
			'hiring-hub/archives'             => [
				'title'         => __( 'Hiring Hub: Archives', 'hiring-hub' ),
				'categories'    => [ self::PATTERN_CATEGORY ],
				'templateTypes' => [ 'archive' ],
				'filePath'      => $this->container->get_path_to( 'block-patterns/pattern-hiring-hub-archives.php' ),
			],
			'hiring-hub/search-and-filtering' => [
				'title'         => __( 'Hiring Hub: Search & Filtering', 'hiring-hub' ),
				'categories'    => [ self::PATTERN_CATEGORY ],
				'templateTypes' => [ 'archive' ],
				'filePath'      => $this->container->get_path_to( 'block-patterns/pattern-hiring-hub-search-and-filtering.php' ),
			],
		];

		foreach ( $patterns as $pattern_key => $pattern ) {
			register_block_pattern( $pattern_key, $pattern );
		}
	}
}
