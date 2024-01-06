<!-- markdownlint-disable MD024 -->
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- Prepare for drupal 10 upgrade
- Upgrade custom module info files
- Disable Bartik
- Set update_default_config_langcodes to false (Config language will not follow
  site default language)
- Fixed issue with profile (address) editing and autocompletion

## [1.2.0] - 2023-11-27

- [DAGPLEJ-102](https://github.com/itk-dev/dagplejelager/pull/59)
  Added fixtures
- Added anonymization of completed orders
  (<https://github.com/itk-dev/dagplejelager/pull/50>)
- [PR-57](https://github.com/itk-dev/dagplejelager/pull/57)
  Updated docker compose setup
- [PR-56](https://github.com/itk-dev/dagplejelager/pull/56)
  Added OIDC IdP service

## [1.0.0]

- [SUPP0RT-943](https://jira.itkdev.dk/browse/SUPP0RT-943)
  Updated facets module
- [SUPP0RT-943](https://jira.itkdev.dk/browse/SUPP0RT-943)
  Upgraded to PHP 8.1
- [DAGPLEJ-62](https://jira.itkdev.dk/browse/DAGPLEJ-62)
  Added day carer name and address lookup.
- [DAGPLEJ-20](https://jira.itkdev.dk/browse/DAGPLEJ-20):
  Added OpenID Connect module and configuration.
- [DAGPLEJ-21](https://jira.itkdev.dk/browse/DAGPLEJ-21):
  Added Drupal Core, base config and Drupal Commerce.
- [DAGPLEJ-30](https://jira.itkdev.dk/browse/DAGPLEJ-30):
  - Added eStore theme, and default product entity.
  - Added front page view
  - Added product category

[Unreleased]: https://github.com/itk-dev/dagplejelager/compare/1.0.0...HEAD
[1.0.0]: https://github.com/itk-dev/dagplejelager/releases/tag/1.0.0
