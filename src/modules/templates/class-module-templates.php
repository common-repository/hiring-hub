<?php
/**
 * Templates class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Templates" class
 */
final class Module_Templates extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Maybe register custom templates, if has not been registered yet.
		add_action( 'switch_theme', [ $this, 'maybe_register_custom_templates' ] );
	}

	/**
	 * On container activation, maybe register custom templates, if has not been registered yet
	 *
	 * @return void
	 */
	public function on_container_activation(): void {
		// Maybe register custom templates, if has not been registered yet.
		$this->maybe_register_custom_templates();
	}

	/**
	 * Maybe register custom templates, if has not been registered yet
	 *
	 * @return void
	 */
	public function maybe_register_custom_templates(): void {
		/**
		 * Allow other plugins and modules to filter whether the
		 * default archive template can be registered
		 *
		 * Themes might want to register their own templates and disable
		 * this feature through the filter below.
		 */
		$can_register = apply_filters( 'hiring_hub__can_register_job_archives_template', true );

		if ( ! $can_register ) {
			return;
		}

		/** @var Utils\Template $template */
		$template = $this->container->get_instance_of( 'template', [ 'archive-job' ] );

		if ( ! $template->exists() ) {
			$template->insert(
				__( 'Archive: Job', 'hiring-hub' ),
				__( 'Displays an archive with the latest jobs', 'hiring-hub' ),
				$this->container->get_path_to( 'templates/template-archive-job.php' ),
			);
		}
	}
}
