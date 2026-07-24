# Dealerlux Utility — AI Development Rules

## 1. Project Scope

This AI workspace is intended exclusively for the following repository:

`https://github.com/JasperDealerlux/dealerlux-utility`

All code analysis, recommendations, documentation, generated code, refactoring, testing, and architectural decisions must relate directly to this repository.

Do not reuse assumptions, code structures, implementation details, or requirements from unrelated Dealer Lux repositories unless the user explicitly requests an integration.

When external Dealer Lux systems are mentioned, treat them as dependencies or integration targets rather than part of this repository.

---

## 2. Project Purpose

`dealerlux-utility` is a reusable WordPress utility plugin intended to centralize Dealer Lux functionality that would otherwise be duplicated across client websites, themes, local plugins, or temporary development snippets.

The project should prioritize:

1. Reusable WordPress utilities.
2. Centralized configuration.
3. Maintainable object-oriented architecture.
4. Safe compatibility across multiple Dealer Lux websites.
5. Clear separation between application logic, configuration, content, and presentation.
6. Predictable registration and initialization of features.
7. Easy extension without modifying existing stable components.
8. Reduction of repeated code across client projects.

The AI should continuously study the repository’s infrastructure and recommend improvements that support these goals.

---

## 3. Repository-Only Rule

Before generating or modifying code, verify that the requested work belongs in `dealerlux-utility`.

A feature belongs in this repository when it is:

* Reusable across multiple Dealer Lux websites.
* A general WordPress utility.
* A centralized configuration or registry.
* A shared shortcode, virtual page, virtual post, debugging tool, helper, or administrative utility.
* Infrastructure that reduces duplication in client-specific plugins.
* A reusable integration layer for Dealer Lux systems.

A feature may not belong in this repository when it is:

* Specific to only one client website.
* A temporary visual adjustment.
* Client-specific copy or branding.
* A one-off marketing campaign.
* A local theme customization.
* A feature tightly coupled to one website without a configurable abstraction.

When client-specific behavior is necessary, prefer configuration, filters, actions, adapters, or extension classes instead of hard-coding a client domain into shared classes.

---

## 4. Existing Architectural Direction

Preserve and improve the repository’s established architectural direction.

The repository currently uses or is moving toward:

* The `DealerluxUtils` PHP namespace.
* A central plugin bootstrap.
* A custom namespace-based autoloader.
* An `Initializer` responsible for registering project classes.
* Reusable singleton behavior where appropriate.
* Classes organized under the `src/` directory.
* Procedural helper functions organized under `utils/`.
* Configuration organized under `config/`.
* Registry classes for centralized WordPress data.
* Virtual pages and posts generated from configuration.
* Shortcode classes for reusable interface output.
* Gutenberg block markup for generated WordPress content.

Do not introduce a competing architecture without a strong technical reason.

---

## 5. Expected Directory Responsibilities

Use the following directory responsibilities when adding or reorganizing functionality:

```text
dealerlux-utility/
├── core.php
├── config/
│   ├── options/
│   ├── posts/
│   ├── pages/
│   └── integrations/
├── src/
│   ├── admin/
│   ├── integrations/
│   ├── registries/
│   ├── shortcodes/
│   ├── services/
│   ├── support/
│   └── traits/
├── utils/
│   └── functions.php
├── assets/
│   ├── css/
│   └── js/
├── tests/
├── docs/
└── .github/
```

Directory guidance:

* `core.php` must remain focused on bootstrapping and loading the application.
* `src/` contains classes and traits.
* `config/` contains declarative configuration and content definitions.
* `utils/` contains carefully scoped procedural helpers.
* `assets/` contains reusable CSS and JavaScript.
* `tests/` contains automated tests.
* `docs/` contains architectural and implementation documentation.
* `.github/` contains repository automation and AI instructions.

Do not place substantial business logic inside `core.php`.

---

## 6. Class and File Naming

Follow the existing WordPress-oriented class-loading convention.

Examples:

```text
DealerluxUtils\Initializer
→ src/class-initializer.php

DealerluxUtils\Shortcodes\Dump_Client_Forms_Shortcode
→ src/shortcodes/class-dump-client-forms-shortcode.php

DealerluxUtils\Traits\Singleton
→ src/traits/trait-singleton.php
```

Rules:

* PHP namespaces must begin with `DealerluxUtils`.
* Class names must clearly describe their responsibility.
* Use one primary class or trait per file.
* Class files use the `class-` prefix.
* Trait files use the `trait-` prefix.
* Convert underscores in class names to hyphens in filenames.
* Keep directory names lowercase.
* Avoid vague class names such as `Manager`, `Helper`, or `Utility` unless paired with a specific responsibility.
* Avoid creating “god classes” that manage unrelated systems.

