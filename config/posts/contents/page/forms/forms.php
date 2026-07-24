<?php
/**
 * DealerLux forms directory content.
 *
 * Displays links to the CTA Forms and Accordion Forms pages.
 *
 * @package DealerluxUtils
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$cta_forms_url = home_url(
	'/dealerlux-forms/cta-forms/'
);

$accordion_forms_url = home_url(
	'/dealerlux-forms/accordion-forms/'
);

return sprintf(
	'<!-- wp:group {"tagName":"section","align":"wide","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide">
	<!-- wp:heading {"textAlign":"center","level":1} -->
	<h1 class="wp-block-heading has-text-align-center">Forms Directory</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center">Choose a form style to preview the available forms.</p>
	<!-- /wp:paragraph -->

	<!-- wp:spacer {"height":"32px"} -->
	<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
	<!-- /wp:spacer -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"24px"}}}} -->
	<div class="wp-block-columns alignwide">

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"border":{"radius":"8px","width":"1px"},"spacing":{"padding":{"top":"40px","right":"32px","bottom":"40px","left":"32px"}}},"borderColor":"contrast-3","layout":{"type":"constrained"}} -->
			<div class="wp-block-group has-border-color has-contrast-3-border-color" style="border-width:1px;border-radius:8px;padding-top:40px;padding-right:32px;padding-bottom:40px;padding-left:32px">

				<!-- wp:heading {"textAlign":"center","level":2} -->
				<h2 class="wp-block-heading has-text-align-center">CTA Forms</h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center"} -->
				<p class="has-text-align-center">View forms designed with prominent calls to action.</p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
				<div class="wp-block-buttons">
					<!-- wp:button -->
					<div class="wp-block-button">
						<a class="wp-block-button__link wp-element-button" href="%1$s">View CTA Forms</a>
					</div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"border":{"radius":"8px","width":"1px"},"spacing":{"padding":{"top":"40px","right":"32px","bottom":"40px","left":"32px"}}},"borderColor":"contrast-3","layout":{"type":"constrained"}} -->
			<div class="wp-block-group has-border-color has-contrast-3-border-color" style="border-width:1px;border-radius:8px;padding-top:40px;padding-right:32px;padding-bottom:40px;padding-left:32px">

				<!-- wp:heading {"textAlign":"center","level":2} -->
				<h2 class="wp-block-heading has-text-align-center">Accordion Forms</h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center"} -->
				<p class="has-text-align-center">View forms organized inside expandable accordion sections.</p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
				<div class="wp-block-buttons">
					<!-- wp:button -->
					<div class="wp-block-button">
						<a class="wp-block-button__link wp-element-button" href="%2$s">View Accordion Forms</a>
					</div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->
</section>
<!-- /wp:group -->',
	esc_url( $cta_forms_url ),
	esc_url( $accordion_forms_url )
);