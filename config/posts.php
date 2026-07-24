<?php
/**
 * Compiled virtual posts registry configuration.
 *
 * Post definitions are discovered from:
 *
 * config/posts/types/*.php
 *
 * Post content files are discovered from:
 *
 * config/posts/contents/{post-type}/
 *
 * The compiled registry is cached in a WordPress option and rebuilt when:
 *
 * - a post-type definition file changes;
 * - a content file changes;
 * - the compiler version changes;
 * - the enabled setting changes;
 * - the WordPress environment changes.
 *
 * @package DealerluxUtils
 */

use DealerluxUtils\Registries\Options_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Options_Registry::instance()->delete_value(
// 	array(
// 		'type'   => 'plugin',
// 		'source' => 'dealerlux-utility',
// 		'name'   => 'posts_registry_cache',
// 	)
// );

/**
 * Increment this value whenever the compiler logic changes.
 *
 * @var string
 */
$compiler_version = '1.0.0';

/**
 * Whether virtual posts are enabled.
 *
 * @var bool
 */
$enable = true;

/**
 * Absolute posts configuration directory.
 *
 * @var string
 */
$posts_directory = wp_normalize_path(
	__DIR__ . '/posts'
);

/**
 * Absolute post-type definitions directory.
 *
 * @var string
 */
$types_directory = wp_normalize_path(
	$posts_directory . '/types'
);

/**
 * Absolute content files directory.
 *
 * @var string
 */
$contents_directory = wp_normalize_path(
	$posts_directory . '/contents'
);

/**
 * Option registry selector used for the compiled posts cache.
 *
 * @var array
 */
$cache_selector = array(
	'type'   => 'plugin',
	'source' => 'dealerlux-utility',
	'name'   => 'posts_registry_cache',
);

/**
 * Determine whether OPcache is enabled.
 *
 * @return bool
 */
$is_opcache_enabled = static function () {
	if ( ! function_exists( 'opcache_invalidate' ) ) {
		return false;
	}

	$enabled = ini_get( 'opcache.enable' );

	if ( false === $enabled ) {
		return false;
	}

	return filter_var(
		$enabled,
		FILTER_VALIDATE_BOOLEAN
	);
};

/**
 * Invalidate cached file metadata and PHP OPcache bytecode.
 *
 * @param string $file_path File path.
 * @return void
 */
$invalidate_file_cache = static function (
	$file_path
) use ( $is_opcache_enabled ) {
	if (
		! is_string( $file_path ) ||
		'' === trim( $file_path )
	) {
		return;
	}

	$file_path = wp_normalize_path(
		$file_path
	);

	clearstatcache(
		true,
		$file_path
	);

	if ( $is_opcache_enabled() ) {
		opcache_invalidate(
			$file_path,
			true
		);
	}
};

/**
 * Recursively discover PHP files.
 *
 * @param string $directory Directory to inspect.
 * @return array
 */
$discover_php_files = static function ( $directory ) {
	if (
		! is_string( $directory ) ||
		'' === trim( $directory ) ||
		! is_dir( $directory ) ||
		! is_readable( $directory )
	) {
		return array();
	}

	$files = array();

	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$directory,
				FilesystemIterator::SKIP_DOTS
			)
		);
	} catch ( UnexpectedValueException $exception ) {
		return array();
	}

	foreach ( $iterator as $file_info ) {
		if (
			! $file_info instanceof SplFileInfo ||
			! $file_info->isFile() ||
			'php' !== strtolower(
				$file_info->getExtension()
			)
		) {
			continue;
		}

		$file_path = wp_normalize_path(
			$file_info->getPathname()
		);

		if (
			is_file( $file_path ) &&
			is_readable( $file_path )
		) {
			$files[] = $file_path;
		}
	}

	$files = array_values(
		array_unique( $files )
	);

	sort(
		$files,
		SORT_STRING
	);

	return $files;
};

/**
 * Load a PHP file that must return an array.
 *
 * @param string $file_path File to load.
 * @return array
 */
$load_array_file = static function (
	$file_path
) use ( $invalidate_file_cache ) {
	if (
		! is_string( $file_path ) ||
		'' === trim( $file_path ) ||
		! is_file( $file_path ) ||
		! is_readable( $file_path )
	) {
		return array();
	}

	$file_path = wp_normalize_path(
		$file_path
	);

	$invalidate_file_cache(
		$file_path
	);

	$value = require $file_path;

	return is_array( $value )
		? $value
		: array();
};

