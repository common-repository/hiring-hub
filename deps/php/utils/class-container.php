<?php
/**
 * Container controller class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Container" class
 *
 * @phpstan-type Type_Instance_Data array{caller:string,class:?string,call_with:'self'|'enhancer',enhancer:?Container}
 */
abstract class Container {
	/**
	 * Data prefix for the container settings
	 *
	 * @var string
	 */
	protected string $data_prefix;

	/**
	 * Keys under which container data are stored
	 *
	 * @var array<string,string[]>
	 */
	protected array $data_keys = [];

	/**
	 * Slug of the container enhanced by this container
	 *
	 * @var ?string
	 */
	protected ?string $enhances = null;

	/**
	 * Array of instantiable classes
	 *
	 * @var array<string,string>
	 */
	protected array $instantiable_classes = [];

	/**
	 * Main directory of the container
	 *
	 * @var string
	 */
	protected string $main_dir;

	/**
	 * Array of modules to register
	 *
	 * @var string[]
	 */
	protected array $modules = [];

	/**
	 * Container's name
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Container's slug
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Whether the container can be network-enabled or not
	 *
	 * @var bool
	 */
	protected bool $supports_network = false;

	/**
	 * Container's version
	 *
	 * @var string
	 */
	protected string $version;

	/**
	 * Container type
	 *
	 * @var string
	 */
	protected string $type;

	/**
	 * Main file path
	 *
	 * @var string
	 */
	protected string $main_file = '';

	/**
	 * Instances of the container modules
	 *
	 * @var array<string,Module>
	 */
	protected array $instances = [];

	/**
	 * Set the data prefix for the container settings
	 *
	 * @param string $data_prefix Data prefix for the container settings.
	 *
	 * @return void
	 */
	public function set_data_prefix( string $data_prefix ): void {
		$this->data_prefix = $data_prefix;
	}

	/**
	 * Set keys under which container data are stored
	 *
	 * @param array<string,string[]> $data_keys Keys under which container data are stored.
	 *
	 * @return void
	 */
	public function set_data_keys( array $data_keys ): void {
		$this->data_keys = $data_keys;
	}

	/**
	 * Set slug of the container enhanced by this container
	 *
	 * @param string $enhances Slug of the container enhanced by this container.
	 *
	 * @return void
	 */
	public function set_enhances( string $enhances ): void {
		$this->enhances = $enhances;
	}

	/**
	 * Set array of instantiable classes
	 *
	 * @param array<string,string> $instantiable_classes Array of instantiable classes.
	 *
	 * @return void
	 */
	public function set_instantiable_classes( array $instantiable_classes ): void {
		$this->instantiable_classes = $instantiable_classes;
	}

	/**
	 * Set main directory of the container
	 *
	 * @param string $main_dir Main directory of the container.
	 *
	 * @return void
	 */
	public function set_main_dir( string $main_dir ): void {
		$this->main_dir = $main_dir;
	}

	/**
	 * Set array of modules to register
	 *
	 * @param string[] $modules Array of modules to register.
	 *
	 * @return void
	 */
	public function set_modules( array $modules ): void {
		$this->modules = $modules;
	}

	/**
	 * Set container's name
	 *
	 * @param string $name Container's name.
	 *
	 * @return void
	 */
	public function set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Set container's slug
	 *
	 * @param string $slug Container's slug.
	 *
	 * @return void
	 */
	public function set_slug( string $slug ): void {
		$this->slug = $slug;
	}

	/**
	 * Set whether the container can be network-enabled
	 *
	 * @param bool $supports_network Whether the container can be network-enabled.
	 *
	 * @return void
	 */
	public function set_supports_network( bool $supports_network ): void {
		$this->supports_network = $supports_network;
	}

	/**
	 * Set container's version
	 *
	 * @param string $version Container's version.
	 *
	 * @return void
	 */
	public function set_version( string $version ): void {
		$this->version = $version;
	}

	/**
	 * Run a given method on all container modules
	 *
	 * @param string $method Method to call on all modules.
	 *
	 * @return void
	 */
	protected function for_all_modules( string $method ): void {
		foreach ( $this->modules as $module ) {
			if ( ! isset( $this->instances[ $module ] ) ) {
				/** @var Module $instance */
				$instance                   = new $module( $this );
				$this->instances[ $module ] = $instance;
			}

			if ( method_exists( $this->instances[ $module ], $method ) ) {
				$this->instances[ $module ]->$method();
			}
		}
	}