---

## 7. Class Registration

Every executable feature class must have an explicit registration path.

Prefer one of the following patterns:

```php
Initializer::register();
```

or registration through a clearly defined class list inside the initializer.

When adding a class:

1. Add the class in the correct namespace and directory.
2. Ensure the custom autoloader can resolve its filename.
3. Register the class through the established initialization system.
4. Confirm its hooks are registered only once.
5. Avoid instantiating feature classes directly from random files.

The bootstrap should load infrastructure. Feature classes should register WordPress behavior.

---

## 8. WordPress Standards

All PHP code must follow WordPress-compatible development practices.

Requirements:

* Guard executable PHP files against direct access.
* Escape output at the point of rendering.
* Sanitize external input before use.
* Validate expected values separately from sanitization.
* Use nonces and capability checks for administrative actions.
* Use WordPress APIs instead of direct database queries whenever practical.
* Use prepared statements for unavoidable database queries.
* Use `wp_json_encode()` rather than raw `json_encode()` in WordPress-facing code.
* Use `wp_normalize_path()` when working with filesystem paths.
* Use `trailingslashit()` and `untrailingslashit()` where appropriate.
* Use `home_url()`, `site_url()`, `plugins_url()`, and related APIs instead of manually constructing URLs.
* Use translation functions for user-facing reusable strings.
* Do not suppress errors using `@`.
* Do not leave unconditional debugging output in production code.

All hook names, option names, transient names, shortcode names, script handles, and CSS classes must use a consistent Dealerlux-specific prefix.

Preferred prefixes:

```text
dealerlux_
dealerlux_utils_
dl_
dl-utils-
```

Use the most appropriate prefix consistently within each API surface.

---

## 9. PHP Compatibility

The current project declares PHP 7.4 compatibility.

Unless the project requirement is deliberately updated:

* Do not use PHP 8-only syntax.
* Do not use union types.
* Do not use constructor property promotion.
* Do not use attributes.
* Do not use named arguments.
* Do not use `match`.
* Do not use nullsafe operators.
* Do not use enums.
* Do not introduce dependencies that require PHP 8 or later.

When recommending a PHP version upgrade, identify:

1. The minimum proposed version.
2. The incompatible syntax or dependencies involved.
3. The affected hosting environments.
4. The migration steps.
5. The expected benefit.

Never silently break the declared runtime requirement.

---

## 10. Configuration-Driven Development

Prefer declarative configuration over repeated imperative registration.

Good candidates for configuration include:

* Virtual pages.
* Virtual posts.
* WordPress options.
* Shortcode definitions.
* Feature flags.
* Administrative utilities.
* Client-specific overrides.
* Reusable content.
* Integration settings.

Configuration files should:

* Return arrays or clearly defined values.
* Avoid performing WordPress actions when included.
* Avoid hidden side effects.
* Include clear keys and predictable schemas.
* Be validated before registration.
* Support extension through filters where appropriate.

Do not combine data definitions, HTML generation, and WordPress hook registration in one configuration file.

---

## 11. Registry Design

Registry classes must have a clear lifecycle:

1. Load configuration.
2. Validate configuration.
3. Normalize entries.
4. Register WordPress hooks.
5. Resolve requested entries.
6. Render or apply the resolved data.
7. Fail safely when an entry is invalid.

Registry classes should not assume every configuration file is valid.

Add safeguards for:

* Missing configuration files.
* Invalid array shapes.
* Duplicate identifiers.
* Duplicate slugs.
* Invalid post types.
* Invalid option names.
* Circular parent relationships.
* Missing content files.
* Unsupported callback definitions.

Where practical, validation failures should be visible in development logs without breaking the public website.

---

## 12. Virtual Pages and Posts

Virtual WordPress content must behave predictably within WordPress.

When implementing virtual pages or posts, verify:

* Canonical URLs.
* Rewrite rules.
* Query variables.
* Main-query behavior.
* Correct post type.
* Correct post status.
* Parent-child page relationships.
* Breadcrumb compatibility.
* SEO plugin compatibility.
* Page title handling.
* Template resolution.
* Body classes.
* Navigation menu behavior.
* REST API behavior when applicable.
* Avoidance of duplicate URLs.
* Avoidance of collisions with real WordPress posts.

Virtual content should not unexpectedly override a real published post with the same slug unless that behavior is explicitly configured.

Content definitions must remain separate from routing and registry logic.

---

## 13. Shortcode Rules

Shortcodes must:

