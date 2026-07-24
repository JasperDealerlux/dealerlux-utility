<?php
/**
 * Gets information about a function through ReflectionFunction.
 *
 * @param string $function_name Function name.
 * @return array|null Function information, or null when unavailable.
 */
function dl_get_function_info( $function_name = '' ) {
	if (
		! is_string( $function_name ) ||
		'' === trim( $function_name ) ||
		! function_exists( $function_name ) ||
		! class_exists( 'ReflectionFunction' )
	) {
		return null;
	}

	try {
		$function = new ReflectionFunction( $function_name );
	} catch ( ReflectionException $exception ) {
		return null;
	}

	$is_internal      = $function->isInternal() ? 'internal' : 'user-defined';
	$name             = $function->getName();
	$filename         = $function->getFileName();
	$start_line       = $function->getStartLine();
	$end_line         = $function->getEndLine();
	$documentation    = $function->getDocComment();
	$static_variables = $function->getStaticVariables();
	$return_type      = $function->getReturnType();

	$display_filename = false === $filename ? '[internal]' : $filename;
	$display_start    = false === $start_line ? 0 : $start_line;
	$display_end      = false === $end_line ? 0 : $end_line;

	return array(
		'get_summary'       => sprintf(
			'The %1$s function "%2$s" is declared in "%3$s" on lines %4$d to %5$d.',
			$is_internal,
			$name,
			$display_filename,
			$display_start,
			$display_end
		),
		'get_documentation' => false === $documentation ? false : $documentation,
		'is_internal'       => $is_internal,
		'get_name'          => $name,
		'get_filename'      => $filename,
		'get_start_line'    => $start_line,
		'get_end_line'      => $end_line,
		'get_parameters'    => $function->getParameters(),
		'get_statics'       => empty( $static_variables ) ? array() : $static_variables,
		'has_return_type'   => $function->hasReturnType(),
		'get_return_type'   => $return_type,
	);
}

/**
 * Converts the strings "true" and "false" to booleans.
 *
 * @param mixed $value Value to convert.
 * @return bool|null Boolean value, or null when invalid.
 */
function dl_convert_string_to_boolean( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}

	if ( ! is_string( $value ) ) {
		return null;
	}

	$value = strtolower( trim( $value ) );

	if ( 'true' === $value ) {
		return true;
	}

	if ( 'false' === $value ) {
		return false;
	}

	return null;
}

/**
 * Determines whether the current request is an administrative request.
 * Front-end AJAX requests are not treated as administrative requests.
 *
 * @return bool
 */
function dl_is_admin_request() {
	if ( ! is_admin() ) {
		return false;
	}

	if ( function_exists( 'wp_doing_ajax' ) ) {
		return ! wp_doing_ajax();
	}

	return ! ( defined( 'DOING_AJAX' ) && DOING_AJAX );
}

/**
 * Gets the current request URL.
 *
 * @return string
 */
function dl_get_current_url() {
	$host        = isset( $_SERVER['HTTP_HOST'] )
		? wp_unslash( $_SERVER['HTTP_HOST'] )
		: '';

	$request_uri = isset( $_SERVER['REQUEST_URI'] )
		? wp_unslash( $_SERVER['REQUEST_URI'] )
		: '/';

	if ( '' === $host ) {
		return '';
	}

	$scheme = is_ssl() ? 'https' : 'http';

	return esc_url_raw( $scheme . '://' . $host . $request_uri );
}

/**
 * Determines whether the current request is for a wp-admin screen.
 *
 * @return bool
 */
function dl_is_admin_screen() {
	if ( ! is_admin() ) {
		return false;
	}

	if ( function_exists( 'wp_doing_ajax' ) ) {
		return ! wp_doing_ajax();
	}

	return ! ( defined( 'DOING_AJAX' ) && DOING_AJAX );
}

/**
 * Returns a value when set; otherwise returns the provided default.
 *
 * @param mixed $item    Value to inspect.
 * @param mixed $default Default value.
 * @return mixed
 */
function dl_isset_val( $item, $default = '' ) {
	return isset( $item ) ? $item : $default;
}

/**
 * Gets an array item when it exists; otherwise returns the default.
 *
 * @param array      $items   Array to inspect.
 * @param string|int $index   Array key.
 * @param mixed      $default Default value.
 * @return mixed
 */
