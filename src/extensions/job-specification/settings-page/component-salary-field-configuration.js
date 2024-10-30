/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { IntegerControl } from '@teydeastudio/components/src/integer-control/index.js';

/**
 * WordPress dependencies
 */
import { Button, PanelBody, PanelRow, TextareaControl, TextControl } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * SalaryFieldConfiguration component
 *
 * @param {Object}   properties          Component properties object.
 * @param {Object}   properties.config   Component configuration.
 * @param {Function} properties.onChange Callback function triggered when any of the config values changes.
 * @param {Function} properties.onDelete Callback function triggered when field is deleted.
 *
 * @return {JSX} SalaryFieldConfiguration component.
 */
export const SalaryFieldConfiguration = ( { config, onChange, onDelete } ) => (
	<PanelBody
		initialOpen={ false }
		title={ createInterpolateElement(
			sprintf(
				// Translators: %s - field name.
				__( 'Spec: %s <code>Salary field</code>', 'hiring-hub' ),
				config.name,
			),
			{
				code: <code />,
			}
		) }
	>
		<PanelRow>
			<TextControl
				label={ __( 'Name', 'hiring-hub' ) }
				value={ config.name }
				help={ __( 'The name of this specification.', 'hiring-hub' ) }

				/**
				 * Update value of the specification name
				 *
				 * @param {string} value Updated specification name.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						name: value,
					} );
				} }
			/>
		</PanelRow>
		<PanelRow>
			<IntegerControl
				// Translators: %s - spec name.
				label={ sprintf( __( 'Default "%s from…" value', 'hiring-hub' ), config.name ) }
				min={ 0 }
				max={ config.defaultMax }
				value={ config.defaultMin }
				defaultValue={ 0 }

				/**
				 * Update the field's min value
				 *
				 * @param {number} value Updated value.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						defaultMin: value,
					} );
				} }
			/>
		</PanelRow>
		<PanelRow>
			<IntegerControl
				// Translators: %s - spec name.
				label={ sprintf( __( 'Default "%s to…" value', 'hiring-hub' ), config.name ) }
				min={ config.defaultMin }
				value={ config.defaultMax }
				defaultValue={ config.defaultMin }

				/**
				 * Update the field's max value
				 *
				 * @param {number} value Updated value.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						defaultMax: value,
					} );
				} }
			/>
		</PanelRow>
		<PanelRow>
			<TextareaControl
				label={ __( 'Units', 'hiring-hub' ) }
				value={ config.units.join( '\n' ) }
				help={ __( 'One value per line. First row will be used as a default choice.', 'hiring-hub' ) }

				/**
				 * Update the list of units
				 *
				 * @param {string} value Updated list of units.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						units: value.split( '\n' ),
					} );
				} }
			/>
		</PanelRow>
		<PanelRow>
			<TextareaControl
				label={ __( 'Currencies', 'hiring-hub' ) }
				value={ config.currencies.join( '\n' ) }
				help={ __( 'One value per line. First row will be used as a default choice. Use ISO 4217 currency format.', 'hiring-hub' ) }

				/**
				 * Update the list of currencies
				 *
				 * @param {string} value Updated list of currencies.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						currencies: value.split( '\n' ),
					} );
				} }
			/>
		</PanelRow>
		<PanelRow>
			<TextareaControl
				label={ __( 'Help content', 'hiring-hub' ) }
				value={ config.help }

				/**
				 * Update the help content
				 *
				 * @param {string} value Updated help content.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						help: value,
					} );
				} }
			/>
		</PanelRow>
		<PanelRow
			className="tsc-settings-container__row tsc-settings-container__row--with-separator"
		>
			<Button
				variant="secondary"
				size="compact"
				isDestructive
				onClick={ () => onDelete() }
			>
				{
					// Translators: %s - spec name.
					sprintf( __( 'Delete the "%s" spec', 'hiring-hub' ), config.name )
				}
			</Button>
		</PanelRow>
	</PanelBody>
);

/**
 * Props validation
 */
SalaryFieldConfiguration.propTypes = {
	config: PropTypes.shape( {
		currencies: PropTypes.array.isRequired,
		defaultMax: PropTypes.number.isRequired,
		defaultMin: PropTypes.number.isRequired,
		help: PropTypes.string.isRequired,
		name: PropTypes.string.isRequired,
		units: PropTypes.array.isRequired,
	} ).isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
};
