# Reactive Form Disclosure

A reusable, configuration-driven feature for showing or hiding informational disclosures inside Dealer Lux website forms based on a visitor's field selections.

## Purpose

Some form notices only matter when a particular option is selected. For example, on the Vienna Hot Tubs website, customers can choose **Text Message** as their preferred contact method. The SMS disclosure should appear when that option is selected and remain hidden otherwise.

The **Reactive Form Disclosure** feature handles this behavior without writing custom show/hide code for every form. It can also be reused for other notices, such as financing information or service-specific terms.

> **Important:** This feature only controls the visibility of disclosure text. It does **not** collect, validate, store, or prove SMS consent. A separate, explicit opt-in mechanism and appropriate server-side processing are needed where required.

## Files and responsibilities

| File | Responsibility |
| --- | --- |
| `fields.php` | Defines reusable form fields, including the HTML disclosure block (`61`). |
| `sections.php` | Groups field IDs into form sections, including the dedicated disclosure section (`34`). |
| `forms.php` | Selects which sections appear in each form. |
| `style.css` | Styles disclosures and hides reactive disclosures until their section is activated. |
| `ReactiveFormDisclosure.js` | Implements reusable field matching, section discovery, class toggling, and event handling. |
| `ReactiveFormDisclosureConfig.js` | Declares the website-specific trigger rules and initializes or reuses the feature. |

### How the parts work together

1. `fields.php` defines an HTML block with the base class `form_message_disclaimer`, a target class such as `sms_consent_disclaimer`, and the `reactive` class.
2. `sections.php` includes that field in a section (for example, section `34`).
3. `forms.php` includes that section in each applicable form. Alternatively, the field can be placed within another section, provided its rendered markup has the required direct-parent structure.
4. `ReactiveFormDisclosureConfig.js` identifies the trigger field(s) and target disclosure class.
5. `ReactiveFormDisclosure.js` checks each form's current field values and toggles `is-visible` on the disclosure's parent `.row`.
6. `style.css` decides whether the **disclosure itself** is displayed; the row is **not** hidden.

## Required HTML structure

The JavaScript expects the disclaimer to be a **direct child** of `.form-group`, and that `.form-group` to be a **direct child** of `.row`:

```html
<div class="row">
    <div class="form-group col-md-12">
        <div class="form_message_disclaimer sms_consent_disclaimer reactive">
            <p>SMS disclosure content...</p>
        </div>
    </div>
</div>
```

When processed, the script adds descriptive classes to the existing parent elements:

```html
<div class="row relative_form_section is-visible">
    <div class="form-group col-md-12 relative_field_container">
        <div class="form_message_disclaimer sms_consent_disclaimer reactive">
            <p>SMS disclosure content...</p>
        </div>
    </div>
</div>
```

- `relative_field_container`: the disclosure's immediate `.form-group` parent.
- `relative_form_section`: the container's immediate `.row` parent.
- `is-visible`: added to the row when a configured show condition is active and no hide condition is active.

The script adds CSS classes; it does not move or duplicate the markup.

## Visibility CSS

```css
/* Hidden by default */
.form_message_disclaimer.reactive {
    display: none;
}

/* Show only the reactive disclaimer when its row is active */
.relative_form_section.is-visible .form_message_disclaimer.reactive {
    display: flex;
}
```

The additional `.form_message_disclaimer` rules in `style.css` provide the blue notice design, Font Awesome information icon, link styling, and mobile adjustments. Font Awesome 5 Free must be available for the current `::before` icon to render.

**Do not hide `.relative_form_section` itself.** Other fields in the same row should remain unaffected.

## Configuring a disclosure

Edit **`ReactiveFormDisclosureConfig.js`** to set the trigger selectors and target class:

```javascript
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
];
```

### Configuration properties

| Property | Meaning |
| --- | --- |
| `trigger.show` | CSS selector or array of selectors; at least one matching field must be **active** to show the target. |
| `trigger.hide` | Optional CSS selector or array of selectors; if any matching field is active, hiding takes priority. |
| `target` | Target class name (for example, `sms_consent_disclaimer`) or array of class names on reactive disclosure elements. Do not use arbitrary complex CSS selectors here. |

Multiple selectors are treated as **OR** conditions within `show` and within `hide`. Overall visibility is:

```text
visible = anyShowTriggerActive && !anyHideTriggerActive
```

A rule with no `show` trigger will not display a disclosure.

The matching fields are searched **within the same `.sss-form`**, so a selection in one form does not affect a separate form.

### How fields are considered active

| Field type | Active when... |
| --- | --- |
| Radio button | Checked. |
| Checkbox | Checked. |
| Single-select dropdown | Selected value is non-empty. |
| Multi-select dropdown | At least one selected value is non-empty. |
| Text, email, telephone, date, number, textarea, and similar controls | Value is non-empty after trimming whitespace. |
| Other matching elements | jQuery considers the element visible. |

For a select option with a **specific** value, target the selected option explicitly (for example, `select[name="topic"] option[value="Financing"]:checked`) instead of just `select[name="topic"]`, which means *any non-empty selection*.

**Selector note:** The class uses jQuery's `.find()`. Use supported CSS/jQuery selectors that match the rendered form markup. Target classes are appended to `.form_message_disclaimer.reactive`.

## Installation and load order

This feature depends on **jQuery** and the website's form markup using `.sss-form`.

Load the scripts in this order:

1. jQuery
2. `ReactiveFormDisclosure.js` (class logic)
3. `ReactiveFormDisclosureConfig.js` (rules and initialization)

Example WordPress registration, with paths adjusted to your theme:

