<?php
/**
 * The "Job Specification" extension query loop filtering customizations
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Job_Specification;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Settings;
use WP_Error;
use WP_Query;

/**
 * The "Query_Loop_Filtering" class
 *
 * @phpstan-import-type Type_Query_Loop_Filtering_Filters from \Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering\Extension
 * @phpstan-import-type Type_Query_Loop_Filtering_Selections from \Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering\Extension
 * @phpstan-import-type Type_Job_Specification_Items from Extension
 */
final class Query_Loop_Filtering extends Utils\Module {
	/**
	 * Cache group
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'job_specification';

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Engueue block editor assets.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );

		// Adjust the available filters data.
		add_filter( 'hiring_hub__query_loop_filtering__available_filters', [ $this, 'filter_available_filters' ], 15 );

		// Maybe filter the query.
		add_filter( 'hiring_hub__query_loop_filtering__query', [ $this, 'maybe_filter_query' ], 10, 3 );

		// Flush the cache on plugin settings update.
		add_action( 'hiring_hub__settings_updated', [ $this, 'flush_cache' ] );
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'extensions-job-specification-query-loop-filtering' ] );
		$asset->enqueue_script();
	}

	/**
	 * Adjust the available filters
	 *
	 * @param Type_Query_Loop_Filtering_Filters $available_filters Data for all available filters.
	 *
	 * @return Type_Query_Loop_Filtering_Filters Updated data.
	 */
	public function filter_available_filters( array $available_filters ): array {
		/** @var Utils\Cache $cache */
		$cache = $this->container->get_instance_of( 'cache' );

		$cache->set_group( self::CACHE_GROUP );
		$cache->set_key( 'filters_list' );

		/** @var false|Type_Query_Loop_Filtering_Filters $filters */
		$filters = $cache->read();

		if ( false === $filters ) {
			$filters = [];

			/** @var Settings $settings */
			$settings     = $this->container->get_instance_of( 'settings' );
			$fields_group = $settings->get_fields_group( Extension::SETTINGS_KEY );

			/** @var Type_Job_Specification_Items|WP_Error $items */
			$items = null !== $fields_group ? $fields_group->get_value() : [];

			if ( is_array( $items ) ) {
				// Collect the job characteristics.
				$characteristics = [];

				// Go through each job specification item.
				foreach ( $items as $item ) {
					if ( in_array( $item['type'], [ 'date', 'integer', 'salary', 'text', 'url' ], true ) ) {
						// Items of this type should not be a part of the query loop filtering.
						continue;
					} elseif ( 'boolean' === $item['type'] ) {
						$characteristics[] = [
							'key'   => $item['key'],
							'label' => $item['name'],
						];

						$choices = [
							[
								'key'   => 'yes',
								'label' => __( 'Yes', 'hiring-hub' ),
							],
							[
								'key'   => 'no',
								'label' => __( 'No', 'hiring-hub' ),
							],
						];
					} elseif ( 'array_of_strings' === $item['type'] ) {
						$choices = array_map(
							/**
							 * Map the possible values into the requred
							 * array shape
							 *
							 * @param string $possible_value Possible value of the choice.
							 *
							 * @return array{key:string,label:string} Possible value mapped to the expected array shape.
							 */
							function ( string $possible_value ): array {
								return [
									'key'   => $possible_value,
									'label' => $possible_value,
								];
							},
							$item['possible_values'],
						);
					}

					$filters[ $item['key'] ] = [
						'extension' => 'job-specification',
						'label'     => $item['name'],
						'key'       => $item['key'],
						'type'      => 'boolean' === $item['type'] ? 'radio' : 'checkbox',
						'choices'   => $choices,
					];
				}

				// Do we have any characteristics defined?
				if ( ! empty( $characteristics ) ) {
					$filters['job-specification-characteristics'] = [
						'extension' => 'job-specification',
						'label'     => __( 'Job characteristics', 'hiring-hub' ),
						'key'       => 'job-specification-characteristics',
						'type'      => 'checkbox',
						'choices'   => $characteristics,
					];
				}

				// Cache results.
				$cache->write( $filters );
			}
		}

		// Return updated filters.
		return array_merge( $available_filters, $filters );
	}

	/**
	 * Construct the meta key
	 *
	 * @param string $key Key of the field.
	 *
	 * @return string Meta key.
	 */
	protected function get_meta_key( string $key ): string {
		return sprintf( 'hiring_hub__%1$s__%2$s', Extension::SETTINGS_KEY, $key );
	}

	/**
	 * Maybe filter the query
	 *
	 * @param WP_Query                             $query      Current query.
	 * @param Type_Query_Loop_Filtering_Selections $selections Filter selections data.
	 * @param Type_Query_Loop_Filtering_Filters    $filters    All available filters.
	 *
	 * @return WP_Query Updated query instance.
	 */
	public function maybe_filter_query( WP_Query $query, array $selections, array $filters ): WP_Query {
		$new_meta_query = [];
		$meta_query     = $query->get( 'meta_query' );

		if ( ! is_array( $meta_query ) || empty( $meta_query ) ) {
			$meta_query = [];
		}

		/**
		 * Loop through all defined filters to only process the
		 * ones defined by this extension
		 */
		foreach ( $filters as $filter ) {
			if ( 'job-specification' !== $filter['extension'] ) {
				continue;
			}

			$key = $filter['key'];

			if ( isset( $selections[ $key ] ) && ! empty( $selections[ $key ]['keys'] ) ) {
				/**
				 * Special case: job characteristics
				 */
				if ( 'job-specification-characteristics' === $key ) {
					foreach ( $selections[ $key ]['keys'] as $selection_key ) {
						$new_meta_query[] = [
							'key'   => $this->get_meta_key( $selection_key ),
							'value' => '1',
						];
					}

					continue;
				}

				switch ( $filter['type'] ) {
					case 'checkbox':
						$filter_meta_query = [];

						foreach ( $selections[ $key ]['keys'] as $selection_key ) {
							$filter_meta_query[] = [
								'key'     => $this->get_meta_key( $key ),
								'value'   => sprintf( '"%s"', $selection_key ),
								'compare' => 'LIKE',
							];
						}

						if ( 1 === count( $filter_meta_query ) ) {
							$filter_meta_query = $filter_meta_query[0];
						} else {
							$filter_meta_query = [
								'relation' => 'OR',
								...$filter_meta_query,
							];
						}

						$new_meta_query[] = $filter_meta_query;
						unset( $filter_meta_query );

						break;
					case 'radio':
						$new_meta_query[] = [
							'key'   => $this->get_meta_key( $key ),
							'value' => 'yes' === $selections[ $key ]['keys'][0] ? '1' : '',
						];

						break;
				}
			}
		}

		if ( ! empty( $new_meta_query ) ) {
			if ( 1 < count( $new_meta_query ) ) {
				$new_meta_query = [
					'relation' => 'AND',
					...$new_meta_query,
				];
			}

			$meta_query['hiring_hub__job_specification__query_loop_filtering'] = $new_meta_query;
			unset( $new_meta_query );
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	/**
	 * Flush the cache on plugin settings update
	 *
	 * @return void
	 */
	public function flush_cache(): void {
		/** @var Utils\Cache $cache */
		$cache = $this->container->get_instance_of( 'cache' );

		$cache->set_group( self::CACHE_GROUP );
		$cache->set_key( 'filters_list' );
		$cache->delete();
	}
}
