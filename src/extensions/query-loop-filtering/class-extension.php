<?php
/**
 * Query Loop Filtering
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering;

use DateTime;
use DateTimeZone;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;
use WP_Query;

/**
 * The "Module_Query_Loop_Filtering" class
 *
 * @phpstan-type Type_Query_Loop_Filtering_Filter array{extension:string,label:string,key:string,type:string,choices:array<int,array{key:string,label:string}>}
 * @phpstan-type Type_Query_Loop_Filtering_Filters array<string,Type_Query_Loop_Filtering_Filter>
 * @phpstan-type Type_Query_Loop_Filtering_Selection array{key:string,name:string,value:string,summary:string,keys:string[]}
 * @phpstan-type Type_Query_Loop_Filtering_Selections array<string,Type_Query_Loop_Filtering_Selection>
 */
final class Extension extends Utils\Module {
	/**
	 * Query argument's key; the "qlff" stands for "Query Loop Filtering Filter"
	 *
	 * @var string
	 */
	const QUERY_ARG_KEY = 'hiring-hub-qlff';

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
	 * The Block_Query_Loop_Filtering_Filter block instance
	 *
	 * @var Block_Query_Loop_Filtering_Filter
	 */
	protected $block_query_loop_filtering_filter;

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the "Query_Loop_Filtering_Filter" block.
		$this->block_query_loop_filtering_filter = new Block_Query_Loop_Filtering_Filter( $this->container );
		$this->block_query_loop_filtering_filter->register();

		// Register the blocks.
		( new Block_Query_Loop_Filtering_Button( $this->container ) )->register();
		( new Block_Query_Loop_Filtering_Container( $this->container ) )->register();
		( new Block_Query_Loop_Filtering_Search_Field( $this->container ) )->register();

		// Collect required data and pass it to the blocks.
		add_action( 'init', [ $this, 'collect_data_and_update_blocks' ] );

		// Maybe adjust the main query.
		add_action( 'pre_get_posts', [ $this, 'maybe_adjust_main_query' ] );

