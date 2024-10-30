<?php
/**
 * Plugin settings page
 *
 * @package Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules
 */

namespace Teydea_Studio\Hiring_Hub\Dependencies\Universal_Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Module_Settings_Page" class
 */
class Module_Settings_Page extends Utils\Module {
	/**
	 * Option key for the onboarding initiation
	 *
	 * @var string
	 */
	const OPTION_KEY__SHOULD_INITIATE_ONBOARDING = 'hiring_hub__should_initiate_onboarding';

	/**
	 * Settings page title in the admin menu context
	 *
	 * @var ?string
	 */
	protected ?string $menu_title = null;

	/**
	 * Settings page title
	 *
	 * @var ?string
	 */
	protected ?string $page_title = null;

	/**
	 * Settings page parent slug for single site installations
	 *
	 * @var string
	 */
	protected string $parent_slug = 'options-general.php';

	/**
	 * Settings page parent slug for network site installations
	 *
	 * @var string
	 */
	protected string $network_parent_slug = 'settings.php';

	/**
	 * Help & support links rendered on the settings page sidebar panel
	 *
	 * @var array<int,array{title:string,url:string}>
	 */
	protected array $help_links = [];

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Define the page title if not provided.
		if ( null === $this->page_title ) {
			$this->page_title = __( 'Settings', 'hiring-hub' );
		}

		// Define the menu title if not provided.
		if ( null === $this->menu_title ) {
			$this->menu_title = $this->page_title;
		}

		// Register settings pages.
		add_action( 'network_admin_menu', [ $this, 'register_settings_page' ] );
		add_action( 'admin_menu', [ $this, 'register_settings_page' ] );

		// Filter the plugin action links.
		add_filter( sprintf( 'network_admin_plugin_action_links_%s', $this->container->get_basename() ), [ $this, 'filter_plugin_action_links' ] );
		add_filter( sprintf( 'plugin_action_links_%s', $this->container->get_basename() ), [ $this, 'filter_plugin_action_links' ] );

		// Maybe redirect user to the plugin settings screen.
		add_action( 'admin_init', [ $this, 'maybe_redirect_after_plugin_activation' ] );

