/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Panel, PanelBody, SelectControl } from '@wordpress/components';
import { createInterpolateElement, Fragment, useEffect, useState } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Get data sources in use
 *
 * @param {Object} settings Plugin settings.
 *
 * @return {Array} Array of data sources in use.
 */
const getDataSourcesInUse = ( settings ) => {
	return Object.values( settings?.schema ?? {} ).filter( ( value ) => '' !== value );
};

/**
 * TabSchema component
 *
 * @param {Object}   properties             Component properties object.
 * @param {Object}   properties.settings    Plugin settings.
 * @param {Function} properties.setSettings Function (callback) used to update the settings.
 *
 * @return {JSX} TabSchema component.
 */
export const TabSchema = ( { settings, setSettings } ) => {
	// List of data sources already in use.
	const [ dataSourcesInUse, setDataSourcesInUse ] = useState( getDataSourcesInUse( settings ) );

	// List of possible data sources to choose from.
	const [ dataSources, setDataSources ] = useState( [] );

	/**
	 * Schema.org properties
	 */
	const properties = [
		{ name: 'applicantLocationRequirements' },
		{ name: 'applicationContact' },
		{
			name: 'baseSalary',
			supportedTypes: [ 'salary' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.baseSalary ?? '',
			},
		},
		{
			name: 'datePosted',
			supportedTypes: [],
			dataSource: {
				type: 'text',
				value: __( 'Job\'s publish date', 'hiring-hub' ),
			},
		},
		{
			name: 'description',
			supportedTypes: [],
			dataSource: {
				type: 'text',
				value: __( 'Job\'s excerpt', 'hiring-hub' ),
			},
		},
		{
			name: 'directApply',
			supportedTypes: [],
			dataSource: {
				type: 'text',
				value: __( 'Set to "true" if Job\'s "Apply on Link" field contains a valid link; otherwise "false"', 'hiring-hub' ),
			},
		},
		{
			name: 'educationRequirements',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.educationRequirements ?? '',
			},
		},
		{
			name: 'eligibilityToWorkRequirement',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.eligibilityToWorkRequirement ?? '',
			},
		},
		{
			name: 'employerOverview',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.employerOverview ?? '',
			},
		},
		{
			name: 'employmentType',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.employmentType ?? '',
			},
		},
		{ name: 'employmentUnit' },
		{
			name: 'estimatedSalary',
			supportedTypes: [ 'salary' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.estimatedSalary ?? '',
			},
		},
		{
			name: 'experienceInPlaceOfEducation',
			supportedTypes: [ 'boolean' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.experienceInPlaceOfEducation ?? '',
			},
		},
		{
			name: 'experienceRequirements',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.experienceRequirements ?? '',
			},
		},
		{ name: 'hiringOrganization' },
		{
			name: 'incentiveCompensation',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.incentiveCompensation ?? '',
			},
		},
		{
			name: 'industry',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.industry ?? '',
			},
		},
		{
			name: 'jobBenefits',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.jobBenefits ?? '',
			},
		},
		{
			name: 'jobImmediateStart',
			supportedTypes: [ 'boolean' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.jobImmediateStart ?? '',
			},
		},
		{ name: 'jobLocation' },
		{
			name: 'jobLocationType',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.jobLocationType ?? '',
			},
		},
		{
			name: 'jobStartDate',
			supportedTypes: [ 'date', 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.jobStartDate ?? '',
			},
		},
		{
			name: 'occupationalCategory',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.occupationalCategory ?? '',
			},
		},
		{
			name: 'physicalRequirement',
			supportedTypes: [ 'text', 'url' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.physicalRequirement ?? '',
			},
		},
		{
			name: 'qualifications',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.qualifications ?? '',
			},
		},
		{ name: 'relevantOccupation' },
		{
			name: 'responsibilities',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.responsibilities ?? '',
			},
		},
		{
			name: 'securityClearanceRequirement',
			supportedTypes: [ 'text', 'url' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.securityClearanceRequirement ?? '',
			},
		},
		{
			name: 'sensoryRequirement',
			supportedTypes: [ 'text', 'url' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.sensoryRequirement ?? '',
			},
		},
		{
			name: 'skills',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.skills ?? '',
			},
		},
		{
			name: 'specialCommitments',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.specialCommitments ?? '',
			},
		},
		{
			name: 'title',
			supportedTypes: [],
			dataSource: {
				type: 'text',
				value: __( 'Job\'s title', 'hiring-hub' ),
			},
		},
		{
			name: 'totalJobOpenings',
			supportedTypes: [ 'integer' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.totalJobOpenings ?? '',
			},
		},
		{
			name: 'url',
			supportedTypes: [],
			dataSource: {
				type: 'text',
				value: __( 'Direct link (URL, permalink) to a job', 'hiring-hub' ),
			},
		},
		{
			name: 'validThrough',
			supportedTypes: [ 'date' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.validThrough ?? '',
			},
		},
		{
			name: 'workHours',
			supportedTypes: [ 'text' ],
			dataSource: {
				type: 'select',
				value: settings?.schema?.workHours ?? '',
			},
		},
	];

	/**
	 * Render the human-readable description of supported types
	 *
	 * @param {Array} supportedTypes Types supported by property.
	 *
	 * @return {string} Human-readable description of supported types.
	 */
	const renderSupportedTypes = ( supportedTypes ) => {
		supportedTypes = supportedTypes.join( ',' );

		switch ( supportedTypes ) {
			case '':
				return '-';
			case 'boolean':
				return __( 'Toggle field', 'hiring-hub' );
			case 'date':
				return __( 'Date field', 'hiring-hub' );
			case 'date,text':
				return __( 'Date or Text field', 'hiring-hub' );
			case 'integer':
				return __( 'Integer field', 'hiring-hub' );
			case 'salary':
				return __( 'Salary field', 'hiring-hub' );
			case 'text':
				return __( 'Text field', 'hiring-hub' );
			case 'text,url':
				return __( 'Text or URL field', 'hiring-hub' );
			case 'url':
				return __( 'URL field', 'hiring-hub' );
		}

		return supportedTypes;
	};

	/**
	 * Render the data source
	 *
	 * @param {Object} dataSource       Data source object.
	 * @param {string} dataSource.type  Type of the data source.
	 * @param {string} dataSource.value Value of the data source.
	 * @param {string} name             Property name.
	 * @param {Array}  supportedTypes   Types this property supports.
	 *
	 * @return {string|JSX} Data source rendition.
	 */
	const renderDataSource = ( { type, value }, name, supportedTypes ) => {
		if ( 'text' === type ) {
			return value;
		}

		return (
			<SelectControl
				value={ value }
				options={ [
					{ value: '', label: __( 'No data source set', 'hiring-hub' ) },
					...dataSources
						.filter( ( dataSource ) => supportedTypes.includes( dataSource.type ) )
						.filter( ( dataSource ) => ( value === dataSource.value || ! dataSourcesInUse.includes( dataSource.value ) ) ),
				] }
				onChange={ ( newValue ) => {
					setSettings( {
						...settings,
						schema: {
							...settings.schema,
							[ name ]: newValue,
						},
					} );
				} }
				__nextHasNoMarginBottom
			/>
		);
	};

	/**
	 * Update the state of data sources in use
	 */
	useEffect( () => {
		setDataSourcesInUse( getDataSourcesInUse( settings ) );
	}, [ settings, dataSources ] );

	/**
	 * Update the state of all possible data sources
	 */
	useEffect( () => {
		/**
		 * List of possible data sources to choose from
		 *
		 * @param {Array}  dataSources Data sources.
		 * @param {Object} settings    Plugin settings.
		 */
		setDataSources( applyFilters( 'hiring_hub__schema__data_sources', [], settings ) );
	}, [ settings ] );

	/**
	 * Render the component
	 *
	 * @return {JSX}
	 */
	return (
		<Fragment>
			<div>
				<p>
					{
						createInterpolateElement(
							__( 'Configure the Schema.org markup to improve the discoverability of the Job posts by presenting their data in a structured way. Find out more about this on the <a>Schema.org</a> website.', 'hiring-hub' ),
							{
								a: <a href="https://schema.org/JobPosting" target="_blank" rel="noreferrer noopener" />, // eslint-disable-line jsx-a11y/anchor-has-content
							},
						)
					}
				</p>
			</div>
			<Panel>
				<PanelBody>
					<table className="tsc-settings-container__table">
						<thead>
							<tr>
								<th>
									{ __( 'Property', 'hiring-hub' ) }
								</th>
								<th>
									{ __( 'Supported types', 'hiring-hub' ) }
								</th>
								<th>
									{ __( 'Data source', 'hiring-hub' ) }
								</th>
							</tr>
						</thead>
						<tbody>
							{
								/**
								 * Render single property row
								 */
								properties.map( ( property ) => {
									// Destructure the property object.
									const { name, supportedTypes, dataSource } = property;

									if ( 'undefined' === typeof supportedTypes ) {
										return (
											<tr key={ name }>
												<td>
													<code>
														{ name }
													</code>
												</td>
												<td
													colSpan={ 2 }
												>
													<em>{ __( 'Not supported yet', 'hiring-hub' ) }</em>
												</td>
											</tr>
										);
									}

									return (
										<tr key={ name }>
											<td>
												<code>
													{ name }
												</code>
											</td>
											<td>
												{ renderSupportedTypes( supportedTypes ) }
											</td>
											<td>
												{ renderDataSource( dataSource, name, supportedTypes ) }
											</td>
										</tr>
									);
								} )
							}
						</tbody>
					</table>
				</PanelBody>
			</Panel>
		</Fragment>
	);
};

/**
 * Props validation
 */
TabSchema.propTypes = {
	settings: PropTypes.object.isRequired,
	setSettings: PropTypes.func.isRequired,
};