		// Maybe filter the query.
		add_filter( 'hiring_hub__query_loop_filtering__query', [ $this, 'maybe_filter_query' ], 10, 2 );
	}

	/**
	 * Collect required data and pass it to the blocks
	 *
	 * @return void
	 */
	public function collect_data_and_update_blocks(): void {
		// Collect all available filters.
		$this->filters = $this->collect_available_filters();

		// Collect the filter selections data.
		$this->selections = $this->collect_filter_selections();

		/**
		 * Pass collected data to blocks
		 */
		$this->block_query_loop_filtering_filter->set_filters( $this->filters );
		$this->block_query_loop_filtering_filter->set_filter_selections_data( $this->selections );
	}

	/**
	 * Collect all available filters
	 *
	 * @return Type_Query_Loop_Filtering_Filters Data of all available filters.
	 */
	protected function collect_available_filters(): array {
		$filters = [
			'published-within' => [
				'extension' => 'query-loop-filtering',
				'label'     => __( 'Published within', 'hiring-hub' ),
				'key'       => 'published-within',
				'type'      => 'radio',
				'choices'   => [
					[
						'key'   => 'last-24h',
						'label' => __( 'Last 24 hours', 'hiring-hub' ),
					],
					[
						'key'   => 'last-3d',
						'label' => __( 'Last 3 days', 'hiring-hub' ),
					],
					[
						'key'   => 'last-7d',
						'label' => __( 'Last 7 days', 'hiring-hub' ),
					],
					[
						'key'   => 'last-14d',
						'label' => __( 'Last 14 days', 'hiring-hub' ),
					],
				],
			],
		];

		/**
		 * Allow other plugins and modules to adjust the available filters data
		 *
		 * @param Type_Query_Loop_Filtering_Filters $filters Data of all available filters.
		 */
		return apply_filters( 'hiring_hub__query_loop_filtering__available_filters', $filters );
	}

	/**
	 * Collect filter selections
	 * - read the GET query args to read what filters should apply
	 *
	 * @return Type_Query_Loop_Filtering_Selections Filter selections data.
	 */
	protected function collect_filter_selections(): array {
		$results = [];

		foreach ( $this->filters ?? [] as $filter_key => $filter ) {
			$result = [
				'key'     => $filter_key,
				'name'    => sprintf( '%1$s[%2$s]', self::QUERY_ARG_KEY, $filter_key ),
				'value'   => '',
				'summary' => '',
				'keys'    => [],
			];

			if ( isset( $_GET[ self::QUERY_ARG_KEY ][ $filter_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				/** @var string[] $value */
				$value = Utils\JSON::decode( sanitize_text_field( wp_unslash( $_GET[ self::QUERY_ARG_KEY ][ $filter_key ] ) ), [] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Get the valid selection keys.
				$result['keys'] = array_values(
					array_filter(
						$value,

						/**
						 * Ensure we only include keys that were defined
						 * (user input from $_GET is validated here)
						 *
						 * @param string $key Key to validate.
						 *
						 * @return bool Whether the key is valid or not.
						 */
						function ( $key ) use ( $filter ): bool {
							return in_array( $key, array_column( $filter['choices'], 'key' ), true );
						}
					),
				);

				// For "radio" fields, only one value is allowed - use the first one on the list.
				if ( 'radio' === $filter['type'] && 1 < count( $result['keys'] ) ) {
					$result['keys'] = [ $result['keys'][0] ];
				}

				// Update the selection value.
				$result['value'] = Utils\JSON::encode( $result['keys'] );

				/**
				 * Construct the summary text
				 */
				$summary = [];

				foreach ( $filter['choices'] as $choice ) {
					if ( in_array( $choice['key'], $result['keys'], true ) ) {
						$summary[] = $choice['label'];
					}
				}

				$result['summary'] = implode( ', ', $summary );
				unset( $summary );
			}

			$results[ $filter_key ] = $result;
		}

		return $results;
	}

	/**
	 * Maybe adjust the main query
	 *
	 * @param WP_Query $query Current query.
	 *
	 * @return void
	 */
	public function maybe_adjust_main_query( WP_Query $query ): void {
		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( $job->get_post_type() ) && null !== $this->selections && null !== $this->filters ) {
			/**
			 * Allow other plugins and modules to adjust the query
			 *
			 * @param WP_Query                             $query      Current query.
			 * @param Type_Query_Loop_Filtering_Selections $selections Filter selections data.
			 * @param Type_Query_Loop_Filtering_Filters    $filters    All available filters.
			 */
			$query = apply_filters( 'hiring_hub__query_loop_filtering__query', $query, $this->selections, $this->filters );
		}
	}

	/**
	 * Maybe filter the query
	 *
	 * @param WP_Query                             $query      Current query.
	 * @param Type_Query_Loop_Filtering_Selections $selections Filter selections data.
	 *
	 * @return WP_Query Updated query instance.
	 */
	public function maybe_filter_query( WP_Query $query, array $selections ): WP_Query {
		/**
		 * Update the query with by-date filtering
		 */
		if ( isset( $selections['published-within'] ) && ! empty( $selections['published-within']['keys'] ) ) {
			$modifier = null;

			switch ( $selections['published-within']['keys'][0] ?? null ) {
				case 'last-24h':
					$modifier = '-24 hours';
					break;
				case 'last-3d':
					$modifier = '-3 days';
					break;
				case 'last-7d':
					$modifier = '-7 days';
					break;
				case 'last-14d':
					$modifier = '-14 days';
					break;
			}

			$date = new DateTime( 'now', new DateTimeZone( '+00:00' ) );
			$date = is_string( $modifier ) ? $date->modify( $modifier ) : $date;

			$query->set(
				'date_query',
				[
					[
						'column'    => 'post_date_gmt',
						'after'     => $date->format( 'Y-m-d H:i:s' ),
						'inclusive' => true,
					],
				],
			);
		}

		/**
		 * Update the query with search phrase
		 */
		if ( isset( $_GET[ self::QUERY_ARG_KEY ]['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$phrase = Utils\Strings::trim( sanitize_text_field( wp_unslash( $_GET[ self::QUERY_ARG_KEY ]['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $phrase ) ) {
				$query->set( 's', $phrase );
			}
		}

		return $query;
	}
}
