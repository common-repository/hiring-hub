/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { SelectControl, Disabled, PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/query-loop-filtering-filter',
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
			const { filterToDisplay, renderAs } = attributes;

			// Collect the necessary data.
			const blockProps = useBlockProps();

			/**
			 * Filter the list of filters the user can choose from
			 *
			 * @param {Array} availableFilters Array of available filters.
			 */
			const availableFilters = applyFilters(
				'hiring_hub__block_query_loop_filtering_filter__available_filters',
				[ {
					label: __( 'Published within', 'hiring-hub' ),
					value: 'published-within',
				} ],
			);

			/**
			 * Render the block
			 */
			return (
				<Fragment>
					<InspectorControls>
						<PanelBody
							title={ __( 'Settings', 'hiring-hub' ) }
						>
							<SelectControl
								label={ __( 'Filter to display', 'hiring-hub' ) }
								value={ filterToDisplay }
								options={ availableFilters }

								/**
								 * Update the attribute
								 *
								 * @param {string} value Updated value.
								 *
								 * @return {void}
								 */
								onChange={ ( value ) => {
									setAttributes( {
										filterToDisplay: value,
									} );
								} }
							/>
							<SelectControl
								label={ __( 'Render as', 'hiring-hub' ) }
								value={ renderAs }
								options={ [
									{
										label: __( 'Dropdown', 'hiring-hub' ),
										value: 'dropdown',
									},
									{
										label: __( 'Inline, in column', 'hiring-hub' ),
										value: 'inline-column',
									},
									{
										label: __( 'Inline, in row', 'hiring-hub' ),
										value: 'inline-row',
									},
								] }

								/**
								 * Update the attribute
								 *
								 * @param {string} value Updated value.
								 *
								 * @return {void}
								 */
								onChange={ ( value ) => {
									setAttributes( {
										renderAs: value,
									} );
								} }
							/>
						</PanelBody>
					</InspectorControls>
					<div { ...blockProps }>
						<Disabled>
							<ServerSideRender
								attributes={ attributes }
								block="hiring-hub/query-loop-filtering-filter"
							/>
						</Disabled>
					</div>
				</Fragment>
			);
		},
	},
);
