(function ($, window) {
    'use strict';

    // Create namespaces safely.
    window.sss = window.sss || {};
    window.sss.form = window.sss.form || {};
    window.sss.form.interactive =
        window.sss.form.interactive || {};

    const namespace = window.sss.form.interactive;

    // Prevent duplicate instances and event listeners.
    if (namespace.disclosure) {
        namespace.disclosure.refresh();
        return;
    }

    class ReactiveFormDisclosure {
        constructor(config = []) {
            this.config = Array.isArray(config) ? config : [config];

            this.eventNamespace = '.sssReactiveDisclosure';

            this.bindEvents();
            this.refresh();
        }

        // Convert selectors to an array.
        toArray(value) {
            if (!value) return [];

            return (Array.isArray(value) ? value : [value])
                .flatMap(item => String(item).split(','))
                .map(item => item.trim())
                .filter(Boolean);
        }

        // Find matching fields within a form.
        findFields($form, selectors) {
            const list = this.toArray(selectors);

            if (!list.length) return $();

            return $form.find(list.join(','));
        }

        // Determine whether a field is active.
        isActive(element) {
            const $field = $(element);

            // Radio buttons and checkboxes
            if ($field.is(':radio, :checkbox')) {
                return $field.prop('checked');
            }

            // Select dropdowns (single and multiple)
            if ($field.is('select')) {
                const value = $field.val();

                return Array.isArray(value)
                    ? value.some(item => String(item).trim() !== '')
                    : String(value ?? '').trim() !== '';
            }

            // Text, email, number, date, textarea, etc.
            if ($field.is('input, textarea')) {
                return String($field.val() ?? '').trim() !== '';
            }

            // Other matching elements
            return $field.is(':visible');
        }

        // Check if any trigger matches an active field.
        matches($form, selectors) {
            return this.findFields($form, selectors)
                .toArray()
                .some(element => this.isActive(element));
        }

        // Locate the disclaimer's immediate parent containers.
        getSection($disclaimer) {
            const $container = $disclaimer.parent('.form-group');
            const $section = $container.parent('.row');

            if (!$container.length || !$section.length) {
                return $();
            }

            // jQuery prevents duplicate classes.
            $container.addClass('relative_field_container');
            $section.addClass('relative_form_section');

            return $section;
        }

        // Process a configuration rule.
        updateRule($form, rule) {
            const targets = this.toArray(rule.target);

            if (!targets.length) return;

            const show = this.matches($form, rule.trigger?.show);
            const hide = this.matches($form, rule.trigger?.hide);

            // Hide takes priority when both match.
            const visible = show && !hide;

            targets.forEach(target => {
                const selector = target.startsWith('.')
                    ? target
                    : `.${target}`;

                $form.find(
                    `.form_message_disclaimer.reactive${selector}`
                ).each((_, element) => {
                    const $section = this.getSection($(element));

                    if (!$section.length) return;

                    // Toggle the section, NOT the disclaimer itself.
                    $section.toggleClass('is-visible', visible);
                });
            });
        }

        // Refresh one form or all forms.
        refresh(form = null) {
            const $forms = form
                ? $(form)
                : $('.sss-form');

            $forms.each((_, element) => {
                const $form = $(element);

                this.config.forEach(rule => {
                    this.updateRule($form, rule);
                });
            });
        }

        // Bind delegated listeners once.
        bindEvents() {
            $(document)
                .off(this.eventNamespace)
                .on(
                    `change${this.eventNamespace} input${this.eventNamespace}`,
                    '.sss-form :input',
                    event => {
                        const $form = $(event.target).closest('.sss-form');

                        this.refresh($form);
                    }
                )
                .on(
                    `sss_formBuilderInit${this.eventNamespace}`,
                    () => this.refresh()
                );
        }
    }

    // ==================================================
    // CONFIGURATION
    // ==================================================

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

        // Add more rules here.
    ];

    // Create and store the singleton instance.
    namespace.disclosure = new ReactiveFormDisclosure(config);

})(jQuery, window);