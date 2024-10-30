<?php
/**
 * The "Query loop filtering: Filter" block companion class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use WP_Block;

/**
 * The "Block_Query_Loop_Filtering_Filter" class
 *
 * @phpstan-import-type Type_Query_Loop_Filtering_Filters from Extension
 * @phpstan-import-type Type_Query_Loop_Filtering_Filter from Extension
 * @phpstan-import-type Type_Query_Loop_Filtering_Selection from Extension
 * @phpstan-import-type Type_Query_Loop_Filtering_Selections from Extension
 */
final class Block_Query_Loop_Filtering_Filter extends Utils\Module {
	/**
	 * Store the data for all available filters
	 *
	 * @var ?Type_Query_Loop_Filtering_Filters
	 */
	protected ?array $filters = null;

	/**
	 * Store the filter selections data
	 *
	 * @var ?Type_Query_Loop_Filtering_Selections
	 */
	protected ?array $selections = null;

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
	 * Set available filters data
	 *
	 * @param Type_Query_Loop_Filtering_Filters $filters Available filters data.
	 *
	 * @return void
	 */
	public function set_filters( array $filters ): void {
		$this->filters = $filters;
	}

	/**
	 * Set the filter selections data
	 *
	 * @param Type_Query_Loop_Filtering_Selections $selections Filter selections data.
	 *
	 * @return void
	 */
	public function set_filter_selections_data( array $selections ): void {
		$this->selections = $selections;
	}

	/**
	 * Register block
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type(
			$this->container->get_block_path( 'extensions-query-loop-filtering-block-query-loop-filtering-filter' ),
			[ 'render_callback' => [ $this, 'render_block' ] ],
		);
	}

	/**
	 * Get the filter data
	 *
	 * @param string $key Filter key.
	 *
	 * @return ?Type_Query_Loop_Filtering_Filter Filter data; null if the filter is unknown.
	 */
	protected function get_filter_data( string $key ): ?array {
		return $this->filters[ $key ] ?? null;
	}

	/**
	 * Get the selection data
	 *
	 * @param string                           $key    Filter key.
	 * @param Type_Query_Loop_Filtering_Filter $filter Filter data.
	 *
	 * @return ?Type_Query_Loop_Filtering_Selection Selection data if found, null otherwise.
	 */
	protected function get_selection_data( string $key, array $filter ): ?array {
		return $this->selections[ $key ] ?? null;
	}

	/**
	 * Get the selection labels mapping
	 *
	 * @param Type_Query_Loop_Filtering_Filter $filter Filter data.
	 *
	 * @return array<string,string> Selection labels mapping.
	 */
	protected function get_selection_labels_mapping( array $filter ): array {
		$selection_labels = [];

		foreach ( $filter['choices'] as $choice ) {
			$selection_labels[ $choice['key'] ] = $choice['label'];
		}

		return $selection_labels;
	}

	/**
	 * Render block
	 *
	 * @param array{filterToDisplay?:string,renderAs?:string,className?:string} $block_attributes Block attributes.
	 * @param string                                                            $block_content    Block content.
	 * @param WP_Block                                                          $instance         Block instance.
	 *
	 * @return string Block markup.
	 */
	public function render_block( array $block_attributes, string $block_content, WP_Block $instance ): string {
		/** @var array{filterToDisplay:string,renderAs:string,className:string} $block_attributes */
		$block_attributes = wp_parse_args(
			$block_attributes,
			[
				'filterToDisplay' => 'published-within',
				'renderAs'        => 'dropdown',
				'className'       => '',
			],
		);

		// Get the filter data.
		$filter = $this->get_filter_data( $block_attributes['filterToDisplay'] );

		if ( null === $filter ) {
			return '';
		}

		// Get the current selection data.
		$selection = $this->get_selection_data( $block_attributes['filterToDisplay'], $filter );

		if ( null === $selection ) {
			return '';
		}

		// Start the output buffering.
		ob_start();

		switch ( $block_attributes['renderAs'] ) {
			// Render the "dropdown" markup.
			case 'dropdown':
				$this->render_markup_dropdown( $filter, $selection, $block_attributes['className'] );
				break;

			// Render the "inline-column" markup.
			case 'inline-column':
				$this->render_markup_inline( $filter, $selection, 'column', $block_attributes['className'] );
				break;

			// Render the "inline-row" markup.
			case 'inline-row':
				$this->render_markup_inline( $filter, $selection, 'row', $block_attributes['className'] );
				break;
		}

		// Get the contents of the active output buffer and turn it off.
		$output = ob_get_clean();

		// Return the file contents.
		return false === $output ? '' : $output;
	}

