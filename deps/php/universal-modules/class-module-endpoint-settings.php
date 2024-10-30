<?php
/**
 * REST API endpoint for getting and updating settings
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The "Module_Endpoint_Settings" class
 */
class Module_Endpoint_Settings extends Utils\Module {
	/**
	 * Hold the Settings class object
	 *
	 * @var ?Utils\Settings
	 */
	protected ?object $settings = null;

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register endpoints.
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register endpoints
	 *
	 * @return void
	 */
	public function register_endpoints(): void {
		register_rest_route(
			sprintf( '%s/v1', $this->container->get_slug() ),
			'/settings',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_settings' ],

				/**
				 * Ensure that user is logged in and has the required
				 * capability
				 *
				 * @return bool Boolean "true" if user has the permission to process this request, "false" otherwise.
				 */
				'permission_callback' => function (): bool {
					/** @var Utils\User $user */
					$user = $this->container->get_instance_of( 'user' );
					return $user->has_managing_permissions();
				},
			],
		);

		register_rest_route(
			sprintf( '%s/v1', $this->container->get_slug() ),
			'/settings',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_settings' ],
				'args'                => [
					'nonce'    => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',

						/**
						 * Nonce value validation
						 *
						 * @param string $value Nonce value.
						 *
						 * @return bool Whether if nonce value is valid or not.
						 */
						'validate_callback' => function ( string $value ): bool {
							/** @var Utils\Nonce $nonce */
							$nonce = $this->container->get_instance_of( 'nonce', [ 'save_settings' ] );
							return $nonce->verify( $value );
						},
					],
					'settings' => [
						'required'          => true,
						'type'              => 'array',

						/**
						 * Settings data sanitization
						 *
						 * @return Utils\Settings Instance of a Settings class.
						 */
						'sanitize_callback' => function (): object {
							if ( null === $this->settings ) {
								/** @var Utils\Settings $settings */
								$settings       = $this->container->get_instance_of( 'settings' );
								$this->settings = $settings;
							}

							return $this->settings;
						},

						/**
						 * Settings data validation
						 *
						 * @param array<string,array<string,mixed>> $data Settings to save.
						 *
						 * @return true|WP_Error Boolean "true" if a given settings data are valid, instance of WP_Error otherwise.
						 */
						'validate_callback' => function ( array $data ) {
							/** @var Utils\Settings $settings */
							$settings       = $this->container->get_instance_of( 'settings', [ $data ] );
							$this->settings = $settings;

							return $this->settings->has_validation_errors()
								? $this->settings->get_first_validation_error()
								: true;
						},
					],
				],

				/**
				 * Ensure that user is logged in and has the required
				 * capability
				 *
				 * @return bool Boolean "true" if user has the permission to process this request, "false" otherwise.
				 */
				'permission_callback' => function (): bool {
					/** @var Utils\User $user */
					$user = $this->container->get_instance_of( 'user' );
					return $user->has_managing_permissions();
				},
			],
		);
	}

	/**
	 * Get plugin settings
	 *
	 * @return WP_Error|WP_REST_Response Instance of WP_REST_Response on success, instance of WP_Error on failure.
	 */
	public function get_settings() {
		/** @var Utils\Settings $settings */
		$settings = $this->container->get_instance_of( 'settings' );

		if ( $settings->has_validation_errors() ) {
			return $settings->get_first_validation_error();
		}

		$data = $settings->get_normalized_data();

		if ( null === $data ) {
			return new WP_Error(
				'validation_errors_found',
				'Can\'t get settings data; resolve validation errors first.',
			);
		}

		return new WP_REST_Response(
			array_merge(
				$data,
				[ 'templates' => $settings->get_templates() ],
			),
			200,
		);
	}

	/**
	 * Save plugin settings
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @return WP_Error|WP_REST_Response Instance of WP_REST_Response on success, instance of WP_Error on failure.
	 *
	 * @phpstan-ignore missingType.generics
	 */
	public function save_settings( WP_REST_Request $request ) {
		/** @var Utils\Settings $settings */
		$settings = $request->get_param( 'settings' );
		$saved    = $settings->save();

		return $saved instanceof WP_Error
			? $saved
			: new WP_REST_Response( [], 200 );
	}
}
