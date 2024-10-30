<?php
/**
 * The "Job Specification" extension
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Job_Specification;

use Closure;
use DateTime;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use Teydea_Studio\Hiring_Hub\Job;
use Teydea_Studio\Hiring_Hub\Settings;
use WP_Error;

/**
 * The "Extension" class
 *
 * @phpstan-import-type Type_Settings_Fields_Config from Utils\Settings
 * @phpstan-type Type_Job_Specification_Item array{allowed_choices:int,help:string,key:string,name:string,possible_values:string[],type:'array_of_strings'}|array{default_value:bool,help:string,key:string,name:string,type:'boolean'}|array{allow_empty:bool,default_value:string,help:string,key:string,name:string,type:'date'}|array{default_value:int,help:string,key:string,max:?int,min:int,name:string,type:'integer',use_max:bool}|array{currencies:string[],default_max:int,default_min:int,default_value:string,help:string,key:string,name:string,type:'salary',units:string[]}|array{default_value:string,help:string,key:string,name:string,type:'text'}|array{default_value:string,help:string,key:string,name:string,type:'url'}
 * @phpstan-type Type_Job_Specification_Items array<string,Type_Job_Specification_Item>
 */
final class Extension extends Utils\Module {
	/**
	 * Settings key
	 *
	 * @var string
	 */
	const SETTINGS_KEY = 'job_specification';

	/**
	 * Salary currencies, in ISO 4217 currency format
	 *
	 * @see https://en.wikipedia.org/wiki/ISO_4217
	 * @var string[]
	 */
	const SALARY_CURRENCIES = [
		'USD',
		'AED',
		'AFN',
		'ALL',
		'AMD',
		'ANG',
		'AOA',
		'ARS',
		'AUD',
		'AWG',
		'AZN',
		'BAM',
		'BBD',
		'BDT',
		'BGN',
		'BHD',
		'BIF',
		'BMD',
		'BND',
		'BOB',
		'BOV',
		'BRL',
		'BSD',
		'BTN',
		'BWP',
		'BYN',
		'BZD',
		'CAD',
		'CDF',
		'CHE',
		'CHF',
		'CHW',
		'CLF',
		'CLP',
		'CNY',
		'COP',
		'COU',
		'CRC',
		'CUP',
		'CVE',
		'CZK',
		'DJF',
		'DKK',
		'DOP',
		'DZD',
		'EGP',
		'ERN',
		'ETB',
		'EUR',
		'FJD',
		'FKP',
		'GBP',
		'GEL',
		'GHS',
		'GIP',
		'GMD',
		'GNF',
		'GTQ',
		'GYD',
		'HKD',
		'HNL',
		'HTG',
		'HUF',
		'IDR',
		'ILS',
		'INR',
		'IQD',
		'IRR',
		'ISK',
		'JMD',
		'JOD',
		'JPY',
		'KES',
		'KGS',
		'KHR',
		'KMF',
		'KPW',
		'KRW',
		'KWD',
		'KYD',
		'KZT',
		'LAK',
		'LBP',
		'LKR',
		'LRD',
		'LSL',
		'LYD',
		'MAD',
		'MDL',
		'MGA',
		'MKD',
		'MMK',
		'MNT',
		'MOP',
		'MRU',
		'MUR',
		'MVR',
		'MWK',
		'MXN',
		'MXV',
		'MYR',
		'MZN',
		'NAD',
		'NGN',
		'NIO',
		'NOK',
		'NPR',
		'NZD',
		'OMR',
		'PAB',
		'PEN',
		'PGK',
		'PHP',
		'PKR',
		'PLN',
		'PYG',
		'QAR',
		'RON',
		'RSD',
		'RUB',
		'RWF',
		'SAR',
		'SBD',
		'SCR',
		'SDG',
		'SEK',
		'SGD',
		'SHP',
		'SLE',
		'SOS',
		'SRD',
		'SSP',
		'STN',
		'SVC',
		'SYP',
		'SZL',
		'THB',
		'TJS',
		'TMT',
		'TND',
		'TOP',
		'TRY',
		'TTD',
		'TWD',
		'TZS',
		'UAH',
		'UGX',
		'USN',
		'UYI',
		'UYU',
		'UYW',
		'UZS',
		'VED',
		'VES',
		'VND',
		'VUV',
		'WST',
		'XAF',
		'XAG',
		'XAU',
		'XBA',
		'XBB',
		'XBC',
		'XBD',
		'XCD',
		'XDR',
		'XOF',
		'XPD',
		'XPF',
		'XPT',
		'XSU',
		'XTS',
		'XUA',
		'XXX',
		'YER',
		'ZAR',
		'ZMW',
		'ZWG',
		'ZWL',
	];

