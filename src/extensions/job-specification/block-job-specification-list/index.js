/**
 * External dependencies
 */
import { areArraysEqual } from '@teydeastudio/utils/src/are-arrays-equal.js';
import { arrayIncludeExclude } from '@teydeastudio/utils/src/array-include-exclude.js';
import { buildId } from '@teydeastudio/utils/src/build-id.js';
import { decodeJSON } from '@teydeastudio/utils/src/json.js';
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
 * @param {Object} jobSpecification Job specification data.
 * @param {Array}  disallowedItems  List of items that should not be shown.
 *
 * @return {Object} Reduced data object.
 */
const dataReducer = ( jobSpecification, disallowedItems ) => {
	let result = [];

	if ( 'undefined' !== typeof jobSpecification ) {
		result = jobSpecification
			.map( ( item ) => {
				if ( disallowedItems.includes( item.field.key ) ) {
					return null;
				}

				let values;

				switch ( item.field.type ) {
					case 'array_of_strings':
						values = item?.value ?? [];
						break;
					case 'boolean':
						values = [ true === item.value ? __( 'Yes', 'hiring-hub' ) : __( 'No', 'hiring-hub' ) ];
						break;
					case 'date':
					case 'text':
					case 'url':
						if ( '' !== item?.value ) {
							values = [ item.value ];
						} else {
							return null;
						}

						break;
					case 'integer':
						values = [ item.value.toString() ];
						break;
					case 'salary': {
						const { locale } = window.teydeaStudio.hiringHub.jobSpecificationList;
						const salary = decodeJSON( item?.value ?? '' );

						if ( null === salary || true !== ( salary?.isDefined ?? false ) ) {
							return null;
						}

						if ( 'undefined' === typeof salary?.currency || 'undefined' === typeof salary?.min || 'undefined' === typeof salary?.max ) {
							return null;
						}

						const config = {
							style: 'currency',
							currency: salary.currency,
							maximumFractionDigits: 0,
						};

						const formattedNumber = salary.min === salary.max
							? new Intl.NumberFormat( locale, config ).format( salary.min )
							: sprintf(
								'%1$s - %2$s',
								new Intl.NumberFormat( locale, config ).format( salary.min ),
								new Intl.NumberFormat( locale, config ).format( salary.max ),
							);

						values = [
							sprintf(
								'%1$s / %2$s',
								formattedNumber,
								salary.unit,
							),
						];

						break;
					}
				}

				if ( 0 === values.length ) {
					return null;
				}

				return {
					label: item.field.name,
					type: item.field.type,
					values,
				};
			} )
			.filter( ( item ) => null !== item );
	}

	return result;
};

/**
 * Format field's value
 *
 * @param {string} value Field's value.
 * @param {string} type  Item type.
 *
 * @return {string|JSX} Formatted value.
 */