* Return output rather than echo it.
* Use output buffering only when necessary.
* Sanitize and validate shortcode attributes.
* Escape generated output.
* Use predictable defaults.
* Avoid enqueueing the same assets multiple times.
* Avoid globally modifying page content.
* Work correctly when used more than once on a page.
* Avoid duplicate HTML IDs.
* Support extension through hooks or configuration.
* Document supported attributes.

For style variants such as CTA and accordion layouts, share data-loading logic and isolate the renderer for each presentation style.

Preferred conceptual structure:

```text
Form discovery service
        ↓
Normalized form definitions
        ↓
Shortcode controller
        ↓
CTA renderer / Accordion renderer
```

Do not duplicate form discovery logic for every display style.

---

## 14. Gutenberg Content

Generated Gutenberg content must remain valid block markup.

When producing block content:

* Preserve opening and closing block comments.
* Ensure nested blocks are properly balanced.
* Escape dynamic URLs and text before interpolation.
* Avoid malformed serialized block attributes.
* Test the content in both the block editor and frontend.
* Avoid hard-coded theme-dependent classes unless configurable.
* Prefer semantic HTML.
* Ensure heading hierarchy is logical.
* Include accessibility attributes where required.

Content files should describe content. Rendering and registration logic should remain in classes.

---

## 15. Hooks and Extensibility

Reusable features should expose intentional extension points.

Use actions when another component may need to perform an additional operation.

Use filters when another component may need to modify a value.

Hook names must:

* Be prefixed.
* Clearly describe the lifecycle stage.
* Pass enough context to be useful.
* Avoid exposing unstable internal implementation details.

Example patterns:

```php
do_action(
    'dealerlux_utils_before_virtual_post_register',
    $post_definition
);

$post_definition = apply_filters(
    'dealerlux_utils_virtual_post_definition',
    $post_definition,
    $post_key
);
```

Do not add hooks without documenting their expected parameters.

---

## 16. Dependency Management

Avoid adding external dependencies unless they solve a meaningful problem.

Before introducing a dependency, evaluate:

* Whether WordPress already provides equivalent functionality.
* PHP-version compatibility.
* Package maintenance.
* Security history.
* Bundle size.
* Autoloading conflicts.
* Licensing.
* Deployment impact for MU plugins.
* Whether Composer is available in the production environment.

If Composer is introduced, preserve compatibility with the existing plugin bootstrap or provide a documented migration.

Do not mix a Composer PSR-4 autoloader and the custom autoloader without a defined loading order and conflict strategy.

---

## 17. Error Handling and Logging

Errors must fail safely.

Public-facing behavior should not reveal:

* Filesystem paths.
* Stack traces.
* SQL queries.
* Authentication values.
* API credentials.
* Private configuration.
* Personally identifiable information.

Development logging should:

* Be conditional.
* Include feature context.
* Use actionable messages.
* Avoid logging sensitive data.
* Avoid repetitive messages on every request.

Where appropriate, use a centralized logging abstraction rather than scattered `error_log()` calls.

---

## 18. Security Requirements

The AI must check every proposed implementation for:

* Cross-site scripting.
* Cross-site request forgery.
* SQL injection.
* Unauthorized administrative access.
* Arbitrary file inclusion.
* Path traversal.
* Unsafe redirects.
* Unsafe unserialization.
* Remote code execution.
* Sensitive data exposure.
* Unvalidated webhook requests.
* Unsanitized REST API parameters.
* Publicly exposed secrets.

Secrets must never be committed to this repository.

Use environment configuration, WordPress constants, secure option storage, or deployment-level secrets where appropriate.

---

## 19. Performance Requirements

This plugin may run across many WordPress websites. Small inefficiencies can multiply significantly.

The AI must watch for:

* Repeated filesystem scanning.
* Repeated parsing of configuration.
* Expensive hooks running on every request.
* Unnecessary database queries.
* Repeated option reads without caching.
* Duplicate asset enqueues.
* Large generated HTML strings.
* Registries loading features that are not needed.
* Rewrite rules being flushed on every request.
* Remote API calls during normal page rendering.
* Unbounded arrays or loops.
* Excessive autoloaded WordPress options.

Cache normalized configuration where appropriate, but provide a reliable invalidation strategy.

Never call `flush_rewrite_rules()` on every page load.

---

## 20. Testing Expectations

For each meaningful feature, consider tests at the appropriate level.

Minimum verification should cover:

* Autoload resolution.
* Class initialization.
* Hook registration.
* Configuration validation.
* Registry normalization.
* Shortcode attribute handling.
* Shortcode output.
* Virtual page resolution.
* Virtual post resolution.
* Duplicate slug handling.
* Missing content files.
* Security-sensitive administrative actions.
* PHP 7.4 syntax compatibility.

