/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { Button, Notice, SnackbarList, TabPanel } from '@wordpress/components';
import { Fragment, useEffect, useReducer } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SettingsContainer } from '../settings-container/index.js';
import { SettingsSidebar } from '../settings-sidebar/index.js';
import { WaitingIndicator } from '../waiting-indicator/index.js';

/**
 * Import styles
 */
import './styles.scss';

/**
 * Data state reducer
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action object.
 *
 * @return {Object} Updated state object.
 */
const dataStateReducer = ( state, action ) => {
	switch ( action.type ) {
		/**
		 * Settings has been fetched from the REST API endpoint
		 */
		case 'fetchedSettings': {
			return {
				...state,
				settings: action.settings,
				hasFetchedSettings: true,
				isSettingsFetchFailed: false,
			};
		}

		/**
		 * Settings fetch failed
		 */
		case 'settingsFetchFailed': {
			return {
				...state,
				hasFetchedSettings: true,
				isSettingsFetchFailed: true,
			};
		}

		/**
		 * Save updated settings
		 */
		case 'saveSettings': {
			return {
				...state,
				isSavingSettings: true,
			};
		}

		/**
		 * Settings saved
		 */
		case 'settingsSaved': {
			return {
				...state,
				notices: [
					...state.notices,
					{
						id: `n:${ Date.now().toString() }`,
						status: 'success',
						content: __( 'Settings saved', 'hiring-hub' ),
						isDismissible: true,
						explicitDismiss: false,
					},
				],
				isSavingSettings: false,
			};
		}

		/**
		 * Settings save failed
		 */
		case 'settingsSaveFailed': {
			let content = __( 'Settings were not saved, something went wrong.', 'hiring-hub' );

			// Try to be more specific.
			if ( 'Invalid parameter(s): settings' === action.error.message && 'string' === typeof action.error?.data?.params?.settings ) {
				content = sprintf(
					// Translators: %s - error message.
					__( 'Settings were not saved due to validation error: %s Please update the invalid field value and try again.', 'hiring-hub' ),
					action.error.data.params.settings,
				);
			}

			return {
				...state,
				notices: [
					...state.notices,
					{
						id: `n:${ Date.now().toString() }`,
						status: 'error',
						content,
						isDismissible: true,
						explicitDismiss: false,
					},
				],
				isSavingSettings: false,
			};
		}

		/**
		 * Settings changed
		 */
		case 'settingsChanged': {
			/**
			 * Allow other plugins and modules to modify the settings
			 * object before it's updated state is saved
			 *
			 * @param {Object} settings Settings object.
			 */
			const updatedSettings = applyFilters( 'hiring_hub__pre_change_settings', action.settings );

			return {
				...state,
				settings: updatedSettings,
			};
		}

		/**
		 * Snackbar notice removed
		 */
		case 'noticeRemoved': {
			return {
				...state,
				notices: [
					...state.notices.filter( ( notice ) => notice.id !== action.noticeId ),
				],
			};
		}
	}

	return state;
};

/**
 * SettingsTabs component
 *
 * @param {Object} properties        Component properties object.
 * @param {string} properties.plugin Plugin key.
 *
 * @return {JSX} SettingsTabs component.
 */