/**
 * Load a PHP content file.
 *
 * Content files must return a string.
 *
 * @param string $file_path Content file path.
 * @return string
 */
$load_content_file = static function (
	$file_path
) use ( $invalidate_file_cache ) {
	if (
		! is_string( $file_path ) ||
		'' === trim( $file_path ) ||
		! is_file( $file_path ) ||
		! is_readable( $file_path )
	) {
		return '';
	}

	$file_path = wp_normalize_path(
		$file_path
	);

	$invalidate_file_cache(
		$file_path
	);

	$content = require $file_path;

	return is_string( $content )
		? $content
		: '';
};

/**
 * Safely resolve a content file inside the content directory.
 *
 * @param string $post_type             Post type.
 * @param string $relative_content_file Relative content path.
 * @return string
 */
$resolve_content_file = static function (
	$post_type,
	$relative_content_file
) use ( $contents_directory ) {
	if (
		! is_string( $post_type ) ||
		! is_string( $relative_content_file )
	) {
		return '';
	}

	$post_type = sanitize_key(
		$post_type
	);

	$relative_content_file = wp_normalize_path(
		ltrim(
			trim( $relative_content_file ),
			'/\\'
		)
	);

	if (
		'' === $post_type ||
		'' === $relative_content_file
	) {
		return '';
	}

	$type_contents_directory = wp_normalize_path(
		trailingslashit( $contents_directory )
		. $post_type
	);

	$candidate_path = wp_normalize_path(
		trailingslashit( $type_contents_directory )
		. $relative_content_file
	);

	$real_directory = realpath(
		$type_contents_directory
	);

	$real_candidate = realpath(
		$candidate_path
	);

	if (
		false === $real_directory ||
		false === $real_candidate
	) {
		return '';
	}

	$real_directory = wp_normalize_path(
		$real_directory
	);

	$real_candidate = wp_normalize_path(
		$real_candidate
	);

	$directory_prefix = trailingslashit(
		$real_directory
	);

	if (
		0 !== strpos(
			$real_candidate,
			$directory_prefix
		)
	) {
		return '';
	}

	if (
		! is_file( $real_candidate ) ||
		! is_readable( $real_candidate )
	) {
		return '';
	}

	return $real_candidate;
};

/**
 * Resolve one post definition's content.
 *
 * Inline content is used when present. Otherwise, content_file is loaded.
 *
 * @param string $post_type  Post type.
 * @param array  $definition Post definition.
 * @return string
 */
$resolve_content = static function (
	$post_type,
	array $definition
) use (
	$resolve_content_file,
	$load_content_file
) {
	$inline_content = array_key_exists(
		'content',
		$definition
	)
		? $definition['content']
		: null;

	if ( is_string( $inline_content ) ) {
		return $inline_content;
	}

	$content_file = isset(
		$definition['content_file']
	)
	&& is_string(
		$definition['content_file']
	)
		? trim(
			$definition['content_file']
		)
		: '';

	if ( '' === $content_file ) {
		return '';
	}

	$content_path = $resolve_content_file(
		$post_type,
		$content_file
	);

	if ( '' === $content_path ) {
		return '';
	}

	return $load_content_file(
		$content_path
	);
};

/**
 * Read the active WordPress environment.
 *
 * @var string
 */
$environment = function_exists(
	'wp_get_environment_type'
)
	? wp_get_environment_type()
	: (
		defined( 'WP_ENVIRONMENT_TYPE' )
			? WP_ENVIRONMENT_TYPE
			: 'production'
	);

$allowed_environments = array(
	'local',
	'development',
	'staging',
	'production',
);

if (
	! is_string( $environment ) ||
	! in_array(
		$environment,
		$allowed_environments,
		true
	)
) {
	$environment = 'production';
}

/**
 * Discover source files.
 *
 * @var array
 */
$type_files = $discover_php_files(
	$types_directory
);

$content_files = $discover_php_files(
	$contents_directory
);

$source_files = array_merge(
	$type_files,
	$content_files
);

$source_files = array_values(
	array_unique( $source_files )
);

sort(
	$source_files,
	SORT_STRING
);

/**
 * Build content hashes for every source file.
 *
 * @var array
 */
$source_metadata = array();

