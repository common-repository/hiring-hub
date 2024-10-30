<?php
/**
 * User class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use WP_User;

/**
 * The "User" class
 */
class User {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * User object, or null if the current user should be loaded
	 *
	 * @var ?WP_User
	 */
	protected ?WP_User $user;

	/**
	 * User ID
	 *
	 * @var ?int
	 */
	protected ?int $user_id = null;

	/**
	 * Constructor
	 *
	 * @param Container $container Container instance.
	 * @param ?WP_User  $user      User object, or null if the current user should be loaded.
	 */
	public function __construct( object $container, ?WP_User $user = null ) {
		$this->container = $container;
		$this->user      = $user;

		if ( null === $this->user ) {
			$user = wp_get_current_user();

			if ( $user->exists() ) {
				$this->user = $user;
			}
		}

		if ( $this->user instanceof WP_User ) {
			$this->user_id = $this->user->ID;
		}
	}

	/**
	 * Get the user object
	 *
	 * @return ?WP_User User object, or null if not set.
	 */
	public function get_user(): ?WP_User {
		return $this->user;
	}

	/**
	 * Get the user ID
	 *
	 * @return ?int User ID, or null if not set.
	 */
	public function get_user_id(): ?int {
		return $this->user_id;
	}

	/**
	 * Get the user login
	 *
	 * @return ?string User login, or null if not set.
	 */
	public function get_user_login(): ?string {
		return null === $this->get_user()
			? null
			: $this->get_user()->user_login;
	}

	/**
	 * Get all roles that applies to the given user
	 *
	 * @return string[] Array of all user roles.
	 */
	public function get_user_roles(): array {
		if ( null === $this->get_user() ) {
			return [];
		}

		return $this->get_user()->roles;
	}

	/**
	 * Check if user is allowed to manage plugin options
	 *
	 * @return bool Boolean "true" if user is allowed to manage the plugin options, "false" otherwise.
	 */
	public function has_managing_permissions(): bool {
		if ( null === $this->get_user() ) {
			return false;
		}

		$capability = $this->container->is_network_enabled()
			? 'manage_network_options'
			: 'manage_options';

		return $this->can( $capability );
	}

	/**
	 * Check if user can manage (install and activate) plugins
	 *
	 * @return bool Boolean "true" if user is allowed to manage (install and activate) plugins, "false" otherwise.
	 */
	public function has_plugin_managing_permissions(): bool {
		return $this->can( 'install_plugins' ) && $this->can( 'activate_plugins' );
	}

	/**
	 * Check if user has the super-admin capabilities
	 *
	 * @return bool Boolean "true" if user has the super-admin capabilities, "false" otherwise.
	 */
	public function is_super_admin(): bool {
		return null !== $this->get_user_id() && is_super_admin( $this->get_user_id() );
	}

	/**
	 * Check whether the user has a specific capability
	 *
	 * @param string $capability Capability to check.
	 *
	 * @return bool Boolean "true" if user has a given capability, "false" otherwise.
	 */
	public function can( string $capability ): bool {
		if ( null === $this->get_user() ) {
			return false;
		}

		return user_can( $this->get_user(), $capability );
	}
}