export const SettingsTabs = ( { plugin } ) => {
	// Collect the necessary data.
	const { slug } = window.teydeaStudio[ plugin ].plugin;
	const { nonce } = window.teydeaStudio[ plugin ].settingsPage;

	// Data state.
	const [ dataState, dispatchDataState ] = useReducer(
		dataStateReducer,
		{
			notices: [],
			settings: {},
			hasFetchedSettings: false,
			isSettingsFetchFailed: false,
			isSavingSettings: false,
		},
	);

	/**
	 * Wrap JSX component into a settings container
	 *
	 * @param {JSX} inner Inner component.
	 *
	 * @return {JSX} Inner component wrapped with the settings container.
	 */
	const withWrapper = ( inner ) => (
		<SettingsContainer
			plugin={ plugin }
			actions={
				<Button
					variant="primary"
					disabled={ dataState.isSavingSettings || ! dataState.hasFetchedSettings || dataState.isSettingsFetchFailed }
					isBusy={ dataState.isSavingSettings }
					onClick={ () => {
						dispatchDataState( { type: 'saveSettings' } );
					} }
				>
					{
						dataState.isSavingSettings
							? __( 'Saving…', 'hiring-hub' )
							: __( 'Save all settings', 'hiring-hub' )
					}
				</Button>
			}
		>
			{ inner }
		</SettingsContainer>
	);

	/**
	 * Tabs configuration
	 *
	 * @param {Array}    tabsConfig        Settings page tabs configuration.
	 * @param {Object}   dataState         Current data state.
	 * @param {Function} dispatchDataState Data state dispatcher.
	 */
	const tabsConfig = applyFilters( 'hiring_hub__settings_page_tabs', [], dataState, dispatchDataState );

	/**
	 * Save updated settings
	 */
	useEffect( () => {
		if ( true === dataState.isSavingSettings ) {
			apiFetch( {
				path: `/${ slug }/v1/settings`,
				method: 'POST',
				data: {
					nonce,
					settings: dataState.settings,
				},
			} )
				.then( ( response ) => {
					dispatchDataState( { type: 'settingsSaved' } );
					return response;
				} )
				.catch( ( error ) => {
					console.error( error ); // eslint-disable-line no-console
					dispatchDataState( { type: 'settingsSaveFailed', error } );
				} );
		}
	}, [ dataState.isSavingSettings ] ); // eslint-disable-line react-hooks/exhaustive-deps

	/**
	 * Fetch saved settings on initial render
	 */
	useEffect( () => {
		apiFetch( {
			path: `/${ slug }/v1/settings`,
			method: 'GET',
		} )
			.then( ( settings ) => {
				dispatchDataState( { type: 'fetchedSettings', settings } );
				return settings;
			} )
			.catch( ( error ) => {
				console.error( error ); // eslint-disable-line no-console
				dispatchDataState( { type: 'settingsFetchFailed' } );
			} );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	/**
	 * Still fetching?
	 */
	if ( false === dataState.hasFetchedSettings ) {
		return withWrapper(
			<div
				className="tsc-settings-tabs"
			>
				<WaitingIndicator
					message={ __( 'Loading…', 'hiring-hub' ) }
				/>
			</div>
		);
	}

	/**
	 * Fetch failed?
	 */
	if ( true === dataState.isSettingsFetchFailed ) {
		return withWrapper(
			<Notice
				status="error"
				isDismissible={ false }
			>
				<p>{ __( 'Settings fetch failed.', 'hiring-hub' ) }</p>
				<p>{ __( 'Please try again; if the issue will be repeating, reach out to our support team.', 'hiring-hub' ) }</p>
			</Notice>
		);
	}

	/**
	 * Render component
	 */
	return withWrapper(
		<Fragment>
			<SnackbarList
				notices={ dataState.notices }

				/**
				 * Remove single notice
				 *
				 * @param {string} noticeId Notice ID.
				 */
				onRemove={ ( noticeId ) => {
					dispatchDataState( { type: 'noticeRemoved', noticeId } );
				} }
			/>
			<div
				className="tsc-settings-tabs"
			>
				<TabPanel
					tabs={ tabsConfig }
					className="tsc-settings-tabs__wrapper"
				>
					{
						/**
						 * Render single tab
						 *
						 * @param {Object} tab Tab object.
						 *
						 * @return {JSX} Tab component.
						 */
						( tab ) => (
							<Fragment key={ tab.name }>
								{ tab.component }
							</Fragment>
						)
					}
				</TabPanel>
				<SettingsSidebar
					plugin={ plugin }
				/>
			</div>
		</Fragment>
	);
};

/**
 * Props validation
 */
SettingsTabs.propTypes = {
	plugin: PropTypes.string.isRequired,
};
