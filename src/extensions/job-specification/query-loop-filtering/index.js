/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useJobSpecification } from '../utils.js';

/**
 * Filter the list of available filters in the
 * "Query loop filtering: Filter" block
 */
addFilter(
	'hiring_hub__block_query_loop_filtering_filter__available_filters',
	'teydeastudio/hiring-hub/job-specification-query-loop-filtering',

	/**
	 * Filter the list of available filters
	 *
	 * @param {Array} availableFilters Array of available filters.
	 *
	 * @return {Array} Updated array of available filters.
	 */
	( availableFilters ) => {
		// Store the additional filters discovered.
		const [ additionalFilters, setAdditionalFilters ] = useState( [] );

		// Collect the necessary data.
		const jobSpecification = useJobSpecification();

		/**
		 * As soon as the job specification is available
		 * or has changed, update the list of additional
		 * filters discovered
		 */
		useEffect( () => {
			if ( 'undefined' !== typeof jobSpecification ) {
				let hasCharacteristics = false;
				let filters = [];

				for ( const item of jobSpecification ) {
					if ( ! hasCharacteristics && 'boolean' === item.field.type ) {
						hasCharacteristics = true;
					}

					filters.push( {
						label: item.field.name,
						value: item.field.key,
					} );
				}

				if ( hasCharacteristics ) {
					filters = [ {
						label: __( 'Job characteristics', 'hiring-hub' ),
						value: 'job-specification-characteristics',
					}, ...filters ];
				}

				setAdditionalFilters( filters );
			}
		}, [ jobSpecification ] );

		return [
			...availableFilters,
			...additionalFilters,
		];
	}
);
