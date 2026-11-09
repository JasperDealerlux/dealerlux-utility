<?php

/**
 * Fetch category models and log DealerLux API responses.
 *
 * Retrieves category base options and the dealer's inventory
 * listing from the DealerLux API. Filters inventory records
 * by the specified category ID and outputs the API response
 * and matching models to the browser's developer console.
 *
 * Intended for debugging and inspecting category model data.
 *
 * @return void
 */
function ssslv_fetch_category_models_debug_response() {

    $category_id = 13; // Sauna category.
    $dealer_id   = defined('SSS_API_DEALER_ID') ? SSS_API_DEALER_ID : 0;
    $api_root    = defined('SSS_API_ROOT_URL') ? SSS_API_ROOT_URL : 'https://api.dealerlux.com';
    $api_token   = defined('SSS_API_AUTH_KEY') ? SSS_API_AUTH_KEY : 'your-token';

    $url = $api_root . '/inventory_manager/dealer/' . $dealer_id . '/category/' . $category_id . '/base_options';

    $response = wp_remote_get($url, [
        'blocking' => true,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
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

        if ((int) $categoryId === $category_id) {
            $models[] = $item;
        }
    }

    // Console log the API response and category models.
    echo '<script>';
    echo 'console.log("API JSON Response:", ' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo 'console.log("Models:", ' . wp_json_encode($models, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo '</script>';
}


/**
 * Fetch category models and generate inventory detail URLs.
 *
 * Retrieves category base options and the dealer's inventory
 * listing from the DealerLux API. Filters inventory records
 * by category ID and generates inventory detail page URLs
 * using the available unit IDs.
 *
 * Displays the generated URLs as clickable HTML links and
 * logs the API response, matching models, and generated URLs
 * to the browser's developer console.
 *
 * Intended for debugging and verifying category inventory URLs.
 *
 * @return void
 */
function ssslv_fetch_category_models_with_inventory_urls() {

    $category_id = 13; // Sauna category.
    $dealer_id   = defined('SSS_API_DEALER_ID') ? SSS_API_DEALER_ID : 0;
    $api_root    = defined('SSS_API_ROOT_URL') ? SSS_API_ROOT_URL : 'https://api.dealerlux.com';
    $api_token   = defined('SSS_API_AUTH_KEY') ? SSS_API_AUTH_KEY : 'your-token';

    $url = $api_root . '/inventory_manager/dealer/' . $dealer_id . '/category/' . $category_id . '/base_options';

    $response = wp_remote_get($url, [
        'blocking' => true,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
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

        if ((int) $categoryId === $category_id) {
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

    // Display the generated inventory URLs.
    echo '<h3>Model URLs</h3>';
    echo '<ul>';

    foreach ($model_urls as $model_url) {
        echo '<li><a href="' . esc_url($model_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($model_url) . '</a></li>';
    }

    echo '</ul>';

    // Log API data, matching models, and inventory URLs.
    echo '<script>';
    echo 'console.log("API JSON Response:", ' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo 'console.log("Models:", ' . wp_json_encode($models, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo 'console.log("Model URLs:", ' . wp_json_encode($model_urls, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');';
    echo '</script>';
}


// Option 1: Debug API responses.
add_action(
    'wp_footer',
    'ssslv_fetch_category_models_debug_response'
);

// Option 2: Display inventory URLs.
// add_action(
//     'wp_footer',
//     'ssslv_fetch_category_models_with_inventory_urls'
// );
