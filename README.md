# Asparagus

[![Build Status](https://secure.travis-ci.org/Benestar/asparagus.png?branch=master)]
(http://travis-ci.org/Benestar/asparagus)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Benestar/asparagus/badges/quality-score.png?b=master)]
(https://scrutinizer-ci.com/g/Benestar/asparagus/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Benestar/asparagus/badges/coverage.png?b=master)]
(https://scrutinizer-ci.com/g/Benestar/asparagus/?branch=master)
[![Download count](https://poser.pugx.org/benestar/asparagus/d/total.png)]
(https://packagist.org/packages/benestar/asparagus)
[![License](https://poser.pugx.org/benestar/asparagus/license.svg)]
(https://packagist.org/packages/benestar/asparagus)

[![Latest Stable Version](https://poser.pugx.org/benestar/asparagus/version.png)]
(https://packagist.org/packages/benestar/asparagus)
[![Latest Unstable Version](https://poser.pugx.org/benestar/asparagus/v/unstable.svg)]
(//packagist.org/packages/benestar/asparagus)

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
Asparagus 0.3:

```js
{
    "require": {
        "benestar/asparagus": "~0.4"
    }
}
```

### Manual

Get the Asparagus code, either via git, or some other means. Also get all dependencies.
You can find a list of the dependencies in the "require" section of the composer.json file.
The "autoload" section of this file specifies how to load the resources provide by this library.

## Usage

Most of the methods in `QueryBuilder` return the builder instance so you can build a query
by calling the methods one by one. Currently, the `QueryBuilder` supports to manage prefixes,
select variables, add basic triple conditions and group them by subject and predicate, and
full support for all query modifiers SPARQL provides.

The `QueryBuilder` instance can be passed to a `QueryExecuter` or the SPARQL can be obtained
as is using `getSPARQL` or formatted using `format`.

### Basic functionality

In the following example, a simple SPARQL query is generated asking for all persons who
have a name and an email address stored in the database.

```php
use Asparagus\QueryBuilder;

$prefixes = array(
	'test' => 'http://www.example.com/test#'
);

$queryBuilder = new QueryBuilder( $prefixes );
$queryBuilder->select( '?name', '?email' )
	->where( '?person', 'test:name', '?name' )
	->also( 'test:email', '?email' )
	->limit( 10 );

echo $queryBuilder->format();
```

The generated query looks like:

```sparql
PREFIX test: <http://www.example.com/test#>

SELECT ?name ?email WHERE {
	?person test:name ?name ;
		test:email ?email .
}
LIMIT 10
```

### Optionals and filters

The following snippet creates a more complex query using optional values and filters. Only persons
who do not have their email address deposited in the database are shown.

```php
use Asparagus\QueryBuilder;

$prefixes = array(
	'test' => 'http://www.example.com/test#'
);

$queryBuilder = new QueryBuilder( $prefixes );
$queryBuilder->select( '?name' )
	->where( '?person', 'test:name', '?name' )
	->optional( '?person', 'test:email', '?email' )
	->filter( '!BOUND (?email)' );

echo $queryBuilder->format();
```

The generated query looks like:

```sparql
PREFIX test: <http://www.example.com/test#>

SELECT ?name WHERE {
	?person test:name ?name .
	OPTIONAL {
		?person test:email ?email .
	}
	FILTER (!BOUND (?email))
}
```

### Unions

More complex queries can be built by using subgraphs or subqueries. To create a new subgrapho or
subquery, you can call `QueryBuilder::newSubgraph` or `QueryBuilder::newSubquery`. `GraphBuilder`
supports all graph functions also supported by `QueryBuilder` and it will return itself as well.
If alternative conditions should be matched, you can use `QueryBuilder::union` to specify several
graph patterns which are all allowed.

The next query returns titles and authors of books recorded using Dublin Core properties from
version 1.0 or version 1.1.

```php
use Asparagus\QueryBuilder;

$prefixes = array(
	'dc10' => 'http://purl.org/dc/elements/1.0/',
	'dc11' => 'http://purl.org/dc/elements/1.1/'
);

$queryBuilder = new QueryBuilder( $prefixes );

$queryBuilder->select( '?title', '?author' )
	->union(
		$queryBuilder->newSubgraph()
			->where( '?book', 'dc10:title', '?title' )
			->also( 'dc10:creator', '?author' ),
		$queryBuilder->newSubgraph()
			->where( '?book', 'dc11:title', '?title' )
			->also( 'dc11:creator', '?author' )
	);

echo $queryBuilder->format();
```

The generated query looks like:

```sparql
PREFIX dc10: <http://purl.org/dc/elements/1.0/>
PREFIX dc11: <http://purl.org/dc/elements/1.1/>

SELECT ?title ?author WHERE {
	{
		?book dc10:title ?title ;
			dc10:creator ?author .
	} UNION {
		?book dc11:title ?title ;
			dc11:creator ?author .
	}
}
```

## Tests

This library comes with a set up PHPUnit tests that cover all non-trivial code. You can run these
tests using the PHPUnit configuration file found in the root directory. The tests can also be run
via TravisCI, as a TravisCI configuration file is also provided in the root directory.

## Release notes

### 0.4.2 (2016-01-21)

* Fixed incompatible changes in `QueryExecuter`

### 0.4.1 (2016-01-21)

* Fixed return value of `QueryExecuter::execute`

### 0.4 (2015-10-01)

* Added `QueryBuilder::describe`
* `QueryBuilder::select`, `QueryBuilder::selectDistinct` and `QueryBuilder::selectReduced` now throw `RuntimeException`

### 0.3.1 (2015-09-06)

* Added support for native values in selects

### 0.3 (2015-06-22)

* Renamed previously package-private `QueryConditionBuilder` to `GraphBuilder`
* Removed `QueryBuilder::hasSubquery`
* Added `QueryBuilder::getSelects`
* Added `QueryBuilder::selectDistinct` and `QueryBuilder::selectReduced`
* Added `QueryBuilder::optional`
* Added `QueryBuilder::filter`, `QueryBuilder::filterExists` and `QueryBuilder::filterNotExists`
* Added `QueryBuilder::union`
* Added `QueryBuilder::newSubgraph`
* `QueryBuilder::select` and `QueryBuilder::groupBy` now require functions to be wrapped by brackets
* `QueryBuilder::groupBy` now accepts multiple arguments
* `QueryBuilder::where` and `QueryBuilder::also` now support property paths in predicates

### 0.2.1 (2015-06-19)

* Fixed README.md to use prefixed variables in `QueryBuilder::select`

### 0.2 (2015-06-18)

* Renamed `QueryBuilder::plus` to `QueryBuilder::also`
* `QueryBuilder::select`, `QueryBuilder::groupBy` and `QueryBuilder::orderBy` now expect prefixed
  variables instead of just the variable name
* Removed `QueryBuilder::prefix` as prefixes should be defined in the constructor
* Added more validation for variables and prefixes. `QueryBuilder::getSPARQL` will throw a
  `RangeException` if the validation fails.
  * Selected variables that don't occur in the conditions are detected
  * Prefixes which haven't been declared are detected
  * Variable names and IRIs now have to mach the correct format
  * A list of supported functions has been added and a check to find bracket mismatches

### 0.1 (2015-06-17)

Initial release with these features:

* A `QueryBuilder` with basic functionality to generate SPARQL queries
* A `QueryFormatter` to make SPARQL queries human-readable
* A `QueryExecuter` which sends queries to a SPARQL endpoint and parses the result

## License

Asparagus is licensed under the GNU General Public License Version 2. A copy of the license can be
found in the [LICENSE file](LICENSE).