	/**
	 * Salary units
	 *
	 * @var string[]
	 */
	const SALARY_UNITS = [
		'hour',
		'day',
		'week',
		'month',
		'quarter',
		'year',
	];

	/**
	 * Mapping between the job specification items and the schema.org properties
	 *
	 * @var array<string,string>
	 */
	const SCHEMA_ORG_MAPPING = [
		'base_salary'                      => 'd:00000000000000010',
		'education_requirements'           => 'd:00000000000000011',
		'eligibility_to_work_requirement'  => 'd:00000000000000012',
		'employer_overview'                => 'd:00000000000000013',
		'employment_type'                  => 'd:00000000000000001',
		'experience_in_place_of_education' => 'd:00000000000000014',
		'experience_requirements'          => 'd:00000000000000015',
		'incentive_compensation'           => 'd:00000000000000016',
		'industry'                         => 'd:00000000000000017',
		'job_benefits'                     => 'd:00000000000000018',
		'job_immediate_start'              => 'd:00000000000000019',
		'job_location_type'                => 'd:00000000000000002',
		'job_start_date'                   => 'd:00000000000000020',
		'occupational_category'            => 'd:00000000000000021',
		'physical_requirement'             => 'd:00000000000000022',
		'qualifications'                   => 'd:00000000000000023',
		'responsibilities'                 => 'd:00000000000000024',
		'security_clearance_requirement'   => 'd:00000000000000025',
		'sensory_requirement'              => 'd:00000000000000026',
		'skills'                           => 'd:00000000000000027',
		'special_commitments'              => 'd:00000000000000028',
		'total_job_openings'               => 'd:00000000000000029',
		'valid_through'                    => 'd:00000000000000030',
		'work_hours'                       => 'd:00000000000000031',
	];

	/**
	 * Runtime cache - job specification items
	 * saved in the plugin settings
	 *
	 * @var ?Type_Job_Specification_Items
	 */
	protected ?array $items = null;

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the blocks.
		( new Block_Job_Specification_Characteristics( $this->container ) )->register();
		( new Block_Job_Specification_List( $this->container ) )->register();

		// Register the Post Meta management module.
		( new Post_Meta( $this->container ) )->register();

		// Register the Query Loop Filtering module.
		( new Query_Loop_Filtering( $this->container ) )->register();

		// Register the Settings Page module.
		( new Settings_Page( $this->container ) )->register();

		// Filter the configuration of the settings fields.
		add_filter( 'hiring_hub__settings_fields_config', [ $this, 'filter_settings_fields_config' ] );

		// Filter out the default settings array.
		add_filter( 'hiring_hub__settings_default', [ $this, 'filter_settings_default' ] );

		// Filter the default value of the specific schema.org element.
		add_filter( 'hiring_hub__schema_element_default_value', [ $this, 'filter_schema_element_default_value' ], 10, 2 );

