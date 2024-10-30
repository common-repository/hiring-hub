<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":"0","postType":"job","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"parents":[]},"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-query alignwide"><!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php echo esc_html__( 'No jobs were found.', 'hiring-hub' ); ?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:0;padding-bottom:var(--wp--preset--spacing--50);padding-left:0"><!-- wp:post-template {"align":"full","layout":{"type":"grid","columnCount":1}} -->
<!-- wp:group {"style":{"border":{"width":"1px","radius":"4px"},"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group" style="border-width:1px;border-radius:4px;padding-top:var(--wp--preset--spacing--10);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--10);padding-left:var(--wp--preset--spacing--20)"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"layout":{"selfStretch":"fill","flexSize":null}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:post-title {"isLink":true,"style":{"layout":{"flexSize":"min(2.5rem, 3vw)","selfStretch":"fixed"}},"fontSize":"large"} /-->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"5px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php echo esc_html__( 'Published:', 'hiring-hub' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:post-date {"format":"M j, Y","isLink":true} /--></div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|accent-5"}}}},"textColor":"accent-5","fontSize":"small"} -->
<p class="has-accent-5-color has-text-color has-link-color has-small-font-size">|</p>
<!-- /wp:paragraph -->

<!-- wp:hiring-hub/job-specification-characteristics {"fontSize":"small","layout":{"type":"flex","orientation":"horizontal","justifyContent":"left"}} -->
<ul class="wp-block-hiring-hub-job-specification-characteristics has-small-font-size"></ul>
<!-- /wp:hiring-hub/job-specification-characteristics --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:hiring-hub/apply-on-link -->
<div><a href="#" class="wp-block-hiring-hub-apply-on-link wp-element-button"><span><?php echo esc_html__( 'Apply now', 'hiring-hub' ); ?></span></a></div>
<!-- /wp:hiring-hub/apply-on-link --></div>
<!-- /wp:group -->

<!-- wp:separator {"backgroundColor":"accent-5","className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-accent-5-color has-alpha-channel-opacity has-accent-5-background-color has-background is-style-wide"/>
<!-- /wp:separator -->

<!-- wp:hiring-hub/job-specification-list {"showAllItems":false,"disallowedItems":["d:00000000000000005","d:00000000000000004","d:00000000000000003"],"fontSize":"small"} -->
<div class="wp-block-hiring-hub-job-specification-list has-small-font-size"></div>
<!-- /wp:hiring-hub/job-specification-list --></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:spacer {"height":"var:preset|spacing|40","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<div style="margin-top:0;margin-bottom:0;height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:group --></div>
<!-- /wp:query -->
