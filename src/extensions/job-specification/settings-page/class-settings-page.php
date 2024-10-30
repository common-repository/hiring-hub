<?php
/**
 * The "Job Specification" extension settings
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Job_Specification;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Settings_Page" class
 */
final class Settings_Page extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Enqueue required scripts and styles.
		add_action( 'hiring_hub__enqueue_settings_page_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts for a settings page
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'extensions-job-specification-settings-page' ] );
		$asset->enqueue_script();
	}
}
