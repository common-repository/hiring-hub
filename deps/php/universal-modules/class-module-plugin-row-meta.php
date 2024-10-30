<?php
/**
 * Filter the plugin's row meta
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Plugin_Row_Meta" class
 */
final class Module_Plugin_Row_Meta extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Filter the plugin's row meta.
		add_filter( 'plugin_row_meta', [ $this, 'filter_plugin_row_meta' ], 10, 2 );
	}

	/**
	 * Filter the plugin's row meta
	 *
	 * The "WordPress.org" UTM medium is meant to be used on WordPress.org plugin directory only.
	 * In the WordPress installation context, this should switch to "Plugin" instead.
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Updated array of the plugin's metadata.
	 */
	public function filter_plugin_row_meta( array $plugin_meta, string $plugin_file ): array {
		if ( $plugin_file === $this->container->get_basename() ) {
			foreach ( $plugin_meta as &$plugin_meta_element ) {
				if ( Utils\Strings::str_contains( $plugin_meta_element, 'utm_medium=WordPress.org' ) ) {
					$plugin_meta_element = str_replace( 'utm_medium=WordPress.org', 'utm_medium=Plugin', $plugin_meta_element );
				}
			}
		}

		return $plugin_meta;
	}
}
