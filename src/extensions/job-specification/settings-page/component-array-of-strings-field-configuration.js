/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button, TextareaControl, PanelBody, PanelRow, SelectControl, TextControl } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * ArrayOfStringsFieldConfiguration component
 *
 * @param {Object}   properties          Component properties object.
 * @param {Object}   properties.config   Component configuration.
 * @param {Function} properties.onChange Callback function triggered when any of the config values changes.
 * @param {Function} properties.onDelete Callback function triggered when field is deleted.
 *
 * @return {JSX} ArrayOfStringsFieldConfiguration component.
 */
export const ArrayOfStringsFieldConfiguration = ( { config, onChange, onDelete } ) => (
	<PanelBody
		initialOpen={ false }
		title={ createInterpolateElement(
			sprintf(
				// Translators: %s - field name.
				__( 'Spec: %s <code>Select (dropdown) field</code>', 'hiring-hub' ),
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
			<TextareaControl
				label={ __( 'Possible values', 'hiring-hub' ) }
				value={ config.possibleValues.join( '\n' ) }
				help={ __( 'One value per line.', 'hiring-hub' ) }

				/**
				 * Update the list of possible values
				 *
				 * @param {string} value Updated list of possible values.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						possibleValues: value.split( '\n' ),
					} );
				} }
			/>
		</PanelRow>
		<PanelRow>
			<SelectControl
				__nextHasNoMarginBottom
				label={ __( 'Number of choices allowed', 'hiring-hub' ) }
				value={ config.allowedChoices }
				help={ __( 'Define how many choices are allowed for each single job.', 'hiring-hub' ) }
				options={ [
					{
						label: __( 'Unlimited', 'hiring-hub' ),
						value: 'unlimited',
					},
					{
						label: __( 'Up to 1', 'hiring-hub' ),
						value: '1',
					},
					{
						label: __( 'Up to 2', 'hiring-hub' ),
						value: '2',
					},
					{
						label: __( 'Up to 3', 'hiring-hub' ),
						value: '3',
					},
					{
						label: __( 'Up to 4', 'hiring-hub' ),
						value: '4',
					},
					{
						label: __( 'Up to 5', 'hiring-hub' ),
						value: '5',
					},
					{
						label: __( 'Up to 6', 'hiring-hub' ),
						value: '6',
					},
					{
						label: __( 'Up to 7', 'hiring-hub' ),
						value: '7',
					},
					{
						label: __( 'Up to 8', 'hiring-hub' ),
						value: '8',
					},
					{
						label: __( 'Up to 9', 'hiring-hub' ),
						value: '9',
					},
					{
						label: __( 'Up to 10', 'hiring-hub' ),
						value: '10',
					},
				] }

				/**
				 * Update value of the number of allowed choices
				 *
				 * @param {string} value Updated number of allowed choices.
				 *
				 * @return {void}
				 */
				onChange={ ( value ) => {
					onChange( {
						...config,
						allowedChoices: value,
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
ArrayOfStringsFieldConfiguration.propTypes = {
	config: PropTypes.shape( {
		allowedChoices: PropTypes.string.isRequired,
		help: PropTypes.string.isRequired,
		name: PropTypes.string.isRequired,
		possibleValues: PropTypes.array.isRequired,
	} ).isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
};
