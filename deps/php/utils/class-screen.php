<?php
/**
 * Screen class
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Utils
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Utils;

use WP_Screen;

/**
 * The "Screen" class
 */
final class Screen {
	/**
	 * Container instance
	 *
	 * @var Container
	 */
	protected object $container;

	/**
	 * Current screen
	 *
	 * @var ?WP_Screen
	 */
	protected ?WP_Screen $current_screen = null;

	/**
	 * Constructor
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( object $container ) {
		$this->container      = $container;
		$this->current_screen = get_current_screen();
	}

	/**
	 * Check if a given screen is currently active
	 *
	 * @param string $screen_key Screen key to check.
	 * @param string $type       Type of the screen.
	 *
	 * @return bool Whether the screen with a given key is active or not.
	 */
	public function is( string $screen_key, string $type = 'settings_page' ): bool {
		if ( ! $this->current_screen instanceof WP_Screen ) {
			return false;
		}

		$valid_ids = [ sprintf( '%1$s_%2$s', $type, $screen_key ) ];

		if ( 'settings_page' === $type && $this->container->is_network_enabled() ) {
			$valid_ids[] = sprintf( '%1$s_%2$s-network', $type, $screen_key );
		}

		return in_array( $this->current_screen->id, $valid_ids, true );
	}

	/**
	 * Check if the current screen is a block editor
	 * for a specific post type
	 *
	 * @param string $post_type Expected post type.
	 *
	 * @return bool Whether the current screen is a block editor for a specific post type, or not.
	 */
	public function is_block_editor_and_post_type( string $post_type ): bool {
		if ( ! $this->current_screen instanceof WP_Screen ) {
			return false;
		}

		return $this->current_screen->is_block_editor && $post_type === $this->current_screen->post_type;
	}
}
