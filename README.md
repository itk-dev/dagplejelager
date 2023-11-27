# Dagplejelager

## Installation

Note: Uses a patched version of
[hook_event_dispatcher](https://www.drupal.org/project/hook_event_dispatcher):
<https://git.drupalcode.org/project/hook_event_dispatcher/-/merge_requests/7/diffs?diff_id=59188&start_sha=570948a169d494a0f2e32bf0200644727ed9aff8#diff-content-4a0ca4f66345e5744406f8af848a255f7f6cf1d0>

### Production

Create `.env.docker.local`:

```sh
COMPOSE_PROJECT_NAME=dagplejelager
COMPOSE_SERVER_DOMAIN=dagplejelager.some.domain
```

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

We'll run behind a proxy, so tell Drupal that we actually use `https`:

```php
# settings.local.php
// @see https://www.drupal.org/node/425990
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'][] = $_SERVER['REMOTE_ADDR'];
// See https://symfony.com/doc/current/deployment/proxies.html.
$settings['reverse_proxy_trusted_headers'] = \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_ALL;
```

Install the site:

```sh
./scripts/server/install
```

Update the site:

```sh
git pull
./scripts/server/update
```

Talk to the site:

```sh
./scripts/server/drush
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

#### Docker compose

Create `.env.docker.local`:

```env
COMPOSE_PROJECT_NAME=dagplejelager
COMPOSE_SERVER_DOMAIN=dagplejelager.some.domain
```

```sh
docker compose --env-file .env.docker.local --file docker compose.server.yml up --detach --build
```

### Importing day carer info

Use this command to import day carers from the source database:

```sh
docker compose exec phpfpm vendor/bin/drush --yes dagplejelager_form:day-carers:import
```

This should be run regularly by a `cron` job.

#### Remote database configuration

The database connection details must be defined in `settings.local.php`:

```php
$config['dagplejelager_form']['import']['database_host'] = '…';
$config['dagplejelager_form']['import']['database_name'] = '…;
$config['dagplejelager_form']['import']['database_username'] = '…;
$config['dagplejelager_form']['import']['database_password'] = '…;
```

### Development

We use a [custom Dockerfile](.docker/development/phpfpm/Dockerfile) to install
[Microsoft SQL Server Functions
(PDO_SQLSRV)](https://www.php.net/manual/en/ref.pdo-sqlsrv.php) in the `phpfpm`
container.

Use `docker compose up --detach --build` to (re)build the custom docker image.

```sh
docker compose up --detach --build
docker compose exec phpfpm composer install --no-interaction
docker compose exec phpfpm vendor/bin/drush --yes site:install minimal --existing-config
# Get the site url
echo "http://$(docker compose port nginx 80)"
# Get admin sign in url
docker compose exec phpfpm vendor/bin/drush --yes --uri="http://$(docker compose port nginx 80)" user:login
```

#### Mails

Mails are caught by [MailHog](https://github.com/mailhog/MailHog) and can be
read on the url reported by

```sh
echo "http://$(docker compose port mailhog 8025)"
```

#### Using `symfony` binary

```sh
docker compose up --detach
symfony composer install --no-interaction
symfony php vendor/bin/drush --yes site:install minimal --existing-config
# Start the server
symfony local:server:start --port=8000 --daemon
# Get admin sign in url
symfony php vendor/bin/drush --uri=https://127.0.0.1:8000 user:login
```

## Coding standards

```sh
docker compose exec phpfpm composer coding-standards-check
docker compose exec phpfpm composer coding-standards-apply
```

```sh
docker compose run --rm node yarn --cwd /app install
docker compose run --rm node yarn --cwd /app coding-standards-check
docker compose run --rm node yarn --cwd /app coding-standards-apply
```

### Fixtures

We have fixtures for content types and content entities.

To load all fixtures, run:

```sh
# Enable our fixtures modules
docker compose exec phpfpm vendor/bin/drush --yes pm:enable dagplejelager_fixtures
# Load the fixtures
docker compose exec phpfpm vendor/bin/drush --yes content-fixtures:load
# Uninstall fixtures modules
docker compose exec phpfpm vendor/bin/drush --yes pm:uninstall content_fixtures
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

## Translations

Import translations by running

```sh
(cd web && ../vendor/bin/drush locale:import --type=customized --override=none da ../translations/translations.da.po)
```

Export translations by running

```sh
(cd web && ../vendor/bin/drush locale:export da --types=customized > ../translations/translations.da.po)
```

Open `web/profiles/custom/os2loop/translations/translations.da.po` with the
latest version of [Poedit](https://poedit.net/) to clean up and then save the
file.

See
<https://medium.com/limoengroen/how-to-deploy-drupal-interface-translations-5653294c4af6>
for further details.

## Anonymizing orders

```php
# settings.local.php:
$settings['dagplejelager_commerce']['anonymize']['anonymize_after'] = '28 days';
```

The Drush commmand `dagplejelager_commerce:orders:anonymize` should be run
regularly by a cron job, e.g. daily:

```cron
0 0 * * * docker compose exec phpfpm vendor/bin/drush dagplejelager_commerce:orders:anonymize
```

```sh
docker compose exec phpfpm vendor/bin/drush dagplejelager_commerce:orders:anonymize --help
```

## Test OIDC login

Edit `settings.local.php` and insert

```php
// https://idp-admin.dagplejelager.local.itkdev.dk/.well-known/openid-configuration
$config['openid_connect.client.generic']['settings']['authorization_endpoint'] = 'http://idp-admin.dagplejelager.local.itkdev.dk/connect/authorize';
$config['openid_connect.client.generic']['settings']['token_endpoint'] = 'http://idp-admin.dagplejelager.local.itkdev.dk/connect/token';
// Makes local test easier.
$config['openid_connect.client.generic']['settings']['end_session_endpoint'] = 'http://idp-admin.dagplejelager.local.itkdev.dk/connect/endsession';
$config['openid_connect.client.generic']['settings']['client_id'] = 'client-id';
$config['openid_connect.client.generic']['settings']['client_secret'] = 'client-secret';
```

Update users under `USERS_CONFIGURATION_INLINE` in
`docker-compose.override.yml`. Beware of JSON inside [YAML inside
YAML](https://yaml.org/spec/1.2.2/#812-literal-style)! edit
