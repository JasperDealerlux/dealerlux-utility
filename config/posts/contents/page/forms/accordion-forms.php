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
<h2 class="wp-block-heading">Accordion Forms</h2>
<!-- /wp:heading -->
 
<!-- wp:shortcode -->
[dl_form_selector]
[dl_forms style="accordion"]
<!-- /wp:shortcode -->
HTML;