<?php
/**
 * The "Apply on Link" extension post-meta settings
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Apply_On_Link;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Dependencies\Validatable_Fields;
use Teydea_Studio\Hiring_Hub\Job;

/**
 * The "Post_Meta" class
 */
final class Post_Meta extends Utils\Module {
	/**
	 * Post meta field key
	 *
	 * @var string
	 */
	const FIELD_KEY = 'apply_on';

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the post meta field.
		add_action( 'init', [ $this, 'register_post_meta_field' ] );

		// Engueue block editor assets.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
	}

	/**
	 * Register the post meta field
	 *
	 * @return void
	 */
	public function register_post_meta_field(): void {
		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		$job->get_post_meta()->register_post_meta_field(
			self::FIELD_KEY,
			new Validatable_Fields\Field_String(
				self::FIELD_KEY,
				'',
				null,

				/**
				 * Additional sanitization, specific for this field
				 *
				 * @param mixed $value Provided value.
				 *
				 * @return string Sanitized value.
				 */
				function ( $value ): string {
					$value = Utils\Type::ensure_string( $value );

					if ( '' === $value ) {
						return $value;
					}

					return is_email( $value )
						? sanitize_email( $value )
						: sanitize_url( $value );
				},
			),
		);
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		/** @var Utils\Screen $screen */
		$screen = $this->container->get_instance_of( 'screen' );

		/** @var Job $job */
		$job = $this->container->get_instance_of( 'job' );

		// Ensure we load custom assets for custom post type only.
		if ( ! $screen->is_block_editor_and_post_type( $job->get_post_type() ) ) {
			return;
		}

		/** @var Utils\Asset $asset */
		$asset = $this->container->get_instance_of( 'asset', [ 'extensions-apply-on-link-post-meta' ] );
		$asset->enqueue_script();
	}
}
