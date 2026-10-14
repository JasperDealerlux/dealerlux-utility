
# SSS Form Default Values

A reusable JavaScript utility for automatically populating fields inside `form.sss-form` elements. It supports website-specific, inline configuration, targeted form rules, and forms opened through modal buttons.

The utility is built as an object-oriented class, uses jQuery for form lookup and delegated events, and maintains a single instance under `window.sss.form.defaults`.

## Features

- Applies configured defaults to all existing `form.sss-form` elements on page load.
- Reapplies defaults when a `.globalShowFormInModal` element is clicked.
- Watches briefly for forms inserted asynchronously after a modal click.
- Applies global defaults when `target_forms` is omitted.
- Limits rules to specified form selectors when `target_forms` is provided.
- Allows targeted rules to override global defaults for the same element.
- Supports common inputs, checkboxes, radio buttons, selects, multi-selects, textareas, and editable text.
- Dispatches bubbling `input` and `change` events on fields whose values actually change.
- Avoids duplicate event listeners through singleton initialization.
- Keeps configuration directly in JavaScript; **no JSON file or `fetch()` is required**.

## Files

| File | Purpose |
| --- | --- |
| `sss-form-default-values.js` | Defines and exposes the `FormDefaultValues` class. |
| `sss-form-default-values-init.js` | Holds the inline configuration and creates or updates the singleton. |

Load **the class first**, followed by **the initialization script**, after jQuery is available.

```html
<script src="/assets/js/sss-form-default-values.js"></script>
<script src="/assets/js/sss-form-default-values-init.js"></script>
```

Replace these paths with the actual script locations on your site. When using WordPress, enqueue them with appropriate dependencies so jQuery and the class script load before initialization.

## Inline configuration

Edit `CONFIGURATION` inside `sss-form-default-values-init.js`:

```javascript
(function (window) {
    'use strict';

    const namespace = window.sss?.form?.defaults;

    if (!namespace?.FormDefaultValues) {
        console.warn('FormDefaultValues is not loaded.');
        return;
    }

    const CONFIGURATION = [
        {
            general_defaults: {
                fields: [
                    {
                        'input[name="source"]': 'Website',
                        'input[name="first_name"]': 'John',
                        'input[name="newsletter"]': false
                    }
                ]
            },
            fireplace_protection_forms: {
                target_forms: [
                    '#sss-form-contact'
                ],
                fields: [
                    {
                        'input[name="model_name"]': 'Fireplace Protection Plan',
                        'input[name="first_name"]': 'Johnny'
                    }
                ]
            }
        }
    ];

    if (namespace.instance) {
        namespace.instance.setConfig(CONFIGURATION);
    } else {
        namespace.instance = new namespace.FormDefaultValues(CONFIGURATION);
    }
})(window);
```

This configuration sets `source` to `Website` and `newsletter` to unchecked in any matching SSS form. It sets `first_name` to `John` globally, but overrides it with `Johnny` in `#sss-form-contact`. The contact form also receives `model_name = Fireplace Protection Plan`.

> **Configuration syntax:** The configuration is a JavaScript array of objects, not a separate `.json` file. Group names such as `general_defaults` are descriptive and can be renamed freely.

### Rule structure

```javascript
const CONFIGURATION = [
    {
        any_descriptive_group_name: {
            // Optional: omit to apply to every form.sss-form.
            target_forms: [
                '#sss-form-contact',
                '#sss-form-routineMaintenanceRequest',
                '#sss-form-smsSubscription'
            ],
            fields: [
                {
                    'input[name="first_name"]': 'John',
                    'select[name="topic"]': 'Service'
                }
            ]
        }
    }
];
```

| Setting | Required? | Description |
| --- | --- | --- |
| Group name | Yes, for grouped configuration | Arbitrary descriptive name for a group of defaults. |
| `target_forms` | No | CSS selector string or array of selectors identifying eligible forms. If omitted, applies to all `form.sss-form` elements. |
| `fields` | Yes | Object or array of objects whose keys are CSS selectors for fields and whose values are their desired defaults. |

**Important:** Each entry in `target_forms` must be a valid CSS selector. Include `#` for ID selectors and `.` for class selectors. For example, `'#sss-form-smsSubscription'`, not `'sss-form-smsSubscription'`.

Field selectors are searched **within each eligible form**, so `input[name="first_name"]` never changes a matching input outside that form.

### Supported value examples

