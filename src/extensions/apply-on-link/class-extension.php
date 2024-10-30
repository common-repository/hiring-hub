<?php
/**
 * The "Apply on Link" extension
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub\Extensions\Apply_On_Link;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;
use Teydea_Studio\Hiring_Hub\Job;

/**
 * The "Extension" class
 */
final class Extension extends Utils\Module {
	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register(): void {
		// Register the block.
		( new Block_Apply_On_Link( $this->container ) )->register();

		// Register the Post Meta management module.
		( new Post_Meta( $this->container ) )->register();

		// Filter the schema markup elements.
		add_filter( 'hiring_hub__schema_elements', [ $this, 'filter_schema_elements' ], 10, 2 );
	}

	/**
	 * Filter the schema markup elements
	 *
	 * @param array<string,mixed> $elements Schema markup elements.
	 * @param Job                 $job      Job object.
	 *
	 * @return array<string,mixed> Updated schema markup elements.
	 */
	public function filter_schema_elements( array $elements, object $job ): array {
		$apply_on_link = $job->get_meta( Post_Meta::FIELD_KEY );

		/**
		 * "Indicates whether an url that is associated with a JobPosting enables direct application for the job, via the posting website".
		 *
		 * @see https://schema.org/directApply
		 */
		$elements['directApply'] = is_string( $apply_on_link ) && '' !== $apply_on_link;
		return $elements;
	}
}
