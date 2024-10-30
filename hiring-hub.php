<?php
/**
 * Plugin Name: Hiring Hub
 * Plugin URI: https://teydeastudio.com/products/hiring-hub/?utm_source=Hiring+Hub&utm_medium=Plugin&utm_campaign=Plugin+research&utm_content=Plugin+header
 * Description: Create a job portal and career page with WordPress. Manage job boards and run recruitment activities, all within your WordPress website.
 * Version: 1.3.3
 * Text Domain: hiring-hub
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 6.6
 * Author: Teydea Studio
 * Author URI: https://teydeastudio.com/?utm_source=Hiring+Hub&utm_medium=WordPress.org&utm_campaign=Company+research&utm_content=Plugin+header
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function Teydea_Studio\Hiring_Hub\get_container;

/**
 * Require loader
 */
require_once __DIR__ . '/loader.php';

/**
 * Initialize the plugin
 */
add_action(
	'plugins_loaded',
	function (): void {
		$container = get_container();

		if ( null !== $container ) {
			$container->init();
		}
	},
);

/**
 * Handle the plugin's activation hook
 */
register_activation_hook(
	__FILE__,
	function (): void {
		$container = get_container();

		if ( null !== $container ) {
			$container->on_activation();
		}
	},
);

/**
 * Handle the plugin's deactivation hook
 */
register_deactivation_hook(
	__FILE__,
	function (): void {
		$container = get_container();

		if ( null !== $container ) {
			$container->on_deactivation();
		}
	},
);