Recommended testing tools:

* PHPUnit.
* WordPress PHPUnit test suite.
* PHP_CodeSniffer with WordPress Coding Standards.
* PHPStan or Psalm at a project-appropriate level.
* JavaScript linting when frontend scripts are introduced.
* Manual WordPress block validation for Gutenberg content.

When automated tests do not exist, clearly state the manual verification procedure.

---

## 21. Documentation Requirements

Every major infrastructure feature should include documentation describing:

* Purpose.
* Location.
* Registration process.
* Configuration schema.
* Public hooks.
* Usage examples.
* Failure behavior.
* Security considerations.
* Testing procedure.
* Extension method.

Avoid documentation that only repeats the code.

Documentation should explain why the component exists and how another developer should extend it safely.

---

## 22. Backward Compatibility

Treat existing public behavior as stable unless the task explicitly authorizes a breaking change.

Public behavior includes:

* Shortcode names.
* Shortcode attributes.
* Public function names.
* Class names used externally.
* Hook names.
* Configuration keys.
* Option names.
* Virtual URLs.
* Generated page slugs.

For necessary breaking changes:

1. Explain the reason.
2. Identify affected usage.
3. Provide a migration path.
4. Add deprecation handling where practical.
5. Update documentation.
6. Avoid combining unrelated breaking changes.

---

## 23. Refactoring Rules

Before refactoring:

1. Identify the current responsibility.
2. Identify duplication or coupling.
3. Determine the stable public interface.
4. Separate behavioral changes from structural changes.
5. Preserve compatibility unless instructed otherwise.
6. Confirm how the refactor will be tested.

Prefer small, reviewable refactors over complete rewrites.

Do not replace working WordPress behavior merely to introduce a design pattern.

Patterns should simplify the project, not make it harder to understand.

---

## 24. Infrastructure Improvement Priorities

The AI should actively identify and recommend improvements in the following order.

### Priority 1: Correctness and Safety

* Fix autoloading edge cases.
* Prevent duplicate initialization.
* Validate configuration.
* Improve escaping and sanitization.
* Add capability and nonce checks.
* Prevent duplicate virtual URLs.
* Prevent asset duplication.
* Remove production debugging output.

### Priority 2: Architectural Clarity

* Keep bootstrap logic minimal.
* Make initializer registration explicit.
* Split registries by responsibility.
* Separate data loading from rendering.
* Separate routing from content definitions.
* Define stable interfaces between components.

### Priority 3: Testability

* Add automated tests.
* Introduce deterministic configuration loaders.
* Avoid hidden global state.
* Isolate WordPress-specific side effects.
* Make renderers testable independently.

### Priority 4: Developer Experience

* Add a clear README.
* Document directory structure.
* Document how to add a new class.
* Document how to register a new shortcode.
* Document how to add virtual content.
* Add coding-standard commands.
* Add static-analysis commands.
* Add local development instructions.

### Priority 5: Delivery Automation

* Add GitHub Actions for syntax validation.
* Add WordPress Coding Standards checks.
* Add unit tests.
* Add static analysis.
* Add release packaging.
* Add version-validation checks.
* Add changelog enforcement where appropriate.

---

## 25. Suggested Initial Infrastructure Improvements

Based on the current architecture, prioritize the following improvements.

### 25.1 Add a Project README

The README should explain:

* What the plugin does.
* Whether it is a standard plugin or MU plugin.
* Installation location.
* Supported PHP and WordPress versions.
* Directory structure.
* Class autoloading rules.
* Initializer registration.
* Available utilities.
* Registry architecture.
* Shortcode usage.
* Virtual page and post behavior.
* Development commands.
* Release process.

### 25.2 Add Automated Coding Standards

Add WordPress Coding Standards using PHP_CodeSniffer.

Recommended checks:

```text
WordPress
WordPress-Core
WordPress-Docs
WordPress-Extra
PHPCompatibilityWP
```

Configure the supported PHP version consistently with the plugin header.

### 25.3 Add Continuous Integration

Create a GitHub Actions workflow that runs:

1. PHP syntax checks.
2. Composer validation when Composer is introduced.
3. PHPCS.
4. PHPUnit tests.
5. Static analysis.
6. Security or dependency checks.

Test against the project’s supported PHP versions.

### 25.4 Improve Bootstrap Separation

Keep `core.php` limited to:

* Plugin metadata.
* Direct-access guard.
* Constant definitions.
* Autoloader registration.
* Utility loading.
* Application initialization.

Move logging, validation, and feature-specific behavior into dedicated classes.

### 25.5 Formalize Configuration Loading

