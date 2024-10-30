/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Import styles
 */
import './styles.scss';

/**
 * PanelFieldsContainer component
 *
 * @param {Object} properties          Component properties object.
 * @param {string} properties.children Children components.
 *
 * @return {JSX} PanelFieldsContainer component.
 */
export const PanelFieldsContainer = ( { children } ) => (
	<div className="tsc-panel-fields-container">
		{ children }
	</div>
);

/**
 * Props validation
 */
PanelFieldsContainer.propTypes = {
	children: PropTypes.element.isRequired,
};
