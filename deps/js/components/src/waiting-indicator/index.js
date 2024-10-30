/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';

/**
 * Import styles
 */
import './styles.scss';

/**
 * WaitingIndicator component
 *
 * @param {Object} properties         Component properties object.
 * @param {string} properties.message Message to display along the spinner.
 *
 * @return {JSX} WaitingIndicator component.
 */
export const WaitingIndicator = ( { message } ) => (
	<div className="tsc-waiting-indicator">
		<Spinner />
		<p>{ message }</p>
	</div>
);

/**
 * Props validation
 */
WaitingIndicator.propTypes = {
	message: PropTypes.string.isRequired,
};
