<?php
/**
 * Service request page content.
 *
 * @package DealerluxUtils
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

return <<<'HTML'
<!-- wp:heading -->
<h2 class="wp-block-heading">CTA Forms</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[dl_dump_forms style="cta"]
<!-- /wp:shortcode -->
HTML;