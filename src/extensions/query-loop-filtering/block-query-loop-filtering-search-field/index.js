/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/query-loop-filtering-search-field',
	{
		/**
		 * The block edit function
		 *
		 * @param {Object}   properties               Block properties.
		 * @param {Object}   properties.attributes    Block attributes.
		 * @param {Function} properties.setAttributes Set attributes callback.
		 *
		 * @return {JSX} Edit component.
		 */
		edit: ( { attributes, setAttributes } ) => {
			// Get the block attributes.
			const { placeholder } = attributes;

			// Collect the necessary data.
			const blockProps = useBlockProps();

			/**
			 * Render the block
			 */
			return (
				<Fragment>
					<InspectorControls>
						<PanelBody
							title={ __( 'Settings', 'hiring-hub' ) }
						>
							<TextControl
								label={ __( 'Field\'s placeholder', 'hiring-hub' ) }
								value={ placeholder }

								/**
								 * Update the attribute
								 *
								 * @param {string} value Updated value.
								 *
								 * @return {void}
								 */
								onChange={ ( value ) => {
									setAttributes( {
										placeholder: value,
									} );
								} }
							/>
						</PanelBody>
					</InspectorControls>
					<input
						{ ...blockProps }
						type="text"
						placeholder={ placeholder }
					/>
				</Fragment>
			);
		},

		/**
		 * The block save function
		 *
		 * @param {Object} properties            Block properties.
		 * @param {Object} properties.attributes Block attributes.
		 *
		 * @return {JSX} Save component.
		 */
		save: ( { attributes } ) => {
			// Get the block attributes.
			const { placeholder } = attributes;

			// Collect the necessary data.
			const blockProps = useBlockProps.save();

			/**
			 * Render the block
			 */
			return (
				<input
					{ ...blockProps }
					type="text"
					placeholder={ placeholder }
					name="hiring-hub-qlff[s]"
				/>
			);
		},
	},
);