function dl_isset_array(
	$items = array(),
	$index = '',
	$default = ''
) {
	if (
		! is_array( $items ) ||
		'' === $index ||
		! array_key_exists( $index, $items )
	) {
		return $default;
	}

	return $items[ $index ];
}

/**
 * Gets an object property when it exists; otherwise returns the default.
 *
 * @param object $items   Object to inspect.
 * @param string $index   Property name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function dl_isset_object(
	$items,
	$index = '',
	$default = ''
) {
	if (
		! is_object( $items ) ||
		'' === $index ||
		! property_exists( $items, $index )
	) {
		return $default;
	}

	return $items->{$index};
}

/**
 * Gets a value from a multidimensional array using a key path.
 *
 * Example:
 *
 * dl_map_array_item(
 *     array(
 *         'collection' => array(
 *             'root' => array(
 *                 'child' => array(
 *                     'value' => 'Dealerlux',
 *                 ),
 *             ),
 *         ),
 *         'collection_map' => array(
 *             'root',
 *             'child',
 *             'value',
 *         ),
 *         'default' => '',
 *     )
 * );
 *
 * @param array $args Configuration containing collection, collection_map,
 *                    and default.
 * @return mixed
 */
function dl_map_array_item( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'collection'     => array(),
			'collection_map' => array(),
			'default'        => '',
		)
	);

	if (
		! is_array( $args['collection'] ) ||
		! is_array( $args['collection_map'] )
	) {
		return $args['default'];
	}

	$value = $args['collection'];

	foreach ( $args['collection_map'] as $key ) {
		if (
			! is_array( $value ) ||
			! array_key_exists( $key, $value )
		) {
			return $args['default'];
		}

		$value = $value[ $key ];
	}

	return $value;
}

/**
 * Returns an empty string for empty values.
 * Otherwise, returns the value unchanged.
 *
 * @param mixed $value Value to inspect.
 * @return mixed|string
 */
function dl_sanity_check( $value ) {
	return empty( $value ) ? '' : $value;
}

/**
 * Renders var_dump output in the page footer or browser console.
 *
 * Supported arguments:
 *
 * - die: Terminate the request after registering the output.
 * - js: Send the value to console.log instead of rendering a pre element.
 * - do_backtrace: Include a PHP backtrace.
 * - backtrace_limit: Maximum number of backtrace entries.
 * - backtrace_color: Retained for compatibility with previous calls.
 *
 * @param mixed $content Content to dump.
 * @param array $args    Output options.
 * @return void
 */
function dl_dump( $content = '', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'die'             => false,
			'js'              => false,
			'do_backtrace'    => false,
			'backtrace_limit' => 5,
			'backtrace_color' => '#E91E63',
		)
	);

	$backtrace = array();

	if ( $args['do_backtrace'] ) {
		$limit = absint( $args['backtrace_limit'] );

		if ( 0 === $limit ) {
			$limit = 5;
		}

		$backtrace = debug_backtrace(
			DEBUG_BACKTRACE_IGNORE_ARGS,
			$limit
		);
	}

	$id = 'dealerlux-utils-dump-' . wp_unique_id();

	if ( $args['js'] ) {
		$payload = array(
			'value'     => $content,
			'backtrace' => $backtrace,
		);

		$output  = '<script id="' . esc_attr( $id ) . '"';
		$output .= ' class="dealerlux-utils-dump">';
		$output .= 'console.log(' . wp_json_encode( $payload ) . ');';
		$output .= '</script>';
	} else {
		ob_start();
		var_dump( $content );
		$dump = ob_get_clean();

		$output  = '<pre id="' . esc_attr( $id ) . '"';
		$output .= ' class="dealerlux-utils-dump">';
		$output .= esc_html( $dump );

		if ( ! empty( $backtrace ) ) {
			$output .= "\n\nBacktrace:\n";
			$output .= esc_html(
				print_r( $backtrace, true )
			);
		}

		$output .= '</pre>';
	}

	$render = static function () use ( $output ) {
		/*
		 * The individual values used to construct this HTML were escaped
		 * before being stored in $output.
		 */
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	};

	add_action( 'admin_footer', $render, 999 );
	add_action( 'wp_footer', $render, 999 );

	if ( $args['die'] ) {
		wp_die();
	}
}

/**
 * Dumps a value and terminates execution.
 *
 * @param mixed $content Content to dump.
 * @param array $args    Additional output options.
 * @return void
 */
function dl_dump_die( $content = '', $args = array() ) {
	$args['die'] = true;
	$args['js']  = false;

	dl_dump( $content, $args );
}

