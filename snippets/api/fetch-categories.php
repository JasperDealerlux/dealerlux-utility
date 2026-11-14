<?php
/**
 * Fetch category data from the Dealer Lux API.
 *
 * Sends an authenticated GET request to the API's `/categories` endpoint
 * using the configured API root URL and authentication token.
 *
 * The API response is decoded into an associative array and returned
 * for further processing.
 *
 * When category data is available, each category is also logged to the
 * browser's developer console for debugging and inspection.
 *
 * Features:
 * - Uses SSS_API_ROOT_URL and SSS_API_AUTH_KEY when defined.
 * - Provides fallback values for local development.
 * - Authenticates requests using a Bearer token.
 * - Handles WordPress HTTP request errors.
 * - Validates the decoded JSON response.
 * - Logs individual category records to the browser console.
 *
 * @uses wp_remote_get()            Sends the HTTP GET request.
 * @uses is_wp_error()              Checks for WordPress HTTP errors.
 * @uses wp_remote_retrieve_body()  Retrieves the API response body.
 * @uses wp_json_encode()           Safely encodes category data for JavaScript.
 *
 * @return array<string|int, mixed> Category data returned by the API,
 *                                  or an empty array if the request fails
 *                                  or the response is invalid.
 */
function fetch_categories() {
    $api_root  = defined('SSS_API_ROOT_URL') ? rtrim(SSS_API_ROOT_URL, '/') : 'http://laravel.test/api';
    $api_token = defined('SSS_API_AUTH_KEY') ? SSS_API_AUTH_KEY : 'your-token';

    // IMPORTANT:
    // SSS_API_ROOT_URL already contains /api
    // so we append only /categories
    $url = $api_root . '/categories';

    $response = wp_remote_get($url, [
        'timeout'  => 30,
        'blocking' => true,
        'headers'  => [
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        error_log('Dealer Lux categories request failed: ' . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data)) {
        return [];
    }

	if (!empty($data)) {
		echo '<script>';
		foreach ($data as $category) {
			// Optional debug output
			echo 'console.log("Categories API Response:", ' . wp_json_encode(
				$category,
				JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
			) . ');';
		}
		echo '</script>';
	}

    return $data;
}