	/**
	 * Initialize the container
	 *
	 * @return void
	 */
	public function init(): void {
		// Register modules.
		$this->for_all_modules( 'register' );

		// Filter self instantiable classes.
		foreach ( $this->instantiable_classes as $instantiable_class_key => $instantiable_class ) {
			add_filter(
				sprintf(
					'hiring_hub__instantiable_class__%s',
					Strings::to_snake_case( $instantiable_class_key ),
				),

				/**
				 * Filter the instance data
				 *
				 * @param Type_Instance_Data $data Instance data.
				 *
				 * @return Type_Instance_Data Updated instance data.
				 */
				function ( $data ) use ( $instantiable_class ): array {
					if ( $data['class'] === $instantiable_class ) {
						return $data;
					}

					$data['class']     = $instantiable_class;
					$data['call_with'] = 'self';

					return $data;
				},
			);
		}

		/**
		 * Filter instantiable classes of the enhanced container
		 */
		if ( null !== $this->enhances ) {
			foreach ( $this->instantiable_classes as $instantiable_class_key => $instantiable_class ) {
				add_filter(
					sprintf(
						'%1$s__instantiable_class__%2$s',
						Strings::to_snake_case( $this->enhances ),
						Strings::to_snake_case( $instantiable_class_key ),
					),

					/**
					 * Filter the instance data
					 *
					 * @param Type_Instance_Data $data Instance data.
					 *
					 * @return Type_Instance_Data Updated instance data.
					 */
					function ( $data ) use ( $instantiable_class ): array {
						if ( $data['class'] === $instantiable_class || ( null === $data['class'] && $data['caller'] === $this->enhances ) ) {
							return $data;
						}

						$data['class']     = $instantiable_class;
						$data['call_with'] = $data['caller'] === $this->slug ? 'self' : 'enhancer';

						if ( 'enhancer' === $data['call_with'] && null === $data['enhancer'] ) {
							$data['enhancer'] = $this;
						}

						return $data;
					},
					15,
				);
			}
		}
	}

	/**
	 * Container uninstall hook
	 *
	 * @return void
	 */
	public function uninstall(): void {}

	/**
	 * Get the data prefix for the container settings
	 *
	 * @return string Data prefix for the container settings.
	 */
	public function get_data_prefix(): string {
		return $this->data_prefix;
	}

	/**
	 * Get instance of the instantiable class
	 *
	 * @param string       $class_key Key of the instantiable class.
	 * @param array<mixed> $params    Params to pass to the class being instantiated.
	 *
	 * @return ?object Object of the instantiated class, null if class not found.
	 */
	public function get_instance_of( string $class_key, array $params = [] ): ?object {
		$data = [
			'caller'    => $this->slug,
			'class'     => $this->instantiable_classes[ $class_key ] ?? null,
			'call_with' => 'self',
			'enhancer'  => null,
		];

		if ( null === $this->enhances ) {
			/**
			 * Filter the instance data
			 *
			 * @param Type_Instance_Data $data Instance data.
			 */
			$data = apply_filters(
				sprintf( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
					'hiring_hub__instantiable_class__%s',
					Strings::to_snake_case( $class_key ),
				),
				$data,
			);
		} else {
			/**
			 * Filter the instance data
			 *
			 * @param Type_Instance_Data $data Instance data.
			 */
			$data = apply_filters(
				sprintf( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
					'%1$s__instantiable_class__%2$s',
					Strings::to_snake_case( $this->enhances ),
					Strings::to_snake_case( $class_key ),
				),
				$data,
			);
		}

		if ( null === $data['class'] ) {
			return null;
		}

		$container = 'self' === $data['call_with'] ? $this : $data['enhancer'];

		if ( null === $container ) {
			return null;
		}

		return new $data['class']( $container, ...$params );
	}

	/**
	 * Get the container type
	 *
	 * @return string Container type.
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get the main directory of the container
	 *
	 * @return string Main directory of the container.
	 */
	public function get_main_dir(): string {
		return $this->main_dir;
	}

	/**
	 * Get the main file of the container
	 *
	 * @return string Main file of the container.
	 */
	public function get_main_file(): string {
		return $this->main_file;
	}

	/**
	 * Get the path to a file in the container directory
	 *
	 * @param string $file      File to get the path to.
	 * @param string $directory Directory the file is located at.
	 *
	 * @return string Path to the file in the container directory.
	 */
	public function get_path_to( string $file, string $directory = 'src/modules' ): string {
		return sprintf( '%1$s/%2$s/%3$s', $this->get_main_dir(), $directory, $file );
	}

	/**
	 * Get the path to the JSON file with metadata definition for the block
	 *
	 * @param string $block The block's slug.
	 *
	 * @return string The block path.
	 */
	public function get_block_path( string $block ): string {
		return sprintf( '%1$s/build/%2$s', $this->main_dir, $block );
	}

	/**
	 * Get the container's basename
	 *
	 * @return string Container's basename.
	 */
	public function get_basename(): string {
		return '';
	}

	/**
	 * Get the container's name
	 *
	 * @return string Container's name.
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the container's slug
	 *
	 * @return string Container's slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Get the container's version
	 *
	 * @return string container's version.
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Determine if a given container is network-enabled
	 *
	 * @return bool Boolean "true" if container is network-enabled, "false" otherwise.
	 */
	public function is_network_enabled(): bool {
		return false;
	}

	/**
	 * Run custom actions on each module during the container activation
	 *
	 * @return void
	 */
	public function on_activation(): void {
		$this->for_all_modules( 'on_container_activation' );
	}

	/**
	 * Run custom actions on each module during the container deactivation
	 *
	 * @return void
	 */
	public function on_deactivation(): void {
		$this->for_all_modules( 'on_container_deactivation' );
	}
}
