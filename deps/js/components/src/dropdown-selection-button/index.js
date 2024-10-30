/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button, Dropdown } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

/**
 * Import styles
 */
import './styles.scss';

/**
 * DropdownSelectionButton component
 *
 * @param {Object}   properties          Component properties object.
 * @param {string}   properties.label    Button label.
 * @param {Array}    properties.options  Options array.
 * @param {Function} properties.onChoice Function callback to trigger on choice.
 *
 * @return {JSX} DropdownSelectionButton component.
 */
export const DropdownSelectionButton = ( { label, options, onChoice } ) => (
	<div className="tsc-dropdown-selection-button">
		<Dropdown
			contentClassName="tsc-dropdown-selection-button__content"
			popoverProps={ { placement: 'bottom-end' } }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					variant="secondary"
					size="compact"
					onClick={ onToggle }
					aria-expanded={ isOpen }
				>
					{ label }
				</Button>
			) }
			renderContent={ ( { onClose } ) => (
				<Fragment>
					{
						options.map( ( option ) => (
							<Button
								key={ option.key }
								onClick={ () => {
									onChoice( option.key );
									onClose();
								} }
							>
								{ option.label }
							</Button>
						) )
					}
				</Fragment>
			) }
		/>
	</div>
);

/**
 * Props validation
 */
DropdownSelectionButton.propTypes = {
	label: PropTypes.string.isRequired,
	options: PropTypes.array.isRequired,
	onChoice: PropTypes.func.isRequired,
};
