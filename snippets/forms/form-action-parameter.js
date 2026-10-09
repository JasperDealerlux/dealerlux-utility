/**
 * Manage form action URL parameters.
 */
if (typeof window.SSS.FormActionParameter === 'undefined') {

    window.SSS.FormActionParameter = class FormActionParameter {

        /**
         * @param {Array<Object>} configs
         */
        constructor(configs = []) {
            this.configs = configs;

            this.init();
        }

        /**
         * Initialize event listeners for all configurations.
         */
        init() {
            this.configs.forEach(({ fieldSelector, parameterName }) => {

                $(document)
                    .off('change.formActionParameter', fieldSelector)
                    .on(
                        'change.formActionParameter',
                        fieldSelector,
                        (event) => this.handleChange(
                            event,
                            parameterName
                        )
                    );
            });
        }

        /**
         * Handle field changes.
         *
         * @param {Event} event
         * @param {string} parameterName
         */
        handleChange(event, parameterName) {
            const $field = $(event.currentTarget);
            const $form = $field.closest('form');

            if (!$form.length) {
                return;
            }

            this.updateFormAction(
                $form,
                parameterName,
                $field.val()
            );
        }

        /**
         * Add, update, or remove a URL parameter.
         *
         * @param {jQuery} $form
         * @param {string} parameterName
         * @param {string} value
         */
        updateFormAction($form, parameterName, value) {
            const action = $form.attr('action');

            if (!action) {
                return;
            }

            const url = new URL(
                action,
                window.location.href
            );

            if (
                value != null &&
                value !== ''
            ) {
                url.searchParams.set(
                    parameterName,
                    value
                );
            } else {
                url.searchParams.delete(
                    parameterName
                );
            }

            $form.attr(
                'action',
                this.getActionURL(
                    url,
                    action
                )
            );
        }

        /**
         * Preserve the original URL format.
         *
         * @param {URL} url
         * @param {string} originalAction
         * @returns {string}
         */
        getActionURL(url, originalAction) {

            if (/^[a-z][a-z\d+\-.]*:/i.test(originalAction)) {
                return url.href;
            }

            if (originalAction.startsWith('//')) {
                return `//${url.host}${url.pathname}${url.search}${url.hash}`;
            }

            if (originalAction.startsWith('?')) {
                return `${url.search}${url.hash}`;
            }

            if (originalAction.startsWith('#')) {
                return `${url.search}${url.hash}`;
            }

            return `${url.pathname}${url.search}${url.hash}`;
        }
    };
}


/**
 * Initialize classes, methods, or functions
 * on document ready.
 */
$(document).ready(function () {

    /**
     * Initialize Form Action URL Parameters.
     *
     * Prevent duplicate initialization if this
     * script is loaded more than once.
     */
    if (
        typeof window.SSS.formActionParameter === 'undefined'
    ) {
        window.SSS.formActionParameter = new window.SSS.FormActionParameter([
            {
                fieldSelector: 'select[name="topic"]',
                parameterName: 'topic'
            },
            {
                fieldSelector: 'select[name="product_interested_in"]',
                parameterName: 'product_interested_in'
            }
        ]);
    }

});