/**
 * Sends a value to the browser console.
 *
 * @param mixed $content Content to log.
 * @param array $args    Additional output options.
 * @return void
 */
function dl_dump_js( $content = '', $args = array() ) {
	$args['die'] = false;
	$args['js']  = true;

	dl_dump( $content, $args );
}

/**
 * Renders print_r output or logs it to the browser console.
 *
 * @param mixed $content Content to print.
 * @param array $args    Output options.
 * @return void
 */
function dl_print_r( $content = '', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'die' => false,
			'js'  => false,
		)
	);

	if ( $args['js'] ) {
		$output  = '<script id="dealerlux-utils-printr-';
		$output .= esc_attr( wp_unique_id() );
		$output .= '" class="dealerlux-utils-printr">';
		$output .= 'console.log(' . wp_json_encode( $content ) . ');';
		$output .= '</script>';

		$render = static function () use ( $output ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		};

		add_action( 'admin_footer', $render, 999 );
		add_action( 'wp_footer', $render, 999 );
	} else {
		echo '<pre class="dealerlux-utils-printr">';
		echo esc_html( print_r( $content, true ) );
		echo '</pre>';
	}

	if ( $args['die'] ) {
		wp_die();
	}
}

/**
 * Prints a value and terminates execution.
 *
 * @param mixed $content Content to print.
 * @return void
 */
function dl_print_r_die( $content = '' ) {
	dl_print_r(
		$content,
		array(
			'die' => true,
		)
	);
}

/**
 * Logs print_r-compatible content to the browser console.
 *
 * @param mixed $content Content to log.
 * @return void
 */
function dl_print_r_js( $content = '' ) {
	dl_print_r(
		$content,
		array(
			'js' => true,
		)
	);
}

/**
 * Gets the file and line from which this helper was called.
 *
 * @return string
 */
function dl_log() {
	$backtrace = debug_backtrace(
		DEBUG_BACKTRACE_IGNORE_ARGS,
		2
	);

	$caller = isset( $backtrace[1] )
		? $backtrace[1]
		: array();

	if ( ! isset( $caller['file'], $caller['line'] ) ) {
		return '';
	}

	return 'file: ' . $caller['file']
		. ': line: ' . $caller['line'];
}

/**
 * Captures var_dump output in an escaped preformatted block.
 *
 * @param mixed $data Value to dump.
 * @return string
 */
function dl_var_dump( $data ) {
	ob_start();
	var_dump( $data );
	$dump = ob_get_clean();

	return '<pre class="dealerlux-utils-var-dump">'
		. esc_html( $dump )
		. '</pre>';
}

/**
 * Creates a browser console.log script.
 *
 * When $hooked is true, the script is automatically added to both the
 * administrative and front-end footers. Otherwise, the script is returned.
 *
 * @param mixed $content Content to log.
 * @param bool  $hooked  Whether to register the script in footer hooks.
 * @return string
 */
function dl_console_log(
	$content = '',
	$hooked = false
) {
	$output  = '<script id="dealerlux-utils-log-';
	$output .= esc_attr( wp_unique_id() );
	$output .= '" class="dealerlux-utils-log">';
	$output .= 'console.log(' . wp_json_encode( $content ) . ');';
	$output .= '</script>';

	if ( $hooked ) {
		$render = static function () use ( $output ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		};

		add_action( 'admin_footer', $render, 999 );
		add_action( 'wp_footer', $render, 999 );
	}

	return $output;
}

/**
 * Gets callbacks registered to a WordPress action or filter hook.
 *
 * @param string $hook Hook name.
 * @return array
 */
