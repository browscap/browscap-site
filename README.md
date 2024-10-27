# browscap-site

[![Continuous Integration](https://github.com/browscap/browscap-site/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/browscap/browscap-site/actions/workflows/continuous-integration.yml) [![Heroku](https://pyheroku-badge.herokuapp.com/?app=browscap&path=/&style=flat)](https://browscap.org/)

This is the website for the Browser Capabilities Project at [browscap.org](https://browscap.org).

## Requirements

 - Docker
 - Docker Compose 2+ with buildx

## Installation

The initial installation process looks like this:

```bash
$ git clone git@github.com:browscap/browscap-site.git
$ cd browscap-site
$ make build
```

This automatically installs, builds and generates metadata for whichever browscap version is specified in the
`composer.json`, which should give you a fully working local browscap.org copy.

## Running the site

```bash
$ make run
```

This will run in the background, so to exit, `docker compose down`.

When it's running, you can visit http://localhost:8080/ to view the site.

A tool called "Adminer" is available on http://localhost:8081/ with which you can inspect the database. The login
credentials for the development environment are:

 * Username: `root`
 * Password: `password`
 * Database: `browscap`

## Updating to Latest

Updating to the latest browscap-site and browscap should be as simple as:

```bash
$ git pull
$ make build
```

## Composer commands

For `composer update`, `composer require` etc., it is recommended to do so inside the container:

```bash
$ docker compose run --rm --no-deps php-server composer update
```

Then rebuild from scratch:

```bash
$ make build
```

## Running the CLI scripts in Docker

 * `make generate-statistics`
 * `make delete-old-download-logs`
 * `make test` or pass additional options with `make test OPTS="--testdox"`
 * `make cs` or pass additional options with `make cs OPTS="-s"`
 * `make static-analysis` or pass additional options with `make static-analysis OPTS="--show-info=true"`

## Creating a Browscap Release

Please see [this wiki article](https://github.com/browscap/browscap/wiki/Public-release-procedure).
