<?php
/**
 * Class Plugin_Switcher
 *
 * Deactivates other managed SSS client plugins and activates the plugin
 * assigned to the selected client environment.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Modules\Client_Switcher;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Synchronize managed client plugins.
 */
class Plugin_Switcher {

	/**
	 * Use the Dealerlux Utility singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Determine whether the class may be registered.
	 *
	 * @return bool
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * This dependency registers no independent hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {}

	/**
	 * Synchronize managed plugins with the selected website.
	 *
	 * Only plugins supplied through $managed_slugs are controlled.
	 * Unrelated WordPress plugins are never changed.
	 *
	 * @param array $website       Selected website configuration.
	 * @param array $managed_slugs Managed client plugin directory slugs.
	 * @return array
	 */
	public function switch_to(
		array $website,
		array $managed_slugs
	) {
		$this->load_plugin_api();

		$target_slug = $this->get_target_slug(
			$website
		);

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

		if ( ! is_array( $installed_plugins ) ) {
			return $this->failure(
				'The installed plugin registry could not be loaded.'
			);
		}

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

		if (
			! in_array(
				$target_slug,
				$managed_slugs,
				true
			)
		) {
			$managed_slugs[] = $target_slug;
		}

		$managed_files = $this->resolve_managed_plugin_files(
			$managed_slugs,
			$installed_plugins
		);

		if (
			! in_array(
				$target_file,
				$managed_files,
				true
			)
		) {
			$managed_files[] = $target_file;
		}

		$active_managed_files = $this->get_active_managed_plugin_files(
			$managed_files
		);

		$target_was_active = in_array(
			$target_file,
			$active_managed_files,
			true
		);

		$active_alternatives = array_values(
			array_diff(
				$active_managed_files,
				array( $target_file )
			)
		);

		/*
		 * Deactivate every other managed client plugin before activation.
		 *
		 * The selected target is not unnecessarily deactivated when it is
		 * already active.
		 */
		if ( ! empty( $active_alternatives ) ) {
			do_action(
				'dealerlux_utility_client_switcher_before_deactivate',
				$active_alternatives,
				$target_file,
				$website
			);

			deactivate_plugins(
				$active_alternatives,
				true,
				false
			);
		}

		$still_active_alternatives =
			$this->get_active_managed_plugin_files(
				$active_alternatives
			);

		if ( ! empty( $still_active_alternatives ) ) {
			return $this->failure(
				sprintf(
					'The following managed plugins could not be deactivated: %s.',
					implode(
						', ',
						$still_active_alternatives
					)
				)
			);
		}

		if ( ! $target_was_active ) {
			do_action(
				'dealerlux_utility_client_switcher_before_activate',
				$target_file,
				$target_slug,
				$website
			);

			$activation_result = activate_plugin(
				$target_file,
				'',
				false,
				false
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

		$final_active_managed_files =
			$this->get_active_managed_plugin_files(
				$managed_files
			);

		$unexpected_active_plugins = array_values(
			array_diff(
				$final_active_managed_files,
				array( $target_file )
			)
		);

		if ( ! empty( $unexpected_active_plugins ) ) {
			return $this->failure(
				sprintf(
					'Unexpected managed client plugins remain active: %s.',
					implode(
						', ',
						$unexpected_active_plugins
					)
				)
			);
		}

		$changed = (
			! $target_was_active ||
			! empty( $active_alternatives )
		);

		do_action(
			'dealerlux_utility_client_switcher_plugin_synchronized',
			$target_file,
			$target_slug,
			$active_alternatives,
			$website
		);

		return array(
			'success'             => true,
			'changed'             => $changed,
			'target_slug'         => $target_slug,
			'target_file'         => $target_file,
			'deactivated_plugins' => $active_alternatives,
			'active_plugins'      => $final_active_managed_files,
			'error'               => null,
		);
	}

	/**
	 * Get the assigned plugin directory slug.
	 *
	 * @param array $website Selected website.
	 * @return string
	 */
	private function get_target_slug(
		array $website
	) {
		if (
			! isset( $website['plugin'] ) ||
			! is_scalar( $website['plugin'] )
		) {
			return '';
		}

		return trim(
			sanitize_text_field(
				(string) $website['plugin']
			),
			'/'
		);
	}

	/**
	 * Resolve installed files for managed plugin slugs.
	 *
	 * @param array $managed_slugs     Managed directory slugs.
	 * @param array $installed_plugins Installed plugin definitions.
	 * @return array
	 */
	private function resolve_managed_plugin_files(
		array $managed_slugs,
		array $installed_plugins
	) {
		$managed_files = array();

		foreach ( $managed_slugs as $plugin_slug ) {
			$plugin_file = $this->find_plugin_file(
				$plugin_slug,
				$installed_plugins
			);

			if ( null !== $plugin_file ) {
				$managed_files[] = $plugin_file;
			}
		}

		return array_values(
			array_unique( $managed_files )
		);
	}

	/**
	 * Get currently active managed plugin files.
	 *
	 * @param array $managed_files Managed plugin files.
	 * @return array
	 */
	private function get_active_managed_plugin_files(
		array $managed_files
	) {
		$active_files = array();

		foreach ( $managed_files as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_files[] = $plugin_file;
			}
		}

		return array_values(
			array_unique( $active_files )
		);
	}

	/**
	 * Find the main plugin file using its directory slug.
	 *
	 * @param string $plugin_slug       Plugin directory slug.
	 * @param array  $installed_plugins Installed plugins.
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

		foreach (
			array_keys( $installed_plugins )
			as $plugin_file
		) {
			$plugin_file = ltrim(
				wp_normalize_path(
					(string) $plugin_file
				),
				'/'
			);

			if (
				0 === strpos(
					$plugin_file,
					$prefix
				)
			) {
				return $plugin_file;
			}
		}

		return null;
	}

	/**
	 * Normalize managed plugin directory slugs.
	 *
	 * @param array $plugin_slugs Plugin directory slugs.
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

			if ( '' !== $plugin_slug ) {
				$normalized[] = $plugin_slug;
			}
		}

		return array_values(
			array_unique( $normalized )
		);
	}

	/**
	 * Restore plugins following a failed target activation.
	 *
	 * @param array $plugin_files Plugin files.
	 * @return void
	 */
	private function restore_plugins(
		array $plugin_files
	) {
		foreach ( $plugin_files as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				continue;
			}

			$result = activate_plugin(
				$plugin_file,
				'',
				false,
				false
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

		require_once ABSPATH
			. 'wp-admin/includes/plugin.php';
	}

	/**
	 * Return a normalized failure result.
	 *
	 * @param string         $message Error message.
	 * @param \WP_Error|null $error   Optional error.
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
			'active_plugins'      => array(),
			'error'               => $error instanceof \WP_Error
				? $error
				: new \WP_Error(
					'dealerlux_utility_client_switcher_failed',
					$message
				),
		);
	}

	/**
	 * Write a Client Switcher message to the error log.
	 *
	 * @param string $message Message.
	 * @return void
	 */
	private function log(
		$message
	) {
		error_log(
			sprintf(
				'[Dealerlux Utility Client Switcher] %s',
				(string) $message
			)
		);
	}
}