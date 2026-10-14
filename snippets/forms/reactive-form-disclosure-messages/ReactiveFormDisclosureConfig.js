
(function (window) {
    'use strict';

    const namespace = window.sss?.form?.interactive;

    if (!namespace?.ReactiveFormDisclosure) {
        console.warn(
            'ReactiveFormDisclosure is not loaded.'
        );
        return;
    }

    // ==========================================
    // WEBSITE CONFIGURATION
    // ==========================================

    const config = [
        {
            trigger: {
                show: [
                    'input[name="contact_preference"][value="Text Message"]'
                ],
                hide: [
                    'input[name="contact_preference"][value="Email"]',
                    'input[name="contact_preference"][value="Phone Call"]',
                    'input[name="contact_preference"][value="I have no preference"]'
                ]
            },
            target: 'sms_consent_disclaimer'
        }

        // Additional rules go here.
    ];

    // ==========================================
    // SINGLETON INITIALIZATION
    // ==========================================

    if (namespace.disclosure) {
        // Reuse the existing instance.
        namespace.disclosure.setConfig(config);
    } else {
        // Create the instance only once.
        namespace.disclosure =
            new namespace.ReactiveFormDisclosure(config);
    }

})(window);