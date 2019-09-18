# browscap-site

[![Build Status](https://travis-ci.org/browscap/browscap-site.svg?branch=master)](https://travis-ci.org/browscap/browscap-site) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/browscap/browscap-site/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/browscap/browscap-site/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/browscap/browscap-site/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/browscap/browscap-site/?branch=master)

This is the website for the Browser Capabilities Project.

## Requirements

 - Docker
 - Docker Compose

## Installation

The initial installation process looks like this:

```bash
$ git clone git@github.com:browscap/browscap-site.git
$ cd browscap-site
$ docker-compose build
$ docker-compose run php-server composer install
```

This automatically installs, builds and generates metadata for whichever browscap version is specified in the
`composer.json`, which should give you a fully working local browscap.org copy.

## Running the site

```bash
$ docker-compose up
```

This will run in the foreground, so to exit, Ctrl+C.

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
$ docker-compose run php-server composer install
```

If you're already running the containers, Ctrl+C, then rebuild.

## Completely reset Docker Containers

```bash
$ docker-compose down
$ docker-compose rm -f
$ docker-compose build
```

## Running the CLI scripts in Docker

 * `docker-compose run php-server bin/browscap-site generate-statistics`
 * `docker-compose run php-server bin/browscap-site generate-build`
 * `docker-compose run php-server bin/browscap-site delete-old-download-logs`

## Creating a Browscap Release

Please see [this wiki article](https://github.com/browscap/browscap/wiki/Public-release-procedure).
