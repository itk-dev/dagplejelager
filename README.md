# Dagplejelager

## Installation

### Production

Create local settings file with database connection:

```sh
cat <<'EOF' > web/sites/default/settings.local.php
<?php
$databases['default']['default'] = [
 'database' => getenv('DATABASE_DATABASE') ?: 'db',
 'username' => getenv('DATABASE_USERNAME') ?: 'db',
 'password' => getenv('DATABASE_PASSWORD') ?: 'db',
 'host' => getenv('DATABASE_HOST') ?: 'mariadb',
 'port' => getenv('DATABASE_PORT') ?: '',
 'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
 'prefix' => '',
];
EOF
```

```sh
composer install --no-dev --optimize-autoloader
vendor/bin/drush --yes site:install minimal --existing-config
vendor/bin/drush --yes locale:update
```

#### Configuration

##### OpenID Connect

Edit `settings.local.php` to configure the OpenID Connect client:

```php
// Get these values from your OpenID Connect discovery document.
$config['openid_connect.client.generic']['settings']['authorization_endpoint'] = '';
$config['openid_connect.client.generic']['settings']['token_endpoint'] = '';
$config['openid_connect.client.generic']['settings']['client_id'] = '';
$config['openid_connect.client.generic']['settings']['client_secret'] = '';
```

AD groups to Drupal roles mapping:

```php
$config['openid_connect.settings']['role_mappings']['Drupal role] = ['AD group'];
```

### Development

```sh
docker-compose up --detach
docker-compose exec phpfpm composer install
docker-compose exec phpfpm vendor/bin/drush --yes site:install minimal --existing-config
# Get the site url
echo "http://$(docker-compose port nginx 80)"
# Get admin sign in url
docker-compose exec phpfpm vendor/bin/drush --yes --uri="http://$(docker-compose port nginx 80)" user:login
```

#### Mails

Mails are caught by [MailHog](https://github.com/mailhog/MailHog) and can be
read on the url reported by

```sh
echo "http://$(docker-compose port mailhog 8025)"
```

#### Using `symfony` binary

```sh
docker-compose up --detach
symfony composer install
symfony php vendor/bin/drush --yes site:install minimal --existing-config
# Start the server
symfony local:server:start --port=8000 --daemon
# Get admin sign in url
symfony php vendor/bin/drush --uri=https://127.0.0.1:8000 user:login
```

## Coding standards

```sh
docker-compose exec phpfpm composer coding-standards-check
docker-compose exec phpfpm composer coding-standards-apply
```

```sh
docker-compose run node yarn --cwd /app install
docker-compose run node yarn --cwd /app coding-standards-check
docker-compose run node yarn --cwd /app coding-standards-apply
```

### Fixtures

We have fixtures for content types and content entities.

To load all fixtures, run:

```sh
# Find and enable all fixtures modules
vendor/bin/drush --yes pm:enable dagplejelager_fixtures
# Load the fixtures
vendor/bin/drush --yes content-fixtures:load
# Uninstall all fixtures modules
vendor/bin/drush --yes pm:uninstall content_fixtures
```

### GitHub Actions

We use [GitHub Actions](https://github.com/features/actions) to check coding
standards whenever a pull request is made.

Before making a pull request you can run the GitHub Actions locally to check for
any problems:

[Install `act`](https://github.com/nektos/act#installation) and run

```sh
act -P ubuntu-latest=shivammathur/node:focal pull_request
```

(cf. <https://github.com/shivammathur/setup-php#local-testing-setup>).

### Twigcs

To run only twigcs:

```sh
composer coding-standards-check/twigcs
```

But this is also a part of

```sh
composer coding-standards-check
```

## Build assets

```sh
docker run --volume ${PWD}:/app --workdir /app node:latest yarn install
docker run --volume ${PWD}:/app --workdir /app node:latest yarn encore dev
 ```