```php
wp_enqueue_script(
    'sss-reactive-form-disclosure',
    get_stylesheet_directory_uri() . '/assets/js/ReactiveFormDisclosure.js',
    ['jquery'],
    '1.0.0',
    true
);

wp_enqueue_script(
    'sss-reactive-form-disclosure-config',
    get_stylesheet_directory_uri() . '/assets/js/ReactiveFormDisclosureConfig.js',
    ['sss-reactive-form-disclosure'],
    '1.0.0',
    true
);
```

Load the corresponding stylesheet through the site's normal CSS pipeline.

## Public JavaScript API

The constructor is registered at:

```javascript
window.sss.form.interactive.ReactiveFormDisclosure
```

The current singleton instance is stored at:

```javascript
window.sss.form.interactive.disclosure
```

Available methods:

```javascript
// Re-evaluate every current form.
window.sss.form.interactive.disclosure.refresh();

// Re-evaluate just one form.
window.sss.form.interactive.disclosure.refresh('#sss-form-siteInspection');

// Replace all active rules and refresh current forms.
window.sss.form.interactive.disclosure.setConfig(newConfig);

// Unbind listeners and clear the active instance reference.
window.sss.form.interactive.disclosure.destroy();
```

The implementation uses delegated `input` and `change` events to respond to field changes. It also listens for `sss_formBuilderInit` to refresh when Dealer Lux forms are initialized again. If a form is inserted by another mechanism, call `refresh()` after it is added.

### Duplicate prevention

- The class constructor is registered only once on `window.sss.form.interactive`.
- The configuration file reuses the existing instance and calls `setConfig()` instead of creating another instance.
- Event handlers are namespaced as `.sssReactiveDisclosure` and rebound without accumulating duplicate handlers.
- `addClass()` does not add duplicate copies of an existing CSS class.

**Limit:** This does not deduplicate PHP definitions or repeated disclosure HTML. If a form renders two instances of the same disclaimer, both remain in the page.

## Adding another disclosure type

1. Create another HTML block in `fields.php` using `form_message_disclaimer`, `reactive`, and a unique type class, such as `financing_disclaimer`.
2. Add its field ID to an appropriate section in `sections.php`.
3. Add that section to the intended forms in `forms.php`.
4. Add a configuration rule targeting `financing_disclaimer` in `ReactiveFormDisclosureConfig.js`.
5. Test its initial state and each relevant field selection.

Example additional rule:

```javascript
{
    trigger: {
        show: 'select[name="topic"] option[value="Financing"]:checked'
    },
    target: 'financing_disclaimer'
}
```

**Best practice:** Place independently controlled reactive disclaimers in separate `.row` elements. Because `is-visible` is applied at the **row** level, two different disclosures in the same row can interfere with each other's visibility.

## Current Vienna Hot Tubs implementation

In the supplied PHP definitions:

- `fields.php` entry **`61`** defines the SMS disclosure, including the `form_message_disclaimer sms_consent_disclaimer reactive` classes.
- `sections.php` entry **`0`** includes fields `[0, 2, 3, 28, 61]`: contact information, contact preference, and the SMS disclosure.
- `sections.php` entry **`34`** also includes field `[61]` as a dedicated disclosure section.
- Many forms include both section `0` **and** section `34`, which causes the same disclosure to appear twice in their rendered markup.

### Configuration issues to review before release

1. **Duplicate placement:** Remove section `34` from forms that already include section `0` with field `61`, or remove field `61` from section `0` and keep the dedicated section. Choose **one** consistent placement strategy.
2. **Unconditional form overrides:** Some forms include section `34` even when their contact information is defined elsewhere. The JavaScript will only reveal the disclaimer when the configured `contact_preference` trigger exists and becomes active.
3. **Duplicate `serviceRequest` key:** `forms.php` declares `"serviceRequest"` twice. In PHP arrays, the later declaration replaces the earlier one; only the last `"serviceRequest"` configuration takes effect.
4. **SMS subscription form:** `textMessageSubscribe` uses section `5` (name and cell number) but does not include a consent control or the reactive disclosure field in that section. It needs its own suitable SMS opt-in and disclosure approach.
5. **`referAFriend` contains a friend's phone number:** A referring customer's submission should not be treated as proof that the friend consented to receive texts.
6. **Shared section effects:** Editing shared section `0` changes every form that uses it. Confirm which forms should display the disclosure before rolling out site-wide.

## Testing checklist

- [ ] Scripts load without console errors and in the correct order.
- [ ] A reactive disclosure is hidden when the form first loads with no matching active trigger.
- [ ] Choosing **Text Message** shows the correct disclosure in that form.
- [ ] Choosing **Email**, **Phone Call**, or **I have no preference** hides the SMS disclosure.
- [ ] Selecting an option in one form does not change the disclaimer in another form.
- [ ] The parent row remains displayed; only the reactive disclosure is hidden.
- [ ] Dynamic forms work after `sss_formBuilderInit` or an explicit `refresh()`.
- [ ] No disclaimer appears twice unless that is intentionally configured.
- [ ] Links to the Privacy Policy and Terms & Conditions work.
- [ ] The Font Awesome icon and responsive styles display correctly.
- [ ] Any required SMS opt-in checkbox is unchecked by default and consent is captured server-side independently of this feature.

## Maintenance guidance

Keep reusable behavior in `ReactiveFormDisclosure.js` and client-specific rules in `ReactiveFormDisclosureConfig.js`. Add new notice text in `fields.php`, assign fields to sections in `sections.php`, and connect those sections to forms through `forms.php`. Avoid editing the core JavaScript class for client-specific wording or selector changes.

### Compliance boundary

A disclosure telling users about SMS messages is **not the same as affirmative consent**. The reactive UI cannot verify identity, prevent unauthorized texting, record audit evidence, or determine whether a messaging program meets Jobber/carrier/legal requirements. Confirm the policy language and opt-in flow with the business before submitting a DPN registration.
