
(function ($, window) {
    'use strict';

    // Create namespaces safely.
    window.sss = window.sss || {};
    window.sss.form = window.sss.form || {};
    window.sss.form.interactive =
        window.sss.form.interactive || {};

    const namespace = window.sss.form.interactive;

    // Prevent duplicate class registration.
    if (namespace.ReactiveFormDisclosure) {
        return;
    }

    class ReactiveFormDisclosure {
        constructor(config = []) {
            this.config = [];
            this.eventNamespace = '.sssReactiveDisclosure';

            this.setConfig(config);
            this.bindEvents();
            this.refresh();
        }

        // Convert selectors into a unique array.
        toArray(value) {
            if (!value) return [];

            return [...new Set(
                (Array.isArray(value) ? value : [value])
                    .flatMap(item => String(item).split(','))
                    .map(item => item.trim())
                    .filter(Boolean)
            )];
        }

        // Update configuration without creating another instance.
        setConfig(config = []) {
            this.config = Array.isArray(config)
                ? config
                : [config];

            this.refresh();

            return this;
        }

        // Find fields inside the current form.
        findFields($form, selectors) {
            const list = this.toArray(selectors);

            if (!list.length) return $();

            return $form.find(list.join(','));
        }

        // Check if a matching field is active.
        isActive(element) {
            const $field = $(element);

            if ($field.is(':radio, :checkbox')) {
                return $field.prop('checked');
            }

            if ($field.is('select')) {
                const value = $field.val();

                return Array.isArray(value)
                    ? value.some(item => String(item).trim() !== '')
                    : String(value ?? '').trim() !== '';
            }

            if ($field.is('input, textarea')) {
                return String($field.val() ?? '').trim() !== '';
            }

            return $field.is(':visible');
        }

        // Return true when any matching trigger is active.
        matches($form, selectors) {
            return this.findFields($form, selectors)
                .toArray()
                .some(element => this.isActive(element));
        }

        // Find the disclaimer's direct parent containers.
        getSection($disclaimer) {
            const $container = $disclaimer.parent('.form-group');

            if (!$container.length) return $();

            const $section = $container.parent('.row');

            if (!$section.length) return $();

            $container.addClass('relative_field_container');
            $section.addClass('relative_form_section');

            return $section;
        }

        // Process a single rule inside one form.
        updateRule($form, rule) {
            const targets = this.toArray(rule.target);

            if (!targets.length) return;

            const show = this.matches($form, rule.trigger?.show);
            const hide = this.matches($form, rule.trigger?.hide);

            // Hide has priority.
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

                    $section.toggleClass('is-visible', visible);
                });
            });
        }

        // Refresh all forms or a specific form.
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

            return this;
        }

        // Bind delegated listeners.
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

        // Clean up event listeners if needed.
        destroy() {
            $(document).off(this.eventNamespace);

            if (namespace.disclosure === this) {
                namespace.disclosure = null;
            }
        }
    }

    // Expose the reusable class.
    namespace.ReactiveFormDisclosure = ReactiveFormDisclosure;

})(jQuery, window);
