<?php
/**
 * WordPress options registry configuration.
 *
 * Contains metadata for WordPress core options, theme-specific options,
 * and plugin-specific options used by Dealerlux projects.
 *
 * This file only defines option information. It does not register, read,
 * update, sanitize, or delete WordPress options.
 *
 * @package DealerluxUtils
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

return array(

	/*
	 |--------------------------------------------------------------------------
	 | Theme Options
	 |--------------------------------------------------------------------------
	 |
	 | Options grouped by the theme that owns them.
	 |
	 */
	'theme' => array(
		'label'       => 'Themes',
		'description' => 'Options grouped by the theme that owns them.',

		'collection' => array(
			'dealerlux' => array(
				'label'       => 'Dealerlux',
				'description' => 'Options created and managed by the Dealerlux theme.',

				'options' => array(
					'settings' => array(
						'name'        => 'dealerlux_theme_settings',
						'label'       => 'Dealerlux Theme Settings',
						'type'        => 'array',
						'default'     => array(),
						'description' => 'General settings used by the Dealerlux theme.',
					),

					'version' => array(
						'name'        => 'dealerlux_theme_version',
						'label'       => 'Dealerlux Theme Version',
						'type'        => 'string',
						'default'     => '',
						'description' => 'The stored version of the Dealerlux theme.',
					),
				),
			),
		),
	),

	/*
	 |--------------------------------------------------------------------------
	 | Plugin Options
	 |--------------------------------------------------------------------------
	 |
	 | Options grouped by the plugin that owns them.
	 |
	 */
	'plugins' => array(
		'label'       => 'Plugins',
		'description' => 'Options grouped by the plugin that owns them.',

		'collection' => array(
			'spa-software-solutions' => array(
				'label'       => 'SPA Software Solutions',
				'description' => 'Options created and managed by the SPA Software Solutions plugin.',

				'options' => array(
					'selected_client_plugin' => array(
						'name'        => 'sss_selected_client_plugin',
						'label'       => 'Selected Client Plugin',
						'type'        => 'array',
						'default'     => array(
							'plugin_directory' => '',
							'plugin_file'      => '',
							'plugin_name'      => '',
						),
						'description' => 'Information about the currently selected SSS client plugin.',
					),
				),
			),

			'dealerlux-utility' => array(
				'label'       => 'Dealerlux Utility',
				'description' => 'Options created and managed by the Dealerlux Utility MU plugin.',

				'options' => array(
					'version' => array(
                        'name'        => 'dealerlux_utility_version',
						'label'       => 'Dealerlux Utility Version',
						'type'        => 'string',
						'default'     => '',
						'description' => 'The stored version of the Dealerlux Utility MU plugin.',
                    ),
                        
                    'settings' => array(
                        'name'        => 'dealerlux_utility_settings',
						'label'       => 'Dealerlux Utility Settings',
						'type'        => 'array',
						'default'     => array(),
						'description' => 'General settings used by the Dealerlux Utility MU plugin.',
					),

					'posts_registry_cache' => array(
						'name'        => 'dealerlux_utility_posts_registry_cache',
						'label'       => 'Posts Registry Cache',
						'type'        => 'array',
						'default'     => array(
							'fingerprint' => '',
							'compiled_at' => 0,
							'config'      => array(),
						),
						'description' => 'Compiled virtual posts registry and its source-file fingerprint.',
					),
				),
			),
		),
	),

	/*
	 |--------------------------------------------------------------------------
	 | WordPress Core Options
	 |--------------------------------------------------------------------------
	 |
	 | Options created and managed by WordPress core.
	 |
	 */
	'wp' => array(
		'label'       => 'WordPress Core',
		'description' => 'Options created and managed by WordPress core.',

		'options' => array(
			'blogname' => array(
				'label'       => 'Site Title',
				'type'        => 'string',
				'default'     => '',
				'description' => 'The title of the WordPress website.',
			),

			'blogdescription' => array(
				'label'       => 'Tagline',
				'type'        => 'string',
				'default'     => '',
				'description' => 'The WordPress website tagline.',
			),

			'admin_email' => array(
				'label'       => 'Administration Email',
				'type'        => 'string',
				'default'     => '',
				'description' => 'The primary administration email address.',
			),

			'posts_per_page' => array(
				'label'       => 'Posts Per Page',
				'type'        => 'integer',
				'default'     => 10,
				'description' => 'The number of posts displayed per page.',
			),

			'show_on_front' => array(
				'label'       => 'Homepage Display',
				'type'        => 'string',
				'default'     => 'posts',
				'description' => 'Determines whether the homepage displays posts or a static page.',
			),

			'page_on_front' => array(
				'label'       => 'Homepage',
				'type'        => 'integer',
				'default'     => 0,
				'description' => 'The post ID of the page used as the static homepage.',
			),

			'page_for_posts' => array(
				'label'       => 'Posts Page',
				'type'        => 'integer',
				'default'     => 0,
				'description' => 'The post ID of the page used to display blog posts.',
			),

			'permalink_structure' => array(
				'label'       => 'Permalink Structure',
				'type'        => 'string',
				'default'     => '',
				'description' => 'The permalink structure used for WordPress content.',
			),

			'date_format' => array(
				'label'       => 'Date Format',
				'type'        => 'string',
				'default'     => 'F j, Y',
				'description' => 'The format used when displaying dates.',
			),

			'time_format' => array(
				'label'       => 'Time Format',
				'type'        => 'string',
				'default'     => 'g:i a',
				'description' => 'The format used when displaying times.',
			),

			'timezone_string' => array(
				'label'       => 'Timezone',
				'type'        => 'string',
				'default'     => '',
				'description' => 'The configured WordPress timezone identifier.',
			),

			'start_of_week' => array(
				'label'       => 'Week Starts On',
				'type'        => 'integer',
				'default'     => 1,
				'description' => 'The numeric day on which the WordPress week starts.',
			),

			'users_can_register' => array(
				'label'       => 'User Registration',
				'type'        => 'boolean',
				'default'     => false,
				'description' => 'Determines whether visitors can register user accounts.',
			),

			'default_role' => array(
				'label'       => 'Default User Role',
				'type'        => 'string',
				'default'     => 'subscriber',
				'description' => 'The default role assigned to newly registered users.',
			),

			'thumbnail_size_w' => array(
				'label'       => 'Thumbnail Width',
				'type'        => 'integer',
				'default'     => 150,
				'description' => 'The default thumbnail image width in pixels.',
			),

			'thumbnail_size_h' => array(
				'label'       => 'Thumbnail Height',
				'type'        => 'integer',
				'default'     => 150,
				'description' => 'The default thumbnail image height in pixels.',
			),

			'medium_size_w' => array(
				'label'       => 'Medium Image Width',
				'type'        => 'integer',
				'default'     => 300,
				'description' => 'The maximum medium image width in pixels.',
			),

			'medium_size_h' => array(
				'label'       => 'Medium Image Height',
				'type'        => 'integer',
				'default'     => 300,
				'description' => 'The maximum medium image height in pixels.',
			),

			'large_size_w' => array(
				'label'       => 'Large Image Width',
				'type'        => 'integer',
				'default'     => 1024,
				'description' => 'The maximum large image width in pixels.',
			),

			'large_size_h' => array(
				'label'       => 'Large Image Height',
				'type'        => 'integer',
				'default'     => 1024,
				'description' => 'The maximum large image height in pixels.',
			),
		),
	)
);