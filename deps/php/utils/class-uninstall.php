<?php
/**
 * Uninstall class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use WP_Site;

/**
 * The "Uninstall" class
 */
class Uninstall {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Constructor
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( object $container ) {
		$this->container = $container;
	}

	/**
	 * Delete options
	 *
	 * @param string[] $option_keys Option keys to delete.
	 *
	 * @return void
	 */
	public function delete_options( array $option_keys ): void {
		if ( $this->container->is_network_enabled() ) {
			foreach ( $option_keys as $option_key ) {
				delete_network_option( get_current_network_id(), $option_key );
			}

			foreach ( get_sites() as $site ) {
				if ( ! $site instanceof WP_Site ) {
					continue;
				}

				switch_to_blog( Type::ensure_int( $site->blog_id ) );

				foreach ( $option_keys as $option_key ) {
					delete_option( $option_key );
				}
			}

			restore_current_blog();
		} else {
			foreach ( $option_keys as $option_key ) {
				delete_option( $option_key );
			}
		}
	}

	/**
	 * Delete user meta
	 *
	 * @param string[] $meta_keys User meta keys to delete.
	 *
	 * @return void
	 */
	public function delete_user_meta( array $meta_keys ): void {
		$user_ids = get_users( [ 'fields' => 'ID' ] );

		foreach ( $user_ids as $user_id ) {
			foreach ( $meta_keys as $meta_key ) {
				delete_user_meta( $user_id, $meta_key );
			}
		}
	}
}
