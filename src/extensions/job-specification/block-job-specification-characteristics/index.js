/**
 * External dependencies
 */
import { areArraysEqual } from '@teydeastudio/utils/src/are-arrays-equal.js';
import { arrayIncludeExclude } from '@teydeastudio/utils/src/array-include-exclude.js';
import { buildId } from '@teydeastudio/utils/src/build-id.js';
import { useEditedPostId } from '@teydeastudio/utils/src/use-edited-post-id.js';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { BaseControl, CheckboxControl, PanelBody, Spinner, ToggleControl, useBaseControlProps } from '@wordpress/components';
import { Fragment, useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { cleanForSlug } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useJobSpecification } from '../utils.js';
import './style.scss';

/**
 * Data reducer
 *
 * @param {Object} jobSpecification          Job specification data.
 * @param {Array}  disallowedCharacteristics List of characteristics that should not be shown.
 *
 * @return {Object} Reduced data object.
 */
const dataReducer = ( jobSpecification, disallowedCharacteristics ) => {
	const result = [];

	for ( const item of jobSpecification ) {
		if ( 'boolean' === item.field.type && true === item.value && ! disallowedCharacteristics.includes( item.field.key ) ) {
			result.push( item.field.name );
		}
	}

	return result;
};

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/job-specification-characteristics',
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
			// Get the block attributes.
			const { showAllEnabledCharacteristics, disallowedCharacteristics } = attributes;

			// Store characteristics rendered in the editor context.
			const [ previewCharacteristics, setPreviewCharacteristics ] = useState( null );
			const [ characteristics, setCharacteristics ] = useState( null );

			// Collect the necessary data.
			const editedPostId = useEditedPostId();
			const jobSpecification = useJobSpecification();
			const blockProps = useBlockProps();
			const innerBlocksProps = useInnerBlocksProps( blockProps );

			// Recognize whether we operate within the loop or a specific post.
			const [ isDifferentPost ] = useState( context.postId !== editedPostId );

			// Get the base control props.
			const { baseControlProps, controlProps } = useBaseControlProps( { preferredId: buildId( 'hiring-hub', 'extensions-job-specification-block-job-specification-characteristics', 'allowed-characteristics' ) } );

			/**
			 * Update the "previewCharacteristics" set
			 */
			useEffect( () => {
				if ( 'undefined' !== typeof jobSpecification ) {
					/**
					 * If in preview mode, as soon as we receive the
					 * job specification data, construct the list of
					 * preview characteristics to pass to the
					 * renderer.
					 *
					 * This is required because post meta might not
					 * have been saved yet, so the REST API endpoint
					 * won't be able to return the correct
					 * characteristics list.
					 *
					 * If not editing the post, ensure there's no
					 * preview characteristics set this way.
					 */
					const updatedPreviewCharacteristics = isDifferentPost ? [] : dataReducer( jobSpecification, disallowedCharacteristics );

					if ( ! areArraysEqual( previewCharacteristics, updatedPreviewCharacteristics ) ) {
						setPreviewCharacteristics( updatedPreviewCharacteristics );
					}
				}
			}, [ jobSpecification, disallowedCharacteristics, previewCharacteristics, isDifferentPost ] );

			/**
			 * Update the characteristics array
			 */
			useEffect( () => {
				if ( isDifferentPost ) {
					/**
					 * If not in the editor mode (for example: in Site Editor),
					 * fetch the characteristics using the REST API endpoint,
					 * which loads them from the post meta.
					 */
					apiFetch( {
						path: `/hiring-hub/v1/block-job-specification-characteristics/?post_id=${ context.postId }&show_all_enabled_characteristics=${ showAllEnabledCharacteristics }&disallowed_characteristics=${ JSON.stringify( disallowedCharacteristics ) }`,
						method: 'GET',
					} )
						.then( ( data ) => setCharacteristics( data ) )
						.catch( ( error ) => {
							console.error( error ); // eslint-disable-line no-console
						} );
				} else {
					/**
					 * If in the editor mode, use dynamic data (which might not
					 * be saved to the post meta yet).
					 */
					setCharacteristics( previewCharacteristics );
				}
			}, [ context.postId, previewCharacteristics, isDifferentPost, showAllEnabledCharacteristics, disallowedCharacteristics ] );

			/**
			 * Ensure we only render the block after all dependencies are resolved
			 */
			if ( null === characteristics ) {
				return (
					<Spinner />
				);
			}

			/**
			 * Render the block
			 */
			return (
				<Fragment>
					<InspectorControls>
						<PanelBody
							title={ __( 'Settings', 'hiring-hub' ) }
						>
							<ToggleControl
								label={ __( 'Show all enabled job specification characteristics', 'hiring-hub' ) }
								checked={ showAllEnabledCharacteristics }

								/**
								 * Update the attribute
								 *
								 * @param {boolean} value Updated value.
								 *
								 * @return {void}
								 */
								onChange={ ( value ) => {
									setAttributes( {
										showAllEnabledCharacteristics: value,
										disallowedCharacteristics: true === value ? [] : disallowedCharacteristics,
									} );
								} }
							/>
							{
								/**
								 * Render a checkbox for all configured characteristics
								 */
								( ! showAllEnabledCharacteristics ) && (
									<BaseControl
										{ ...baseControlProps }
										label={ __( 'Allow following characteristics:', 'hiring-hub' ) }
										help={ __( 'If a characteristic is disabled at the post level, it will not be displayed anyway. In this settings you control whether or not to display characteristics that are enabled at the post level.', 'hiring-hub' ) }
									>
										<Fragment
											{ ...controlProps }
										>
											{
												jobSpecification && jobSpecification.map( ( item ) => {
													if ( 'boolean' !== item.field.type ) {
														return null;
													}

													return (
														<CheckboxControl
															__nextHasNoMarginBottom
															key={ item.field.key }
															label={ item.field.name }
															checked={ ! disallowedCharacteristics.includes( item.field.key ) }

															/**
															 * Update the attribute
															 *
															 * @param {boolean} checked Whether the checkox is checked or not.
															 *
															 * @return {void}
															 */
															onChange={ ( checked ) => {
																setAttributes( {
																	disallowedCharacteristics: arrayIncludeExclude( disallowedCharacteristics, item.field.key, ! checked ),
																} );
															} }
														/>
													);
												} )
											}
										</Fragment>
									</BaseControl>
								)
							}
						</PanelBody>
					</InspectorControls>
					<ul { ...innerBlocksProps }>
						{
							characteristics.map( ( characteristic, index ) => (
								<li
									key={ sprintf( '%1$d-%2$s', index, cleanForSlug( characteristic ) ) }
									className={ `wp-block-hiring-hub-job-specification-characteristics__characteristic wp-block-hiring-hub-job-specification__characteristic--${ cleanForSlug( characteristic ) }` }
								>
									{ characteristic }
								</li>
							) )
						}
					</ul>
				</Fragment>
			);
		},

		/**
		 * The block save function
		 *
		 * @return {JSX} Save component.
		 */
		save: () => {
			// Collect the necessary data.
			const blockProps = useBlockProps.save();
			const innerBlocksProps = useInnerBlocksProps.save( blockProps );

			return (
				<ul { ...innerBlocksProps } />
			);
		},
	},
);
