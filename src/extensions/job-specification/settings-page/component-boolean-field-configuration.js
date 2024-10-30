/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { buildId } from '@teydeastudio/utils/src/build-id.js';

/**
 * WordPress dependencies
 */
import { BaseControl, Button, PanelBody, PanelRow, TextareaControl, TextControl, ToggleControl, useBaseControlProps } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { cleanForSlug } from '@wordpress/url';

/**
 * BooleanFieldConfiguration component
 *
 * @param {Object}   properties          Component properties object.
 * @param {Object}   properties.config   Component configuration.
 * @param {Function} properties.onChange Callback function triggered when any of the config values changes.
 * @param {Function} properties.onDelete Callback function triggered when field is deleted.
 *
 * @return {JSX} BooleanFieldConfiguration component.
 */
export const BooleanFieldConfiguration = ( { config, onChange, onDelete } ) => {
	// Get the base control props.
	const { baseControlProps, controlProps } = useBaseControlProps( { preferredId: buildId( 'hiring-hub', 'extensions-job-specification-settings-page', cleanForSlug( config.name ) ) } );

	return (
		<PanelBody
			initialOpen={ false }
			title={ createInterpolateElement(
				sprintf(
					// Translators: %s - field name.
					__( 'Spec: %s <code>Toggle field</code>', 'hiring-hub' ),
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
					__nextHasNoMarginBottom
					label={ __( 'Default value', 'hiring-hub' ) }
				>
					<ToggleControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						label={ __( 'Enable by default', 'hiring-hub' ) }
						checked={ config.defaultValue }
						help={ config.defaultValue
							? __( 'This field value will be "on" by default.', 'hiring-hub' )
							: __( 'This field value will be "off" by default.', 'hiring-hub' )
						}

						/**
						 * Update the field's default value
						 *
						 * @return {void}
						 */
						onChange={ () => {
							onChange( {
								...config,
								defaultValue: ! config.defaultValue,
							} );
						} }
					/>
				</BaseControl>
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
BooleanFieldConfiguration.propTypes = {
	config: PropTypes.shape( {
		defaultValue: PropTypes.bool.isRequired,
		help: PropTypes.string.isRequired,
		name: PropTypes.string.isRequired,
	} ).isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
};
