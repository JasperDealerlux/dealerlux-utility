<?php
/**
 * Trait Clients_Config_Loader
 *
 * Locates and loads clients-config.php from the directory containing
 * WordPress's wp-config.php file.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Traits;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Provides external client configuration loading.
 */
trait Clients_Config_Loader {

	/**
	 * Name of the external client configuration file.
	 *
	 * @var string
	 */
	private $clients_configuration_filename = 'clients-config.php';

	/**
	 * Load the Dealerlux clients configuration.
	 *
	 * The configuration file must exist in the same directory as the active
	 * wp-config.php file.
	 *
	 * @return bool
	 */
	private function load_clients_configuration() {
		/*
		 * Another bootstrap file may already have loaded the configuration.
		 */
		if ( defined( 'SSS_CLIENT_ENVIRONMENT' ) ) {
			return $this->validate_clients_environment_constant();
		}

		$configuration_file =
			$this->locate_clients_configuration_file();

		if ( null === $configuration_file ) {
			$this->log_clients_configuration_error(
				sprintf(
					'Unable to locate %s beside wp-config.php.',
					$this->clients_configuration_filename
				)
			);

			return false;
		}

		require_once $configuration_file;

		if ( ! defined( 'SSS_CLIENT_ENVIRONMENT' ) ) {
			$this->log_clients_configuration_error(
				sprintf(
					'The configuration file was loaded, but SSS_CLIENT_ENVIRONMENT was not defined: %s',
					$configuration_file
				)
			);

			return false;
		}

		if ( ! $this->validate_clients_environment_constant() ) {
			$this->log_clients_configuration_error(
				sprintf(
					'The SSS_CLIENT_ENVIRONMENT value loaded from "%s" is invalid.',
					$configuration_file
				)
			);

			return false;
		}

		/**
		 * Fires after the external clients configuration has been loaded and
		 * validated.
		 *
		 * @param string $configuration_file Absolute configuration file path.
		 * @param array  $configuration      Client environment configuration.
		 */
		do_action(
			'dealerlux_utility_clients_configuration_loaded',
			$configuration_file,
			constant( 'SSS_CLIENT_ENVIRONMENT' )
		);

		return true;
	}

	/**
	 * Locate clients-config.php beside the active wp-config.php file.
	 *
	 * WordPress supports wp-config.php in either:
	 *
	 * 1. The WordPress installation directory.
	 * 2. One directory above the WordPress installation directory.
	 *
	 * @return string|null
	 */
	private function locate_clients_configuration_file() {
		$wp_config_file = $this->locate_wordpress_configuration_file();

		if ( null === $wp_config_file ) {
			return null;
		}

		$configuration_file = wp_normalize_path(
			trailingslashit(
				dirname( $wp_config_file )
			) . $this->clients_configuration_filename
		);

		if (
			! is_file( $configuration_file ) ||
			! is_readable( $configuration_file )
		) {
			return null;
		}

		return $configuration_file;
	}

	/**
	 * Locate the active wp-config.php file.
	 *
	 * @return string|null
	 */
	private function locate_wordpress_configuration_file() {
		if ( ! defined( 'ABSPATH' ) ) {
			$this->log_clients_configuration_error(
				'ABSPATH is not defined; wp-config.php cannot be located.'
			);

			return null;
		}

		$wordpress_root = wp_normalize_path(
			untrailingslashit( ABSPATH )
		);

		$possible_files = array(
			$wordpress_root . '/wp-config.php',
			wp_normalize_path(
				dirname( $wordpress_root )
				. '/wp-config.php'
			),
		);

		foreach ( $possible_files as $wp_config_file ) {
			if (
				is_file( $wp_config_file ) &&
				is_readable( $wp_config_file )
			) {
				return $wp_config_file;
			}
		}

		$this->log_clients_configuration_error(
			'Unable to locate a readable wp-config.php file.'
		);

		return null;
	}

	/**
	 * Validate SSS_CLIENT_ENVIRONMENT.
	 *
	 * This performs structural validation required for loading. Deeper Client
	 * Switcher validation remains inside Client_Switcher.
	 *
	 * @return bool
	 */
	private function validate_clients_environment_constant() {
		if ( ! defined( 'SSS_CLIENT_ENVIRONMENT' ) ) {
			return false;
		}

		$configuration = constant(
			'SSS_CLIENT_ENVIRONMENT'
		);

		if ( ! is_array( $configuration ) ) {
			$this->log_clients_configuration_error(
				'SSS_CLIENT_ENVIRONMENT must contain an array.'
			);

			return false;
		}

		if (
			! isset( $configuration['env'] ) ||
			! is_array( $configuration['env'] )
		) {
			$this->log_clients_configuration_error(
				'SSS_CLIENT_ENVIRONMENT does not contain a valid env configuration.'
			);

			return false;
		}

		if (
			! isset( $configuration['websites'] ) ||
			! is_array( $configuration['websites'] ) ||
			empty( $configuration['websites'] )
		) {
			$this->log_clients_configuration_error(
				'SSS_CLIENT_ENVIRONMENT does not contain a valid websites configuration.'
			);

			return false;
		}

		return true;
	}

	/**
	 * Write a clients configuration error to the PHP error log.
	 *
	 * @param string $message Error message.
	 * @return void
	 */
	private function log_clients_configuration_error(
		$message
	) {
		error_log(
			sprintf(
				'[Dealerlux Utility Clients Config Loader] %s',
				(string) $message
			)
		);
	}
}