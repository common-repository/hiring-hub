<?php
/**
 * Extensions class
 * - load extensions
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Extensions\Apply_On_Link\Extension as Extension_Apply_On_Link;
use Teydea_Studio\Hiring_Hub\Extensions\Job_Specification\Extension as Extension_Job_Specification;
use Teydea_Studio\Hiring_Hub\Extensions\Query_Loop_Filtering\Extension as Extension_Query_Loop_Filtering;

/**
 * The "Module_Extensions" class
 */
final class Module_Extensions extends Utils\Module {
	/**
	 * List of extensions
	 *
	 * @var string[]
	 */
	protected $extensions = [
		Extension_Apply_On_Link::class,
		Extension_Job_Specification::class,
		Extension_Query_Loop_Filtering::class,
	];

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( $this->extensions as $extension ) {
			/** @var Utils\Module $instance */
			$instance = new $extension( $this->container );
			$instance->register();
		}
	}
}
