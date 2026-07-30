<?php
/**
 * Class Environment_Resolver
 *
 * Resolves the website selected by SSS_CLIENT_ENVIRONMENT.
 *
 * @package DealerluxUtils
 */

namespace DealerluxUtils\Modules\Client_Switcher;

use DealerluxUtils\Traits\Singleton as Singleton_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Resolve the selected client website.
 */
class Environment_Resolver {

	/**
	 * Use the singleton loader.
	 */
	use Singleton_Trait;

	/**
	 * Supported environment selector fields.
	 *
	 * @var array
	 */
	private $allowed_selectors = array(
		'domain',
		'client_id',
		'dealer_group_id',
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
	 * Resolve the selected website.
	 *
	 * @param array $configuration Client environment configuration.
	 * @return array|null
	 */
	public function resolve(
		array $configuration
	) {
		$environment = (
			isset( $configuration['env'] ) &&
			is_array( $configuration['env'] )
		)
			? $configuration['env']
			: array();

		$selector = isset( $environment['use'] )
			? strtolower(
				trim(
					(string) $environment['use']
				)
			)
			: 'client_id';

		$value = isset( $environment['set'] )
			? trim(
				(string) $environment['set']
			)
			: '';

		if (
			! in_array(
				$selector,
				$this->allowed_selectors,
				true
			)
		) {
			$this->log(
				sprintf(
					'Invalid environment selector "%1$s". Allowed selectors: %2$s.',
					$selector,
					implode(
						', ',
						$this->allowed_selectors
					)
				)
			);

			return null;
		}

		if ( '' === $value ) {
			$this->log(
				'The configured env.set value cannot be empty.'
			);

			return null;
		}

		$websites = (
			isset( $configuration['websites'] ) &&
			is_array( $configuration['websites'] )
		)
			? $configuration['websites']
			: array();

		foreach ( $websites as $domain => $website ) {
			if ( ! is_array( $website ) ) {
				continue;
			}

			if ( 'domain' === $selector ) {
				if (
					$this->normalize_domain( $domain ) !==
					$this->normalize_domain( $value )
				) {
					continue;
				}

				return $this->prepare_website(
					$domain,
					$website
				);
			}

			if ( ! array_key_exists( $selector, $website ) ) {
				continue;
			}

			if (
				(string) $website[ $selector ] !==
				(string) $value
			) {
				continue;
			}

			return $this->prepare_website(
				$domain,
				$website
			);
		}

		$this->log(
			sprintf(
				'No configured website matched %1$s=%2$s.',
				$selector,
				$value
			)
		);

		return null;
	}

	/**
	 * Normalize a configured domain or environment key.
	 *
	 * @param mixed $domain Domain value.
	 * @return string
	 */
	private function normalize_domain(
		$domain
	) {
		$domain = strtolower(
			trim(
				(string) $domain
			)
		);

		$domain = preg_replace(
			'#^https?://#i',
			'',
			$domain
		);

		$domain = preg_replace(
			'#/.*$#',
			'',
			$domain
		);

		$domain = preg_replace(
			'#:\d+$#',
			'',
			$domain
		);

		$domain = preg_replace(
			'#^www\.#i',
			'',
			$domain
		);

		return rtrim(
			(string) $domain,
			'.'
		);
	}

	/**
	 * Prepare the selected website.
	 *
	 * @param string $domain  Configured website key.
	 * @param array  $website Website configuration.
	 * @return array
	 */
	private function prepare_website(
		$domain,
		array $website
	) {
		$website['domain'] = sanitize_text_field(
			(string) $domain
		);

		return $website;
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