# Security Policy for Guard Core for EspoCRM

## Supported Versions

I provide security updates for the following versions of Guard Core for EspoCRM:

| Version | Supported |
| ------- | --------- |
| 1.x | :white_check_mark: bug and security fixes |

There is no older major version.

## Reporting a Vulnerability

I take the security of Guard Core for EspoCRM seriously. If you believe you've found a security vulnerability, please follow these steps:

1. **Do not disclose the vulnerability publicly** until I have fixed it.
2. **Report the vulnerability through GitHub's security advisory feature**:
   - Go to the [Security tab](https://github.com/Guard-Core/espocrm-guard/security/advisories) of the Guard Core for EspoCRM repository
   - Click on "New draft security advisory"
   - Fill in the details of the vulnerability
   - Submit the advisory

   Alternatively, you can report vulnerabilities through [GitHub's private vulnerability reporting feature](https://github.com/Guard-Core/espocrm-guard/security/advisories/new).

3. Include the following information in your report:
   - A description of the vulnerability and its potential impact
   - Steps to reproduce the issue
   - Affected versions
   - Any potential mitigations or workarounds

I will acknowledge your report within 48 hours and send a detailed response within 7 days, including the next steps in handling the vulnerability.

## Security Considerations

Guard Core for EspoCRM is a security extension: report anything that weakens detection, bypasses rate limiting or bans, leaks secrets into logs or responses, or trusts unvalidated input.

The `guardCore` config key can hold a Redis URL and a telemetry agent API key. The extension declares `guardCore` (and the deprecated `guardEnabled` and `guard`) at level `system` so EspoCRM does not return them from `GET /api/v1/Settings`, for any user. Uninstalling the extension deletes the keys from the config files for the same reason: once the extension is gone, nothing would hide them. A report that shows `guardCore`, `guardEnabled` or `guard` reaching any API response or the front end is a vulnerability.
