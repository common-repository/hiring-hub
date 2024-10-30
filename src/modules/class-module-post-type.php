<?php
/**
 * Class responsible for registering a custom post type
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Modules;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use Teydea_Studio\Hiring_Hub\Job;
use Teydea_Studio\Hiring_Hub\Settings;
use WP_Post_Type;
use WP_Role;

/**
 * The "Module_Post_Type" class
 */
final class Module_Post_Type extends Utils\Module {
	/**
	 * List of capabilities to grant to the administrator user role
	 *
	 * @var string[]
	 */
	const ADMINISTRATOR_CAPABILITIES = [
		'delete_jobs',
		'delete_others_jobs',
		'delete_private_jobs',
		'delete_published_jobs',
		'edit_jobs',
		'edit_others_jobs',
		'edit_private_jobs',
		'edit_published_jobs',
		'publish_jobs',
		'read_private_jobs',
	];

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the custom post type.
		add_action( 'init', [ $this, 'register_post_type' ] );

		// Maybe flush rewrite rules if the plugin's specific settings changes.
		add_action( 'hiring_hub__settings_updated', [ $this, 'maybe_flush_rewrite_rules_on_plugin_settings_change' ], 10, 3 );
	}

	/**
	 * Add capabilities to user roles on container activation
	 *
	 * @return void
	 */
	public function on_container_activation(): void {
		$role = get_role( 'administrator' );

		if ( $role instanceof WP_Role ) {
			foreach ( self::ADMINISTRATOR_CAPABILITIES as $capability ) {
				$role->add_cap( $capability );
			}
		}
	}

	/**
	 * Remove capabilities from user roles on container deactivation
	 *
	 * @return void
	 */
	public function on_container_deactivation(): void {
		$role = get_role( 'administrator' );

		if ( $role instanceof WP_Role ) {
			foreach ( self::ADMINISTRATOR_CAPABILITIES as $capability ) {
				$role->remove_cap( $capability );
			}
		}
	}

	/**
	 * Register the custom post type
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		/** @var Settings $settings */
		$settings = $this->container->get_instance_of( 'settings' );
		$general  = $settings->get_fields_group( 'general' );

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Default values.
		$archive_slug = $settings::DEFAULT_ARCHIVE_SLUG;
		$post_slug    = $settings::DEFAULT_POST_SLUG;

		if ( null !== $general ) {
			/** @var string $archive_slug */
			$archive_slug = $general->get_field_value( 'archive_slug' ) ?? $archive_slug;

			/** @var string $post_slug */
			$post_slug = $general->get_field_value( 'post_slug' ) ?? $post_slug;
		}

		// Define the labels.
		$labels = [
			'add_new'               => __( 'Add New Job', 'hiring-hub' ),
			'add_new_item'          => __( 'Add New Job', 'hiring-hub' ),
			'all_items'             => __( 'All Jobs', 'hiring-hub' ),
			'archives'              => _x( 'Job archives', 'The post type archive label used in nav menus. Default "Post Archives". Added in 4.4', 'hiring-hub' ),
			'edit_item'             => __( 'Edit Job', 'hiring-hub' ),
			'filter_items_list'     => _x( 'Filter jobs list', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list". Added in 4.4', 'hiring-hub' ),
			'insert_into_item'      => _x( 'Insert into job', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post). Added in 4.4', 'hiring-hub' ),
			'items_list'            => _x( 'Jobs list', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list". Added in 4.4', 'hiring-hub' ),
			'items_list_navigation' => _x( 'Jobs list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation". Added in 4.4', 'hiring-hub' ),
			'menu_name'             => _x( 'Hiring Hub', 'Admin Menu text', 'hiring-hub' ),
			'name'                  => _x( 'Jobs', 'Post type general name', 'hiring-hub' ),
			'name_admin_bar'        => _x( 'Job', 'Add New on Toolbar', 'hiring-hub' ),
			'new_item'              => __( 'New Job', 'hiring-hub' ),
			'not_found'             => __( 'No jobs found.', 'hiring-hub' ),
			'not_found_in_trash'    => __( 'No jobs found in Trash.', 'hiring-hub' ),
			'parent_item_colon'     => __( 'Parent Jobs:', 'hiring-hub' ),
			'search_items'          => __( 'Search Jobs', 'hiring-hub' ),
			'singular_name'         => _x( 'Job', 'Post type singular name', 'hiring-hub' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this job', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post). Added in 4.4', 'hiring-hub' ),
			'view_item'             => __( 'View Job', 'hiring-hub' ),
			'view_items'            => __( 'View Jobs', 'hiring-hub' ),
		];

		register_post_type(
			$job->get_post_type(),
			[
				'capability_type'     => $job->get_post_type(),
				'delete_with_user'    => true,
				'description'         => __( 'This is where you can create and manage a single job.', 'hiring-hub' ),
				'exclude_from_search' => false,
				'has_archive'         => $archive_slug,
				'hierarchical'        => false,
				'labels'              => $labels,
				'map_meta_cap'        => true,
				'menu_icon'           => sprintf(
					'data:image/svg+xml;base64,%s',
					base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="black" d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0ZM5.385 17.083v-.93a3.85 3.85 0 0 1 3.846-3.845h1.538a3.85 3.85 0 0 1 3.846 3.846v.93A8.401 8.401 0 0 1 10 18.461a8.408 8.408 0 0 1-4.615-1.379ZM10 10a2.31 2.31 0 0 1-2.308-2.308A2.31 2.31 0 0 1 10 5.385a2.31 2.31 0 0 1 2.308 2.307A2.31 2.31 0 0 1 10 10Zm6.136 5.81a5.388 5.388 0 0 0-4.08-4.879 3.838 3.838 0 0 0 1.79-3.238A3.85 3.85 0 0 0 10 3.847a3.85 3.85 0 0 0-3.846 3.846 3.839 3.839 0 0 0 1.79 3.239 5.388 5.388 0 0 0-4.08 4.877A8.423 8.423 0 0 1 1.538 10c0-4.665 3.797-8.462 8.462-8.462 4.665 0 8.462 3.797 8.462 8.462 0 2.25-.889 4.291-2.326 5.81Z"/></svg>' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'query_var'           => true,
				'rewrite'             => [
					'slug' => $post_slug,
				],
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'show_ui'             => true,
				'supports'            => [
					'author',
					'custom-fields',
					'editor',
					'thumbnail',
					'title',
				],
				'template'            => $this->get_post_template(),
			],
		);
	}

	/**
	 * Get the post template
	 *
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/
	 *
	 * @return array<mixed> Post template.
	 */
	public function get_post_template(): array {
		/**
		 * Filter the default post template
		 *
		 * @param array<mixed> $template Post template.
		 */
		return apply_filters(
			'hiring_hub__job_template',
			[
				[
					'core/columns',
					[
						'align' => 'wide',
						'style' => [
							'spacing' => [
								'blockGap' => [
									'left' => 'var:preset|spacing|50',
								],
							],
						],
					],
					[
						[
							'core/column',
							[ 'width' => '66.66%' ],
							[
								[
									'core/heading',
									[ 'placeholder' => __( 'What you will do:', 'hiring-hub' ) ],
								],
								[
									'core/paragraph',
									[ 'placeholder' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc quam dolor, posuere et tellus eu, venenatis consequat nisl. Sed vel urna tortor. Pellentesque a consectetur elit. Nam non congue tellus, eget ultricies erat. Morbi sit amet dolor nec odio gravida tempor at et ligula.' ],
								],
								[ 'hiring-hub/apply-on-link' ],
							],
						],
						[
							'core/column',
							[ 'width' => '33.33%' ],
							[
								[
									'core/group',
									[
										'style' => [
											'position' => [
												'type' => 'sticky',
												'top'  => '2rem',
											],
										],
									],
									[
										[ 'hiring-hub/job-specification-list' ],
									],
								],
							],
						],
					],
				],
			],
		);
	}

	/**
	 * Maybe flush rewrite rules if the plugin's specific settings changes.
	 *
	 * @param array<string,mixed> $data     Updated plugin data.
	 * @param array<string,mixed> $old_data Plugin data prior to the recent update.
	 * @param Settings            $settings Settings class instance.
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules_on_plugin_settings_change( array $data, array $old_data, object $settings ): void {
		$archive_slug_field_key = null;
		$post_slug_field_key    = null;

		$general = $settings->get_fields_group( 'general' );

		if ( null !== $general ) {
			// Get the fields.
			$archive_slug_field = $general->get_field( 'archive_slug' );
			$post_slug_field    = $general->get_field( 'post_slug' );

			// Get the field keys.
			$archive_slug_field_key = null !== $archive_slug_field ? $archive_slug_field->get_key_camel_case() : $archive_slug_field_key;
			$post_slug_field_key    = null !== $post_slug_field ? $post_slug_field->get_key_camel_case() : $post_slug_field_key;
		}

		/** @var array{archiveSlug?:string} $new_data */
		$new_data = $data['general'] ?? [];

		/** @var array{archiveSlug?:string} $old_data */
		$old_data = $old_data['general'] ?? [];

		/** @var string $new_archive_slug */
		$new_archive_slug = ( null !== $archive_slug_field_key && isset( $new_data[ $archive_slug_field_key ] ) ) ? $new_data[ $archive_slug_field_key ] : $settings::DEFAULT_ARCHIVE_SLUG;
		$old_archive_slug = ( null !== $archive_slug_field_key && isset( $old_data[ $archive_slug_field_key ] ) ) ? $old_data[ $archive_slug_field_key ] : $settings::DEFAULT_ARCHIVE_SLUG;

		/** @var string $new_post_slug */
		$new_post_slug = ( null !== $post_slug_field_key && isset( $new_data[ $post_slug_field_key ] ) ) ? $new_data[ $post_slug_field_key ] : $settings::DEFAULT_POST_SLUG;
		$old_post_slug = ( null !== $post_slug_field_key && isset( $old_data[ $post_slug_field_key ] ) ) ? $old_data[ $post_slug_field_key ] : $settings::DEFAULT_POST_SLUG;

		if ( $new_archive_slug !== $old_archive_slug || $new_post_slug !== $old_post_slug ) {
			global $wp_post_types;

			/** @var Job $job */
			$job       = $this->container->get_instance_of( 'job' );
			$post_type = $job->get_post_type();

			if ( ! isset( $wp_post_types[ $post_type ] ) || ! $wp_post_types[ $post_type ] instanceof WP_Post_Type ) {
				return;
			}

			$wp_post_types[ $post_type ]->remove_rewrite_rules();
			$wp_post_types[ $post_type ]->has_archive     = $new_archive_slug;
			$wp_post_types[ $post_type ]->rewrite['slug'] = $new_post_slug; // @phpstan-ignore-line
			$wp_post_types[ $post_type ]->add_rewrite_rules();

			flush_rewrite_rules();
		}
	}
}