function dl_get_filters( $hook = '' ) {
	global $wp_filter;

	if (
		! is_string( $hook ) ||
		'' === $hook ||
		! isset( $wp_filter[ $hook ] )
	) {
		return array();
	}

	if ( $wp_filter[ $hook ] instanceof WP_Hook ) {
		$callbacks = $wp_filter[ $hook ]->callbacks;
	} else {
		$callbacks = $wp_filter[ $hook ];
	}

	if ( ! is_array( $callbacks ) ) {
		return array();
	}

	$result = array();

	foreach ( $callbacks as $priority => $registered_callbacks ) {
		if ( ! is_array( $registered_callbacks ) ) {
			continue;
		}

		foreach (
			$registered_callbacks as $hook_id => $callback_data
		) {
			$callback = isset( $callback_data['function'] )
				? $callback_data['function']
				: null;

			$label = '[unknown callback]';

			if ( is_string( $callback ) ) {
				$label = $callback;
			} elseif (
				is_array( $callback ) &&
				2 === count( $callback )
			) {
				$class = is_object( $callback[0] )
					? get_class( $callback[0] )
					: (string) $callback[0];

				$label = $class . '::' . $callback[1];
			} elseif ( $callback instanceof Closure ) {
				$label = 'Closure';
			} elseif (
				is_object( $callback ) &&
				method_exists( $callback, '__invoke' )
			) {
				$label = get_class( $callback ) . '::__invoke';
			}

			$result[] = array(
				'hook'          => $label,
				'hook_id'       => $hook_id,
				'priority'      => (int) $priority,
				'accepted_args' => isset(
					$callback_data['accepted_args']
				)
					? (int) $callback_data['accepted_args']
					: 1,
			);
		}
	}

	return $result;
}

/**
 * Logs enqueued scripts and/or styles in the browser console.
 *
 * @param string $fetch One of all, scripts, or styles.
 * @return void
 */
function dl_dump_enqueues( $fetch = 'all' ) {
	$allowed_values = array(
		'all',
		'scripts',
		'styles',
	);

	$fetch = in_array( $fetch, $allowed_values, true )
		? $fetch
		: 'all';

	add_action(
		'wp_footer',
		static function () use ( $fetch ) {
			$result = array();

			if (
				'all' === $fetch ||
				'scripts' === $fetch
			) {
				$wp_scripts = wp_scripts();

				foreach ( $wp_scripts->queue as $handle ) {
					if (
						! isset(
							$wp_scripts->registered[ $handle ]
						)
					) {
						continue;
					}

					$result['scripts'][ $handle ] =
						$wp_scripts
							->registered[ $handle ]
							->src;
				}
			}

			if (
				'all' === $fetch ||
				'styles' === $fetch
			) {
				$wp_styles = wp_styles();

				foreach ( $wp_styles->queue as $handle ) {
					if (
						! isset(
							$wp_styles->registered[ $handle ]
						)
					) {
						continue;
					}

					$result['styles'][ $handle ] =
						$wp_styles
							->registered[ $handle ]
							->src;
				}
			}

			echo dl_console_log( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		},
		999
	);
}

/**
 * Logs callbacks registered to a WordPress hook.
 *
 * @param string $hook Hook name.
 * @return void
 */
function dl_dump_hooks( $hook = '' ) {
	add_action(
		'wp_footer',
		static function () use ( $hook ) {
			$callbacks = dl_get_filters( $hook );

			echo dl_console_log( $callbacks ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		},
		999
	);
}

/**
 * Builds a safe absolute path inside a WordPress plugin directory.
 *
 * @param string $plugin_directory Absolute plugin directory.
 * @param string $relative_path    Path relative to the plugin directory.
 * @return string
 */
function dl_get_plugin_file_path(
	$plugin_directory,
	$relative_path
) {
	if (
		! is_string( $plugin_directory ) ||
		! is_string( $relative_path ) ||
		'' === trim( $plugin_directory ) ||
		'' === trim( $relative_path )
	) {
		return '';
	}

	$plugin_directory = wp_normalize_path(
		untrailingslashit( $plugin_directory )
	);

	$relative_path = wp_normalize_path(
		ltrim( $relative_path, '/\\' )
	);

	$file_path = wp_normalize_path(
		$plugin_directory . '/' . $relative_path
	);

	/*
	 * Prevent a path such as "../../wp-config.php" from escaping
	 * the selected plugin directory.
	 */
	$real_plugin_directory = realpath( $plugin_directory );
	$real_file_path        = realpath( $file_path );

	if (
		false === $real_plugin_directory ||
		false === $real_file_path
	) {
		return '';
	}

	$real_plugin_directory = wp_normalize_path(
		$real_plugin_directory
	);

	$real_file_path = wp_normalize_path(
		$real_file_path
	);

	if (
		0 !== strpos(
			$real_file_path,
			trailingslashit( $real_plugin_directory )
		)
	) {
		return '';
	}

	return $real_file_path;
}

/**
 * Determines whether a plugin is active.
 *
 * @param string $path Plugin path relative to wp-content/plugins.
 * @return bool
 */
function dl_is_plugin_active( $path = '' ) {
	if (
		! is_string( $path ) ||
		'' === trim( $path )
	) {
		return false;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return is_plugin_active( $path );
}