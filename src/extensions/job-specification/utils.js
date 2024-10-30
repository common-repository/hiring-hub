/**
 * External dependencies
 */
import { getStore, fromStore } from '@teydeastudio/utils/src/data-store.js';
import { useEditedPostMeta } from '@teydeastudio/utils/src/use-edited-post-meta.js';

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';

/**
 * Get the data store
 */
const store = getStore( 'hiring-hub' );

/**
 * "Job specification" data reducer
 *
 * @param {Object} settings Settings object.
 * @param {Object} postMeta Post meta object.
 *
 * @return {Object} Reduced data object.
 */
const dataReducer = ( settings, postMeta ) => Object.values( settings.data?.jobSpecification ?? {} ).map( ( field ) => {
	const metaKey = `hiring_hub__job_specification__${ field.key }`;
	const value = postMeta?.[ metaKey ] ?? field.defaultValue;
	const result = {
		field,
		metaKey,
		value,
	};

	if ( 'array_of_strings' === field.type ) {
		result.allowedChoices = 'unlimited' === field.allowedChoices
			? 'unlimited'
			: Number.parseInt( field.allowedChoices, 10 );
	}

	return result;
} );

/**
 * Get the "Job specification" data
 *
 * @return {Object} "Job specification" data.
 */
export const useJobSpecification = () => {
	// Data key.
	const DATA_KEY = 'jobSpecification';

	// Fetch the data from store and keep it as state.
	const [ data, setData ] = useState( fromStore( store, DATA_KEY ) );

	// Get the current value of the post meta field.
	const postMeta = useEditedPostMeta();

	// Fetch plugin settings from store.
	const settings = fromStore( store, 'settings' );

	/**
	 * Update the data store as soon as the post
	 * meta or settings changes
	 */
	useEffect( () => {
		if ( settings.hasFinishedResolution ) {
			setData( dataReducer( settings, postMeta ) );
		}
	}, [ postMeta, settings ] );

	/**
	 * Dispatch updated data back to the data store
	 * as soon as it changes
	 */
	useEffect( () => {
		dispatch( store ).setData( { key: DATA_KEY, value: data } );
	}, [ data ] );

	// Return resolved data.
	return data;
};
