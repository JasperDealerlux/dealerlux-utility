<?php
/**
 * Fetch DealerLux models API from the WordPress footer.
 *
 * @return void
 */
function ssslv_fetch_models_on_footer() {
	$dealer_id   = defined('SSS_API_DEALER_ID') ? SSS_API_DEALER_ID : 0;
	
    ?>
    <script>
        (async function () {
            const url = 'http://localhost/api/models/searchResultPage?page=1';
            const token = 'F1tT2hWkSrtwc2z3aKhLjsuoEMN4TKxr1niCtfCQO22kxSM4dPWo3ZOBE93w';

            async function fetchModels() {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
							"dealer_id": "<?php echo $dealer_id; ?>"
						})
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }

                    const data = await response.json();

                    console.log('[DealerLux API] Models:', data);

                    return data;

                } catch (error) {
                    console.error(
                        '[DealerLux API] Request Failed:',
                        error
                    );
                }
            }

            await fetchModels();
        })();
    </script>
    <?php
}

add_action('wp_head', 'ssslv_fetch_models_on_footer', 100);
