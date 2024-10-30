<?php
/**
 * Flush rewrite rules on plugin update
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Rewrite_Rules" class
 */
final class Module_Rewrite_Rules extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Maybe flush rewrite rules.
		add_action( 'init', [ $this, 'maybe_flush_rewrite_rules' ] );
	}

	/**
	 * Maybe flush rewrite rules
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules(): void {
		/**
		 * Use plugin version as a flag to run this operation only once,
		 * until a plugin gets upgraded
		 */
		$option_name = sprintf( '%s__rewrite_rules_flushed', $this->container->get_data_prefix() );

		if ( $this->container->get_version() !== get_option( $option_name, '' ) ) {
			flush_rewrite_rules();
			update_option( $option_name, $this->container->get_version() );
		}
	}
}
