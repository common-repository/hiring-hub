<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- wp:hiring-hub/query-loop-filtering-container {"align":"wide","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
<form class="wp-block-hiring-hub-query-loop-filtering-container alignwide" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:hiring-hub/query-loop-filtering-search-field {"style":{"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10","left":"var:preset|spacing|10","right":"var:preset|spacing|10"}},"border":{"radius":"4px","color":"#b5b5b5","width":"1px"}},"fontSize":"medium"} -->
<input class="wp-block-hiring-hub-query-loop-filtering-search-field has-border-color has-medium-font-size" style="border-color:#b5b5b5;border-width:1px;border-radius:4px;padding-top:var(--wp--preset--spacing--10);padding-right:var(--wp--preset--spacing--10);padding-bottom:var(--wp--preset--spacing--10);padding-left:var(--wp--preset--spacing--10)" type="text" placeholder="<?php echo esc_attr__( 'Search by job title or description', 'hiring-hub' ); ?>" name="hiring-hub-qlff[s]"/>
<!-- /wp:hiring-hub/query-loop-filtering-search-field -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group"><!-- wp:hiring-hub/query-loop-filtering-filter /-->

<!-- wp:hiring-hub/query-loop-filtering-filter {"filterToDisplay":"job-specification-characteristics"} /-->

<!-- wp:hiring-hub/query-loop-filtering-filter {"filterToDisplay":"d:00000000000000001"} /-->

<!-- wp:hiring-hub/query-loop-filtering-filter {"filterToDisplay":"d:00000000000000002"} /-->

<!-- wp:hiring-hub/query-loop-filtering-filter {"filterToDisplay":"d:00000000000000004"} /--></div>
<!-- /wp:group -->

<!-- wp:hiring-hub/query-loop-filtering-button {"style":{"layout":{"selfStretch":"fit","flexSize":null}}} -->
<button type="submit" class="wp-block-hiring-hub-query-loop-filtering-button wp-element-button"><span><?php echo esc_html__( 'Search', 'hiring-hub' ); ?></span></button>
<!-- /wp:hiring-hub/query-loop-filtering-button --></div>
<!-- /wp:group --></form>
<!-- /wp:hiring-hub/query-loop-filtering-container -->
