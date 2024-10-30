<?php
/**
 * Translations module
 * - load plugin textdomain
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Translations" class
 */
class Module_Translations extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Load plugin textdomain.
		add_action( 'init', [ $this, 'load_plugin_textdomain' ], 1 );
	}

	/**
	 * Load plugin textdomain
	 *
	 * @return void
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			$this->container->get_slug(),
			false,
			sprintf(
				'%s/languages',
				dirname( $this->container->get_basename() ),
			),
		);
	}
}
