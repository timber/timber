# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 2.x     | :white_check_mark: |
| 1.x     | :x:                |

Only the 2.x release line receives security fixes. Timber 1.x has reached its end of life and will not receive any further security updates. If you are still running Timber 1.x, please [upgrade to Timber 2.x](https://timber.github.io/docs/v2/upgrade-guides/2.0/).

Reports are assessed against the PHP versions Timber 2.x supports (`^8.2`) and a WordPress version that still receives updates.

## Reporting a Vulnerability
If you discover a security vulnerability within Timber, please submit your report via the link below. Please be mindful of the fact that the maintainers are working on Timber in their free time, so the initial response can take some time.

### Disclosure Policy
Please do not discuss any vulnerabilities (even resolved ones) without express consent.

### Scope

Timber is a library. Most of the code that ends up on a live site is written by the theme developer, so it matters where the bug lives.

A report is in scope when the problem is in Timber. Some examples:

- Timber itself passes attacker-controlled data into a dangerous operation, and the theme did nothing unusual to make that happen.
- One of Timber's own output helpers or escapers fails to escape what it says it escapes.
- A Timber default, filter, or documented usage pattern is unsafe in a way the documentation does not describe.
- Data from a request reaches a file read, a file include, a query, or template compilation through Timber's own code path.

A report is out of scope when the vulnerable code is the theme's. Some examples:

- Passing untrusted input to an API whose documented job is to take a template name, template source, or a file path. That includes `Timber::compile()`, `Timber::render()`, `Timber::compile_string()`, `Timber::render_string()` and `Timber::get_sidebar()`. These behave like `include` and `eval`. Handing them user input is a bug in the calling code.
- Anything that requires the attacker to author a Twig or PHP template. In WordPress, whoever can write template files already has PHP execution, so this grants no new privilege.
- Output not being escaped in a theme. Timber does not enable Twig's `autoescape` by default, for the same reason WordPress does not escape `the_content()`. This is deliberate and is documented in the [escaping guide](https://timber.github.io/docs/v2/guides/escaping/), which also shows how to turn it on.
- Findings that only work on a configuration that PHP or WordPress no longer ships by default, for example `allow_url_include=On`, or a libxml older than 2.9.
- Vulnerabilities in WordPress core, in another plugin, or in a dependency, that you happened to reach through Timber. Please report those to the relevant project.

If you are not sure which side of the line your finding falls on, send it anyway and say what you are unsure about. We would rather triage a borderline report than miss a real one.

### Submit your report
When you've found a security issue that abides by the rules and scope of this project, please submit the report to us via [GitHub](https://github.com/timber/timber/security/advisories/new).

To let us triage it quickly, please include:

- The path from attacker-controlled input to the vulnerable code, using an unmodified Timber and a theme that follows the documented usage. If you had to write the vulnerable line yourself to reproduce it, please say so.
- The PHP version, the WordPress version, and the version of any relevant extension (libxml, for instance).
- Any non-default configuration your finding depends on.
- A severity that reflects Timber as we ship it, rather than the worst integration you can imagine. We will discuss the score with you, and a well argued low severity report is more useful to us than an inflated one.

### After your submission
We will make a best effort to meet the following response targets for security reports:

- Time to first response (from report submit) - 7 business days
- Time to triage (from report submit) - 15 business days
- Time to fix (from triage) - 30 business days

If we close a report as out of scope, we will say which part of the scope section applies and why. You are welcome to push back if you think we got it wrong.
