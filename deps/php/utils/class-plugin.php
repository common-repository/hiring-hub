<?php
/**
 * Plugin controller class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Plugin" class
 */
final class Plugin extends Container {
	/**
	 * Container type
	 *
	 * @var string
	 */
	protected string $type = 'plugin';

	/**
	 * Get the main file of the plugin
	 *
	 * @return string Main file of the plugin.
	 */
	public function get_main_file(): string {
		if ( '' === $this->main_file ) {
			// Generate path to the main file of a plugin.
			$this->main_file = sprintf( '%1$s/%2$s.php', $this->main_dir, $this->slug );
		}

		return $this->main_file;
	}

	/**
	 * Get the plugin's basename
	 *
	 * @return string Plugin's basename.
	 */
	public function get_basename(): string {
		return plugin_basename( $this->get_main_file() );
	}

	/**
	 * Determine if a given plugin is network-enabled
	 *
	 * @return bool Boolean "true" if plugin is network-enabled, "false" otherwise.
	 */
	public function is_network_enabled(): bool {
		if ( false === $this->supports_network ) {
			return false;
		}

		if ( ! is_multisite() ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		return is_plugin_active_for_network( $this->get_basename() );
	}

	/**
	 * Uninstall container
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$uninstall = new Uninstall( $this );

		if ( ! empty( $this->data_keys['option'] ?? [] ) ) {
			$uninstall->delete_options( $this->data_keys['option'] );
		}

		if ( ! empty( $this->data_keys['user_meta'] ?? [] ) ) {
			$uninstall->delete_user_meta( $this->data_keys['user_meta'] );
		}
	}
}
