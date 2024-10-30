/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Import styles
 */
import './styles.scss';

/**
 * PanelFieldHelp component
 *
 * This is built specifically for WordPress control fields which
 * does not support the "help" property.
 *
 * @param {Object} properties         Component properties object.
 * @param {string} properties.content Help content.
 *
 * @return {JSX} PanelFieldHelp component.
 */
export const PanelFieldHelp = ( { content } ) => (
	<p className="tsc-panel-field-help">
		{ content }
	</p>
);

/**
 * Props validation
 */
PanelFieldHelp.propTypes = {
	content: PropTypes.string.isRequired,
};
