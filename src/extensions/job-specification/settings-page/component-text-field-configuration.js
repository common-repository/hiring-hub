/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button, PanelBody, PanelRow, TextareaControl, TextControl } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * TextFieldConfiguration component
 *
 * @param {Object}   properties          Component properties object.
 * @param {Object}   properties.config   Component configuration.
 * @param {Function} properties.onChange Callback function triggered when any of the config values changes.
 * @param {Function} properties.onDelete Callback function triggered when field is deleted.
 *
 * @return {JSX} TextFieldConfiguration component.
 */
export const TextFieldConfiguration = ( { config, onChange, onDelete } ) => (
	<PanelBody
		initialOpen={ false }
		title={ createInterpolateElement(
			sprintf(
				// Translators: %s - field name.
				__( 'Spec: %s <code>Text field</code>', 'hiring-hub' ),
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
				label={ __( 'Default value of this field', 'hiring-hub' ) }
				value={ config.defaultValue }

				/**
				 * Update the field's default value
				 *
				 * @param {string} value Updated default value.
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
TextFieldConfiguration.propTypes = {
	config: PropTypes.shape( {
		defaultValue: PropTypes.string.isRequired,
		help: PropTypes.string.isRequired,
		name: PropTypes.string.isRequired,
	} ).isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
};
