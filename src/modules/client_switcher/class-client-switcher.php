<?php
/**
 * Class Client_Switcher
 *
 * Coordinates client environment resolution, configuration validation,
 * managed plugin synchronization, SSS API constants, and persistence.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Modules\Client_Switcher;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Dealerlux Utility Client Switcher.
 */
class Client_Switcher {

	/**
	 * Use the Dealerlux Utility singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Whether the switcher has already run during this request.
	 *
	 * @var bool
	 */
	private $has_run = false;

	/**
	 * Selected website configuration.
	 *
	 * @var array|null
	 */
	private $selected_website = null;

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Determine whether this class may be registered.
	 *
	 * Always register so fallback constants can be defined when the external
	 * configuration is unavailable or invalid.
	 *
	 * @return bool
	 */
	protected static function can_register() {
		return true;
	}

	/**
	 * Register the Client Switcher.
	 *
	 * Initializer calls this class while muplugins_loaded is running, before
	 * ordinary active plugins are loaded.
	 *
	 * @return void
	 */
	public function register_hooks() {
		$this->run();
	}

	/**
	 * Execute the Client Switcher.
	 *
	 * @return bool
	 */
	public function run() {
		if ( $this->has_run ) {
			return null !== $this->selected_website;
		}

		$this->has_run = true;

		$configuration = $this->get_configuration();

		if ( null === $configuration ) {
			$this->define_fallback_api_constants();

			return false;
		}

		$validation_error = $this->validate_configuration(
			$configuration
		);

		if ( $validation_error instanceof \WP_Error ) {
			$this->define_fallback_api_constants();

			$this->dispatch_failure(
				null,
				$validation_error
			);

			return false;
		}

		$website = Environment_Resolver::instance()->resolve(
			$configuration
		);

		if ( null === $website ) {
			$this->define_fallback_api_constants();

			$this->dispatch_failure(
				null,
				new \WP_Error(
					'dealerlux_utility_client_environment_not_found',
					$this->build_selection_failure_message(
						$configuration
					)
				)
			);

			return false;
		}

		$this->selected_website = $website;

		/*
		 * These constant names must remain unchanged because they are consumed
		 * by the SPA Software Solutions plugin.
		 */
		$this->define_api_constants(
			$website
		);

		/*
		 * Managed client plugins are derived from websites[*].plugin.
		 *
		 * There is no second manually maintained plugins array.
		 */
		$managed_plugins = $this->get_managed_plugin_slugs(
			$configuration
		);

		if ( empty( $managed_plugins ) ) {
			$this->dispatch_failure(
				$website,
				new \WP_Error(
					'dealerlux_utility_client_plugins_empty',
					'No managed client plugins could be derived from the websites configuration.'
				)
			);

			return false;
		}

		/*
		 * Lightweight path for ordinary requests.
		 *
		 * When the selected environment, stored selection, and active managed
		 * plugin state already match, no plugin discovery, activation,
		 * deactivation, or database write occurs.
		 */
		if (
			$this->is_environment_current(
				$website,
				$managed_plugins
			)
		) {
			return true;
		}

		do_action(
			'dealerlux_utility_client_switcher_before_switch',
			$website,
			$managed_plugins
		);

		$result = Plugin_Switcher::instance()->switch_to(
			$website,
			$managed_plugins
		);

		if (
			! is_array( $result ) ||
			empty( $result['success'] )
		) {
			$error = (
				is_array( $result ) &&
				isset( $result['error'] ) &&
				$result['error'] instanceof \WP_Error
			)
				? $result['error']
				: new \WP_Error(
					'dealerlux_utility_client_switcher_failed',
					'The Client Switcher could not synchronize the managed client plugins.'
				);

			$this->dispatch_failure(
				$website,
				$error,
				is_array( $result )
					? $result
					: array()
			);

			return false;
		}

		$target_slug = isset( $result['target_slug'] )
			? trim(
				(string) $result['target_slug'],
				'/'
			)
			: '';

		$target_file = isset( $result['target_file'] )
			? ltrim(
				wp_normalize_path(
					(string) $result['target_file']
				),
				'/'
			)
			: '';

		if (
			'' === $target_slug ||
			'' === $target_file
		) {
			$this->dispatch_failure(
				$website,
				new \WP_Error(
					'dealerlux_utility_client_switcher_invalid_result',
					'The Client Switcher completed without a valid target plugin.'
				),
				$result
			);

			return false;
		}

		$stored = Selection_Store::instance()->save(
			$target_slug,
			$target_file,
			$website
		);

		if ( ! $stored ) {
			$this->log(
				sprintf(
					'The selected plugin state could not be stored for "%s".',
					$target_slug
				)
			);
		}

		do_action(
			'dealerlux_utility_client_switcher_switched',
			$website,
			$result
		);

		return true;
	}

