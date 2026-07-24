# Dealerlux Utility Project Rules

This ChatGPT Project is intended exclusively for the following GitHub repository:

`https://github.com/JasperDealerlux/dealerlux-utility`

## Primary Project Scope

All development assistance, code generation, debugging, documentation, architectural recommendations, refactoring, and repository analysis must focus on:

`JasperDealerlux/dealerlux-utility`

Do not treat unrelated Dealer Lux repositories, client plugins, themes, websites, or temporary scripts as part of this project unless the user explicitly requests an integration with them.

External systems may be studied as dependencies or integration targets, but all proposed implementation work must clearly identify what belongs inside `dealerlux-utility`.

## Repository as Source of Truth

Before providing substantial code or architectural recommendations:

1. Inspect the relevant files in the GitHub repository.
2. Review the current directory structure and recent implementation patterns.
3. Follow the repository’s existing conventions.
4. Do not assume that previously discussed code still matches the current repository.
5. Prefer repository evidence over generic WordPress assumptions.

When the user refers to “the project,” “the plugin,” “this repository,” or “our infrastructure,” assume they mean:

`JasperDealerlux/dealerlux-utility`

## Project Purpose

The repository is a reusable WordPress utility plugin for Dealer Lux.

Its purpose is to centralize functionality that may otherwise be duplicated across:

* Dealer Lux client websites.
* Local WordPress plugins.
* Themes.
* MU plugins.
* Administrative tools.
* Temporary development utilities.
* Reusable shortcodes.
* Virtual WordPress pages and posts.
* WordPress configuration and option management.

The project should continually move toward reusable, configurable, maintainable, secure, and testable infrastructure.

## Architectural Direction

Preserve and improve the repository’s established architecture, including:

* The `DealerluxUtils` PHP namespace.
* The central plugin bootstrap.
* The custom class autoloader.
* The `Initializer` registration system.
* Classes under `src/`.
* Traits under `src/traits/`.
* Procedural utilities under `utils/`.
* Declarative configuration under `config/`.
* Registry classes.
* Shortcode classes.
* Virtual page and post definitions.
* Gutenberg-compatible content.

Do not introduce an entirely separate architecture unless there is a strong technical reason and the impact is clearly explained.

## Infrastructure Study Rule

Continuously study the repository infrastructure while working on tasks.

Look for opportunities to improve:

* Code organization.
* Class responsibilities.
* Autoloading.
* Initialization.
* Configuration loading.
* Registry design.
* Reusability.
* Extensibility.
* WordPress compatibility.
* Security.
* Performance.
* Testing.
* Logging.
* Error handling.
* Documentation.
* Deployment reliability.
* Developer experience.

Do not force unrelated refactors into a requested task. Mention significant infrastructure concerns separately when they are relevant.

## Repository Placement Rule

Before recommending a new feature, determine whether it belongs in this repository.

A feature generally belongs in `dealerlux-utility` when it is:

* Reusable across multiple Dealer Lux websites.
* A shared WordPress utility.
* A reusable shortcode.
* A centralized registry.
* A virtual page or post system.
* A reusable configuration system.
* A debugging or administrative utility.
* Shared integration infrastructure.
* A feature that reduces repeated code across client projects.

A feature may not belong here when it is:

* Specific to one client website.
* A one-time content update.
* Client-specific branding.
* A temporary CSS adjustment.
* A local theme customization.
* A marketing campaign specific to one dealer.
* Hard-coded behavior for one domain.

When client-specific behavior must be supported, prefer:

* Configuration.
* Filters.
* Actions.
* Feature flags.
* Adapters.
* Extension classes.
* Client-level overrides.

Avoid hard-coding client domains or client-specific content into shared infrastructure.

## PHP and WordPress Standards

All generated PHP must:

* Use the `DealerluxUtils` namespace where applicable.
* Follow the repository’s autoloading filename convention.
* Remain compatible with the project’s declared PHP version.
* Guard executable files against direct access.
* Sanitize and validate external input.
* Escape output at render time.
* Use WordPress APIs where appropriate.
* Use nonces and capability checks for administrative actions.
* Avoid exposing sensitive information.
* Avoid unconditional debugging output.
* Avoid duplicate hook registration.
* Avoid duplicate asset enqueueing.
* Use prefixed hooks, options, transients, shortcodes, handles, and CSS classes.

Do not silently introduce PHP syntax that exceeds the version declared by the plugin.

## Class Creation Rules

When adding a class:

1. Select the correct namespace.
2. Place it in the correct `src/` directory.
3. Follow the class filename convention.
4. Ensure the custom autoloader can locate it.
5. Register it through the established initialization system.
6. Keep the class focused on one clear responsibility.
7. Avoid direct instantiation from unrelated files.
8. Document its public hooks and configuration.

Avoid vague or oversized classes that handle unrelated features.

## Bootstrap Rule

Keep the main bootstrap focused on:

* Plugin metadata.
* Constants.
* Direct-access protection.
* Autoloader registration.
* Loading utility functions.
* Starting the initializer.

Do not place substantial feature logic, rendering logic, configuration parsing, or administrative behavior directly in the bootstrap.

## Configuration Rules

Prefer configuration-driven development for:

* Virtual pages.
* Virtual posts.
* WordPress options.
* Feature flags.
* Reusable content.
* Shortcode definitions.
* Integration settings.
* Client overrides.

Configuration files should:

* Return predictable values or arrays.
* Avoid hidden side effects.
* Avoid registering hooks directly.
* Be validated before use.
* Use a documented schema.
* Keep content definitions separate from routing and rendering.

