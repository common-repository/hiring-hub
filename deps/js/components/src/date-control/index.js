/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { BaseControl, Button, DatePicker, Dropdown, useBaseControlProps } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Import styles
 */
import './styles.scss';

/**
 * DateControl component
 *
 * @param {Object}   properties              Component properties object.
 * @param {string}   properties.defaultValue Default value.
 * @param {boolean}  properties.allowEmpty   Whether the empty value is allowed.
 * @param {string}   properties.help         Help.
 * @param {string}   properties.label        Label.
 * @param {Function} properties.onChange     Function callback to trigger on value change.
 * @param {string}   properties.value        Field's value.
 *
 * @return {JSX} DateControl component.
 */
export const DateControl = ( { allowEmpty, defaultValue, help, label, onChange, value } ) => {
	// Get the base control props.
	const { baseControlProps, controlProps } = useBaseControlProps( {} );

	/**
	 * Ensure default value is set if
	 * empty value is not allowed
	 */
	useEffect( () => {
		if ( ! allowEmpty && '' === value ) {
			if ( '' !== defaultValue ) {
				onChange( defaultValue );
			} else {
				let date = new Date();
				date = date.toISOString().split( 'T' )[ 0 ];

				onChange( date );
			}
		}
	}, [ allowEmpty, defaultValue, value, onChange ] );

	return (
		<BaseControl
			{ ...baseControlProps }
			label={ label }
			help={ help }
			className="tsc-date-control"
		>
			<Dropdown
				{ ...controlProps }
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Button
						onClick={ onToggle }
						variant="secondary"
						size="compact"
						aria-expanded={ isOpen }
					>
						{
							'' === value
								? __( '(empty)', 'hiring-hub' )
								: value
						}
					</Button>
				) }
				renderContent={ ( { onClose } ) => (
					<>
						<div
							className="tsc-date-control__dropdown-header"
						>
							<div
								className="tsc-date-control__dropdown-header-actions"
							>
								{
									allowEmpty && (
										<Button
											onClick={ () => {
												onChange( '' );
												onClose();
											} }
											variant="tertiary"
											size="compact"
										>
											{ __( 'Set as empty', 'hiring-hub' ) }
										</Button>
									)
								}
							</div>
							<Button
								onClick={ () => {
									onClose();
								} }
								variant="tertiary"
								size="compact"
							>
								{ __( 'Close', 'hiring-hub' ) }
							</Button>
						</div>
						<DatePicker
							currentDate={ value }

							/**
							 * Update the field's value
							 *
							 * @param {string} updatedValue Updated value.
							 *
							 * @return {void}
							 */
							onChange={ ( updatedValue ) => {
								onChange( updatedValue.split( 'T' )[ 0 ] );
							} }
						/>
					</>
				) }
			/>
		</BaseControl>
	);
};

/**
 * Props validation
 */
DateControl.propTypes = {
	allowEmpty: PropTypes.bool.isRequired,
	defaultValue: PropTypes.string.isRequired,
	help: PropTypes.string.isRequired,
	label: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	value: PropTypes.string.isRequired,
};
