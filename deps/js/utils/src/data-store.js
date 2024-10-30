/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createReduxStore, register, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ucfirst } from './ucfirst.js';
import { toCamelCase } from './to-camel-case.js';

/**
 * Get the data store
 *
 * Registers a new store if it has not been yet registered; otherwise,
 * returns the existing store's object.
 *
 * @param {string} slug Store slug.
 *
 * @return {Object} Data store.
 */
export const getStore = ( slug ) => {
	// Get the plugin key.
	const pluginKey = toCamelCase( slug );

	// Check if a given store is already registered.
	if ( 'undefined' === typeof window?.teydeaStudio?.[ pluginKey ]?.dataStore ) {
		// Keep the data store in global object.
		window.teydeaStudio = window.teydeaStudio || {};
		window.teydeaStudio[ pluginKey ] = window.teydeaStudio[ pluginKey ] || {};

		/**
		 * Default state
		 */
		const DEFAULT_STATE = {
			data: {},
			settings: {},
		};

		/**
		 * Store actions
		 */
		const actions = {
			/**
			 * Set specific data
			 *
			 * @param {Object} data Data to set.
			 *
			 * @return {Object} Action config.
			 */
			setData( data ) {
				return {
					type: 'SET_DATA',
					data,
				};
			},

			/**
			 * Set settings
			 *
			 * @param {Object} data Settings data.
			 *
			 * @return {Object} Action config.
			 */
			setSettings( data ) {
				return {
					type: 'SET_SETTINGS',
					data,
				};
			},

			/**
			 * Fetch data from API
			 *
			 * @param {string} path API endpoint path.
			 *
			 * @return {Object} Action config.
			 */
			fetchFromAPI( path ) {
				return {
					type: 'FETCH_FROM_API',
					path,
				};
			},
		};

		/**
		 * Store
		 */
		window.teydeaStudio[ pluginKey ].dataStore = createReduxStore(
			pluginKey,
			{
				/**
				 * Reducer
				 *
				 * @param {Object} state  Store's state.
				 * @param {Object} action Action data.
				 *
				 * @return {Object} Updated state.
				 */
				reducer( state = DEFAULT_STATE, action ) {
					switch ( action.type ) {
						case 'SET_DATA':
							return {
								...state,
								data: {
									...state.data,
									[ action.data.key ]: action.data.value,
								},
							};
						case 'SET_SETTINGS':
							return {
								...state,
								settings: action.data,
							};
					}

					return state;
				},

				// Actions.
				actions,

				// Initial state.
				initialState: DEFAULT_STATE,

				// Selectors.
				selectors: {
					/**
					 * Get specific data
					 *
					 * @param {Object} state Store's state.
					 * @param {string} key   Data key.
					 *
					 * @return {*} Data value; undefined if unknown.
					 */
					getData( state, key ) {
						return state.data?.[ key ];
					},

					/**
					 * Get settings
					 *
					 * @param {Object} state Store's state.
					 *
					 * @return {Object} Settings data.
					 */
					getSettings( state ) {
						return state.settings;
					},
				},

				// Controls.
				controls: {
					/**
					 * Fetch from API
					 *
					 * @param {Object} action Action data.
					 *
					 * @return {Object} Fetch result.
					 */
					FETCH_FROM_API( action ) {
						return apiFetch( { path: action.path } );
					},
				},

				// Resolvers.
				resolvers: {
					/**
					 * Get settings
					 *
					 * @yield {Object} Action config.
					 *
					 * @return {Object} Action config.
					 */
					*getSettings() {
						const path = `/${ slug }/v1/settings`;
						const data = yield actions.fetchFromAPI( path );

						return actions.setSettings( data );
					},
				},
			},
		);

		// Register the store.
		register( window.teydeaStudio[ pluginKey ].dataStore );
	}

	// Return store object for further use.
	return window.teydeaStudio[ pluginKey ].dataStore;
};

/**
 * Utility function to simplify getting the specific data from store
 *
 * @param {Object} store Store to get the data from.
 * @param {string} field Field key to get.
 *
 * @return {Object} Result data.
 */
export const fromStore = ( store, field ) => useSelect( ( select ) => {
	if ( 'settings' === field ) {
		const method = `get${ ucfirst( field ) }`;

		return {
			data: select( store )[ method ](),
			hasStartedResolution: select( store ).hasStartedResolution( method ),
			hasFinishedResolution: select( store ).hasFinishedResolution( method ),
			isResolving: select( store ).isResolving( method ),
		};
	}

	return select( store ).getData( field );
} );
