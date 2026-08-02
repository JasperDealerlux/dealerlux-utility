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
use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Convention-based virtual post asset loader.
 */
class Posts_Asset_Loader {

	use Singleton_Trait;

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
	 * Plugin root filesystem path.
	 *
	 * @var string
	 */
	private $plugin_directory;

	/**
	 * Plugin root public URL.
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->plugin_directory = trailingslashit(
			wp_normalize_path(
				dirname( __DIR__, 2 )
			)
		);

		$this->plugin_url = trailingslashit(
			plugin_dir_url(
				$this->plugin_directory . 'core.php'
			)
		);
	}

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

		$post_type = sanitize_key( $post_type );
		$post_key  = sanitize_key( $post_key );
		$slug      = sanitize_title( $current_post['slug'] );

		if (
			'' === $post_type ||
			'' === $slug
		) {
			return;
		}

		$asset_directory = $this->get_asset_directory(
			$post_type,
			$slug
		);

		$asset_url = $this->get_asset_url(
			$post_type,
			$slug
		);

		$this->enqueue_style(
			$asset_directory,
			$asset_url,
			$post_type,
			$post_key,
			$slug
		);

		$this->enqueue_script(
			$asset_directory,
			$asset_url,
			$post_type,
			$post_key,
			$slug
		);
	}

	/**
	 * Get the asset filesystem directory for a virtual post.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Virtual post slug.
	 * @return string
	 */
	private function get_asset_directory(
		$post_type,
		$slug
	) {
		return trailingslashit(
			$this->plugin_directory
			. self::ASSETS_DIRECTORY
			. '/'
			. $post_type
			. '/'
			. $slug
		);
	}

	/**
	 * Get the public asset URL for a virtual post.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Virtual post slug.
	 * @return string
	 */
	private function get_asset_url(
		$post_type,
		$slug
	) {
		return trailingslashit(
			$this->plugin_url
			. self::ASSETS_DIRECTORY
			. '/'
			. rawurlencode( $post_type )
			. '/'
			. rawurlencode( $slug )
		);
	}

	/**
	 * Enqueue the virtual post stylesheet when it exists.
	 *
	 * @param string $asset_directory Asset filesystem directory.
	 * @param string $asset_url       Public asset URL.
	 * @param string $post_type       Post type.
	 * @param string $post_key        Registry key.
	 * @param string $slug            Post slug.
	 * @return void
	 */
	private function enqueue_style(
		$asset_directory,
		$asset_url,
		$post_type,
		$post_key,
		$slug
	) {
		$file_path = $asset_directory
			. self::STYLE_FILENAME;

		if ( ! is_file( $file_path ) ) {
			return;
		}

		wp_enqueue_style(
			$this->build_handle(
				$post_type,
				$post_key,
				$slug,
				'style'
			),
			$asset_url . self::STYLE_FILENAME,
			array(),
			$this->get_asset_version( $file_path )
		);
	}

	/**
	 * Enqueue the virtual post script when it exists.
	 *
	 * @param string $asset_directory Asset filesystem directory.
	 * @param string $asset_url       Public asset URL.
	 * @param string $post_type       Post type.
	 * @param string $post_key        Registry key.
	 * @param string $slug            Post slug.
	 * @return void
	 */
	private function enqueue_script(
		$asset_directory,
		$asset_url,
		$post_type,
		$post_key,
		$slug
	) {
		$file_path = $asset_directory
			. self::SCRIPT_FILENAME;

		if ( ! is_file( $file_path ) ) {
			return;
		}

		wp_enqueue_script(
			$this->build_handle(
				$post_type,
				$post_key,
				$slug,
				'script'
			),
			$asset_url . self::SCRIPT_FILENAME,
			array(),
			$this->get_asset_version( $file_path ),
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

		return sanitize_key( $identifier );
	}

	/**
	 * Get a cache-busting asset version.
	 *
	 * File modification time means developers do not need to manually update
	 * an asset version after editing CSS or JavaScript.
	 *
	 * @param string $file_path Asset filesystem path.
	 * @return string|null
	 */
	private function get_asset_version( $file_path ) {
		$modified_time = filemtime( $file_path );

		return false !== $modified_time
			? (string) $modified_time
			: null;
	}
}