	/**
	 * Render the markup for "dropdown" style
	 *
	 * @param Type_Query_Loop_Filtering_Filter                             $filter     Filter data.
	 * @param array{name:string,value:string,summary:string,keys:string[]} $selection  Selection data.
	 * @param string                                                       $class_name Additional custom CSS class name to append on main element.
	 *
	 * @return void
	 */
	protected function render_markup_dropdown( array $filter, array $selection, string $class_name ): void {
		$class_names = [
			'wp-block-hiring-hub-query-loop-filtering-filter',
			'wp-block-hiring-hub-query-loop-filtering-filter--dropdown',
			$class_name,
		];

		?>
		<div class="<?php echo esc_attr( implode( ' ', $class_names ) ); ?>" data-type="<?php echo esc_attr( $filter['type'] ); ?>" data-selection-labels-mapping="<?php echo esc_attr( Utils\JSON::encode( $this->get_selection_labels_mapping( $filter ) ) ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $selection['name'] ); ?>" value="<?php echo esc_attr( $selection['value'] ); ?>" />
			<button type="button" aria-label="<?php echo esc_attr( $filter['label'] ); ?>" class="wp-block-hiring-hub-query-loop-filtering-filter__button">
				<span>
					<?php

					// Output the filter's label and summary.
					printf(
						'%1$s<strong class="wp-block-hiring-hub-query-loop-filtering-filter__selection-summary">%2$s</strong>',
						esc_html( $filter['label'] ),
						esc_html( $selection['summary'] ),
					);

					?>
				</span>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" focusable="false">
					<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"></path>
				</svg>
			</button>
			<div class="wp-block-hiring-hub-query-loop-filtering-filter__dropdown">
				<header>
					<span><?php echo esc_html( $filter['label'] ); ?></span>
					<button type="button" class="wp-block-hiring-hub-query-loop-filtering-filter__clear-button">
						<?php esc_html_e( 'Clear', 'hiring-hub' ); ?>
					</button>
				</header>
				<ul>
					<?php

					foreach ( $filter['choices'] as $choice ) {
						$item_classes = [ 'wp-block-hiring-hub-query-loop-filtering-filter__choice' ];
						$icon_classes = [ sprintf( 'wp-block-hiring-hub-query-loop-filtering-filter__%s', esc_attr( $filter['type'] ) ) ];

						if ( in_array( $choice['key'], $selection['keys'], true ) ) {
							$item_classes[] = 'wp-block-hiring-hub-query-loop-filtering-filter__choice--selected';
							$icon_classes[] = sprintf( 'wp-block-hiring-hub-query-loop-filtering-filter__%s--selected', esc_attr( $filter['type'] ) );
						}

						?>
						<li aria-label="<?php echo esc_attr( $choice['label'] ); ?>" class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
							<button type="button" aria-label="<?php echo esc_attr( $choice['label'] ); ?>" data-value="<?php echo esc_attr( $choice['key'] ); ?>">
								<span class="<?php echo esc_attr( implode( ' ', $icon_classes ) ); ?>">
									<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
										<path d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z" />
									</svg>
								</span>
								<span class="wp-block-hiring-hub-query-loop-filtering-filter__label">
									<?php echo esc_html( $choice['label'] ); ?>
								</span>
							</button>
						</li>
						<?php
					}

					?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the markup for "inline" style
	 *
	 * @param Type_Query_Loop_Filtering_Filter                             $filter     Filter data.
	 * @param array{name:string,value:string,summary:string,keys:string[]} $selection  Selection data.
	 * @param string                                                       $variant    Style variant; either "row" or "column".
	 * @param string                                                       $class_name Additional custom CSS class name to append on main element.
	 *
	 * @return void
	 */
	protected function render_markup_inline( array $filter, array $selection, string $variant, string $class_name ): void {
		$class_names = [
			'wp-block-hiring-hub-query-loop-filtering-filter',
			sprintf( 'wp-block-hiring-hub-query-loop-filtering-filter--inline-%s', esc_attr( $variant ) ),
			$class_name,
		];

		?>
		<div class="<?php echo esc_attr( implode( ' ', $class_names ) ); ?>" data-type="<?php echo esc_attr( $filter['type'] ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $selection['name'] ); ?>" value="<?php echo esc_attr( $selection['value'] ); ?>" />
			<strong class="wp-block-hiring-hub-query-loop-filtering-filter__label">
				<?php printf( '%s:', esc_html( $filter['label'] ) ); ?>
			</strong>
			<ul>
				<?php

				foreach ( $filter['choices'] as $choice ) {
					$item_classes = [ 'wp-block-hiring-hub-query-loop-filtering-filter__choice' ];
					$icon_classes = [ sprintf( 'wp-block-hiring-hub-query-loop-filtering-filter__%s', esc_attr( $filter['type'] ) ) ];

					if ( in_array( $choice['key'], $selection['keys'], true ) ) {
						$item_classes[] = 'wp-block-hiring-hub-query-loop-filtering-filter__choice--selected';
						$icon_classes[] = sprintf( 'wp-block-hiring-hub-query-loop-filtering-filter__%s--selected', esc_attr( $filter['type'] ) );
					}

					?>
					<li aria-label="<?php echo esc_attr( $choice['label'] ); ?>" class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
						<button type="button" aria-label="<?php echo esc_attr( $choice['label'] ); ?>" data-value="<?php echo esc_attr( $choice['key'] ); ?>">
							<span class="<?php echo esc_attr( implode( ' ', $icon_classes ) ); ?>">
								<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
									<path d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z" />
								</svg>
							</span>
							<span class="wp-block-hiring-hub-query-loop-filtering-filter__label">
								<?php echo esc_html( $choice['label'] ); ?>
							</span>
						</button>
					</li>
					<?php
				}

				?>
			</ul>
		</div>
		<?php
	}
}
