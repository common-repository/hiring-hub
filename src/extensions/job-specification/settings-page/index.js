/**
 * External dependencies
 */
import { AreaPlaceholder } from '@teydeastudio/components/src/area-placeholder/index.js';
import { DropdownSelectionButton } from '@teydeastudio/components/src/dropdown-selection-button/index.js';

/**
 * WordPress dependencies
 */
import { Panel } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ArrayOfStringsFieldConfiguration } from './component-array-of-strings-field-configuration.js';
import { BooleanFieldConfiguration } from './component-boolean-field-configuration.js';
import { DateFieldConfiguration } from './component-date-field-configuration.js';
import { IntegerFieldConfiguration } from './component-integer-field-configuration.js';
import { SalaryFieldConfiguration } from './component-salary-field-configuration.js';
import { TextFieldConfiguration } from './component-text-field-configuration.js';
import { URLFieldConfiguration } from './component-url-field-configuration.js';

/**
 * Fields config
 */
const fieldsConfig = [
	{
		label: __( 'Date field', 'hiring-hub' ),
		template: 'date',
		type: 'date',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<DateFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
	{
		label: __( 'Integer field', 'hiring-hub' ),
		template: 'integer',
		type: 'integer',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<IntegerFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
	{
		label: __( 'Salary field', 'hiring-hub' ),
		template: 'salary',
		type: 'salary',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<SalaryFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
	{
		label: __( 'Select (dropdown) field', 'hiring-hub' ),
		template: 'arrayOfStrings',
		type: 'array_of_strings',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<ArrayOfStringsFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
	{
		label: __( 'Text field', 'hiring-hub' ),
		template: 'text',
		type: 'text',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<TextFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
	{
		label: __( 'Toggle field', 'hiring-hub' ),
		template: 'boolean',
		type: 'boolean',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<BooleanFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
	{
		label: __( 'URL field', 'hiring-hub' ),
		template: 'url',
		type: 'url',

		/**
		 * The field component
		 *
		 * @param {Object}   field    Field data (config).
		 * @param {Function} onChange Callback function to call when a fields config has changed.
		 * @param {Function} onDelete Callback function triggered when field is deleted.
		 *
		 * @return {JSX} Field component.
		 */
		component: ( field, onChange, onDelete ) => (
			<URLFieldConfiguration
				config={ { ...field } }
				onChange={ ( updatedConfig ) => onChange( field, updatedConfig ) }
				onDelete={ () => onDelete( field ) }
			/>
		),
	},
];

/**
 * Filter the settings page tabs configuration to include
 * the Job Specification extension settings
 */
addFilter(
	'hiring_hub__settings_page_tabs',
	'teydeastudio/hiring-hub/job-specification-settings-page',

	/**
	 * Filter the settings page tabs configuration
	 *
	 * @param {Array}    tabsConfig        Settings page tabs configuration.
	 * @param {Object}   dataState         Current data state.
	 * @param {Function} dispatchDataState Data state dispatcher.
	 *
	 * @return {Array} Updated settings page tabs configuration.
	 */
	( tabsConfig, dataState, dispatchDataState ) => {
		/**
		 * Get the fields data
		 */
		const jobSpecification = dataState.settings?.jobSpecification ?? {};

		/**
		 * Handle field addition
		 *
		 * @param {string} fieldTemplateKey Key of the field template chosen.
		 */
		const onFieldAdd = ( fieldTemplateKey ) => {
			const fieldKey = `d:${ Date.now().toString() }0000`;
			const fieldTemplate = dataState.settings.templates?.jobSpecification?.[ fieldTemplateKey ] ?? {};

			dispatchDataState( {
				type: 'settingsChanged',
				settings: {
					...dataState.settings,
					jobSpecification: {
						[ fieldKey ]: Object.assign( {}, { key: fieldKey, ...fieldTemplate } ),
						...dataState.settings.jobSpecification,
					},
				},
			} );
		};

		/**
		 * Handle field data (config) change
		 *
		 * @param {Object} updatedField       Field data (config).
		 * @param {Object} updatedFieldConfig Field's updated config.
		 */
		const onFieldChange = ( updatedField, updatedFieldConfig ) => {
			jobSpecification[ updatedField.key ] = {
				...jobSpecification[ updatedField.key ],
				...updatedFieldConfig,
			};

			dispatchDataState( {
				type: 'settingsChanged',
				settings: {
					...dataState.settings,
					jobSpecification,
				},
			} );
		};

		/**
		 * Handle field removal
		 *
		 * @param {Object} deletedField Data of the deleted field.
		 */
		const onFieldDelete = ( deletedField ) => {
			delete jobSpecification[ deletedField.key ];

			dispatchDataState( {
				type: 'settingsChanged',
				settings: {
					...dataState.settings,
					jobSpecification,
				},
			} );
		};

		/**
		 * Add custom tab configuration to
		 * the filtered array
		 */
		tabsConfig = [
			{
				name: 'job-specification',
				title: __( 'Job specification', 'hiring-hub' ),
				component: (
					<Fragment>
						<div>
							<p>{ __( 'Manage the job specification configurations in this section. Add as many specs as you need.', 'hiring-hub' ) }</p>
						</div>
						<div className="tsc-settings-tabs__actions">
							<DropdownSelectionButton
								label={ __( 'Add new specification', 'hiring-hub' ) }
								options={ fieldsConfig.map( ( { label, template } ) => ( { label, key: template } ) ) }
								onChoice={ onFieldAdd }
							/>
						</div>
						{
							0 === Object.keys( jobSpecification ).length
								? (
									<AreaPlaceholder
										message={ __( 'You don\'t have any job specification field configured yet. Let\'s add one!', 'hiring-hub' ) }
									/>
								)
								: (
									<Panel>
										{
											Object.values( jobSpecification ).map( ( field ) => {
												const fieldConfig = fieldsConfig.find( ( config ) => config.type === field.type );

												return 'undefined' === typeof fieldConfig
													? null
													: fieldConfig.component( field, onFieldChange, onFieldDelete );
											} )
										}
									</Panel>
								)
						}
					</Fragment>
				),
			},
			...tabsConfig,
		];

		return tabsConfig;
	},
);

/**
 * Integrate with the schema.org data sources
 */
addFilter(
	'hiring_hub__schema__data_sources',
	'teydeastudio/hiring-hub/job-specification-settings-page',

	/**
	 * Filter the list of possible data sources to choose from
	 *
	 * @param {Array}  dataSources Data sources.
	 * @param {Object} settings    Plugin settings.
	 *
	 * @return {Array} Updated list of data sources.
	 */
	( dataSources, settings ) => {
		const { jobSpecification } = settings;

		for ( const item of Object.values( jobSpecification ) ) {
			dataSources.push( {
				// Array of string will eventually map to text.
				type: 'array_of_strings' === item.type ? 'text' : item.type,

				// Translators: %s - item name.
				label: sprintf( __( '%s (Job specification)', 'hiring-hub' ), item.name ),
				value: item.key,
			} );
		}

		return dataSources;
	},
);

/**
 * Ensure that removed job specification items are not used
 * as data sources for schema.org elements (no dangling keys)
 */
addFilter(
	'hiring_hub__pre_change_settings',
	'teydeastudio/hiring-hub/job-specification-settings-page',

	/**
	 * Modify the settings object before
	 * it's updated state is saved
	 *
	 * @param {Object} settings Plugin settings.
	 *
	 * @return {Object} Updated object with plugin settings.
	 */
	( settings ) => {
		const validJobSpecifications = Object.values( settings.jobSpecification ).map( ( item ) => item.key );

		for ( const [ key, value ] of Object.entries( settings.schema ) ) {
			if ( ! validJobSpecifications.includes( value ) ) {
				settings.schema[ key ] = '';
			}
		}

		return settings;
	},
);
