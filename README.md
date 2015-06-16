# Asparagus

[![Build Status](https://secure.travis-ci.org/Benestar/asparagus.png?branch=master)](http://travis-ci.org/Benestar/asparagus)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Benestar/asparagus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Benestar/asparagus/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Benestar/asparagus/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Benestar/asparagus/?branch=master)
[![Dependency Status](https://www.versioneye.com/php/wikibase:data-model/dev-master/badge.svg)](https://www.versioneye.com/php/wikibase:data-model/dev-master)
[![Download count](https://poser.pugx.org/benestar/asparagus/d/total.png)](https://packagist.org/packages/benestar/asparagus)
[![License](https://poser.pugx.org/benestar/asparagus/license.svg)](https://packagist.org/packages/benestar/asparagus)

[![Latest Stable Version](https://poser.pugx.org/benestar/asparagus/version.png)](https://packagist.org/packages/benestar/asparagus)
[![Latest Unstable Version](https://poser.pugx.org/benestar/asparagus/v/unstable.svg)](//packagist.org/packages/benestar/asparagus)

**Asparagus** is a SPARQL abstraction layer for PHP. It's design is inspired
by the DBAL query builder.

## Installation

You can use [Composer](http://getcomposer.org/) to download and install
this package as well as its dependencies. Alternatively you can simply clone
the git repository and take care of loading yourself.

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `benestar/asparagus` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
Asparagus 0.1:

```js
{
    "require": {
        "benestar/asparagus": "~0.1"
    }
}
```

### Manual

Get the Asparagus code, either via git, or some other means. Also get all dependencies.
You can find a list of the dependencies in the "require" section of the composer.json file.
The "autoload" section of this file specifies how to load the resources provide by this library.

## Tests

This library comes with a set up PHPUnit tests that cover all non-trivial code. You can run these
tests using the PHPUnit configuration file found in the root directory. The tests can also be run
via TravisCI, as a TravisCI configuration file is also provided in the root directory.

## License

Asparagus is licensed under the GNU General Public License Version 2. A copy of the license can be found in the [LICENSE file](LICENSE).
