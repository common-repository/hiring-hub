<?php
/**
 * Module class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module" class
 */
abstract class Module {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Construct the module object
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( object $container ) {
		$this->container = $container;
	}

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {}

	/**
	 * Run custom actions on container activation
	 *
	 * @return void
	 */
	public function on_container_activation(): void {}

	/**
	 * Run custom actions on container deactivation
	 *
	 * @return void
	 */
	public function on_container_deactivation(): void {}
}
