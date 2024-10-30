/**
 * WordPress dependencies
 */
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/query-loop-filtering-button',
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
			// Collect the necessary data.
			const blockProps = useBlockProps( { className: 'wp-element-button' } );

			/**
			 * Render the block
			 */
			return (
				<button
					type="button"
					{ ...blockProps }
				>
					<RichText
						tagName="span"
						value={ attributes.text }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
						placeholder={ __( 'Button text…', 'hiring-hub' ) }

						/**
						 * Update the link text
						 *
						 * @param {string} text Updated text.
						 *
						 * @return {void}
						 */
						onChange={ ( text ) => {
							setAttributes( { text } );
						} }
					/>
				</button>
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
		save: ( { attributes } ) => (
			<button
				type="submit"
				{ ...useBlockProps.save( { className: 'wp-element-button' } ) }
			>
				<RichText.Content
					tagName="span"
					value={ attributes.text }
				/>
			</button>
		),
	},
);