const formatValue = ( value, type ) => {
	if ( 'url' === type ) {
		return (
			<a
				href={ value }
				target="_blank"
				rel="noopener noreferrer"
			>
				{ value }
			</a>
		);
	}

	return value;
};

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/job-specification-list',
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
			const { showAllItems, disallowedItems } = attributes;

			// Store list rendered in the editor context.
			const [ previewItems, setPreviewItems ] = useState( null );
			const [ items, setItems ] = useState( null );

			// Collect the necessary data.
			const editedPostId = useEditedPostId();
			const jobSpecification = useJobSpecification();
			const blockProps = useBlockProps();
			const innerBlocksProps = useInnerBlocksProps( blockProps );

			// Recognize whether we operate within the loop or a specific post.
			const [ isDifferentPost ] = useState( context.postId !== editedPostId );

			// Get the base control props.
			const { baseControlProps, controlProps } = useBaseControlProps( { preferredId: buildId( 'hiring-hub', 'extensions-job-specification-block-job-specification-list', 'allowed-items' ) } );

			/**
			 * Update the "previewList" set
			 */
			useEffect( () => {
				if ( 'undefined' !== typeof jobSpecification ) {
					/**
					 * If in preview mode, as soon as we receive the
					 * job specification data, construct the list of
					 * preview items to pass to the renderer.
					 *
					 * This is required because post meta might not
					 * have been saved yet, so the REST API endpoint
					 * won't be able to return the correct items.
					 *
					 * If not editing the post, ensure there's no
					 * items set this way.
					 */
					const updatedPreviewItems = isDifferentPost ? [] : dataReducer( jobSpecification, disallowedItems );

					if ( ! areArraysEqual( previewItems, updatedPreviewItems ) ) {
						setPreviewItems( updatedPreviewItems );
					}
				}
			}, [ jobSpecification, disallowedItems, isDifferentPost, previewItems ] );

			/**
			 * Update the preview items array
			 */
			useEffect( () => {
				if ( isDifferentPost ) {
					/**
					 * If not in the editor mode (for example: in Site Editor),
					 * fetch the list items using the REST API endpoint,
					 * which loads them from the post meta.
					 */
					apiFetch( {
						path: `/hiring-hub/v1/block-job-specification-list/?post_id=${ context.postId }&show_all_items=${ showAllItems }&disallowed_items=${ JSON.stringify( disallowedItems ) }`,
						method: 'GET',
					} )
						.then( ( data ) => setItems( data ) )
						.catch( ( error ) => {
							console.error( error ); // eslint-disable-line no-console
						} );
				} else {
					/**
					 * If in the editor mode, use dynamic data (which might not
					 * be saved to the post meta yet).
					 */
					setItems( previewItems );
				}
			}, [ context.postId, previewItems, isDifferentPost, showAllItems, disallowedItems ] );

			/**
			 * Ensure we only render the block after all dependencies are resolved
			 */
			if ( null === items ) {
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
								label={ __( 'Show all job specification items', 'hiring-hub' ) }
								checked={ showAllItems }

								/**
								 * Update the attribute
								 *
								 * @param {boolean} value Updated value.
								 *
								 * @return {void}
								 */
								onChange={ ( value ) => {
									setAttributes( {
										showAllItems: value,
										disallowedItems: true === value ? [] : disallowedItems,
									} );
								} }
							/>
							{
								/**
								 * Render a checkbox for all configured items
								 */
								( ! showAllItems ) && (
									<BaseControl
										{ ...baseControlProps }
										label={ __( 'Allow following items:', 'hiring-hub' ) }
									>
										<Fragment
											{ ...controlProps }
										>
											{
												jobSpecification && jobSpecification.map( ( item ) => (
													<CheckboxControl
														__nextHasNoMarginBottom
														key={ item.field.key }
														label={ item.field.name }
														checked={ ! disallowedItems.includes( item.field.key ) }

														/**
														 * Update the attribute
														 *
														 * @param {boolean} checked Whether the checkox is checked or not.
														 *
														 * @return {void}
														 */
														onChange={ ( checked ) => {
															setAttributes( {
																disallowedItems: arrayIncludeExclude( disallowedItems, item.field.key, ! checked ),
															} );
														} }
													/>
												) )
											}
										</Fragment>
									</BaseControl>
								)
							}
						</PanelBody>
					</InspectorControls>
					<div { ...innerBlocksProps }>
						{
							items.map( ( item, index ) => (
								<div
									key={ sprintf( '%1$d-%2$s', index, cleanForSlug( item.label ) ) }
									className={ `wp-block-hiring-hub-job-specification-items__item wp-block-hiring-hub-job-specification__item--${ cleanForSlug( item.label ) }` }
								>
									<p className="wp-block-hiring-hub-job-specification-list__item-label">
										{ item.label }
									</p>
									<p className="wp-block-hiring-hub-job-specification-list__item-values">
										{
											item.values.map( ( value, subIndex ) => (
												<span
													key={ sprintf( '%1$d-%2$s', subIndex, cleanForSlug( value ) ) }
													className={ `wp-block-hiring-hub-job-specification-list__item-value wp-block-hiring-hub-job-specification-list__item-value--${ cleanForSlug( value ) }` }
												>
													{ formatValue( value, item.type ) }
												</span>
											) )
										}
									</p>
								</div>
							) )
						}
					</div>
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
				<div { ...innerBlocksProps } />
			);
		},
	},
);