		// Filter the schema.org element value.
		add_filter( 'hiring_hub__schema_element_value', [ $this, 'filter_schema_element_value' ], 10, 3 );
	}

	/**
	 * Filter the configuration of the settings fields
	 *
	 * @param Type_Settings_Fields_Config $fields_config Configuration of the settings fields.
	 *
	 * @return Type_Settings_Fields_Config Updated configuration of the settings fields.
	 */
	public function filter_settings_fields_config( array $fields_config ): array { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
		/**
		 * Build the fields config array
		 */
		$fields_config[ self::SETTINGS_KEY ] = [
			'type'   => Validatable_Fields\Dynamic_Fields_Group::TYPE,
			'config' => [
				'array_of_strings' => [
					'fields'    => [
						'allowed_choices' => Validatable_Fields\Configuration::string_of_choice_field( 'unlimited', [ 'unlimited', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ] ),
						'help'            => Validatable_Fields\Configuration::string_field(),
						'key'             => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'name'            => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'possible_values' => Validatable_Fields\Configuration::array_of_strings_field( [] ),
						'type'            => Validatable_Fields\Configuration::exact_string_field( 'array_of_strings' ),
					],

					/**
					 * Value restorer for the dynamic field build based on the
					 * "array_of_strings" field template
					 *
					 * @param mixed                           $values       Current data under the values.
					 * @param Validatable_Fields\Fields_Group $fields_group Instance of the Fields Group this field belongs to.
					 *
					 * @return array Restored values.
					 */
					'restorer'  => function ( $values, Validatable_Fields\Fields_Group $fields_group ): array {
						$correct_values = [];
						$values         = is_array( $values ) ? $values : [];

						/** @var string[] $possible_values */
						$possible_values = $fields_group->get_field_value( 'possible_values' );

						/** @var string $allowed_choices */
						$allowed_choices = $fields_group->get_field_value( 'allowed_choices' );

						foreach ( $values as $value ) {
							if ( in_array( $value, $possible_values, true ) ) {
								$correct_values[] = Utils\Strings::trim( sanitize_text_field( $value ) );
							}
						}

						if ( 'unlimited' !== $allowed_choices ) {
							$allowed_choices = Utils\Type::ensure_int( $allowed_choices );

							if ( count( $correct_values ) > $allowed_choices ) {
								$correct_values = array_slice( $correct_values, 0, $allowed_choices );
							}
						}

						return $correct_values;
					},

					/**
					 * Validator for the dynamic field build based on the
					 * "array_of_strings" field template
					 *
					 * @param mixed                           $values       Values to validate.
					 * @param Validatable_Fields\Fields_Group $fields_group Instance of the Fields Group this field belongs to.
					 *
					 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
					 */
					'validator' => function ( $values, Validatable_Fields\Fields_Group $fields_group ) {
						/** @var string[] $possible_values */
						$possible_values = $fields_group->get_field_value( 'possible_values' );

						/** @var string $allowed_choices */
						$allowed_choices = $fields_group->get_field_value( 'allowed_choices' );

						if ( ! is_array( $values ) ) {
							return new WP_Error(
								'non_array_value',
								sprintf(
									'Value must be an array, %s given.',
									gettype( $values ),
								),
							);
						}

						foreach ( $values as $value ) {
							if ( ! in_array( $value, $possible_values, true ) ) {
								return new WP_Error(
									'field_value_out_of_scope',
									sprintf(
										'"%1$s" of the "%2$s" field is not a value within "%3$s".',
										$value,
										Utils\Type::ensure_string( $fields_group->get_field_value( 'name' ) ),
										implode( ', ', $possible_values ),
									),
								);
							}
						}

						if ( 'unlimited' !== $allowed_choices ) {
							$allowed_choices = Utils\Type::ensure_int( $allowed_choices );

							if ( count( $values ) > $allowed_choices ) {
								return new WP_Error(
									'field_value_incorrect',
									sprintf(
										'Up to %1$s in "%2$s" field allowed, %3$d given.',
										( 1 === $allowed_choices ? '1 value' : sprintf( '%d values', $allowed_choices ) ),
										Utils\Type::ensure_string( $fields_group->get_field_value( 'name' ) ),
										count( $values ),
									),
								);
							}
						}

						return true;
					},
				],
				'boolean'          => [
					'fields' => [
						'default_value' => Validatable_Fields\Configuration::boolean_field( false ),
						'help'          => Validatable_Fields\Configuration::string_field(),
						'key'           => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'name'          => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'type'          => Validatable_Fields\Configuration::exact_string_field( 'boolean' ),
					],
				],
				'date'             => [
					'fields'    => [
						'allow_empty'   => Validatable_Fields\Configuration::boolean_field( true ),
						'default_value' => Validatable_Fields\Configuration::string_field( '', Validatable_Fields\Closures::date_field_restorer(), null, Validatable_Fields\Closures::date_field_validator() ),
						'help'          => Validatable_Fields\Configuration::string_field(),
						'key'           => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'name'          => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'type'          => Validatable_Fields\Configuration::exact_string_field( 'date' ),
					],
					'restorer'  => Validatable_Fields\Closures::date_field_restorer(),
					'validator' => Validatable_Fields\Closures::date_field_validator(),
				],
				'integer'          => [
					'fields' => [
						'default_value' => Validatable_Fields\Configuration::integer_field( 1, 0 ),
						'help'          => Validatable_Fields\Configuration::string_field(),
						'key'           => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'max'           => Validatable_Fields\Configuration::integer_field( 10_000, 0 ),
						'min'           => Validatable_Fields\Configuration::integer_field( 0, 0 ),
						'name'          => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'type'          => Validatable_Fields\Configuration::exact_string_field( 'integer' ),
						'use_max'       => Validatable_Fields\Configuration::boolean_field( false ),
					],
				],
				'salary'           => [
					'fields'    => [
						'currencies'    => Validatable_Fields\Configuration::array_of_strings_field( self::SALARY_CURRENCIES, null, Validatable_Fields\Closures::alphanumeric_array_of_strings_field_sanitizer() ),
						'default_max'   => Validatable_Fields\Configuration::integer_field( 10_000, 0 ),
						'default_min'   => Validatable_Fields\Configuration::integer_field( 5_000, 0 ),
						'default_value' => Validatable_Fields\Configuration::string_field(),
						'help'          => Validatable_Fields\Configuration::string_field( __( 'The salary of the job or of an employee.', 'hiring-hub' ) ),
						'key'           => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'name'          => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'type'          => Validatable_Fields\Configuration::exact_string_field( 'salary' ),
						'units'         => Validatable_Fields\Configuration::array_of_strings_field( self::SALARY_UNITS, null, Validatable_Fields\Closures::alphanumeric_array_of_strings_field_sanitizer() ),
					],

					/**
					 * Value restorer for the dynamic field build based on the
					 * "salary" field template
					 *
					 * @return string Restored value.
					 */
					'restorer'  => function (): string {
						return '';
					},

					/**
					 * Validator for the dynamic field build based on the
					 * "salary" field template
					 *
					 * @param mixed                           $value        Value to validate.
					 * @param Validatable_Fields\Fields_Group $fields_group Instance of the Fields Group this field belongs to.
					 *
					 * @return true|WP_Error Boolean "true" on success, instance of WP_Error otherwise.
					 */
					'validator' => function ( $value, Validatable_Fields\Fields_Group $fields_group ) {
						if ( ! is_string( $value ) ) {
							return new WP_Error(
								'non_string_value',
								sprintf(
									'Value must be string, %s given.',
									gettype( $value ),
								),
							);
						}

						// Verify whether the value given is a valid JSON.
						if ( ! Utils\JSON::is_valid( $value ) ) {
							return new WP_Error(
								'invalid_json',
								'Value must be a valid JSON.',
							);
						}

						/** @var string[] $currencies */
						$currencies = $fields_group->get_field_value( 'currencies' );

						/** @var string[] $units */
						$units = $fields_group->get_field_value( 'units' );

						/** @var ?array<string,mixed> $value */
						$value = Utils\JSON::decode( $value );

						// Verify whether the currency exists and is valid.
						if ( ! isset( $value['currency'] ) ) {
							return new WP_Error(
								'invalid_json',
								'Missing currency.',
							);
						} elseif ( ! is_string( $value['currency'] ) ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'Currency must be a string, %s given.',
									gettype( $value['currency'] ),
								),
							);
						} elseif ( ! in_array( $value['currency'], $currencies, true ) ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'"%1$s" currency is not within the set of supported currencies (%2$s).',
									$value['currency'],
									implode( ', ', $currencies ),
								),
							);
						}

						// Verify whether the unit exists and is valid.
						if ( ! isset( $value['unit'] ) ) {
							return new WP_Error(
								'invalid_json',
								'Missing unit.',
							);
						} elseif ( ! is_string( $value['unit'] ) ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'Unit must be a string, %s given.',
									gettype( $value['unit'] ),
								),
							);
						} elseif ( ! in_array( $value['unit'], $units, true ) ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'"%1$s" unit is not within the set of supported units (%2$s).',
									$value['unit'],
									implode( ', ', $units ),
								),
							);
						}

						// Verify whether the min value exists and is valid.
						if ( ! isset( $value['min'] ) ) {
							return new WP_Error(
								'invalid_json',
								'Missing min.',
							);
						} elseif ( ! is_int( $value['min'] ) ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'Min must be an integer, %s given.',
									gettype( $value['min'] ),
								),
							);
						} elseif ( $value['min'] < 0 ) {
							return new WP_Error(
								'invalid_json',
								'Min must be greater than or equal to 0.',
							);
						}

						// Verify whether the max value exists and is valid.
						if ( ! isset( $value['max'] ) ) {
							return new WP_Error(
								'invalid_json',
								'Missing max.',
							);
						} elseif ( ! is_int( $value['max'] ) ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'Max must be an integer, %s given.',
									gettype( $value['max'] ),
								),
							);
						} elseif ( $value['min'] > $value['max'] ) {
							return new WP_Error(
								'invalid_json',
								sprintf(
									'Max must be greater than or equal to %d.',
									$value['min'],
								),
							);
						}

						return true;
					},
				],
				'text'             => [
					'fields' => [
						'default_value' => Validatable_Fields\Configuration::string_field(),
						'help'          => Validatable_Fields\Configuration::string_field(),
						'key'           => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'name'          => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'type'          => Validatable_Fields\Configuration::exact_string_field( 'text' ),
					],
				],
				'url'              => [
					'fields'    => [
						'default_value' => Validatable_Fields\Configuration::string_field( '', null, Validatable_Fields\Closures::url_field_sanitizer(), Validatable_Fields\Closures::url_field_validator() ),
						'help'          => Validatable_Fields\Configuration::string_field(),
						'key'           => Validatable_Fields\Configuration::dynamic_field_key_field(),
						'name'          => Validatable_Fields\Configuration::string_field( __( 'New field', 'hiring-hub' ) ),
						'type'          => Validatable_Fields\Configuration::exact_string_field( 'url' ),
					],
					'sanitizer' => Validatable_Fields\Closures::url_field_sanitizer(),
					'validator' => Validatable_Fields\Closures::url_field_validator(),
				],
			],
		];

		/** @var Type_Settings_Fields_Config $fields_config */
		return $fields_config;
	}

	/**
	 * Filter out the default settings array
	 *
	 * @param array{}|array<string,array<string,mixed>> $default_value Empty array if no default settings defined, array of default settings otherwise.
	 *
	 * @return array<string,array<string,mixed>> Updated default settings array.
	 */
	public function filter_settings_default( array $default_value ): array {
		$default_value['jobSpecification'] = [
			'd:00000000000000001' => [
				'allowedChoices' => '3',
				'help'           => '',
				'key'            => 'd:00000000000000001',
				'name'           => __( 'Employment type', 'hiring-hub' ),
				'possibleValues' => [
					__( 'Full time', 'hiring-hub' ),
					__( 'Part time', 'hiring-hub' ),
					__( 'Contract', 'hiring-hub' ),
					__( 'Temporary', 'hiring-hub' ),
					__( 'Seasonal', 'hiring-hub' ),
					__( 'Internship', 'hiring-hub' ),
				],
				'type'           => 'array_of_strings',
			],
			'd:00000000000000002' => [
				'allowedChoices' => '3',
				'help'           => '',
				'key'            => 'd:00000000000000002',
				'name'           => __( 'Workplace', 'hiring-hub' ),
				'possibleValues' => [
					__( 'Remote', 'hiring-hub' ),
					__( 'Hybrid', 'hiring-hub' ),
					__( 'On-site', 'hiring-hub' ),
				],
				'type'           => 'array_of_strings',
			],
			'd:00000000000000003' => [
				'defaultValue' => true,
				'help'         => '',
				'key'          => 'd:00000000000000003',
				'name'         => __( 'Paid time off', 'hiring-hub' ),
				'type'         => 'boolean',
			],
			'd:00000000000000004' => [
				'defaultValue' => false,
				'help'         => '',
				'key'          => 'd:00000000000000004',
				'name'         => __( 'Relocation bonus', 'hiring-hub' ),
				'type'         => 'boolean',
			],
			'd:00000000000000010' => [
				'currencies'    => self::SALARY_CURRENCIES,
				'default_max'   => 10_000,
				'default_min'   => 5_000,
				'default_value' => '',
				'help'          => __( 'The base salary of the job or of an employee.', 'hiring-hub' ),
				'key'           => 'd:00000000000000010',
				'name'          => __( 'Base salary', 'hiring-hub' ),
				'type'          => 'salary',
				'units'         => self::SALARY_UNITS,
			],
			'd:00000000000000011' => [
				'defaultValue' => '',
				'help'         => __( 'Educational background needed for the position.', 'hiring-hub' ),
				'key'          => 'd:00000000000000011',
				'name'         => __( 'Education requirements', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000012' => [
				'defaultValue' => '',
				'help'         => __( 'The legal requirements such as citizenship, visa and other documentation required for an applicant to this job.', 'hiring-hub' ),
				'key'          => 'd:00000000000000012',
				'name'         => __( 'Eligibility to work requirement', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000013' => [
				'defaultValue' => '',
				'help'         => __( 'A description of the employer, career opportunities and work environment for this position.', 'hiring-hub' ),
				'key'          => 'd:00000000000000013',
				'name'         => __( 'Employer overview', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000014' => [
				'defaultValue' => true,
				'help'         => '',
				'key'          => 'd:00000000000000014',
				'name'         => __( 'Experience in place of education', 'hiring-hub' ),
				'type'         => 'boolean',
			],
			'd:00000000000000015' => [
				'defaultValue' => '',
				'help'         => __( 'Description of skills and experience needed for the position.', 'hiring-hub' ),
				'key'          => 'd:00000000000000015',
				'name'         => __( 'Experience requirements', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000016' => [
				'defaultValue' => '',
				'help'         => __( 'Description of bonus and commission compensation aspects of the job.', 'hiring-hub' ),
				'key'          => 'd:00000000000000016',
				'name'         => __( 'Incentive compensation', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000017' => [
				'defaultValue' => '',
				'help'         => __( 'The industry associated with the job position.', 'hiring-hub' ),
				'key'          => 'd:00000000000000017',
				'name'         => __( 'Industry', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000018' => [
				'defaultValue' => '',
				'help'         => __( 'Description of benefits associated with the job.', 'hiring-hub' ),
				'key'          => 'd:00000000000000018',
				'name'         => __( 'Job benefits', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000019' => [
				'defaultValue' => true,
				'help'         => __( 'An indicator as to whether a position is available for an immediate start.', 'hiring-hub' ),
				'key'          => 'd:00000000000000019',
				'name'         => __( 'Immediate start', 'hiring-hub' ),
				'type'         => 'boolean',
			],
			'd:00000000000000020' => [
				'allowEmpty'   => true,
				'defaultValue' => '',
				'help'         => __( 'The date on which a successful applicant for this job would be expected to start work. Choose a specific date in the future or use the "Immediate start" property to indicate the position is to be filled as soon as possible.', 'hiring-hub' ),
				'key'          => 'd:00000000000000020',
				'name'         => __( 'Start date', 'hiring-hub' ),
				'type'         => 'date',
			],
			'd:00000000000000021' => [
				'defaultValue' => '',
				'help'         => __( 'A category describing the job, preferably using a term from a taxonomy such as BLS O*NET-SOC, ISCO-08 or similar, with the property repeated for each applicable value. Ideally the taxonomy should be identified, and both the textual label and formal code for the category should be provided. For historical reasons, any textual label and formal code provided as a literal may be assumed to be from O*NET-SOC.', 'hiring-hub' ),
				'key'          => 'd:00000000000000021',
				'name'         => __( 'Occupational category', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000022' => [
				'defaultValue' => '',
				'help'         => __( 'A description of the types of physical activity associated with the job.', 'hiring-hub' ),
				'key'          => 'd:00000000000000022',
				'name'         => __( 'Physical requirement', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000023' => [
				'defaultValue' => '',
				'help'         => __( 'Specific qualifications required for this role.', 'hiring-hub' ),
				'key'          => 'd:00000000000000023',
				'name'         => __( 'Qualifications', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000024' => [
				'defaultValue' => '',
				'help'         => __( 'Responsibilities associated with this role.', 'hiring-hub' ),
				'key'          => 'd:00000000000000024',
				'name'         => __( 'Responsibilities', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000025' => [
				'defaultValue' => '',
				'help'         => __( 'A description of any security clearance requirements of the job.', 'hiring-hub' ),
				'key'          => 'd:00000000000000025',
				'name'         => __( 'Security clearance requirement', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000026' => [
				'defaultValue' => '',
				'help'         => __( 'A description of any sensory requirements and levels necessary to function on the job, including hearing and vision.', 'hiring-hub' ),
				'key'          => 'd:00000000000000026',
				'name'         => __( 'Sensory requirement', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000027' => [
				'defaultValue' => '',
				'help'         => __( 'A statement of knowledge, skill, ability, task or any other assertion expressing a competency that is desired or required to fulfill this role or to work in this occupation.', 'hiring-hub' ),
				'key'          => 'd:00000000000000027',
				'name'         => __( 'Skills', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000028' => [
				'defaultValue' => '',
				'help'         => __( 'Any special commitments associated with this job posting. Valid entries include VeteranCommit, MilitarySpouseCommit, etc.', 'hiring-hub' ),
				'key'          => 'd:00000000000000028',
				'name'         => __( 'Special commitments', 'hiring-hub' ),
				'type'         => 'text',
			],
			'd:00000000000000029' => [
				'defaultValue' => 1,
				'help'         => __( 'The number of positions open for this job posting.', 'hiring-hub' ),
				'key'          => 'd:00000000000000029',
				'max'          => 10_000,
				'min'          => 1,
				'name'         => __( 'Total job openings', 'hiring-hub' ),
				'type'         => 'integer',
				'useMax'       => false,
			],
			'd:00000000000000030' => [
				'allowEmpty'   => true,
				'defaultValue' => '',
				'help'         => __( 'Date after which the job advertisement becomes invalid.', 'hiring-hub' ),
				'key'          => 'd:00000000000000030',
				'name'         => __( 'Valid through', 'hiring-hub' ),
				'type'         => 'date',
			],
			'd:00000000000000031' => [
				'defaultValue' => '',
				'help'         => __( 'The typical working hours for this job (e.g. 1st shift, night shift, 8am-5pm).', 'hiring-hub' ),
				'key'          => 'd:00000000000000031',
				'name'         => __( 'Work hours', 'hiring-hub' ),
				'type'         => 'text',
			],
		];

		return $default_value;
	}

	/**
	 * Filter the default value of the specific schema.org element
	 *
	 * @param string $default_value Default value.
	 * @param string $property      Schema element property.
	 *
	 * @return string Updated default value.
	 */
	public function filter_schema_element_default_value( string $default_value, string $property ): string {
		if ( isset( self::SCHEMA_ORG_MAPPING[ $property ] ) ) {
			$default_value = self::SCHEMA_ORG_MAPPING[ $property ];
		}

		return $default_value;
	}

	/**
	 * Get single job specification item configuration
	 *
	 * @param string $key Job specification item key.
	 *
	 * @return ?Type_Job_Specification_Item Job specification item configuration, null if item with requested key was not found.
	 */
	protected function get_item( string $key ) {
		if ( null === $this->items ) {
			/** @var Settings $settings */
			$settings     = $this->container->get_instance_of( 'settings' );
			$fields_group = $settings->get_fields_group( self::SETTINGS_KEY );

			/** @var Type_Job_Specification_Items|WP_Error $items */
			$items       = null !== $fields_group ? $fields_group->get_value() : [];
			$this->items = $items instanceof WP_Error ? [] : $items;
		}

		return isset( $this->items[ $key ] )
			? $this->items[ $key ]
			: null;
	}

	/**
	 * Filter the schema.org element value
	 *
	 * @param mixed  $value Schema element value - in our case, it might be a job specification key.
	 * @param string $key   Schema element key (property).
	 * @param Job    $job   Job object.
	 *
	 * @return mixed Updated value of the schema element.
	 */
	public function filter_schema_element_value( $value, string $key, object $job ) {
		if ( is_string( $value ) && '' !== $value ) {
			$item       = $this->get_item( $value );
			$meta_value = $job->get_meta( sprintf( '%s__%s', self::SETTINGS_KEY, $value ) );

			if ( null !== $item ) {
				// Item exists, but has not been set for this job.
				if ( null === $meta_value ) {
					return '';
				}

				/**
				 * Structure the salary value
				 */
				if ( 'salary' === $item['type'] ) {
					/** @var ?array{isDefined?:bool,currency:string,min:int,max:int,unit:string} $salary */
					$salary = Utils\JSON::decode( is_string( $meta_value ) ? $meta_value : '' );

					if ( null === $salary || ! isset( $salary['isDefined'] ) || true !== $salary['isDefined'] ) {
						return '';
					}

					return [
						'@type'    => 'MonetaryAmount',
						'currency' => $salary['currency'],
						'value'    => [
							'minValue' => $salary['min'],
							'maxValue' => $salary['max'],
							'unitText' => $salary['unit'],
							'@type'    => 'QuantitativeValue',
						],
					];
				}

				/**
				 * Structure all the other fields
				 */
				$value = is_array( $meta_value )
					? implode( ', ', $meta_value )
					: $meta_value;
			}
		}

		return $value;
	}
}
