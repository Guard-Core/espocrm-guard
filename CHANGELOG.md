# Changelog

## 1.0.0

- First release: the opt-in Guard Core API middleware as an EspoCRM extension, packaged from espocrm/espocrm#3836.
- Config keys `guardEnabled` and `guard`, hidden from the Settings API through the module's `app/config.json` (level `system`).
- Default policy: passive mode, attack screening, ban rules on repeated 401, rate limits on lead capture and password routes.
- Uninstall removes `guardEnabled` and `guard` from the config files.
