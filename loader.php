<?php
/**
 * Load plugin tokens and dependencies
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * Class autoloader
 */
spl_autoload_register(
	/**
	 * Autoload plugin classes
	 *
	 * @param string $class_name Class name.
	 *
	 * @return void
	 */
	function ( string $class_name ): void {
		$class_map = include __DIR__ . '/classmap.php';

		if ( isset( $class_map[ $class_name ] ) ) {
			require_once __DIR__ . $class_map[ $class_name ];
		}
	},
);

/**
 * Get the plugin container object
 *
 * @return ?Utils\Plugin Plugin container object, null if couldn't construct.
 */
function get_container(): ?object {
	// Check if dependencies are met.
	if ( ! class_exists( 'Teydea_Studio\Hiring_Hub\Dependencies\Utils\Plugin' ) ) {
		return null;
	}

	// Construct the plugin object.
	$plugin = new Utils\Plugin();

	$plugin->set_data_prefix( 'hiring_hub' );
	$plugin->set_data_keys(
		[
			'option' => [
				'hiring_hub__rewrite_rules_flushed',
				'hiring_hub__settings',
				'hiring_hub__should_initiate_onboarding',
			],
		],
	);

	$plugin->set_instantiable_classes(
		[
			'asset'    => Utils\Asset::class,
			'cache'    => Utils\Cache::class,
			'job'      => Job::class,
			'nonce'    => Utils\Nonce::class,
			'screen'   => Utils\Screen::class,
			'settings' => Settings::class,
			'template' => Utils\Template::class,
			'user'     => Utils\User::class,
		],
	);

	$plugin->set_main_dir( __DIR__ );
	$plugin->set_modules(
		[
			Modules\Module_Block_Editor::class,
			Modules\Module_Block_Patterns::class,
			Modules\Module_Extensions::class,
			Modules\Module_Post_Type::class,
			Modules\Module_Schema::class,
			Modules\Module_Settings_Page::class,
			Modules\Module_Templates::class,
			Universal_Modules\Module_Endpoint_Settings::class,
			Universal_Modules\Module_Plugin_Row_Meta::class,
			Universal_Modules\Module_Rewrite_Rules::class,
			Universal_Modules\Module_Translations::class,
		],
	);

	$plugin->set_name( 'Hiring Hub' );
	$plugin->set_slug( 'hiring-hub' );
	$plugin->set_version( '1.3.3' );

	return $plugin;
}
