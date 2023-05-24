# Dagplejelager commerce

## Anonymize orders

```sh
// local.settings.php
// Anonymize completed orders after 30 days.
$settings['dagplejelager_commerce']['anonymize']['anonymize_after'] = '30 days';
```

```sh
vendor/bin/drush dagplejelager_commerce:orders:anonymize --help
```
