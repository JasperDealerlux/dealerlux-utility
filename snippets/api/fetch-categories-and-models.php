<?php
/**
 * Fetch and inspect Dealer Lux category inventory API data.
 *
 * Retrieves category-specific base options and the dealer's complete
 * inventory listing from the Dealer Lux API.
 *
 * Filters the inventory records by the configured category ID and
 * outputs the retrieved data to the browser's developer console
 * for debugging and inspection.
 *
 * API endpoints:
 * - /inventory_manager/dealer/{dealer_id}/category/{category_id}/base_options
 * - /inventories/dealers/{dealer_id}/listing
 *
 * Features:
 * - Uses the configured Dealer Lux API credentials.
 * - Retrieves category-specific base options.
 * - Fetches the complete dealer inventory listing.
 * - Filters inventory records by category ID.
 * - Supports both category_id and categoryId field formats.
 * - Logs API responses, filtered models, and inventory listings.
 * - Handles WordPress HTTP request errors.
 *
 * Requirements:
 * - SSS_API_DEALER_ID
 * - SSS_API_ROOT_URL
 * - SSS_API_AUTH_KEY
 * - HttpRequest::get()
 *
 * Note:
 * This function is intended for development and debugging only.
 * It directly outputs JavaScript and should be executed during
 * frontend page rendering, not during REST or AJAX responses.
 *
 * @uses wp_remote_get()           Performs the authenticated API request.
 * @uses wp_remote_retrieve_body() Retrieves the HTTP response body.
 * @uses is_wp_error()             Checks for WordPress request errors.
 * @uses HttpRequest::get()        Retrieves the dealer inventory listing.
 * @uses wp_json_encode()          Encodes API data for JavaScript output.
 *
 * @return void Outputs debugging information to the browser console.
 */
function ssslv_debug_category_inventory_api_data() {
	
	$category_id = 13; // Example category ID for saunas.
	$desired_cat_id = 13;
	$dealer_id   = defined('SSS_API_DEALER_ID') ? SSS_API_DEALER_ID : 0;
	$api_root    = defined('SSS_API_ROOT_URL') ? SSS_API_ROOT_URL : 'https://api.dealerlux.com';
	$api_token   = defined('SSS_API_AUTH_KEY') ? SSS_API_AUTH_KEY : 'your-token';

	$url = $api_root . '/inventory_manager/dealer/' . $dealer_id . '/category/' . $category_id . '/base_options';
	
	$response = wp_remote_get($url, [
		'blocking' => true,
		'headers' => [
			'Authorization' => 'Bearer ' . $api_token,
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		],
	]);
	
	if (is_wp_error($response)) {
		return;
	}
	
	$data = json_decode(wp_remote_retrieve_body($response), true);
	
	$listing = HttpRequest::get('/inventories/dealers/' . $dealer_id . '/listing');
	
	$models = [];
	
	foreach ($listing['inventoryData']['inventories'] ?? [] as $item) {
		$categoryId = $item['category_id'] ?? $item['categoryId'] ?? null;
	
		if ((int)$categoryId === $desired_cat_id) {
			$models[] = $item;
		}
	}
	
    // Output retrieved API data to the browser console.
    echo '<script>';

    echo 'console.log("API JSON Response:", ' . wp_json_encode(
        $data,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) . ');';

    echo 'console.log("Models:", ' . wp_json_encode(
        $models,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) . ');';

    echo 'console.log("Listings:", ' . wp_json_encode(
        $listing,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) . ');';
}

