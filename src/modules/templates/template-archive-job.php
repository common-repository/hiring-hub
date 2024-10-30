<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- wp:template-part {"slug":"header","tagName":"header","area":"header"} /-->

<!-- wp:group {"tagName":"main","align":"full","layout":{"type":"constrained"}} -->
<main class="wp-block-group alignfull"><!-- wp:query-title {"type":"archive","align":"wide","style":{"typography":{"lineHeight":"1"},"spacing":{"padding":{"top":"var:preset|spacing|50"}}}} /-->

<?php include $this->container->get_path_to( 'block-patterns/pattern-hiring-hub-search-and-filtering.php' ); // @phpstan-ignore-line ?>

<?php include $this->container->get_path_to( 'block-patterns/pattern-hiring-hub-archives.php' ); // @phpstan-ignore-line ?>
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer","area":"footer"} /-->