foreach ( $source_files as $source_file ) {
	if (
		! is_string( $source_file ) ||
		'' === trim( $source_file )
	) {
		continue;
	}

	$source_file = wp_normalize_path(
		$source_file
	);

	clearstatcache(
		true,
		$source_file
	);

	if (
		! is_file( $source_file ) ||
		! is_readable( $source_file )
	) {
		continue;
	}

	$file_hash = hash_file(
		'sha256',
		$source_file
	);

	$source_metadata[] = array(
		'file' => $source_file,
		'hash' => false === $file_hash
			? ''
			: $file_hash,
	);
}

/**
 * Build the complete fingerprint payload.
 *
 * @var array
 */
$fingerprint_payload = array(
	'compiler_version' => $compiler_version,
	'enable'           => $enable,
	'environment'      => $environment,
	'sources'          => $source_metadata,
);

$fingerprint_json = wp_json_encode(
	$fingerprint_payload
);

$fingerprint = hash(
	'sha256',
	false === $fingerprint_json
		? ''
		: $fingerprint_json
);

/**
 * Load the existing cache.
 *
 * @var Options_Registry $options_registry
 */
$options_registry = Options_Registry::instance();

$cache = $options_registry->get_value(
	$cache_selector,
	array()
);

$cached_fingerprint = (
	is_array( $cache ) &&
	isset( $cache['fingerprint'] ) &&
	is_string( $cache['fingerprint'] )
)
	? $cache['fingerprint']
	: '';

$cached_config = (
	is_array( $cache ) &&
	isset( $cache['config'] ) &&
	is_array( $cache['config'] )
)
	? $cache['config']
	: array();

/**
 * Return the current cached configuration.
 */
if (
	'' !== $cached_fingerprint &&
	hash_equals(
		$cached_fingerprint,
		$fingerprint
	) &&
	! empty( $cached_config )
) {
	return $cached_config;
}

/**
 * Source files changed, so invalidate PHP bytecode before requiring them.
 */
foreach ( $source_files as $source_file ) {
	$invalidate_file_cache(
		$source_file
	);
}

/**
 * Compile posts grouped by post type.
 *
 * @var array
 */
$compiled_posts = array();

foreach ( $type_files as $type_file ) {
	$post_type = sanitize_key(
		pathinfo(
			$type_file,
			PATHINFO_FILENAME
		)
	);

	if ( '' === $post_type ) {
		continue;
	}

	$type_definitions = $load_array_file(
		$type_file
	);

	$compiled_posts[ $post_type ] = array();

	if ( empty( $type_definitions ) ) {
		continue;
	}

	foreach (
		$type_definitions as $registry_key => $definition
	) {
		if (
			! is_string( $registry_key ) ||
			'' === trim( $registry_key ) ||
			! is_array( $definition )
		) {
			continue;
		}

		$registry_key = preg_replace(
			'/[^A-Za-z0-9_-]/',
			'',
			trim( $registry_key )
		);

		if (
			! is_string( $registry_key ) ||
			'' === $registry_key
		) {
			continue;
		}

		$definition_environment = isset(
			$definition['env']
		)
		&& is_string(
			$definition['env']
		)
			? strtolower(
				trim( $definition['env'] )
			)
			: '';

		if (
			'' !== $definition_environment &&
			$environment !== $definition_environment
		) {
			continue;
		}

		$definition['post_type'] = $post_type;

		$definition['content'] = $resolve_content(
			$post_type,
			$definition
		);

		$compiled_posts[ $post_type ][ $registry_key ] =
			$definition;
	}
}

/**
 * Final compiled registry.
 *
 * @var array
 */
$config = array(
	'settings' => array(
		'enable'           => $enable,
		'environment'      => $environment,
		'compiler_version' => $compiler_version,
	),

	'paths' => array(
		'base'     => $posts_directory,
		'types'    => $types_directory,
		'contents' => $contents_directory,
	),

	'posts' => $compiled_posts,
);

/**
 * Cache value.
 *
 * @var array
 */
$cache_value = array(
	'fingerprint' => $fingerprint,
	'compiled_at' => time(),
	'config'      => $config,
);

/**
 * Save the compiled cache.
 */
if ( $options_registry->value_exists( $cache_selector ) ) {
	$options_registry->update_value(
		$cache_selector,
		$cache_value,
		false
	);
} else {
	$options_registry->add_value(
		$cache_selector,
		$cache_value,
		false
	);
}

return $config;