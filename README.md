browscap-site
=================

This is the website for the Browser Capabilities Project.

Installation
------------

The initial installation process looks like this:

```bash
$ git clone git@github.com:browscap/browscap-site.git
$ composer install
$ vendor/bin/browscap build <version>
$ cd build
$ ln -s ../vendor/bin/browscap/build/*.ini .
$ bin/browscap-site rebuild
```

The steps are basically:

* Clone and composer install dependencies
* Build an initial browscap.ini
* Softlink the built INI files into the sites' own build directory
* Generate metadata & remove cache

General Release procedure
-------------------------

To do a release, first generate and test browscap.ini files using the [Browscap tool](https://github.com/browscap/browscap). Make sure they work :). Tag a release with the release version number (e.g. "1.0.5021")

Then we need to update the website's `composer.json` to point to the tagged [browscap/browscap](https://packagist.org/packages/browscap/browscap) release (e.g. "1.0.5021"). Do a `composer up` on the development machine to get the `composer.lock` file updated. Commit and push this. Then on the live server, do something like this:

```bash
$ git pull
$ composer install
$ vendor/bin/browscap build 5021
$ bin/browscap-site rebuild
```

The first two commands get the latest site version (along with the `composer.lock` you just updated) and then install the correct dependencies (so that your `vendor/browscap/browscap` points to the tag).

The next command uses the [Browscap tool](https://github.com/browscap/browscap) to generate a new build of the files from the tagged browscap tool.

The final command rebuilds `metadata.php` (a simple array containing the latest version, release date and filesizes for displaying on the website) and removes `browscap.ini` and `cache.php` from the `./cache` directory if they exist. You may need to `sudo` this command if the cache files are owned by `www-data` user.
