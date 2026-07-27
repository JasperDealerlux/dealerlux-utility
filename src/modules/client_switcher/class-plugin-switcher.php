<?php
/**
 * Class Plugin_Switcher
 *
 * Activates the assigned SSS client plugin and deactivates the other managed
 * SSS client plugins.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Modules\Client_Switcher;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Synchronize managed SSS client plugins.
 */
class Plugin_Switcher {

	/**
	 * Use the singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Whether managed plugins should be activated network-wide.
	 *
	 * @var bool
	 */
	private $network_wide = false;

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Determine whether this class should be registered.
	 *
	 * @return bool
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * This dependency does not register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {}

	/**
	 * Synchronize managed plugins with the selected website.
	 *
	 * @param array $website       Selected website configuration.
	 * @param array $managed_slugs Managed plugin directory slugs.
	 * @return array
	 */
	public function switch_to(
		array $website,
		array $managed_slugs
	) {
		$this->load_plugin_api();

		$target_slug = isset( $website['plugin'] )
			? trim(
				sanitize_text_field(
					(string) $website['plugin']
				),
				'/'
			)
			: '';

		if ( '' === $target_slug ) {
			return $this->failure(
				sprintf(
					'No client plugin is configured for "%s".',
					isset( $website['domain'] )
						? $website['domain']
						: 'the selected website'
				)
			);
		}

		$installed_plugins = get_plugins();

		$target_file = $this->find_plugin_file(
			$target_slug,
			$installed_plugins
		);

		if ( null === $target_file ) {
			return $this->failure(
				sprintf(
					'The assigned client plugin is not installed: %s.',
					$target_slug
				)
			);
		}

		$managed_slugs = $this->normalize_plugin_slugs(
			$managed_slugs
		);

		if ( ! in_array( $target_slug, $managed_slugs, true ) ) {
			$managed_slugs[] = $target_slug;
		}

		$active_alternatives = $this->find_active_alternatives(
			$target_file,
			$managed_slugs,
			$installed_plugins
		);

		$target_was_active = is_plugin_active(
			$target_file
		);

		if ( ! empty( $active_alternatives ) ) {
			deactivate_plugins(
				$active_alternatives,
				true,
				$this->network_wide
			);
		}

		if ( ! $target_was_active ) {
			$activation_result = activate_plugin(
				$target_file,
				'',
				false,
				$this->network_wide
			);

			if ( is_wp_error( $activation_result ) ) {
				$this->restore_plugins(
					$active_alternatives
				);

				return $this->failure(
					sprintf(
						'Activation failed for "%1$s": %2$s',
						$target_slug,
						$activation_result->get_error_message()
					),
					$activation_result
				);
			}
		}

		if ( ! is_plugin_active( $target_file ) ) {
			$this->restore_plugins(
				$active_alternatives
			);

			return $this->failure(
				sprintf(
					'The assigned plugin "%s" is not active after activation.',
					$target_slug
				)
			);
		}

		return array(
			'success'             => true,
			'changed'             => (
				! $target_was_active ||
				! empty( $active_alternatives )
			),
			'target_slug'         => $target_slug,
			'target_file'         => $target_file,
			'deactivated_plugins' => $active_alternatives,
			'error'               => null,
		);
	}

	/**
	 * Find an installed plugin's main file by directory slug.
	 *
	 * @param string $plugin_slug       Plugin directory slug.
	 * @param array  $installed_plugins Installed plugin definitions.
	 * @return string|null
	 */
	private function find_plugin_file(
		$plugin_slug,
		array $installed_plugins
	) {
		$plugin_slug = trim(
			(string) $plugin_slug,
			'/'
		);

		if ( '' === $plugin_slug ) {
			return null;
		}

		$prefix = $plugin_slug . '/';

		foreach ( array_keys( $installed_plugins ) as $plugin_file ) {
			$plugin_file = ltrim(
				wp_normalize_path(
					(string) $plugin_file
				),
				'/'
			);

			if ( 0 === strpos( $plugin_file, $prefix ) ) {
				return $plugin_file;
			}
		}

		return null;
	}

	/**
	 * Find active managed plugins other than the target.
	 *
	 * @param string $target_file       Target plugin file.
	 * @param array  $managed_slugs     Managed plugin slugs.
	 * @param array  $installed_plugins Installed plugins.
	 * @return array
	 */
	private function find_active_alternatives(
		$target_file,
		array $managed_slugs,
		array $installed_plugins
	) {
		$active_plugins = array();

		foreach ( $managed_slugs as $plugin_slug ) {
			$plugin_file = $this->find_plugin_file(
				$plugin_slug,
				$installed_plugins
			);

			if (
				null === $plugin_file ||
				$target_file === $plugin_file ||
				! is_plugin_active( $plugin_file )
			) {
				continue;
			}

			$active_plugins[] = $plugin_file;
		}

		return array_values(
			array_unique( $active_plugins )
		);
	}

	/**
	 * Normalize managed plugin slugs.
	 *
	 * @param array $plugin_slugs Plugin slugs.
	 * @return array
	 */
	private function normalize_plugin_slugs(
		array $plugin_slugs
	) {
		$normalized = array();

		foreach ( $plugin_slugs as $plugin_slug ) {
			if ( ! is_scalar( $plugin_slug ) ) {
				continue;
			}

			$plugin_slug = trim(
				sanitize_text_field(
					(string) $plugin_slug
				),
				'/'
			);

			if ( '' === $plugin_slug ) {
				continue;
			}

			$normalized[] = $plugin_slug;
		}

		return array_values(
			array_unique( $normalized )
		);
	}

	/**
	 * Restore plugins deactivated before a failed target activation.
	 *
	 * @param array $plugin_files Plugin files to restore.
	 * @return void
	 */
	private function restore_plugins( array $plugin_files ) {
		foreach ( $plugin_files as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				continue;
			}

			$result = activate_plugin(
				$plugin_file,
				'',
				false,
				$this->network_wide
			);

			if ( is_wp_error( $result ) ) {
				$this->log(
					sprintf(
						'Rollback activation failed for "%1$s": %2$s',
						$plugin_file,
						$result->get_error_message()
					)
				);
			}
		}
	}

	/**
	 * Load WordPress plugin-management functions.
	 *
	 * @return void
	 */
	private function load_plugin_api() {
		if (
			function_exists( 'get_plugins' ) &&
			function_exists( 'activate_plugin' ) &&
			function_exists( 'deactivate_plugins' ) &&
			function_exists( 'is_plugin_active' )
		) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	/**
	 * Create a normalized failure result.
	 *
	 * @param string         $message Error message.
	 * @param \WP_Error|null $error   Optional WordPress error.
	 * @return array
	 */
	private function failure(
		$message,
		$error = null
	) {
		$message = (string) $message;

		$this->log( $message );

		return array(
			'success'             => false,
			'changed'             => false,
			'target_slug'         => '',
			'target_file'         => '',
			'deactivated_plugins' => array(),
			'error'               => $error instanceof \WP_Error
				? $error
				: new \WP_Error(
					'dealerlux_utility_client_switcher_failed',
					$message
				),
		);
	}

	/**
	 * Write a Client Switcher message to the PHP error log.
	 *
	 * @param string $message Log message.
	 * @return void
	 */
	private function log( $message ) {
		error_log(
			sprintf(
				'[Dealerlux Utility Client Switcher] %s',
				(string) $message
			)
		);
	}
}