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
 * Data reducer
 *
 * @param {Object} postMeta Post meta object.
 *
 * @return {Object} Reduced data object.
 */
const dataReducer = ( postMeta ) => {
	const metaKey = 'hiring_hub__apply_on';

	return {
		metaKey,
		value: postMeta?.[ metaKey ] ?? '',
	};
};

/**
 * Get the "Apply on link" data
 *
 * @return {Object} "Apply on link" data.
 */
export const useApplyOnLink = () => {
	// Data key.
	const DATA_KEY = 'applyOnLink';

	// Fetch the data from store and keep it as state.
	const [ data, setData ] = useState( fromStore( store, DATA_KEY ) );

	// Get the current value of the post meta field.
	const postMeta = useEditedPostMeta();

	/**
	 * Update the data store as soon as the post
	 * meta or settings changes
	 */
	useEffect( () => {
		setData( dataReducer( postMeta ) );
	}, [ postMeta ] );

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
