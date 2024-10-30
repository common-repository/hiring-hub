/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { IntegerControl } from '@teydeastudio/components/src/integer-control/index.js';
import { buildId } from '@teydeastudio/utils/src/build-id.js';

/**
 * WordPress dependencies
 */
import { BaseControl, Button, PanelBody, PanelRow, TextareaControl, TextControl, ToggleControl, useBaseControlProps } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * IntegerFieldConfiguration component
 *
 * @param {Object}   properties          Component properties object.
 * @param {Object}   properties.config   Component configuration.
 * @param {Function} properties.onChange Callback function triggered when any of the config values changes.
 * @param {Function} properties.onDelete Callback function triggered when field is deleted.
 *
 * @return {JSX} IntegerFieldConfiguration component.
 */
export const IntegerFieldConfiguration = ( { config, onChange, onDelete } ) => {
	// Get the base control props.
	const { baseControlProps, controlProps } = useBaseControlProps( { preferredId: buildId( 'hiring-hub', 'extensions-job-specification-settings-page', 'integer-field-max-value-usage' ) } );

	/**
	 * Return the component
	 *
	 * @return {JSX} IntegerFieldConfiguration component.
	 */
	return (
		<PanelBody
			initialOpen={ false }
			title={ createInterpolateElement(
				sprintf(
					// Translators: %s - field name.
					__( 'Spec: %s <code>Integer field</code>', 'hiring-hub' ),
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
					label={ __( 'Default value of this field', 'hiring-hub' ) }
					min={ config.min }
					max={ config.useMax ? config.max : undefined }
					value={ config.defaultValue }
					defaultValue={ config.min }

					/**
					 * Update the field's default value
					 *
					 * @param {number} value Updated value.
					 *
					 * @return {void}
					 */
					onChange={ ( value ) => {
						onChange( {
							...config,
							defaultValue: value,
						} );
					} }
				/>
			</PanelRow>
			<PanelRow>
				<IntegerControl
					label={ __( 'Minimum valid value of this field', 'hiring-hub' ) }
					min={ 0 }
					max={ config.useMax ? config.max : undefined }
					value={ config.min }
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
							defaultValue: value > config.defaultValue ? value : config.defaultValue,
							min: value,
						} );
					} }
				/>
			</PanelRow>
			<PanelRow>
				<BaseControl
					{ ...baseControlProps }
					label={ __( 'Define maximum valid value', 'hiring-hub' ) }
				>
					<ToggleControl
						{ ...controlProps }
						label={ __( 'Define maximum valid value', 'hiring-hub' ) }
						checked={ config.useMax }

						/**
						 * Update the field's default value
						 *
						 * @return {void}
						 */
						onChange={ () => {
							const useMax = ! config.useMax;

							onChange( {
								...config,
								defaultValue: ( true === useMax && config.max < config.defaultValue ) ? config.max : config.defaultValue,
								min: ( true === useMax && config.max < config.min ) ? config.max : config.min,
								useMax,
							} );
						} }
					/>
				</BaseControl>
			</PanelRow>
			{
				( config.useMax ) && (
					<PanelRow>
						<IntegerControl
							label={ __( 'Maximum valid value of this field', 'hiring-hub' ) }
							min={ config.min }
							value={ config.max }
							defaultValue={ config.min }

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
									defaultValue: value < config.defaultValue ? value : config.defaultValue,
									min: value < config.min ? value : config.min,
									max: value,
								} );
							} }
						/>
					</PanelRow>
				)
			}
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
IntegerFieldConfiguration.propTypes = {
	config: PropTypes.shape( {
		defaultValue: PropTypes.number.isRequired,
		help: PropTypes.string.isRequired,
		max: PropTypes.number,
		min: PropTypes.number.isRequired,
		name: PropTypes.string.isRequired,
		useMax: PropTypes.bool.isRequired,
	} ).isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
};
