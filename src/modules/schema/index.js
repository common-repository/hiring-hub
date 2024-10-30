/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TabSchema } from './component-tab-schema.js';

/**
 * Filter the settings page tabs configuration to include
 * the Schema settings
 */
addFilter(
	'hiring_hub__settings_page_tabs',
	'teydeastudio/hiring-hub/schema',

	/**
	 * Filter the settings page tabs configuration
	 *
	 * @param {Array}    tabsConfig        Settings page tabs configuration.
	 * @param {Object}   dataState         Current data state.
	 * @param {Function} dispatchDataState Data state dispatcher.
	 *
	 * @return {Array} Updated settings page tabs configuration.
	 */
	( tabsConfig, dataState, dispatchDataState ) => {
		/**
		 * Add custom tab configuration to
		 * the filtered array
		 */
		tabsConfig.push( {
			name: 'schema',
			title: __( 'Schema.org', 'hiring-hub' ),
			component: (
				<TabSchema
					settings={ dataState.settings }
					setSettings={ ( settings ) => {
						dispatchDataState( {
							type: 'settingsChanged',
							settings,
						} );
					} }
				/>
			),
		} );

		return tabsConfig;
	}
);
