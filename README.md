# Finalist Drupal base project
This is a base project we use for starting new Drupal projects.

This project contains a base set of modules, tests and a install profile.

## Installation
```shell
mkdir my-drupal-project
cd my-drupal-project
ddev config --project-type=drupal10 --docroot=web --create-docroot
```
Create your local DDEV config as described below and continue with the commands below.
```shell
ddev start
ddev composer create finalist/base-project
ddev drush site:install
ddev get ddev/ddev-drupal9-solr
ddev restart
```

## After install checklist
* If you want you can copy the file `examples/example.settings.local.php` to `web/sites.default/settings.local.php` or copy some of the contents into the `settings.ddev.php` file. This will enable the dev config split, disable some caching, change the solr endpoint, etc.
* We have added some custom ddev commands that make your life easier. You 
  need to copy the files from `ddev-commands` to the `.ddev/commands/web folder`
* Add the config_sync_directory value to the settings.php so that config is exported to the correct location: `$settings['config_sync_directory'] = '../config/sync';`

## Local DDEV config
Because of Packeton and local ssh keys we need to have some ddev hooks configured locally. You can do this by 
creating a file in the project specific `.ddev` folder called `config.local.yaml`.

This file need to contain the following code:
```yaml
hooks:
  post-start:
    - exec: "composer --global config repositories.finalist composer https://packeton.finalist.nl/"
    - exec: "composer config --global --auth http-basic.packeton.finalist.nl <username> <token>"
    - exec-host: "ddev auth ssh"
```
For the values of the username and token you can take a look at your [Packeton profile](https://packeton.finalist.nl/profile/).