Create a configuration loader that:

* Resolves known configuration directories.
* Confirms that files exist.
* Ensures files return arrays.
* Normalizes configuration.
* Reports duplicate keys.
* Applies filters.
* Optionally caches normalized results.

### 25.6 Add Registry Interfaces

Where registries share behavior, define lightweight contracts such as:

```php
interface Registry_Interface {

    public function register(): void;
}
```

Do not create interfaces unless multiple implementations genuinely benefit from the shared contract.

### 25.7 Centralize Logging

Introduce a project logger or logging service with:

* Debug, warning, and error levels.
* Conditional output.
* Feature context.
* Sensitive-data protection.
* Optional integration with WordPress debugging.

### 25.8 Add Feature Flags

Allow higher-risk or optional features to be enabled through configuration or filters.

Examples:

* Virtual posts.
* Virtual pages.
* development-only utility pages.
* debugging tools.
* shortcode preview directories.
* external integrations.

### 25.9 Add Version and Schema Tracking

Track:

* Plugin version.
* Configuration schema version.
* Database or option migrations.
* Required migration state.

Avoid using plugin activation hooks as the only migration mechanism when the plugin may operate as an MU plugin.

### 25.10 Add an Internal Diagnostics Screen

Consider an administrator-only diagnostics page displaying:

* Plugin version.
* PHP version.
* WordPress version.
* Loaded classes.
* Registered features.
* Configuration errors.
* Duplicate virtual slugs.
* Missing content files.
* Current feature flags.

The page must require an appropriate administrative capability and must not expose secrets.

---

## 26. AI Response Rules

When answering questions about this project, the AI must:

1. Study the relevant repository files before proposing substantial architectural changes.
2. Name the files that should be created or modified.
3. Explain how the change fits the existing architecture.
4. Preserve established naming and loading conventions.
5. Provide complete code when the user requests complete code.
6. Avoid unexplained placeholders in production-ready code.
7. Identify assumptions explicitly.
8. Identify backward-compatibility risks.
9. Identify security implications.
10. Include a testing procedure.
11. Recommend documentation updates.
12. Avoid modifying unrelated components.
13. Prefer repository evidence over generic WordPress assumptions.
14. Check recent changes before assuming the repository structure is unchanged.
15. Treat the repository’s current implementation as the source of truth.

---

## 27. Code Review Checklist

Before presenting a final implementation, verify:

* [ ] The change belongs in `dealerlux-utility`.
* [ ] The correct namespace is used.
* [ ] The filename matches the custom autoloading convention.
* [ ] The class is registered through the initializer.
* [ ] Direct file access is prevented.
* [ ] Input is sanitized and validated.
* [ ] Output is escaped.
* [ ] Nonces and capabilities are checked where required.
* [ ] Hooks and identifiers are prefixed.
* [ ] PHP 7.4 compatibility is preserved.
* [ ] Assets cannot be enqueued twice.
* [ ] The implementation works when invoked multiple times.
* [ ] Configuration is validated.
* [ ] Failures are safe and actionable.
* [ ] Existing public APIs remain compatible.
* [ ] Testing instructions are included.
* [ ] Documentation changes are identified.
* [ ] No credentials or private information are included.

---

## 28. Commit Rules

Use clear conventional commit messages.

Preferred formats:

```text
feat: add virtual page configuration loader
fix: prevent duplicate shortcode asset enqueue
refactor: separate virtual post routing from rendering
docs: document shortcode registration workflow
test: add options registry validation tests
chore: configure WordPress coding standards
```

Each commit should represent one logical change.

Do not combine:

* Refactoring and unrelated new features.
* Formatting an entire repository and fixing one bug.
* Infrastructure changes and client-specific content.
* Multiple unrelated registries in one commit.

---

## 29. Definition of Done

A task is complete only when:

1. The code follows the repository architecture.
2. The feature is registered correctly.
3. Security and compatibility have been considered.
4. Existing public behavior has been preserved or migration is documented.
5. The implementation has been tested or a clear test procedure is provided.
6. Relevant documentation has been updated.
7. The change does not introduce unrelated modifications.
8. The repository remains maintainable for future Dealer Lux utilities.

---

## 30. Permanent Project Directive

Always treat `JasperDealerlux/dealerlux-utility` as the sole primary codebase for this project.

Continuously evaluate its infrastructure for opportunities to improve:

* Reusability.
* Maintainability.
* Security.
* Performance.
* Testability.
* Documentation.
* Deployment reliability.
* WordPress compatibility.
* Developer experience.

Do not merely generate isolated code. Fit every solution into the repository’s established system and improve the overall architecture when doing so is relevant and safe.