<?php
/**
 * Single job class
 *
 * @package Teydea_Studio\Hiring_Hub
 */

namespace Teydea_Studio\Hiring_Hub;

use Teydea_Studio\Hiring_Hub\Dependencies\Utils;

/**
 * The "Job" class
 */
class Job extends Utils\Post {
	/**
	 * Post type
	 *
	 * @var string
	 */
	protected string $post_type = 'job';

	/**
	 * Capability required to edit the single job
	 *
	 * @var string
	 */
	protected string $edit_capability = 'edit_jobs';
}
