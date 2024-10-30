/**
 * External dependencies
 */
import { DateControl } from '@teydeastudio/components/src/date-control/index.js';
import { IntegerControl } from '@teydeastudio/components/src/integer-control/index.js';
import { PanelFieldHelp } from '@teydeastudio/components/src/panel-field-help/index.js';
import { PanelFieldsContainer } from '@teydeastudio/components/src/panel-fields-container/index.js';
import { SalaryControl } from '@teydeastudio/components/src/salary-control/index.js';
import { URLControl } from '@teydeastudio/components/src/url-control/index.js';
import { buildId } from '@teydeastudio/utils/src/build-id.js';

/**
 * WordPress dependencies
 */
import { BaseControl, FormTokenField, SelectControl, ToggleControl, TextareaControl, useBaseControlProps } from '@wordpress/components';
import { dispatch } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { useJobSpecification } from '../utils.js';

/**
 * Component
 *
 * @return {JSX} Post meta component.
 */
const Component = () => {
	// Update the post meta field value.
	const setPostMeta = ( meta ) => dispatch( 'core/editor' ).editPost( { meta } );

	// Collect the necessary data.
	const jobSpecification = useJobSpecification();
	const toggles = [];

	// Get the base control props.
	const { baseControlProps, controlProps } = useBaseControlProps( { preferredId: buildId( 'hiring-hub', 'extensions-job-specification-post-meta', 'toggles' ) } );

	/**
	 * Ensure we only render the component after all dependencies are resolved
	 */
	if ( 'undefined' === typeof jobSpecification ) {
		return null;
	}

	/**
	 * Render the component
	 *
	 * @return {JSX}
	 */
	return (
		<PluginDocumentSettingPanel
			title={ __( 'Job specification', 'hiring-hub' ) }
			initialOpen={ false }
		>
			<PanelFieldsContainer>
				{
					jobSpecification.map( ( { field, metaKey, value, allowedChoices } ) => {
						switch ( field.type ) {
							case 'array_of_strings':
								if ( '1' === field.allowedChoices ) {
									return (
										<SelectControl
											label={ field.name }
											help={ field.help }
											value={ value }
											options={ [
												{ value: '', label: __( '-- Choose --', 'hiring-hub' ), disabled: true },
												...field.possibleValues.map( ( possibleValue ) => ( { value: possibleValue, label: possibleValue } ) ),
											] }

											/**
											 * Update the post meta value
											 *
											 * @param {string} newValue Updated post meta value.
											 *
											 * @return {void}
											 */
											onChange={ ( newValue ) => {
												setPostMeta( { [ metaKey ]: [ newValue ] } );
											} }
										/>
									);
								}

								return (
									<div>
										<FormTokenField
											label={ field.name }
											value={ value }
											suggestions={ field.possibleValues }

											/**
											 * Update the post meta value
											 *
											 * @param {Array} values Updated post meta value.
											 *
											 * @return {void}
											 */
											onChange={ ( values ) => {
												// Ensure only predefined values are allowed.
												values = values.filter( ( singleValue ) => -1 !== field.possibleValues.indexOf( singleValue ) );

												// Ensure only defined number of choices is allowed.
												if ( 'unlimited' !== field.allowedChoices ) {
													values = values.splice( 0, allowedChoices );
												}

												setPostMeta( { [ metaKey ]: values } );
											} }
										/>
										<PanelFieldHelp
											content={ sprintf(
												// Translators: %1$s - comma separated list of possible values; %2$s - summary of allowed choices number; %3$s - help content.
												__( 'Choose %1$s. Possible values include: %2$s. %3$s', 'hiring-hub' ),
												(
													'unlimited' === allowedChoices
														? __( 'as many values as you want', 'hiring-hub' )
														: sprintf(
															// Translators: %s - singular or plural text.
															__( 'up to %s', 'hiring-hub' ),
															sprintf(
																// Translators: %d - number of allowed choices.
																_n(
																	'%d value',
																	'%d values',
																	allowedChoices,
																	'hiring-hub',
																),
																allowedChoices,
															),
														)
												),
												field.possibleValues.join( ', ' ),
												field.help,
											) }
										/>
									</div>
								);
							case 'boolean':
								toggles.push(
									<ToggleControl
										label={ field.name }
										help={ field.help }
										checked={ value }

										/**
										 * Update the post meta value
										 *
										 * @return {void}
										 */
										onChange={ () => {
											setPostMeta( { [ metaKey ]: ! value } );
										} }
									/>
								);

								return null;
							case 'date':
								return (
									<div>
										<DateControl
											label={ field.name }
											help={ field.help }
											allowEmpty={ field.allowEmpty }
											defaultValue={ field.defaultValue }
											value={ value }

											/**
											 * Update the post meta value
											 *
											 * @param {string} updatedValue Updated post meta value.
											 *
											 * @return {void}
											 */
											onChange={ ( updatedValue ) => {
												setPostMeta( { [ metaKey ]: updatedValue } );
											} }
										/>
									</div>
								);
							case 'integer':
								return (
									<div>
										<IntegerControl
											label={ field.name }
											help={ field.help }
											min={ field.min }
											max={ field.max }
											value={ value }
											defaultValue={ field.defaultValue }

											/**
											 * Update the post meta value
											 *
											 * @param {number} updatedValue Updated post meta value.
											 *
											 * @return {void}
											 */
											onChange={ ( updatedValue ) => {
												setPostMeta( { [ metaKey ]: updatedValue } );
											} }
										/>
									</div>
								);
							case 'salary':
								return (
									<div>
										<SalaryControl
											currencies={ field.currencies }
											defaultMax={ field.defaultMax }
											defaultMin={ field.defaultMin }
											help={ field.help }
											label={ field.name }
											units={ field.units }
											value={ value }

											/**
											 * Update the post meta value
											 *
											 * @param {string} updatedValue Updated post meta value.
											 *
											 * @return {void}
											 */
											onChange={ ( updatedValue ) => {
												setPostMeta( { [ metaKey ]: updatedValue } );
											} }
										/>
									</div>
								);
							case 'text':
								return (
									<div>
										<TextareaControl
											__nextHasNoMarginBottom
											label={ field.name }
											help={ field.help }
											value={ value }

											/**
											 * Update the post meta value
											 *
											 * @param {string} updatedValue Updated post meta value.
											 *
											 * @return {void}
											 */
											onChange={ ( updatedValue ) => {
												setPostMeta( { [ metaKey ]: updatedValue } );
											} }
										/>
									</div>
								);
							case 'url':
								return (
									<URLControl
										label={ field.name }
										help={ field.help }
										value={ value }

										/**
										 * Update the post meta
										 *
										 * @param {string} updatedValue Updated value.
										 *
										 * @return {void}
										 */
										onChange={ ( updatedValue ) => {
											setPostMeta( { [ metaKey ]: updatedValue } );
										} }
									/>
								);
						}

						return null;
					} )
				}
				{
					( 0 < toggles.length ) && (
						<BaseControl
							{ ...baseControlProps }
							label={ __( 'Toggles', 'hiring-hub' ) }
						>
							<Fragment
								{ ...controlProps }
							>
								{ toggles.map( ( toggle ) => toggle ) }
							</Fragment>
						</BaseControl>
					)
				}
			</PanelFieldsContainer>
		</PluginDocumentSettingPanel>
	);
};

/**
 * Register plugin
 */
domReady( () => {
	registerPlugin( buildId( 'hiring-hub', 'extensions-job-specification-post-meta', 'plugin' ), {
		render: Component,
	} );
} );
