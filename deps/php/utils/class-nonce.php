<?php
/**
 * Nonce utils class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use Closure;

/**
 * The "Nonce" class
 */
final class Nonce {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Action to sign or verify with nonce
	 *
	 * @var string
	 */
	protected string $action;

	/**
	 * Constructor
	 *
	 * @param Container $container Container instance.
	 * @param string    $action    Action to sign or verify with nonce.
	 */
	public function __construct( object $container, string $action ) {
		$this->container = $container;
		$this->action    = $action;
	}

	/**
	 * Build an array of query arguments:
	 * - first argument is an action with a given value
	 * - second argument is an associated nonce
	 *
	 * This can be later used by the "verify_and_get" method
	 * to verify nonce and get the associated value.
	 *
	 * @param string $value Value to pass.
	 *
	 * @return array<string,string> Query arguments array.
	 */
	public function build_query_args( string $value ): array {
		return [
			$this->get_action() => $value,
			$this->get_key()    => $this->create(),
		];
	}

	/**
	 * Create the nonce string
	 *
	 * @return string Nonce string.
	 */
	public function create(): string {
		return wp_create_nonce( $this->get_action() );
	}

	/**
	 * Get the nonce action
	 *
	 * @return string Nonce action.
	 */
	public function get_action(): string {
		return sprintf(
			'%1$s__%2$s',
			$this->container->get_data_prefix(),
			$this->action,
		);
	}

	/**
	 * Get the key under which we should expect
	 * the nonce value to be available
	 *
	 * @return string Key to access the nonce through.
	 */
	public function get_key(): string {
		return sprintf(
			'%1$s__nonce_on_%2$s',
			$this->container->get_data_prefix(),
			$this->action,
		);
	}

	/**
	 * Render the nonce field
	 *
	 * @return void
	 */
	public function render_field(): void {
		wp_nonce_field( $this->get_action(), $this->get_key() );
	}

	/**
	 * Verify the nonce string
	 *
	 * @param string $nonce Nonce string to verify.
	 *
	 * @return bool Whether the nonce is valid or not.
	 */
	public function verify( string $nonce ): bool {
		return false !== wp_verify_nonce( $nonce, $this->get_action() );
	}

	/**
	 * Verify nonce and get the value from a given data set
	 *
	 * @param string         $method    HTTP method to use as a data set; either GET or POST.
	 * @param ?string        $key       Key under which the value should be accessible; defaults to nonce action.
	 * @param string|Closure $sanitizer Callback function to use for value sanitization.
	 *
	 * @return ?string Sanitized value loaded from a verified data source; null otherwise.
	 */
	public function verify_and_get( string $method = 'GET', ?string $key = null, $sanitizer = 'sanitize_text_field' ): ?string {
		$key ??= $this->get_action();
		$nonce = '';
		$value = null;

		switch ( $method ) {
			case 'GET':
				$nonce = isset( $_GET[ $this->get_key() ] ) ? sanitize_text_field( wp_unslash( $_GET[ $this->get_key() ] ) ) : '';
				break;
			case 'POST':
				$nonce = isset( $_POST[ $this->get_key() ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->get_key() ] ) ) : '';
				break;
		}

		if ( wp_verify_nonce( $nonce, $this->get_action() ) ) {
			switch ( $method ) {
				case 'GET':
					$value = isset( $_GET[ $key ] ) && is_callable( $sanitizer ) ? $sanitizer( wp_unslash( $_GET[ $key ] ) ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- input is sanitized by $sanitizer callback function.
					break;
				case 'POST':
					$value = isset( $_POST[ $key ] ) && is_callable( $sanitizer ) ? $sanitizer( wp_unslash( $_POST[ $key ] ) ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- input is sanitized by $sanitizer callback function.
					break;
			}
		}

		return is_string( $value ) ? $value : null;
	}
}
