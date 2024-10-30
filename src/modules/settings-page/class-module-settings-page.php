<?php
/**
 * Plugin settings page
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;
use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;

/**
 * The "Module_Settings_Page" class
 */
final class Module_Settings_Page extends Universal_Modules\Module_Settings_Page {
	/**
	 * Construct the module object
	 *
	 * @param Utils\Container $container Container instance.
	 */
	public function __construct( object $container ) {
		$this->container = $container;

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Define the settings page parent slug for single site installations.
		$this->parent_slug = sprintf( 'edit.php?post_type=%s', $job->get_post_type() );

		// Define the menu title.
		$this->menu_title = __( 'Settings', 'hiring-hub' );

		// Define the page title.
		$this->page_title = __( 'Hiring Hub', 'hiring-hub' );

		// Define the list of help & support links.
		$this->help_links = [
			[
				'url'   => sprintf( 'https://wordpress.org/support/plugin/%s/', $this->container->get_slug() ),
				'title' => __( 'Support forum', 'hiring-hub' ),
			],
			[
				'url'   => 'mailto:hello@teydeastudio.com',
				'title' => __( 'Contact email', 'hiring-hub' ),
			],
			[
				'url'   => sprintf( 'https://wordpress.org/plugins/%s/', $this->container->get_slug() ),
				'title' => __( 'Plugin on WordPress.org directory', 'hiring-hub' ),
			],
			[
				'url'   => 'https://teydeastudio.com/products/hiring-hub/?utm_source=Hiring+Hub&utm_medium=Plugin&utm_campaign=Plugin+research&utm_content=Settings+sidebar',
				'title' => __( 'Plugin on TeydeaStudio.com', 'hiring-hub' ),
			],
		];
	}

	/**
	 * Check if the page requested is a settings page
	 *
	 * @return bool Whether the page requested is a settings page or not.
	 */
	public function is_settings_page(): bool {
		/** @var Utils\Screen $screen */
		$screen = $this->container->get_instance_of( 'screen' );

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Check if the page requested is a Job's plugin' settings page.
		return $screen->is( $this->get_page_slug(), sprintf( '%s_page', $job->get_post_type() ) );
	}
}
