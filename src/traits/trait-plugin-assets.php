<?php
/**
 * Trait Plugin_Assets
 *
 * Provides reusable filesystem paths, public URLs, and cache-safe versions
 * for files belonging to the Dealerlux Utility MU plugin.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Traits;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Reusable Dealerlux Utility plugin asset helpers.
 */
trait Plugin_Assets {

	/**
	 * Default asset version used when a file cannot be found.
	 *
	 * @var string
	 */
	private static $plugin_asset_fallback_version = '1.0.0';

	/**
	 * Get the Dealerlux Utility plugin root filesystem path.
	 *
	 * The trait resides in:
	 *
	 * src/traits/
	 *
	 * @return string
	 */
	protected function get_plugin_root_path() {
		return wp_normalize_path(
			dirname( __DIR__, 2 )
		);
	}

	/**
	 * Get the Dealerlux Utility bootstrap file path.
	 *
	 * The repository bootstrap file is core.php.
	 *
	 * @return string
	 */
	protected function get_plugin_file_path() {
		return wp_normalize_path(
			$this->get_plugin_root_path()
			. '/core.php'
		);
	}

	/**
	 * Get an absolute filesystem path inside Dealerlux Utility.
	 *
	 * An empty relative path returns the plugin root directory.
	 *
	 * @param string $relative_path Relative path from the plugin root.
	 * @return string
	 */
	protected function get_plugin_path( $relative_path = '' ) {
		$relative_path = $this->normalize_plugin_relative_path(
			$relative_path
		);

		if ( '' === $relative_path ) {
			return $this->get_plugin_root_path();
		}

		return wp_normalize_path(
			$this->get_plugin_root_path()
			. '/'
			. $relative_path
		);
	}

	/**
	 * Get an absolute filesystem path for a plugin asset.
	 *
	 * The supplied path must be relative to the Dealerlux Utility root.
	 *
	 * @param string $relative_path Relative asset path.
	 * @return string
	 */
	protected function get_asset_path( $relative_path ) {
		$relative_path = $this->normalize_plugin_relative_path(
			$relative_path
		);

		if ( '' === $relative_path ) {
			return '';
		}

		return $this->get_plugin_path(
			$relative_path
		);
	}

	/**
	 * Get a public URL for a plugin asset.
	 *
	 * The supplied path must be relative to the Dealerlux Utility root.
	 *
	 * @param string $relative_path Relative asset path.
	 * @return string
	 */
	protected function get_asset_url( $relative_path ) {
		$relative_path = $this->normalize_plugin_relative_path(
			$relative_path
		);

		if ( '' === $relative_path ) {
			return '';
		}

		return plugins_url(
			$relative_path,
			$this->get_plugin_file_path()
		);
	}

	/**
	 * Get a cache-safe asset version.
	 *
	 * The asset's last-modified timestamp is used so developers do not need
	 * to update version strings manually after changing CSS or JavaScript.
	 *
	 * @param string $relative_path Relative asset path.
	 * @return string
	 */
	protected function get_asset_version( $relative_path ) {
		$absolute_path = $this->get_asset_path(
			$relative_path
		);

		if (
			'' !== $absolute_path &&
			is_file( $absolute_path )
		) {
			$modified_time = filemtime(
				$absolute_path
			);

			if ( false !== $modified_time ) {
				return (string) $modified_time;
			}
		}

		return self::$plugin_asset_fallback_version;
	}

	/**
	 * Determine whether a plugin asset exists and is readable.
	 *
	 * @param string $relative_path Relative asset path.
	 * @return bool
	 */
	protected function plugin_asset_exists( $relative_path ) {
		$absolute_path = $this->get_asset_path(
			$relative_path
		);

		return (
			'' !== $absolute_path &&
			is_file( $absolute_path ) &&
			is_readable( $absolute_path )
		);
	}

	/**
	 * Normalize and validate a relative plugin path.
	 *
	 * Directory traversal segments are rejected so consuming classes cannot
	 * accidentally resolve files outside the plugin directory.
	 *
	 * @param mixed $relative_path Relative plugin path.
	 * @return string
	 */
	private function normalize_plugin_relative_path( $relative_path ) {
		if ( ! is_string( $relative_path ) ) {
			return '';
		}

		$relative_path = wp_normalize_path(
			trim( $relative_path )
		);

		$relative_path = ltrim(
			$relative_path,
			'/'
		);

		if ( '' === $relative_path ) {
			return '';
		}

		$segments = explode(
			'/',
			$relative_path
		);

		foreach ( $segments as $segment ) {
			if (
				'' === $segment ||
				'.' === $segment ||
				'..' === $segment
			) {
				return '';
			}
		}

		return implode(
			'/',
			$segments
		);
	}
}