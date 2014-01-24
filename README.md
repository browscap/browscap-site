browscap-site
=================

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

Manually building
-----------------

Normally you would not need to "manually" build the browscap.ini as the `composer install` hook takes care of that for you. However, should you wish to manually build a specific build number, just run the following:

```bash
$ vendor/bin/browscap build --output="./build" 5021-yyy
$ bin/browscap-site rebuild
```

The steps are basically:

* Build an initial browscap.ini (using `vendor/bin/browscap build`) - this builds the INI files themselves for the specified version.
* Generate metadata & remove cache (using `bin/browscap-site rebuild`) - this generates the metadata.php file which browscap-site uses to offer the correct download links and version.

Alternatively, you could try running the `autobuild` command:

```bash
$ bin/browscap-site autobuild 5021-yyy
```
