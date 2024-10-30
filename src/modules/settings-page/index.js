/**
 * External dependencies
 */
import { PromotedPluginsPanel } from '@teydeastudio/components/src/promoted-plugins-panel/index.js';
import { SettingsTabs } from '@teydeastudio/components/src/settings-tabs/index.js';
import { render } from '@teydeastudio/utils/src/render.js';

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TabGeneral } from './component-tab-general.js';

/**
 * Filter the settings page tabs configuration to include
 * the General settings
 */
addFilter(
	'hiring_hub__settings_page_tabs',
	'teydeastudio/hiring-hub/settings-page',

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
			name: 'general',
			title: __( 'General', 'hiring-hub' ),
			component: (
				<TabGeneral
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

/**
 * Render the "promoted plugins" panel
 */
addFilter(
	'hiring_hub__promoted_plugins_panel',
	'teydeastudio/hiring-hub/settings-page',

	/**
	 * Render the "promoted plugins" panel
	 *
	 * @return {JSX} Updated "promoted plugins" panel.
	 */
	() => (
		<PromotedPluginsPanel
			plugins={ [
				{
					url: 'https://teydeastudio.com/products/password-policy-and-complexity-requirements/?utm_source=Hiring+Hub&utm_medium=Plugin&utm_campaign=Plugin+cross-reference&utm_content=Settings+sidebar',
					name: __( 'Password Policy & Complexity Requirements', 'hiring-hub' ),
					description: __( 'Set up the password policy and complexity requirements for the users of your WordPress website.', 'hiring-hub' ),
				},
				{
					url: 'https://teydeastudio.com/products/password-reset-enforcement/?utm_source=Hiring+Hub&utm_medium=Plugin&utm_campaign=Plugin+cross-reference&utm_content=Settings+sidebar',
					name: __( 'Password Reset Enforcement', 'hiring-hub' ),
					description: __( 'Force users to reset their WordPress passwords. Execute for all users at once, by role, or only for specific users.', 'hiring-hub' ),
				},
			] }
		/>
	),
);

/**
 * Render the settings form
 */
render(
	<SettingsTabs
		plugin="hiringHub"
	/>,
	document.querySelector( 'div#hiring-hub-settings-page' ),
);
