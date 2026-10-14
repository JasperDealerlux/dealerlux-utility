(function ($, window, document) {
    'use strict';

    // Create namespaces safely.
    window.sss = window.sss || {};
    window.sss.form = window.sss.form || {};
    window.sss.form.defaults = window.sss.form.defaults || {};

    const namespace = window.sss.form.defaults;

    // Prevent duplicate class registration.
    if (namespace.FormDefaultValues) {
        return;
    }

    class FormDefaultValues {
        constructor(config = []) {
            this.config = [];
            this.eventNamespace = '.sssFormDefaultValues';

            this.modalObserver = null;
            this.modalObserverTimer = null;

            // Store the initial configuration.
            this.setConfig(config, false);

            this.bindEvents();
            this.refreshOnPageLoad();
        }

        // Check for a plain configuration object.
        isObject(value) {
            return value !== null &&
                typeof value === 'object' &&
                !Array.isArray(value);
        }

        /*
         * Normalize a selector or selector list.
         *
         * We intentionally do not split strings by comma because:
         *
         * "#form-one, #form-two"
         *
         * is already a valid CSS selector.
         */
        normalizeSelectors(value) {
            if (!value) {
                return [];
            }

            return (Array.isArray(value) ? value : [value])
                .map(item => String(item).trim())
                .filter(Boolean);
        }

        /*
         * Convert:
         *
         * fields: [
         *     {
         *         'input[name="first_name"]': 'John',
         *         'input[name="last_name"]': 'Doe'
         *     }
         * ]
         *
         * into a consistent internal format.
         */
        normalizeFields(fields) {
            const blocks = Array.isArray(fields)
                ? fields
                : [fields];

            const normalized = [];

            blocks.forEach(block => {
                if (!this.isObject(block)) {
                    return;
                }

                Object.entries(block).forEach(([selector, value]) => {
                    const cleanSelector = String(selector).trim();

                    if (!cleanSelector) {
                        return;
                    }

                    normalized.push({
                        selector: cleanSelector,
                        value
                    });
                });
            });

            return normalized;
        }

        /*
         * Supports both:
         *
         * {
         *     fields: [...]
         * }
         *
         * and:
         *
         * {
         *     apply_to_all_forms: {
         *         fields: [...]
         *     },
         *     specific_forms: {
         *         target_forms: [...],
         *         fields: [...]
         *     }
         * }
         */
        normalizeConfig(config = []) {
            const containers = Array.isArray(config)
                ? config
                : [config];

            const rules = [];

            const addRule = (rule, name = '') => {
                if (
                    !this.isObject(rule) ||
                    !('fields' in rule)
                ) {
                    return;
                }

                const fields = this.normalizeFields(
                    rule.fields
                );

                if (!fields.length) {
                    return;
                }

                rules.push({
                    name,
                    targetForms: this.normalizeSelectors(
                        rule.target_forms
                    ),
                    fields
                });
            };

            containers.forEach(container => {
                if (!this.isObject(container)) {
                    return;
                }

                /*
                 * Direct rule:
                 *
                 * {
                 *     fields: [...]
                 * }
                 */
                if ('fields' in container) {
                    addRule(container);
                    return;
                }

                /*
                 * Named/grouped rules:
                 *
                 * {
                 *     apply_to_all_forms: {...},
                 *     contact_forms: {...}
                 * }
                 */
                Object.entries(container).forEach(
                    ([name, rule]) => {
                        addRule(rule, name);
                    }
                );
            });

            return rules;
        }

        // Update configuration without creating another instance.
        setConfig(config = [], refresh = true) {
            this.config = this.normalizeConfig(config);

            if (refresh) {
                this.refresh();
            }

            return this;
        }

        // ==========================================
        // PAGE LOAD
        // ==========================================

        refreshOnPageLoad() {
            const callback = () => {
                this.refresh();
            };

            if (document.readyState === 'loading') {
                document.addEventListener(
                    'DOMContentLoaded',
                    callback,
                    {
                        once: true
                    }
                );

                return;
            }

            callback();
        }

        // ==========================================
        // FORM LOOKUP
        // ==========================================

        /*
         * Return all forms, or all forms contained
         * inside a supplied root element.
         */
        getForms(root = null) {
            if (!root) {
                return $('form.sss-form');
            }

            const $root = $(root);

            return $root
                .filter('form.sss-form')
                .add(
                    $root.find('form.sss-form')
                );
        }

        // Safely check a target_forms selector.
        formMatchesSelector($form, selector) {
            try {
                return $form.is(selector);
            } catch (error) {
                console.warn(
                    `FormDefaultValues: invalid target_forms selector "${selector}".`,
                    error
                );

                return false;
            }
        }

        /*
         * Global rules are returned first.
         * Targeted rules are returned second.
         *
         * This means a targeted rule can override
         * a global default.
         */
        getApplicableRules($form) {
            const globalRules = [];
            const targetedRules = [];

            this.config.forEach(rule => {
                // No target_forms = applies everywhere.
                if (!rule.targetForms.length) {
                    globalRules.push(rule);
                    return;
                }

                const matches =
                    rule.targetForms.some(selector =>
                        this.formMatchesSelector(
                            $form,
                            selector
                        )
                    );

                if (matches) {
                    targetedRules.push(rule);
                }
            });

            return [
                ...globalRules,
                ...targetedRules
            ];
        }

        // Safely find configured fields.
        findFields($form, selector) {
            try {
                return $form.find(selector);
            } catch (error) {
                console.warn(
                    `FormDefaultValues: invalid field selector "${selector}".`,
                    error
                );

                return $();
            }
        }

        /*
         * Resolve all assignments before changing
         * any actual field values.
         *
         * Map() is important here because if a targeted
         * rule affects the same element as a global rule,
         * the targeted value replaces the global value.
         */
        buildAssignments($form) {
            const assignments = new Map();

            const rules =
                this.getApplicableRules($form);

            rules.forEach(rule => {
                rule.fields.forEach(field => {
                    this.findFields(
                        $form,
                        field.selector
                    ).each((_, element) => {
                        assignments.set(
                            element,
                            field.value
                        );
                    });
                });
            });

            return assignments;
        }

        // ==========================================
        // VALUE COMPARISON
        // ==========================================

        valuesEqual(first, second) {
            /*
             * Array comparison is mainly for
             * multi-select fields.
             *
             * Selection order does not matter.
             */
            if (
                Array.isArray(first) ||
                Array.isArray(second)
            ) {
                const normalize = value => {
                    if (
                        value === null ||
                        value === undefined
                    ) {
                        return [];
                    }

                    return (
                        Array.isArray(value)
                            ? value
                            : [value]
                    )
                        .map(item => String(item))
                        .sort();
                };

                const a = normalize(first);
                const b = normalize(second);

                return (
                    a.length === b.length &&
                    a.every(
                        (item, index) =>
                            item === b[index]
                    )
                );
            }

            return String(first ?? '') ===
                String(second ?? '');
        }

        // ==========================================
        // EVENTS
        // ==========================================

        /*
         * Fire normal browser events.
         *
         * These bubble, so delegated jQuery/native
         * listeners will also receive them.
         */
        dispatchFieldEvents(element) {
            element.dispatchEvent(
                new Event('input', {
                    bubbles: true
                })
            );

            element.dispatchEvent(
                new Event('change', {
                    bubbles: true
                })
            );
        }

        // ==========================================
        // FIELD TYPES
        // ==========================================

        setCheckboxValue(element, value) {
            const current = element.checked;

            let next = false;

            /*
             * Single checkbox:
             *
             * true / false
             */
            if (typeof value === 'boolean') {
                next = value;

            /*
             * Checkbox group:
             *
             * [
             *     "Hot Tubs",
             *     "Saunas"
             * ]
             */
            } else if (Array.isArray(value)) {
                next = value
                    .map(item => String(item))
                    .includes(
                        String(element.value)
                    );

            /*
             * Also supports selecting a checkbox
             * based on its value.
             */
            } else if (
                value !== null &&
                value !== undefined
            ) {
                next =
                    String(element.value) ===
                    String(value);
            }

            if (current === next) {
                return false;
            }

            element.checked = next;

            return true;
        }

        setRadioValue(element, value) {
            const current = element.checked;

            let next = false;

            /*
             * Boolean is useful when the selector
             * targets one exact radio option.
             */
            if (typeof value === 'boolean') {
                next = value;

            /*
             * Normally radios should use their
             * desired value:
             *
             * 'Phone Call'
             */
            } else if (
                value !== null &&
                value !== undefined
            ) {
                next =
                    String(element.value) ===
                    String(value);
            }

            if (current === next) {
                return false;
            }

            element.checked = next;

            return true;
        }

        setSelectValue(element, value) {
            const $field = $(element);

            const before = $field.val();

            /*
             * Multi-select expects an array.
             *
             * Single select expects one value.
             */
            const next = element.multiple
                ? (
                    Array.isArray(value)
                        ? value
                        : (
                            value === null ||
                            value === undefined
                                ? []
                                : [value]
                        )
                ).map(item => String(item))
                : (
                    value === null ||
                    value === undefined
                        ? ''
                        : String(value)
                );

            $field.val(next);

            const after = $field.val();

            return !this.valuesEqual(
                before,
                after
            );
        }

        /*
         * Used for:
         *
         * text
         * hidden
         * email
         * tel
         * number
         * date
         * time
         * url
         * textarea
         * etc.
         */
        setStandardValue(element, value) {
            const $field = $(element);

            const before = $field.val();

            const next =
                value === null ||
                value === undefined
                    ? ''
                    : String(value);

            $field.val(next);

            return !this.valuesEqual(
                before,
                $field.val()
            );
        }

        setContentEditableValue(element, value) {
            const before = element.textContent;

            const next =
                value === null ||
                value === undefined
                    ? ''
                    : String(value);

            if (before === next) {
                return false;
            }

            element.textContent = next;

            return true;
        }

        /*
         * Write the appropriate value based on
         * the type of field.
         */
        writeValue(element, value) {
            const tagName = String(
                element.tagName || ''
            ).toLowerCase();

            const type = String(
                element.type || ''
            ).toLowerCase();

            // Optional support for contenteditable.
            if (
                element.matches?.(
                    '[contenteditable="true"]'
                )
            ) {
                return this.setContentEditableValue(
                    element,
                    value
                );
            }

            // Ignore non-form-field elements.
            if (
                ![
                    'input',
                    'select',
                    'textarea'
                ].includes(tagName)
            ) {
                return false;
            }

            /*
             * Browser security prevents JavaScript
             * from assigning local files.
             */
            if (type === 'file') {
                return false;
            }

            /*
             * These are form controls but not
             * data-entry fields.
             */
            if (
                [
                    'button',
                    'submit',
                    'reset',
                    'image'
                ].includes(type)
            ) {
                return false;
            }

            if (type === 'checkbox') {
                return this.setCheckboxValue(
                    element,
                    value
                );
            }

            if (type === 'radio') {
                return this.setRadioValue(
                    element,
                    value
                );
            }

            if (tagName === 'select') {
                return this.setSelectValue(
                    element,
                    value
                );
            }

            return this.setStandardValue(
                element,
                value
            );
        }

        // ==========================================
        // FORM REFRESH
        // ==========================================

        /*
         * Populate one form.
         *
         * Values are written first.
         * Events are dispatched afterward.
         *
         * This prevents event handlers from seeing
         * a half-populated form.
         */
        refreshForm(form) {
            const $form = $(form);

            const assignments =
                this.buildAssignments($form);

            const changedFields = [];

            assignments.forEach(
                (value, element) => {
                    if (
                        this.writeValue(
                            element,
                            value
                        )
                    ) {
                        changedFields.push(element);
                    }
                }
            );

            changedFields.forEach(element => {
                this.dispatchFieldEvents(element);
            });

            return this;
        }

        /*
         * Refresh every form or forms inside
         * a supplied root.
         */
        refresh(root = null) {
            this.getForms(root).each(
                (_, form) => {
                    this.refreshForm(form);
                }
            );

            return this;
        }

        // ==========================================
        // MODAL FORM HANDLING
        // ==========================================

        /*
         * Determine which .sss-form elements were
         * affected by DOM nodes inserted after the
         * modal CTA was clicked.
         */
        getFormsFromMutations(mutations) {
            const forms = new Set();

            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType !== 1) {
                        return;
                    }

                    // The inserted node is a form.
                    if (
                        node.matches?.(
                            'form.sss-form'
                        )
                    ) {
                        forms.add(node);
                    }

                    // The inserted node contains forms.
                    node.querySelectorAll?.(
                        'form.sss-form'
                    ).forEach(form => {
                        forms.add(form);
                    });

                    /*
                     * Fields may have been inserted
                     * into an existing form.
                     */
                    const parentForm =
                        node.closest?.(
                            'form.sss-form'
                        );

                    if (parentForm) {
                        forms.add(parentForm);
                    }
                });
            });

            return forms;
        }

        stopModalObserver() {
            if (this.modalObserver) {
                this.modalObserver.disconnect();

                this.modalObserver = null;
            }

            if (this.modalObserverTimer) {
                window.clearTimeout(
                    this.modalObserverTimer
                );

                this.modalObserverTimer = null;
            }
        }

        /*
         * A modal form may not exist immediately
         * when the CTA is clicked.
         *
         * Watch temporarily for forms inserted by
         * the modal/form builder.
         */
        startModalObserver() {
            this.stopModalObserver();

            if (!document.body) {
                return;
            }

            this.modalObserver =
                new MutationObserver(
                    mutations => {
                        const forms =
                            this.getFormsFromMutations(
                                mutations
                            );

                        forms.forEach(form => {
                            this.refreshForm(form);
                        });
                    }
                );

            this.modalObserver.observe(
                document.body,
                {
                    childList: true,
                    subtree: true
                }
            );

            /*
             * Do not leave MutationObserver running
             * indefinitely.
             */
            this.modalObserverTimer =
                window.setTimeout(() => {
                    this.stopModalObserver();
                }, 5000);
        }

        handleModalTriggerClick() {
            /*
             * Start watching first so we cannot miss
             * an asynchronously inserted form.
             */
            this.startModalObserver();

            /*
             * Run after the current click handler
             * stack finishes.
             *
             * This handles forms already present in
             * the DOM that the modal script opens or
             * resets.
             */
            window.setTimeout(() => {
                this.refresh();
            }, 0);
        }

        // ==========================================
        // EVENTS
        // ==========================================

        bindEvents() {
            $(document)
                .off(this.eventNamespace)
                .on(
                    `click${this.eventNamespace}`,
                    '.globalShowFormInModal',
                    () => {
                        this.handleModalTriggerClick();
                    }
                );
        }

        // ==========================================
        // CLEANUP
        // ==========================================

        destroy() {
            $(document).off(
                this.eventNamespace
            );

            this.stopModalObserver();

            if (namespace.instance === this) {
                namespace.instance = null;
            }
        }
    }

    // Expose the reusable class.
    namespace.FormDefaultValues =
        FormDefaultValues;

})(jQuery, window, document);