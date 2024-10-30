/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Import styles
 */
import './styles.scss';

/**
 * AreaPlaceholder component
 *
 * @param {Object} properties         Component properties object.
 * @param {string} properties.message Message to display in the placeholder.
 *
 * @return {JSX} AreaPlaceholder component.
 */
export const AreaPlaceholder = ( { message } ) => (
	<div className="tsc-area-placeholder">
		<p>{ message }</p>
	</div>
);

/**
 * Props validation
 */
AreaPlaceholder.propTypes = {
	message: PropTypes.string.isRequired,
};
