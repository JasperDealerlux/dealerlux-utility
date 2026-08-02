<?php
/**
 * Class Posts_Asset_Loader
 *
 * Automatically loads CSS and JavaScript assets belonging to the currently
 * matched Dealerlux virtual post.
 *
 * Asset convention:
 *
 * assets/posts/{post-type}/{slug}/style.css
 * assets/posts/{post-type}/{slug}/script.js
 *
 * Example:
 *
 * assets/posts/page/dealerlux-forms/style.css
 * assets/posts/page/dealerlux-forms/script.js
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Assets;

use DealerluxUtils\Registries\Posts_Registry;
use DealerluxUtils\Traits\Plugin_Assets as Plugin_Assets_Trait;
use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Convention-based virtual post asset loader.
 */
class Posts_Asset_Loader {

	/**
	 * Use the singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Use shared Dealerlux Utility asset helpers.
	 */
	use Plugin_Assets_Trait;

	/**
	 * Relative directory containing virtual post assets.
	 *
	 * @var string
	 */
	private const ASSETS_DIRECTORY = 'assets/posts';

	/**
	 * Default stylesheet filename.
	 *
	 * @var string
	 */
	private const STYLE_FILENAME = 'style.css';

	/**
	 * Default script filename.
	 *
	 * @var string
	 */
	private const SCRIPT_FILENAME = 'script.js';

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Determine whether this component can be registered.
	 *
	 * @return bool
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_current_post_assets' ),
			20
		);
	}

	/**
	 * Enqueue assets for the current virtual post.
	 *
	 * Nothing is enqueued when:
	 *
	 * - The current request is not a Dealerlux virtual post.
	 * - The post type or slug is unavailable.
	 * - The corresponding asset files do not exist.
	 *
	 * @return void
	 */
	public function enqueue_current_post_assets() {
		$posts_registry = Posts_Registry::instance();

		if ( ! $posts_registry->is_virtual_post() ) {
			return;
		}

		$current_post = $posts_registry->get_current_post();
		$post_type    = $posts_registry->get_current_post_type();
		$post_key     = $posts_registry->get_current_post_key();

		if (
			! is_array( $current_post ) ||
			empty( $current_post['slug'] ) ||
			! is_string( $current_post['slug'] )
		) {
			return;
		}

		$post_type = sanitize_key(
			$post_type
		);

		$post_key = sanitize_key(
			$post_key
		);

		$slug = sanitize_title(
			$current_post['slug']
		);

		if (
			'' === $post_type ||
			'' === $slug
		) {
			return;
		}

		$relative_asset_directory = $this->get_relative_asset_directory(
			$post_type,
			$slug
		);

		if ( '' === $relative_asset_directory ) {
			return;
		}

		$this->enqueue_style(
			$relative_asset_directory,
			$post_type,
			$post_key,
			$slug
		);

		$this->enqueue_script(
			$relative_asset_directory,
			$post_type,
			$post_key,
			$slug
		);
	}

	/**
	 * Get the relative asset directory for a virtual post.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Virtual post slug.
	 * @return string
	 */
	private function get_relative_asset_directory(
		$post_type,
		$slug
	) {
		$post_type = sanitize_key(
			$post_type
		);

		$slug = sanitize_title(
			$slug
		);

		if (
			'' === $post_type ||
			'' === $slug
		) {
			return '';
		}

		return self::ASSETS_DIRECTORY
			. '/'
			. $post_type
			. '/'
			. $slug;
	}

	/**
	 * Enqueue the virtual post stylesheet when it exists.
	 *
	 * @param string $relative_asset_directory Relative asset directory.
	 * @param string $post_type               Post type.
	 * @param string $post_key                Registry key.
	 * @param string $slug                    Post slug.
	 * @return void
	 */
	private function enqueue_style(
		$relative_asset_directory,
		$post_type,
		$post_key,
		$slug
	) {
		$relative_file_path = $relative_asset_directory
			. '/'
			. self::STYLE_FILENAME;

		if ( ! $this->plugin_asset_exists( $relative_file_path ) ) {
			return;
		}

		$asset_url = $this->get_asset_url(
			$relative_file_path
		);

		if ( '' === $asset_url ) {
			return;
		}

		wp_enqueue_style(
			$this->build_handle(
				$post_type,
				$post_key,
				$slug,
				'style'
			),
			$asset_url,
			array(),
			$this->get_asset_version(
				$relative_file_path
			)
		);
	}

	/**
	 * Enqueue the virtual post script when it exists.
	 *
	 * @param string $relative_asset_directory Relative asset directory.
	 * @param string $post_type               Post type.
	 * @param string $post_key                Registry key.
	 * @param string $slug                    Post slug.
	 * @return void
	 */
	private function enqueue_script(
		$relative_asset_directory,
		$post_type,
		$post_key,
		$slug
	) {
		$relative_file_path = $relative_asset_directory
			. '/'
			. self::SCRIPT_FILENAME;

		if ( ! $this->plugin_asset_exists( $relative_file_path ) ) {
			return;
		}

		$asset_url = $this->get_asset_url(
			$relative_file_path
		);

		if ( '' === $asset_url ) {
			return;
		}

		wp_enqueue_script(
			$this->build_handle(
				$post_type,
				$post_key,
				$slug,
				'script'
			),
			$asset_url,
			array(),
			$this->get_asset_version(
				$relative_file_path
			),
			true
		);
	}

	/**
	 * Build a unique WordPress asset handle.
	 *
	 * The registry key is included to prevent collisions when multiple virtual
	 * posts use the same leaf slug.
	 *
	 * @param string $post_type Post type.
	 * @param string $post_key  Registry key.
	 * @param string $slug      Post slug.
	 * @param string $asset     Asset type.
	 * @return string
	 */
	private function build_handle(
		$post_type,
		$post_key,
		$slug,
		$asset
	) {
		$identifier = implode(
			'-',
			array_filter(
				array(
					'dealerlux',
					'virtual-post',
					$post_type,
					$post_key,
					$slug,
					$asset,
				)
			)
		);

		return sanitize_key(
			$identifier
		);
	}
}