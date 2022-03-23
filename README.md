# browscap-site

[![Build Status](https://travis-ci.org/browscap/browscap-site.svg?branch=master)](https://travis-ci.org/browscap/browscap-site) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/browscap/browscap-site/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/browscap/browscap-site/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/browscap/browscap-site/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/browscap/browscap-site/?branch=master)

This is the website for the Browser Capabilities Project.

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

 * `docker compose run --rm php-server bin/browscap-site generate-statistics`
 * `docker-compose run --rm php-server bin/browscap-site delete-old-download-logs`

## Creating a Browscap Release

Please see [this wiki article](https://github.com/browscap/browscap/wiki/Public-release-procedure).