	/**
	 * Validate the Client Switcher configuration.
	 *
	 * @param array $configuration Client environment configuration.
	 * @return true|\WP_Error
	 */
	private function validate_configuration(
		array $configuration
	) {
		if (
			! isset( $configuration['env'] ) ||
			! is_array( $configuration['env'] )
		) {
			return new \WP_Error(
				'dealerlux_utility_client_env_missing',
				'The Client Switcher env configuration is missing or invalid.'
			);
		}

		if (
			! isset( $configuration['websites'] ) ||
			! is_array( $configuration['websites'] ) ||
			empty( $configuration['websites'] )
		) {
			return new \WP_Error(
				'dealerlux_utility_client_websites_missing',
				'The Client Switcher websites configuration is missing or empty.'
			);
		}

		$seen_client_ids       = array();
		$seen_plugin_slugs     = array();
		$configuration_errors = array();

		foreach (
			$configuration['websites']
			as $website_key => $website
		) {
			if ( ! is_array( $website ) ) {
				$configuration_errors[] = sprintf(
					'Website "%s" must contain an array.',
					$website_key
				);

				continue;
			}

			$plugin_slug = isset( $website['plugin'] )
				? trim(
					(string) $website['plugin'],
					'/'
				)
				: '';

			if ( '' === $plugin_slug ) {
				$configuration_errors[] = sprintf(
					'Website "%s" does not define a plugin.',
					$website_key
				);
			} else {
				$seen_plugin_slugs[ $plugin_slug ] = true;
			}

			$client_id = isset( $website['client_id'] )
				? absint( $website['client_id'] )
				: 0;

			if ( 0 === $client_id ) {
				$configuration_errors[] = sprintf(
					'Website "%s" does not define a valid client_id.',
					$website_key
				);
			} elseif ( isset( $seen_client_ids[ $client_id ] ) ) {
				$configuration_errors[] = sprintf(
					'Website "%1$s" duplicates client_id %2$d already assigned to "%3$s".',
					$website_key,
					$client_id,
					$seen_client_ids[ $client_id ]
				);
			} else {
				$seen_client_ids[ $client_id ] = $website_key;
			}

			$dealer_group_id = isset(
				$website['dealer_group_id']
			)
				? absint( $website['dealer_group_id'] )
				: 0;

			if ( 0 === $dealer_group_id ) {
				$configuration_errors[] = sprintf(
					'Website "%s" does not define a valid dealer_group_id.',
					$website_key
				);
			}
		}

		if ( ! empty( $configuration_errors ) ) {
			return new \WP_Error(
				'dealerlux_utility_client_configuration_invalid',
				implode(
					' ',
					$configuration_errors
				)
			);
		}

		return true;
	}

	/**
	 * Derive managed client plugin slugs from websites[*].plugin.
	 *
	 * This is the single source of truth for managed plugins.
	 *
	 * @param array $configuration Client environment configuration.
	 * @return array
	 */
	private function get_managed_plugin_slugs(
		array $configuration
	) {
		$managed_plugins = array();

		if (
			! isset( $configuration['websites'] ) ||
			! is_array( $configuration['websites'] )
		) {
			return $managed_plugins;
		}

		foreach (
			$configuration['websites']
			as $website
		) {
			if (
				! is_array( $website ) ||
				! isset( $website['plugin'] ) ||
				! is_scalar( $website['plugin'] )
			) {
				continue;
			}

			$plugin_slug = trim(
				sanitize_text_field(
					(string) $website['plugin']
				),
				'/'
			);

			if ( '' !== $plugin_slug ) {
				$managed_plugins[] = $plugin_slug;
			}
		}

		return array_values(
			array_unique(
				$managed_plugins
			)
		);
	}

