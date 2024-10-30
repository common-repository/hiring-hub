/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { BaseControl, SelectControl, ToggleControl, useBaseControlProps } from '@wordpress/components';
import { Fragment, useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { IntegerControl } from '../integer-control/index.js';
import { PanelFieldHelp } from '../panel-field-help/index.js';

/**
 * Import styles
 */
import './styles.scss';

/**
 * SalaryControl component
 *
 * @param {Object}   properties            Component properties object.
 * @param {Array}    properties.currencies Currencies.
 * @param {number}   properties.defaultMax Default value of the "Salary to..." field.
 * @param {number}   properties.defaultMin Default value of the "Salary from..." field.
 * @param {Array}    properties.units      Units ("month", "year", etc.)
 * @param {Function} properties.onChange   Function callback to trigger on value change.
 * @param {string}   properties.help       Field's help.
 * @param {string}   properties.label      Field's label.
 * @param {string}   properties.value      Field's value.
 *
 * @return {JSX} SalaryControl component.
 */
export const SalaryControl = ( { currencies, defaultMax, defaultMin, help, label, onChange, units, value } ) => {
	// Default values.
	const defaults = useMemo( () => ( {
		isDefined: false,
		currency: currencies[ 0 ] ?? null,
		min: defaultMin,
		max: defaultMax,
		unit: units[ 0 ] ?? null,
	} ), [ currencies, defaultMin, defaultMax, units ] );

	/**
	 * Parse field's raw value
	 *
	 * @param {string} rawValue Raw value to parse.
	 *
	 * @return {Object} Parsed value.
	 */
	const parseRawValue = useCallback( ( rawValue ) => {
		rawValue = '' === rawValue
			? defaults
			: JSON.parse( rawValue );

		return {
			isDefined: 'undefined' !== typeof rawValue?.isDefined ? rawValue.isDefined : defaults.isDefined,
			currency: 'undefined' !== typeof rawValue?.currency ? rawValue.currency : defaults.currency,
			min: 'undefined' !== typeof rawValue?.min ? rawValue.min : defaults.min,
			max: 'undefined' !== typeof rawValue?.max ? rawValue.max : defaults.max,
			unit: 'undefined' !== typeof rawValue?.unit ? rawValue.unit : defaults.unit,
		};
	}, [ defaults ] );

	// Parse the field's value.
	const [ editedValue, setEditedValue ] = useState( parseRawValue( value ) );

	// Get the base control props.
	const { baseControlProps, controlProps } = useBaseControlProps( {} );

	/**
	 * Handle the value update
	 */
	useEffect( () => {
		setEditedValue( parseRawValue( value ) );
	}, [ value, parseRawValue ] );

	/**
	 * Notify the parent about value update
	 */
	useEffect( () => {
		onChange( JSON.stringify( editedValue ) );
	}, [ editedValue, onChange ] );

	/**
	 * Return component
	 *
	 * @return {JSX} SalaryControl component.
	 */
	return (
		<div className="tsc-salary-control">
			<BaseControl
				{ ...baseControlProps }
				label={ label }
				className="tsc-salary-control__toggle"
			>
				<ToggleControl
					{ ...controlProps }
					label={ __( 'Define the value', 'hiring-hub' ) }
					checked={ editedValue.isDefined }
					help={ editedValue.isDefined
						? __( 'Define the salary information below.', 'hiring-hub' )
						: __( 'If not defined, salary information will not be attached to this job.', 'hiring-hub' )
					}

					/**
					 * Update the attribute
					 *
					 * @return {void}
					 */
					onChange={ () => {
						setEditedValue( {
							...defaults,
							isDefined: ! editedValue.isDefined,
						} );
					} }
				/>
			</BaseControl>
			{
				editedValue.isDefined && (
					<Fragment>
						<div className="tsc-salary-control__group">
							<div className="tsc-salary-control__row">
								<IntegerControl
									// Translators: %s - field's label.
									label={ sprintf( __( '%s from…', 'hiring-hub' ), label ) }
									help=""
									min={ 0 }
									max={ editedValue.max }
									value={ editedValue.min }
									defaultValue={ 0 }

									/**
									 * Update the value
									 *
									 * @param {number} updatedValue Updated value.
									 *
									 * @return {void}
									 */
									onChange={ ( updatedValue ) => {
										setEditedValue( {
											...editedValue,
											min: updatedValue,
										} );
									} }
								/>
								<IntegerControl
									// Translators: %s - field's label.
									label={ sprintf( __( '%s to…', 'hiring-hub' ), label ) }
									help=""
									min={ editedValue.min }
									value={ editedValue.max }
									defaultValue={ editedValue.min }

									/**
									 * Update the value
									 *
									 * @param {number} updatedValue Updated value.
									 *
									 * @return {void}
									 */
									onChange={ ( updatedValue ) => {
										setEditedValue( {
											...editedValue,
											max: updatedValue,
										} );
									} }
								/>
							</div>
							<PanelFieldHelp
								content={ __( 'Optionally, keep the "from" and "to" field\'s values equal to use specific value instead of the range.', 'hiring-hub' ) }
							/>
						</div>
						<SelectControl
							__nextHasNoMarginBottom
							label={ __( 'Currency', 'hiring-hub' ) }
							value={ editedValue.currency }

							// Translators: %s - field's label.
							help={ sprintf( __( '%s currency.', 'hiring-hub' ), label ) }
							options={ [
								{ value: '', label: __( '-- Choose --', 'hiring-hub' ), disabled: true },
								...currencies.map( ( currency ) => ( { label: currency, value: currency } ) ),
							] }

							/**
							 * Update the value
							 *
							 * @param {string} updatedValue Updated value.
							 *
							 * @return {void}
							 */
							onChange={ ( updatedValue ) => {
								setEditedValue( {
									...editedValue,
									currency: updatedValue,
								} );
							} }
						/>
						<SelectControl
							__nextHasNoMarginBottom
							label={ __( 'Unit', 'hiring-hub' ) }
							value={ editedValue.unit }

							// Translators: %s - field's label.
							help={ sprintf( __( '%s unit ("per" period).', 'hiring-hub' ), label ) }
							options={ [
								{ value: '', label: __( '-- Choose --', 'hiring-hub' ), disabled: true },
								...units.map( ( unit ) => ( { label: unit, value: unit } ) ),
							] }

							/**
							 * Update the value
							 *
							 * @param {string} updatedValue Updated value.
							 *
							 * @return {void}
							 */
							onChange={ ( updatedValue ) => {
								setEditedValue( {
									...editedValue,
									unit: updatedValue,
								} );
							} }
						/>
						<PanelFieldHelp
							content={ help }
						/>
					</Fragment>
				)
			}
		</div>
	);
};

/**
 * Props validation
 */
SalaryControl.propTypes = {
	currencies: PropTypes.array.isRequired,
	defaultMax: PropTypes.number.isRequired,
	defaultMin: PropTypes.number.isRequired,
	help: PropTypes.string,
	label: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	units: PropTypes.array.isRequired,
	value: PropTypes.string.isRequired,
};