/**
 * Fetch, generate, and inspect Dealer Lux category model detail URLs.
 *
 * Retrieves category-specific base options and the dealer's inventory
 * listing from the Dealer Lux API, then filters inventory records
 * belonging to the configured category.
 *
 * Generates WordPress inventory detail page URLs using the available
 * unit identifiers and displays them as clickable HTML links.
 *
 * API endpoints:
 * - /inventory_manager/dealer/{dealer_id}/category/{category_id}/base_options
 * - /inventories/dealers/{dealer_id}/listing
 *
 * Generated URL format:
 * - /inventory/detail/?unit_id={unit_id}
 *
 * Features:
 * - Uses the configured Dealer Lux API credentials.
 * - Retrieves category-specific base options.
 * - Fetches and filters inventory records by category ID.
 * - Supports both category_id and categoryId field formats.
 * - Extracts unit IDs from multiple supported field names.
 * - Generates absolute WordPress inventory detail URLs.
 * - Displays generated URLs as clickable HTML links.
 * - Logs API responses, filtered models, and model URLs.
 * - Escapes generated URLs and HTML output.
 * - Handles WordPress HTTP request errors.
 *
 * Supported unit identifier fields:
 * - unit_id
 * - unitId
 * - id
 * - inventory_id
 * - inventoryId
 *
 * Requirements:
 * - SSS_API_DEALER_ID
 * - SSS_API_ROOT_URL
 * - SSS_API_AUTH_KEY
 * - HttpRequest::get()
 *
 * Note:
 * This function is intended for development and debugging only.
 * It directly outputs HTML and JavaScript and should be executed
 * during frontend page rendering.
 *
 * @uses wp_remote_get()           Performs the authenticated API request.
 * @uses wp_remote_retrieve_body() Retrieves the HTTP response body.
 * @uses is_wp_error()             Checks for WordPress request errors.
 * @uses HttpRequest::get()        Retrieves the dealer inventory listing.
 * @uses home_url()                Builds absolute WordPress URLs.
 * @uses absint()                  Sanitizes inventory unit identifiers.
 * @uses esc_url()                 Escapes generated link destinations.
 * @uses esc_html()                Escapes displayed link text.
 * @uses wp_json_encode()          Encodes API data for JavaScript output.
 *
 * @return void Outputs generated model URLs and debugging information.
 */
function ssslv_debug_category_model_detail_urls() {

    $category_id = 13;
    $dealer_id   = defined('SSS_API_DEALER_ID') ? SSS_API_DEALER_ID : 0;
    $api_root    = defined('SSS_API_ROOT_URL') ? SSS_API_ROOT_URL : 'https://api.dealerlux.com';
    $api_token   = defined('SSS_API_AUTH_KEY') ? SSS_API_AUTH_KEY : 'your-token';

    $url = $api_root . '/inventory_manager/dealer/' . $dealer_id . '/category/' . $category_id . '/base_options';

    $response = wp_remote_get($url, [
        'blocking' => true,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    $listing = HttpRequest::get('/inventories/dealers/' . $dealer_id . '/listing');

    $models = [];

    foreach ($listing['inventoryData']['inventories'] ?? [] as $item) {
        $categoryId = $item['category_id'] ?? $item['categoryId'] ?? null;

        if ((int) $categoryId === 13) {
            $models[] = $item;
        }
    }

    $model_urls = [];

    foreach ($models as $item) {
        $unit_id = $item['unit_id'] ?? $item['unitId'] ?? $item['id'] ?? $item['inventory_id'] ?? $item['inventoryId'] ?? null;

        if ($unit_id) {
            $model_urls[] = home_url('/inventory/detail/?unit_id=' . absint($unit_id));
        }
    }

    echo '<h3>Model URLs</h3>';
    echo '<ul>';
    foreach ($model_urls as $model_url) {
        echo '<li><a href="' . esc_url($model_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($model_url) . '</a></li>';
    }
    echo '</ul>';

    echo '<script>';
    echo 'console.log("API JSON Response:", ' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo 'console.log("Models:", ' . wp_json_encode($models, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo 'console.log("Model URLs:", ' . wp_json_encode($model_urls, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo '</script>';
}

/**
 * Retrieve models belonging to the Sauna category.
 *
 * @return array|WP_Error
 */
function fetch_category_models_log() {
	$fetcher = new SSSLV_Category_Models_Fetcher();

	$config = $fetcher->config([
		'category_id' => 13,
		'schedule'    => DAY_IN_SECONDS,
	]);

	if (is_wp_error($config)) {
		error_log('[SSSLV] Config Error: ' . $config->get_error_message());
		return;
	}

	$models = $fetcher->get_models();

	if (is_wp_error($models)) {
		error_log('[SSSLV] Fetch Error: ' . $models->get_error_message());
		return;
	}

	$model_urls = $fetcher->get_model_urls();



    echo '<script>';
    echo 'console.log("DealerLux Category Models:", ' .
        wp_json_encode(
            $models,
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ) .
        ');';
    echo 'console.log("DealerLux Sauna Model URL`s:", ' .
        wp_json_encode(
            $model_urls,
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ) .
        ');';
    echo '</script>';
}

// add_action( 'wp_footer', 'fetch_category_models_log' );
// fetch_category_models_log();
// ssslv_debug_category_inventory_api_data();
// ssslv_debug_category_model_detail_urls();