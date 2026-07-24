<?php
/**
 * Virtual page definitions.
 *
 * @package DealerluxUtils
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

return array(
	'dealerlux_forms' => array(
		'slug'         => 'dealerlux-forms',
		'title'        => 'Forms Directory',
		'content_file' => 'forms/forms.php',
		'template'     => '',
		'status'       => 'publish',
		'env'          => 'local',
		'description'  => 'Displays all available client forms.',
	),

	'cta_forms' => array(
		'parent'       => 'dealerlux_forms',
		'slug'         => 'cta-forms',
		'title'        => 'CTA Forms',
		'content_file' => 'forms/cta-forms.php',
		'template'     => '',
		'status'       => 'publish',
		'env'          => '',
		'description'  => 'Displays the CTA forms.',
	),

	'accordion_forms' => array(
		'parent'       => 'dealerlux_forms',
		'slug'         => 'accordion-forms',
		'title'        => 'Accordion Forms',
		'content_file' => 'forms/accordion-forms.php',
		'template'     => '',
		'status'       => 'publish',
		'env'          => '',
		'description'  => 'Displays the Accordion Forms.',
	),

	'virtual-consultation-request' => array(
		'parent'       => 'virtual_forms',
		'slug'         => 'virtual-consultation-request',
		'title'        => 'Consultation Request',
		'content_file' => 'forms/consultation-request.php',
		'template'     => '',
		'status'       => 'publish',
		'env'          => '',
		'description'  => 'Displays the consultation request form.',
	),

	'virtual-vehicle-search' => array(
		'slug'         => 'virtual-vehicle-search',
		'title'        => 'Vehicle Search',
		'content_file' => 'vehicle-search.php',
		'template'     => '',
		'status'       => 'publish',
		'env'          => '',
		'description'  => 'Displays the vehicle search interface.',
	),
);