	/**
	 * Determine whether the currently selected environment is already correct.
	 *
	 * @param array $website        Selected website configuration.
	 * @param array $managed_plugins Managed plugin directory slugs.
	 * @return bool
	 */
	private function is_environment_current(
		array $website,
		array $managed_plugins
	) {
		$stored_selection = Selection_Store::instance()->get();

		if ( empty( $stored_selection ) ) {
			return false;
		}

		$target_slug = isset( $website['plugin'] )
			? trim(
				(string) $website['plugin'],
				'/'
			)
			: '';

		if ( '' === $target_slug ) {
			return false;
		}

		$expected_values = array(
			'plugin_slug'     => $target_slug,
			'domain'          => isset( $website['domain'] )
				? (string) $website['domain']
				: '',
			'client_id'       => isset( $website['client_id'] )
				? (string) absint( $website['client_id'] )
				: '0',
			'dealer_group_id' => isset( $website['dealer_group_id'] )
				? (string) absint(
					$website['dealer_group_id']
				)
				: '0',
		);

		foreach ( $expected_values as $key => $expected_value ) {
			$stored_value = isset( $stored_selection[ $key ] )
				? (string) $stored_selection[ $key ]
				: '';

			if ( $stored_value !== $expected_value ) {
				return false;
			}
		}

		$target_file = isset( $stored_selection['plugin_file'] )
			? ltrim(
				wp_normalize_path(
					(string) $stored_selection['plugin_file']
				),
				'/'
			)
			: '';

		if ( '' === $target_file ) {
			return false;
		}

		$active_plugins = get_option(
			'active_plugins',
			array()
		);

		if ( ! is_array( $active_plugins ) ) {
			return false;
		}

		$active_plugins = $this->normalize_plugin_files(
			$active_plugins
		);

		if (
			! in_array(
				$target_file,
				$active_plugins,
				true
			)
		) {
			return false;
		}

		$managed_plugins = $this->normalize_plugin_slugs(
			$managed_plugins
		);

		foreach ( $active_plugins as $active_plugin_file ) {
			$active_directory = dirname(
				$active_plugin_file
			);

			if ( '.' === $active_directory ) {
				continue;
			}

			if (
				in_array(
					$active_directory,
					$managed_plugins,
					true
				) &&
				$active_plugin_file !== $target_file
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Normalize plugin directory slugs.
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
				(string) $plugin_slug,
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
	 * Normalize plugin files.
	 *
	 * @param array $plugin_files Plugin files.
	 * @return array
	 */
	private function normalize_plugin_files(
		array $plugin_files
	) {
		$normalized = array();

		foreach ( $plugin_files as $plugin_file ) {
			if ( ! is_scalar( $plugin_file ) ) {
				continue;
			}

			$plugin_file = ltrim(
				wp_normalize_path(
					(string) $plugin_file
				),
				'/'
			);

			if ( '' !== $plugin_file ) {
				$normalized[] = $plugin_file;
			}
		}

		return array_values(
			array_unique( $normalized )
		);
	}

	/**
	 * Get the selected website configuration.
	 *
	 * @return array|null
	 */
	public function get_selected_website() {
		return is_array( $this->selected_website )
			? $this->selected_website
			: null;
	}

	/**
	 * Get the Client Switcher configuration.
	 *
	 * @return array|null
	 */
	private function get_configuration() {
		if ( ! defined( 'SSS_CLIENT_ENVIRONMENT' ) ) {
			$this->log(
				'SSS_CLIENT_ENVIRONMENT is not defined.'
			);

			return null;
		}

		$configuration = constant(
			'SSS_CLIENT_ENVIRONMENT'
		);

		if ( ! is_array( $configuration ) ) {
			$this->log(
				'SSS_CLIENT_ENVIRONMENT must contain an array.'
			);

			return null;
		}

		return $configuration;
	}

	/**
	 * Define the required SSS API constants.
	 *
	 * @param array $website Selected website.
	 * @return void
	 */
	private function define_api_constants(
		array $website
	) {
		if ( ! defined( 'SSS_API_DEALER_ID' ) ) {
			define(
				'SSS_API_DEALER_ID',
				isset( $website['client_id'] )
					? absint( $website['client_id'] )
					: 0
			);
		}

		if ( ! defined( 'SSS_API_DEALER_GROUP_ID' ) ) {
			define(
				'SSS_API_DEALER_GROUP_ID',
				isset( $website['dealer_group_id'] )
					? absint( $website['dealer_group_id'] )
					: 0
			);
		}
	}

	/**
	 * Define compatibility constants when selection fails.
	 *
	 * @return void
	 */
	private function define_fallback_api_constants() {
		if ( ! defined( 'SSS_API_DEALER_ID' ) ) {
			define(
				'SSS_API_DEALER_ID',
				0
			);
		}

		if ( ! defined( 'SSS_API_DEALER_GROUP_ID' ) ) {
			define(
				'SSS_API_DEALER_GROUP_ID',
				0
			);
		}
	}

	/**
	 * Build the environment selection failure message.
	 *
	 * @param array $configuration Client configuration.
	 * @return string
	 */
	private function build_selection_failure_message(
		array $configuration
	) {
		$environment = (
			isset( $configuration['env'] ) &&
			is_array( $configuration['env'] )
		)
			? $configuration['env']
			: array();

		$selector = isset( $environment['use'] )
			? (string) $environment['use']
			: 'client_id';

		$value = isset( $environment['set'] )
			? (string) $environment['set']
			: '';

		return sprintf(
			'No website matched %1$s=%2$s.',
			$selector,
			$value
		);
	}

	/**
	 * Dispatch a Client Switcher failure.
	 *
	 * @param array|null $website Selected website.
	 * @param \WP_Error  $error   Failure.
	 * @param array      $context Additional context.
	 * @return void
	 */
	private function dispatch_failure(
		$website,
		\WP_Error $error,
		array $context = array()
	) {
		$this->log(
			$error->get_error_message()
		);

		do_action(
			'dealerlux_utility_client_switcher_failed',
			$website,
			$error,
			$context
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