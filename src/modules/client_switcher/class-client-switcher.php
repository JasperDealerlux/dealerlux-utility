<?php
/**
 * Class Client_Switcher
 *
 * Coordinates client environment resolution, SSS API constant definition,
 * client plugin activation, and selected-plugin persistence.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Modules\Client_Switcher;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Dealerlux Utility Client Switcher module.
 */
class Client_Switcher {

	/**
	 * Use the singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Whether the module has already executed during this request.
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
	 * Determine whether this class should be registered.
	 *
	 * Client Switcher requires its external environment configuration.
	 *
	 * @return bool
	 */
	protected static function can_register() {
		return defined( 'SSS_CLIENT_ENVIRONMENT' );
	}

	/**
	 * Register the module.
	 *
	 * Initializer registers this class while the muplugins_loaded action is
	 * running. The switch must therefore execute immediately instead of adding
	 * another muplugins_loaded callback.
	 *
	 * @return void
	 */
	public function register_hooks() {
		$this->run();
	}

	/**
	 * Execute Client Switcher.
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

		$website = Environment_Resolver::instance()->resolve(
			$configuration
		);

		if ( null === $website ) {
			$this->define_fallback_api_constants();

			do_action(
				'dealerlux_utility_client_switcher_failed',
				null,
				new \WP_Error(
					'dealerlux_utility_client_environment_not_found',
					$this->build_selection_failure_message(
						$configuration
					)
				),
				array()
			);

			return false;
		}

		$this->selected_website = $website;

		$this->define_api_constants(
			$website
		);

		$managed_plugins = (
			isset( $configuration['plugins'] ) &&
			is_array( $configuration['plugins'] )
		)
			? $configuration['plugins']
			: array();


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

		if ( empty( $result['success'] ) ) {
			do_action(
				'dealerlux_utility_client_switcher_failed',
				$website,
				isset( $result['error'] )
					? $result['error']
					: null,
				$result
			);

			return false;
		}

		$stored = Selection_Store::instance()->save(
			$result['target_slug'],
			$result['target_file'],
			$website
		);

		if ( ! $stored ) {
			$this->log(
				sprintf(
					'The selected plugin state could not be stored for "%s".',
					$result['target_slug']
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
     * Determine whether the selected client plugin state is already correct.
     *
     * This avoids loading the full WordPress plugin API and scanning installed
     * plugin headers when no environment or active-plugin state has changed.
     *
     * @param array $website        Selected website configuration.
     * @param array $managed_plugins Managed client plugin slugs.
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
            ? trim( (string) $website['plugin'], '/' )
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
                ? (string) absint( $website['dealer_group_id'] )
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

        $active_plugins = array_map(
            'wp_normalize_path',
            $active_plugins
        );

        if ( ! in_array( $target_file, $active_plugins, true ) ) {
            return false;
        }

        $managed_plugins = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $plugin_slug ) {
                            return trim(
                                (string) $plugin_slug,
                                '/'
                            );
                        },
                        $managed_plugins
                    )
                )
            )
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
	 * Get the selected website.
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

		if (
			! isset( $configuration['env'] ) ||
			! is_array( $configuration['env'] )
		) {
			$this->log(
				'The Client Switcher env configuration is missing.'
			);

			return null;
		}

		if (
			! isset( $configuration['websites'] ) ||
			! is_array( $configuration['websites'] ) ||
			empty( $configuration['websites'] )
		) {
			$this->log(
				'The Client Switcher websites configuration is missing or empty.'
			);

			return null;
		}

		if (
			isset( $configuration['plugins'] ) &&
			! is_array( $configuration['plugins'] )
		) {
			$this->log(
				'The Client Switcher plugins configuration must be an array.'
			);

			return null;
		}

		return $configuration;
	}

	/**
	 * Define the required SSS API constants.
	 *
	 * These two names must remain unchanged for SSS compatibility.
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
	 * Define compatibility fallbacks when no environment can be resolved.
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
	 * Build an environment-selection failure message.
	 *
	 * @param array $configuration Client Switcher configuration.
	 * @return string
	 */
	private function build_selection_failure_message(
		array $configuration
	) {
		$environment = isset( $configuration['env'] )
			&& is_array( $configuration['env'] )
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