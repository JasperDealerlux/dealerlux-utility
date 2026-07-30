<?php
/**
 * Class Selection_Store
 *
 * Persists Client Switcher state through Options_Registry.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Modules\Client_Switcher;

use DealerluxUtils\Registries\Options_Registry;
use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Persist the selected client plugin.
 */
class Selection_Store {

	/**
	 * Use the singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Options Registry selector.
	 *
	 * @var array
	 */
	private $option_selector = array(
		'type'   => 'plugin',
		'source' => 'dealerlux-utility',
		'name'   => 'client_switcher_selected_plugin',
	);

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
	 * Save the selected plugin record.
	 *
	 * @param string $plugin_slug Plugin directory slug.
	 * @param string $plugin_file Plugin file relative to WP_PLUGIN_DIR.
	 * @param array  $website     Selected website.
	 * @return bool
	 */
	public function save(
		$plugin_slug,
		$plugin_file,
		array $website
	) {
		$plugin_slug = trim(
			sanitize_text_field(
				(string) $plugin_slug
			),
			'/'
		);

		$plugin_file = ltrim(
			wp_normalize_path(
				(string) $plugin_file
			),
			'/'
		);

		if (
			'' === $plugin_slug ||
			'' === $plugin_file
		) {
			$this->log(
				'The selected plugin slug or file is empty.'
			);

			return false;
		}

		$plugin_absolute_path = wp_normalize_path(
			trailingslashit( WP_PLUGIN_DIR )
			. $plugin_file
		);

		$selection = array(
			'plugin_slug'          => $plugin_slug,
			'plugin_file'          => $plugin_file,
			'plugin_absolute_path' => $plugin_absolute_path,
			'plugin_directory'     => wp_normalize_path(
				dirname(
					$plugin_absolute_path
				)
			),
			'domain'               => isset( $website['domain'] )
				? sanitize_text_field(
					(string) $website['domain']
				)
				: '',
			'client_id'            => isset( $website['client_id'] )
				? absint( $website['client_id'] )
				: 0,
			'dealer_group_id'      => isset( $website['dealer_group_id'] )
				? absint(
					$website['dealer_group_id']
				)
				: 0,
			'selected_at'          => current_time(
				'mysql',
				true
			),
		);

		$registry = Options_Registry::instance();

		if (
			! $registry->has_option(
				$this->option_selector
			)
		) {
			$this->log(
				'The Client Switcher option is not registered in config/options.php.'
			);

			return false;
		}

		$current_selection = $registry->get_value(
			$this->option_selector,
			array()
		);

		if (
			is_array( $current_selection ) &&
			$this->represents_same_selection(
				$current_selection,
				$selection
			)
		) {
			return true;
		}

		if (
			$registry->value_exists(
				$this->option_selector
			)
		) {
			return $registry->update_value(
				$this->option_selector,
				$selection,
				false
			);
		}

		return $registry->add_value(
			$this->option_selector,
			$selection,
			false
		);
	}

	/**
	 * Get the stored plugin selection.
	 *
	 * @return array
	 */
	public function get() {
		$selection = Options_Registry::instance()->get_value(
			$this->option_selector,
			array()
		);

		return is_array( $selection )
			? $selection
			: array();
	}

	/**
	 * Delete the stored plugin selection.
	 *
	 * @return bool
	 */
	public function delete() {
		return Options_Registry::instance()->delete_value(
			$this->option_selector
		);
	}

	/**
	 * Determine whether two records represent the same selection.
	 *
	 * selected_at is intentionally excluded so an unchanged environment does
	 * not create a database write on every request.
	 *
	 * @param array $current   Existing record.
	 * @param array $selection New record.
	 * @return bool
	 */
	private function represents_same_selection(
		array $current,
		array $selection
	) {
		$comparison_keys = array(
			'plugin_slug',
			'plugin_file',
			'plugin_absolute_path',
			'plugin_directory',
			'domain',
			'client_id',
			'dealer_group_id',
		);

		foreach ( $comparison_keys as $key ) {
			$current_value = array_key_exists( $key, $current )
				? (string) $current[ $key ]
				: '';

			$selection_value = array_key_exists(
				$key,
				$selection
			)
				? (string) $selection[ $key ]
				: '';

			if ( $current_value !== $selection_value ) {
				return false;
			}
		}

		return true;
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