| Field type | Example selector | Default value |
| --- | --- | --- |
| Text, email, telephone, hidden | `input[name="first_name"]` | `'John'` |
| Number | `input[name="quantity"]` | `2` |
| Textarea | `textarea[name="message"]` | `'Please contact me.'` |
| Single select | `select[name="topic"]` | `'Service'` |
| Multiple select | `select[multiple][name="interests[]"]` | `['Hot Tubs', 'Saunas']` |
| Single checkbox | `input[name="newsletter"]` | `true` or `false` |
| Checkbox group | `input[type="checkbox"][name="interests[]"]` | `['Hot Tubs', 'Saunas']` |
| Radio group | `input[type="radio"][name="contact_preference"]` | `'Phone Call'` |
| Date | `input[type="date"][name="preferred_date"]` | `'2026-10-15'` |
| Contenteditable element | `[contenteditable="true"]` | `'Default text'` |

For checkbox groups, an array marks matching checkbox **values** as checked and unchecks nonmatching ones. For radio groups, provide the desired radio **value**; use a selector matching every radio in that group. A boolean is useful when targeting an individual radio option.

For selects, the desired option values must exist. Browser validation and input constraints still apply (for example, an invalid date/number may not be accepted).

**Not supported:** File inputs cannot be populated programmatically for browser security reasons. Button, submit, reset, and image inputs are also skipped.

## When values are applied

### 1. Initial page load

When the initialization script creates the instance, the class processes existing `form.sss-form` elements when the DOM is ready (or immediately if it is already ready).

### 2. Modal button click

The class listens for delegated clicks on `.globalShowFormInModal`. On click it:

1. Begins observing the document for newly inserted forms or fields.
2. Refreshes existing forms after the current click handler stack completes.
3. Refreshes SSS forms found in inserted content during the observation window.
4. Stops observing after **5 seconds**.

The observer is temporary and is not a general-purpose monitor for all forms inserted at arbitrary times.

### Change events

For each field whose value actually changes, the class dispatches native, bubbling `input` and `change` events. Unchanged fields do not receive these events.

## Rule priority

When several rules affect the same field element, priority works as follows:

1. Global rules are applied first.
2. Rules with matching `target_forms` are applied afterward and override global rules.
3. Among rules in the same category, later configuration entries override earlier entries for the same element.

Assignments are resolved **before** values are written. Each changed field receives its final value once rather than receiving repeated intermediate values.

## Public API

The instance is available at:

```javascript
window.sss.form.defaults.instance
```

Refresh all existing SSS forms:

```javascript
window.sss.form.defaults.instance.refresh();
```

Refresh a particular form:

```javascript
window.sss.form.defaults.instance.refresh('#sss-form-contact');
```

Replace the configuration and refresh existing forms:

```javascript
window.sss.form.defaults.instance.setConfig(CONFIGURATION);
```

Stop modal observation and remove the instance's delegated listeners:

```javascript
window.sss.form.defaults.instance.destroy();
```

After `destroy()`, create a **new instance** if you want to re-enable event handling; `setConfig()` on the destroyed object does not rebind events.

## Important behavior and limitations

- **Values are defaults, not placeholders.** Applying a rule modifies the actual field value or checked state.
- **Existing user input can be overwritten.** Reapplying defaults on modal clicks or manual `refresh()` calls may replace values a visitor has already entered. The current implementation does not have an "only if empty" or "do not overwrite user changes" option.
- **All existing forms are refreshed on a modal click.** The click listener is global; it does not restrict refresh to only the modal's form. Use `target_forms` to scope rules where appropriate.
- **Event listeners can trigger other page behavior.** Conditional field logic, validation, or third-party scripts may react to the dispatched `input` and `change` events.
- **No persistent observer.** Forms inserted well after page load and outside the modal observation period must be populated with `refresh()` or another explicit integration point.
- **Checkbox and radio selectors should cover the full group** if you want the desired option selected and other options deselected.
- **Do not put sensitive values in configuration.** All client-side JavaScript is visible to website visitors. Never include passwords, API keys, or private data.
- **This is not a security control.** Any browser-populated field value can be modified by the visitor. Validate and authorize values on the server.

## Troubleshooting

**`FormDefaultValues is not loaded.`** Verify that jQuery and `sss-form-default-values.js` load before the initialization script. Also check for JavaScript errors preventing the class from registering.

**A field is not populated.** Confirm that the form has class `sss-form`, that its ID matches `target_forms` where specified, that the field selector matches an element *inside* that form, and that the configured value is valid for its control type.

**A modal field is not populated.** Confirm that the clicked CTA or one of its ancestors matches `.globalShowFormInModal`. If the modal injects its form more than 5 seconds later, invoke `refresh()` after the form is inserted or change the integration to listen for the modal's completion event.

**A value changes unexpectedly.** Look for a targeted rule that overrides a global one, another rule later in the configuration, or another script reacting to `input`/`change`.

**A radio selection behaves incorrectly.** Match every radio in the group with one selector and configure the exact value of the option to select.

## Namespace

```javascript
window.sss.form.defaults.FormDefaultValues // Reusable class
window.sss.form.defaults.instance          // Singleton instance
```

The namespace and singleton pattern prevent repeated initialization from creating duplicate delegated click handlers.
