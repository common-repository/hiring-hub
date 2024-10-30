/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { PluginIcon } from '../plugin-icon/index.js';

/**
 * Import styles
 */
import './styles.scss';

/**
 * SettingsContainer component
 *
 * @param {Object} properties          Component properties object.
 * @param {string} properties.plugin   Plugin key.
 * @param {JSX}    properties.actions  Actions components.
 * @param {JSX}    properties.children Child component to render.
 *
 * @return {JSX} Settings component.
 */
export const SettingsContainer = ( { plugin, actions, children } ) => {
	// Collect the necessary data.
	const { pageTitle } = window.teydeaStudio[ plugin ].settingsPage;

	/**
	 * Render component
	 */
	return (
		<div className="tsc-settings-container">
			<div className="tsc-settings-container__header">
				<PluginIcon
					plugin={ plugin }
				/>
				<h1>{ pageTitle }</h1>
				<div className="tsc-settings-container__actions">
					{ actions }
				</div>
			</div>
			<div className="tsc-settings-container__container">
				{ children }
			</div>
		</div>
	);
};

/**
 * Props validation
 */
SettingsContainer.propTypes = {
	plugin: PropTypes.string.isRequired,
	actions: PropTypes.element.isRequired,
	children: PropTypes.element.isRequired,
};