		// Enqueue required scripts and styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Filter the body classes in admin settings page.
		add_filter( 'admin_body_class', [ $this, 'filter_admin_body_class' ] );
	}

	/**
	 * Insert the option flag to redirect the user who has activated
	 * the plugin, to the plugin settings page
	 *
	 * @return void
	 */
	public function on_container_activation(): void {
		// If plugin is activated in network admin options, skip redirect.
		if ( is_network_admin() ) {
			return;
		}

		// Skip redirect if WP_DEBUG is enabled, for the engineers convenience.
		if ( Utils\Environment::is_debug_mode() && ! Utils\Environment::is_local_dev_environment() ) {
			return;
		}

		// Skip redirect if activating plugin through a WP-CLI command.
		if ( Utils\Environment::is_wp_cli_request() ) {
			return;
		}

		// Don't do redirects when multiple plugins are bulk activated.
		if (
			( isset( $_REQUEST['action'] ) && 'activate-selected' === $_REQUEST['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- just checking the admin action.
			&&
			( isset( $_REQUEST['checked'] ) && count( $_REQUEST['checked'] ) > 1 ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- just checking the admin action.
		) {
			return;
		}

		/** @var Utils\User $user */
		$user    = $this->container->get_instance_of( 'user' );
		$user_id = $user->get_user_id();

		if ( null !== $user_id ) {
			add_option( self::OPTION_KEY__SHOULD_INITIATE_ONBOARDING, $user_id );
		}
	}

	/**
	 * Get the settings page slug
	 *
	 * @return string The settings page slug.
	 */
	protected function get_page_slug(): string {
		return sprintf( '%s-settings-page', $this->container->get_slug() );
	}

	/**
	 * Check if the page requested is a settings page
	 *
	 * @return bool Whether the page requested is a settings page or not.
	 */
	public function is_settings_page(): bool {
		/** @var Utils\Screen $screen */
		$screen = $this->container->get_instance_of( 'screen' );

		// Check if the page requested is a settings page.
		return $screen->is( $this->get_page_slug() );
	}

	/**
	 * Register settings page
	 *
	 * @return void
	 */
	public function register_settings_page(): void {
		// Only register the settings page if titles are defined.
		if ( null === $this->page_title || null === $this->menu_title ) {
			return;
		}

		if ( true === $this->container->is_network_enabled() ) {
			$parent_slug = $this->network_parent_slug;
			$capability  = 'manage_network_options';
		} else {
			$parent_slug = $this->parent_slug;
			$capability  = 'manage_options';
		}

		add_submenu_page(
			$parent_slug,
			$this->page_title,
			$this->menu_title,
			$capability,
			$this->get_page_slug(),
			[ $this, 'render_page' ],
		);
	}

	/**
	 * Filter the plugin action links
	 *
	 * @param array<string,string> $actions An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'. With Multisite active this can also include 'network_active' and 'network_only' items.
	 *
	 * @return array<string,string> Updated array of plugin action links.
	 */
	public function filter_plugin_action_links( array $actions ): array {
		/** @var Utils\User $user */
		$user = $this->container->get_instance_of( 'user' );

		if ( $user->has_managing_permissions() ) {
			$settings_link = $this->container->is_network_enabled()
				? network_admin_url(
					add_query_arg(
						[ 'page' => $this->get_page_slug() ],
						$this->network_parent_slug,
					),
				)
				: admin_url(
					add_query_arg(
						[ 'page' => $this->get_page_slug() ],
						$this->parent_slug,
					),
				);

			$actions = array_merge(
				[
					'settings' => sprintf(
						'<a href="%1$s">%2$s</a>',
						$settings_link,
						__( 'Settings', 'hiring-hub' ),
					),
				],
				$actions,
			);
		}

		return $actions;
	}

	/**
	 * Maybe redirect user to the plugin settings screen
	 *
	 * @return void
	 */
	public function maybe_redirect_after_plugin_activation(): void {
		// Get the ID of the user who has initiated the plugin activation.
		$user_id = get_option( self::OPTION_KEY__SHOULD_INITIATE_ONBOARDING, null );

		if ( null === $user_id ) {
			return;
		}

		/** @var Utils\User $user */
		$user = $this->container->get_instance_of( 'user' );

		// Only proceed further if processing request from the same user.
		if ( absint( $user_id ) !== $user->get_user_id() ) {
			return;
		}

		// Ensure the environment dependencies match.
		if ( ! is_admin() || Utils\Environment::is_cron_request() || Utils\Environment::is_ajax_request() || Utils\Environment::is_wp_cli_request() ) {
			return;
		}

		// Delete option so the redirection only happens once.
		delete_option( self::OPTION_KEY__SHOULD_INITIATE_ONBOARDING );

		// No need to worry about the network URL as we skip this action for the network admins.
		wp_safe_redirect(
			admin_url(
				add_query_arg(
					[ 'page' => $this->get_page_slug() ],
					$this->parent_slug,
				),
			),
		);

		exit;
	}

	/**
	 * Enqueue required scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		if ( ! $this->is_settings_page() ) {
			return;
		}

		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'settings-page' ] );

		/** @var Utils\Nonce $nonce */
		$nonce = $this->container->get_instance_of( 'nonce', [ 'save_settings' ] );

		$asset->enqueue_style( [ 'wp-components' ] );
		$asset->enqueue_script(
			true,
			/**
			 * Allow other plugins to filter inline data passed
			 * to the settings page script
			 *
			 * @param array<string,mixed> $data Inline data array.
			 */
			apply_filters(
				'hiring_hub__settings_page_inline_data',
				[
					'nonce'     => $nonce->create(),
					'pageTitle' => $this->page_title,
					'helpLinks' => $this->help_links,
				],
			),
		);

		/**
		 * Allow other plugins and modules to load their scripts
		 * on a plugin settings page
		 */
		do_action( 'hiring_hub__enqueue_settings_page_scripts' );
	}

	/**
	 * Filter the body classes in admin settings page.
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 */
	public function filter_admin_body_class( string $classes ): string {
		$class_name = ' teydeastudio-settings-page ';

		if ( $this->is_settings_page() && ! Utils\Strings::str_contains( $classes, $class_name ) ) {
			$classes .= $class_name;
		}

		return $classes;
	}

	/**
	 * Render page
	 *
	 * @return void
	 */
	public function render_page(): void {
		?>
		<div id="<?php echo esc_attr( $this->get_page_slug() ); ?>"></div>
		<?php
	}
}
