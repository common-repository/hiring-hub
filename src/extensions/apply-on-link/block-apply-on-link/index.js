/**
 * External dependencies
 */
import { useEditedPostId } from '@teydeastudio/utils/src/use-edited-post-id.js';

/**
 * WordPress dependencies
 */
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { isEmail, isURL } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useApplyOnLink } from '../utils.js';
import './style.scss';

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/apply-on-link',
	{
		/**
		 * The block edit function
		 *
		 * @param {Object}   properties               Block properties.
		 * @param {Object}   properties.attributes    Block attributes.
		 * @param {Function} properties.setAttributes Set attributes callback.
		 * @param {Object}   properties.context       Block context.
		 *
		 * @return {JSX} Edit component.
		 */
		edit: ( { attributes, setAttributes, context } ) => {
			// Collect the necessary data.
			const editedPostId = useEditedPostId();
			const applyOnLink = useApplyOnLink();
			const blockProps = useBlockProps( { className: 'wp-element-button' } );

			// Recognize whether we operate within the loop or a specific post.
			const [ isDifferentPost ] = useState( context.postId !== editedPostId );

			/**
			 * Ensure we only render the block after all dependencies are resolved
			 */
			if ( 'undefined' === typeof applyOnLink ) {
				return (
					<Spinner />
				);
			}

			// Recognize whether the link is correctly set.
			const hasLink = '' !== applyOnLink.value && ( isEmail( applyOnLink.value ) || isURL( applyOnLink.value ) );

			/**
			 * Render the block
			 */
			return (
				<div>
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
					{
						/**
						 * Render the notice if "apply on link" is incorrect or not provided
						 */
						( ! isDifferentPost && ! hasLink ) && (
							<p className="has-small-font-size">
								<em>{ __( 'Note: The "Apply on" link\'s field value is empty or invalid; please fill it in the post\'s panel (on the right side). Without this value being correctly set, the "Apply now" button will not be rendered on the front-end.', 'hiring-hub' ) }</em>
							</p>
						)
					}
				</div>
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
			<div>
				<a href="#" { ...useBlockProps.save( { className: 'wp-element-button' } ) /* eslint-disable-line jsx-a11y/anchor-is-valid -- URL is provided by the server rendering */ }>
					<RichText.Content
						tagName="span"
						value={ attributes.text }
					/>
				</a>
			</div>
		),
	},
);
