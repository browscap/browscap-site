browscap-site
=================

This is the website for the Browser Capabilities Project.

Installation
------------

The installation process looks like this:

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

```bash
$ vendor/bin/browscap build <version>
$ bin/browscap-site rebuild
```

The first command uses the [Browscap tool](https://github.com/browscap/browscap) to generate a new build of the files.

The second command rebuilds `metadata.php` (a simple array containing the latest version, release date and filesizes for displaying on the website) and removes `browscap.ini` and `cache.php` from the `./cache` directory.