## Registry Rules

Registry classes should follow a predictable lifecycle:

1. Load configuration.
2. Validate configuration.
3. Normalize entries.
4. Detect duplicates or conflicts.
5. Register required hooks.
6. Resolve requested entries.
7. Apply or render the result.
8. Fail safely when configuration is invalid.

Check for:

* Missing files.
* Invalid schemas.
* Duplicate keys.
* Duplicate slugs.
* Invalid post types.
* Missing content definitions.
* Invalid callback definitions.
* Conflicts with real WordPress content.

## Virtual Content Rules

Virtual pages and posts must be checked for:

* URL conflicts.
* Rewrite behavior.
* Main query behavior.
* Canonical URLs.
* Correct post types and statuses.
* Parent-child relationships.
* Template resolution.
* Page titles.
* Breadcrumb compatibility.
* SEO plugin compatibility.
* Navigation behavior.
* REST API behavior where applicable.
* Conflicts with real WordPress posts.

Virtual content must not unexpectedly replace a real published page with the same slug unless explicitly configured.

## Shortcode Rules

Shortcodes must:

* Return output rather than echo it.
* Validate and sanitize attributes.
* Escape generated output.
* Support safe default values.
* Work when used multiple times on one page.
* Avoid duplicate HTML IDs.
* Avoid duplicate scripts and styles.
* Avoid globally modifying page content.
* Document all supported attributes.
* Separate data collection from presentation.

When a shortcode supports multiple layouts, reuse shared data logic and create separate renderers for each layout.

## Gutenberg Rules

Generated Gutenberg content must:

* Contain valid block comments.
* Keep nested blocks balanced.
* Use valid serialized block attributes.
* Escape dynamic text and URLs.
* Work in both the block editor and frontend.
* Use semantic HTML.
* Maintain a logical heading hierarchy.
* Avoid unnecessary theme-specific dependencies.
* Include accessibility attributes where required.

## Security Rules

Review every implementation for:

* Cross-site scripting.
* Cross-site request forgery.
* SQL injection.
* Unauthorized access.
* Path traversal.
* Arbitrary file inclusion.
* Unsafe redirects.
* Unsafe unserialization.
* Remote code execution.
* REST API misuse.
* Webhook validation.
* Sensitive data exposure.
* Credentials committed to GitHub.

Never place API keys, passwords, tokens, private URLs, or other secrets in repository code.

## Performance Rules

Consider that this plugin may run across many Dealer Lux websites.

Avoid:

* Repeated filesystem scans.
* Repeated configuration parsing.
* Expensive operations on every request.
* Excessive database queries.
* Duplicate option reads.
* Duplicate asset enqueueing.
* Remote API calls during normal page rendering.
* Rewrite flushing on every request.
* Unbounded loops.
* Excessive autoloaded WordPress options.
* Loading features that are not needed for the current request.

Use caching where appropriate and always define a reliable invalidation strategy.

## Testing Rules

For meaningful changes, provide or recommend tests for:

* PHP syntax.
* Autoloading.
* Class initialization.
* Hook registration.
* Configuration validation.
* Registry normalization.
* Shortcode attributes.
* Shortcode output.
* Virtual page resolution.
* Virtual post resolution.
* Duplicate slug behavior.
* Missing files.
* Security-sensitive actions.
* Supported PHP compatibility.

When automated testing is unavailable, provide exact manual testing steps.

## Documentation Rules

Major features should document:

* Purpose.
* File location.
* Registration process.
* Configuration schema.
* Usage.
* Public hooks.
* Failure behavior.
* Security considerations.
* Testing procedure.
* Extension points.

Documentation should explain how and why the system works, not merely repeat the code.

## Backward Compatibility

Treat the following as stable public interfaces unless the user explicitly approves a breaking change:

* Shortcode names.
* Shortcode attributes.
* Public functions.
* Public class names.
* Hook names.
* Configuration keys.
* Option names.
* Virtual URLs.
* Page and post slugs.

When a breaking change is necessary:

1. Explain why.
2. Identify affected code.
3. Provide a migration path.
4. Add deprecation support where practical.
5. Update documentation.

## GitHub Work Rules

When using GitHub:

* Confirm the repository is `JasperDealerlux/dealerlux-utility`.
* Inspect files before recommending substantial changes.
* Review recent commits when repository state matters.
* Name all files that should be created or modified.
* Do not modify unrelated files.
* Use focused conventional commits.
* Prefer a branch and pull request for significant changes.
* Do not push, merge, delete, or rewrite repository content unless the user explicitly requests it.

Preferred commit formats:

```text
feat: add reusable configuration loader
fix: prevent duplicate shortcode assets
refactor: separate registry loading from rendering
docs: document virtual page registration
test: add shortcode output tests
chore: configure WordPress coding standards
```

## Response Requirements

For implementation requests, provide:

1. A brief explanation of the current repository behavior.
2. The proposed solution.
3. The files to create or modify.
4. Complete code when complete code is requested.
5. How the implementation fits the architecture.
6. Security and compatibility considerations.
7. Testing instructions.
8. Any documentation updates.
9. Any relevant infrastructure improvement discovered.

Do not provide generic code that ignores the repository’s current structure.

## Permanent Directive

Always treat `JasperDealerlux/dealerlux-utility` as the sole primary repository for this ChatGPT Project.

The goal is not only to complete isolated development tasks, but also to understand and steadily improve the repository’s overall infrastructure while preserving stability, compatibility, security, and maintainability.
