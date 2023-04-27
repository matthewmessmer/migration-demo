
# Migration Demo

Drupal 9 migration demo.

Comes with preconfigured Lando dev environment.

## System requirements:
* [Docker for Mac](https://docs.docker.com/desktop/mac/install/) or [Docker for Windows](https://docs.docker.com/desktop/windows/install/) (Linux doesn’t requires this software)
* [Lando](https://docs.lando.dev/getting-started/installation.html)

## Setup Local Environment
* Clone the repository
```
git clone git@github.com:matthewmessmer/migration-demo.git && cd migration-demo
```
* Run the following commands to install the site:
```
composer install --ignore-platform-reqs
lando start
lando drush si --existing-config
```
* If you are on Linux and have issues starting Lando, try adding `export LANDO_SSH_AUTH_SOCK="${SSH_AUTH_SOCK}"` at the end of your ~/.bashrc

## Access the site
* `https://migration-demo.lndo.site` Drupal site
* `http://migration-api.lndo.site` Mock API powered by `https://github.com/typicode/json-server`

## Running Migrations

* List migrations
```
lando drush ms
```

* Run migration import
```
lando drush mim *migration_name*
lando drush mim *migration_name* --update
lando drush mim *migration_name* --limit=10
lando drush mim *migration_name* --idlist=123
lando drush mim --group=*group_name*
```

* Rollback migration
```
lando drush mr *migration_name*
lando drush mr --group=*group_name*
```

* Reset migrations status to idle
```
lando drush mrs *migration_name*
```
