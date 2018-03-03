browscap-site
=================

[![Build Status](https://travis-ci.org/browscap/browscap-site.svg?branch=master)](https://travis-ci.org/browscap/browscap-site) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/browscap/browscap-site/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/browscap/browscap-site/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/browscap/browscap-site/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/browscap/browscap-site/?branch=master)

This is the website for the Browser Capabilities Project.

Installation
------------

The initial installation process looks like this:

```bash
$ git clone git@github.com:browscap/browscap-site.git
$ composer install
```

This automatically installs, builds and generates metadata for whichever browscap version is specified in the composer.json, which should give you a fully working local browscap.org copy.

Updating to Latest
------------------

Updating to the latest browscap-site and browscap should be as simple as:

```bash
$ git pull
$ composer install
```

Creating a Browscap Release
---------------------------

Please see [this wiki article](https://github.com/browscap/browscap/wiki/Public-release-procedure).
