(function (window) {
    'use strict';

    const namespace =
        window.sss?.form?.defaults;

    if (!namespace?.FormDefaultValues) {
        console.warn(
            'FormDefaultValues is not loaded.'
        );

        return;
    }

    // ==========================================
    // WEBSITE CONFIGURATION
    // ==========================================

    const CONFIGURATION = [
        {
            general_defaults: {
                fields: [
                    {
                        '[type="checkbox"][name="newsletter"]': false,
                    }
                ]
            },

            fireplace_protection_forms: {
                target_forms: [
                    '#sss-form-contact'
                ],
                fields: [
                    {
                        'input[name="model_name"]': 'Fireplace Protection Plan'
                    }
                ]
            }
        }

        // Additional rule groups go here.
    ];

    // ==========================================
    // SINGLETON INITIALIZATION
    // ==========================================

    if (namespace.instance) {
        // Reuse the existing instance.
        namespace.instance.setConfig(
            CONFIGURATION
        );
    } else {
        // Create the instance only once.
        namespace.instance =
            new namespace.FormDefaultValues(
                CONFIGURATION
            );
    }

})(window);