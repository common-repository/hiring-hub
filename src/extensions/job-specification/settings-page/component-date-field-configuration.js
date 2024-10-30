/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { DateControl } from '@teydeastudio/components/src/date-control/index.js';
import { buildId } from '@teydeastudio/utils/src/build-id.js';

/**
 * WordPress dependencies
 */
import { BaseControl, Button, PanelBody, PanelRow, TextareaControl, TextControl, ToggleControl, useBaseControlProps } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * DateFieldConfiguration component
 *
 * @param {Object}   properties          Component properties object.
 * @param {Object}   properties.config   Component configuration.
 * @param {Function} properties.onChange Callback function triggered when any of the config values changes.
 * @param {Function} properties.onDelete Callback function triggered when field is deleted.
 *
 * @return {JSX} DateFieldConfiguration component.
 */
export const DateFieldConfiguration = ( { config, onChange, onDelete } ) => {
	// Get the base control props.
	const { baseControlProps, controlProps } = useBaseControlProps( { preferredId: buildId( 'hiring-hub', 'extensions-job-specification-settings-page', 'date-field-empty-value-allowance' ) } );

	/**
	 * Return the component
	 *
	 * @return {JSX} DateFieldConfiguration component.
	 */
	return (
		<PanelBody
			initialOpen={ false }
			title={ createInterpolateElement(
				sprintf(
					// Translators: %s - field name.
					__( 'Spec: %s <code>Date field</code>', 'hiring-hub' ),
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
				<BaseControl
					{ ...baseControlProps }
					label={ __( 'Empty value allowance', 'hiring-hub' ) }
				>
					<ToggleControl
						{ ...controlProps }
						label={ __( 'Allow empty value', 'hiring-hub' ) }
						checked={ config.allowEmpty }
						help={ config.allowEmpty
							? __( 'The empty value will be allowed.', 'hiring-hub' )
							: __( 'The empty value will not be allowed.', 'hiring-hub' )
						}

						/**
						 * Update the field's default value
						 *
						 * @return {void}
						 */
						onChange={ () => {
							onChange( {
								...config,
								allowEmpty: ! config.allowEmpty,
							} );
						} }
					/>
				</BaseControl>
			</PanelRow>
			<PanelRow>
				<DateControl
					label={ __( 'Default value of this field', 'hiring-hub' ) }
					allowEmpty={ config.allowEmpty }
					defaultValue=""
					value={ config.defaultValue }

					/**
					 * Update the default value
					 *
					 * @param {string} updatedValue Updated default value.
					 *
					 * @return {void}
					 */
					onChange={ ( updatedValue ) => {
						onChange( {
							...config,
							defaultValue: updatedValue,
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
};

/**
 * Props validation
 */
DateFieldConfiguration.propTypes = {
	config: PropTypes.shape( {
		allowEmpty: PropTypes.bool.isRequired,
		defaultValue: PropTypes.string.isRequired,
		help: PropTypes.string.isRequired,
		name: PropTypes.string.isRequired,
	} ).isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
};
