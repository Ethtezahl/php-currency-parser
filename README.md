PHP Currency Parser
=========

[![Build Status](https://img.shields.io/travis/nyamsprod/php-currency-parser/master.svg?style=flat-square)](https://travis-ci.org/nyamsprod/php-currency-parser)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/github/release/nyamsprod/php-currency-parser.svg?style=flat-square)](https://github.com/nyamsprod/php-currency-parser/releases)

**PHP Currency Parser** enable integrating and manipulating the [ISO 4217 Code List](http://www.currency-iso.org/dam/downloads/lists/) in PHP codebase.

This work is **heavily** inspired by

- [payum/iso4217](https://github.com/payum/iso4217)
- [alcohol/iso4217](https://github.com/alcohol/iso4217)
- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser)

What is ISO 4217 ?
-------

> ISO 4217 is a standard published by the International Organization for Standardization, which delineates currency designators, country codes (alpha and numeric), and references to minor units in three tables.
>
> *-- [Wikipedia](http://en.wikipedia.org/wiki/ISO_4217)*

Highlights
-------

* Simple API
* Self Update Currencies Data
* Fully documented
* Fully unit tested
* Framework-agnostic
* Composer ready, [PSR-2] and [PSR-4] compliant

System Requirements
-------

You need **PHP >= 5.5.9** but the latest stable version of PHP/HHVM is recommended.

Install
-------

The library is available on [Packagist]() and should be installed using [Composer](https://getcomposer.org/). This can be done by running the following command on a composer installed box:

```bash
$ composer require nyamsprod/php-currency-parser
```

Usage
------

Retrieving The public currency list.

``` php
<?php

use Pcp\PublicListManager;

$manager = new PublicListManager();
$collection = $manager->getList(); //$collection is a Pcp\Collection object

$euro = $collection['EUR'];
// or
$euro = $collection->getByNumCode('978');

//in both cases a Pcp\Currency object representing the EURO currency is returned

$euro->getName();              // Euro
$euro->getAlphaCode();         // EUR
$euro->getNumCode();           // 978
$euro->getMinorUnitExponent(); // 2
$euro->isFund();               // returns false
$euro->getCountries();         // [..., 'BE' => 'BELGIUM', ..., 'FR' => 'FRANCE', ....]
$euro->isLegalIn('FR');        // returns true
$euro->isLegalIn('US');        // returns false
echo $euro;                    // displays 'EUR'
```

Documentation
------

### The Public List Manager

Obtaining, writing and caching the currency list is done using the `Pcp\PublicListManager` object.

#### Getting the currencies data

The `Pcp\PublicListManager` constructor can takes 2 optional arguments:

- a directory path if you prefer another location to write and cache the currency listing instead of the library `data` directory.
- a `GuzzleHttp\Client` object to suite your own requirements

``` php
<?php

use Pcp\PublicListManager;

$cacheDir = '/another/path';

$manager = new PublicListManager($cacheDir);
$collection = $manager->getList();
```

The `PublicListManager::getList` method will instantiate and return a `Pcp\Collection` object from the cached files data. If they do not exist, the object will try to fetch and recreate the data into the specified directory before `Pcp\Collection` instantiation.

#### Updating the Currency list

If you need to force the currency data update. You can call the `PublicListManager::refreshList` method.

``` php
<?php

use Pcp\PublicListManager;

$manager = new PublicListManager();
$manager->refreshList();
$collection = $manager->getList();
```

#### Refreshing the currency data

While a cached PHP copy of the currency list is provided for you in the
`data` directory, that copy may or may not be up to date. Please use the provided vendor binary to
refresh your cached copy of the currency list.

From the root of your project, simply call:

``` bash
$ ./vendor/bin/update-currency-list
```

You may verify the update by checking the timestamp on the files located in the
`data` directory.

**Important**:

- The vendor binary `update-currency-list` depends on an internet connection to
update the cached Currency List.
- If the country code is "Unknown or Invalid Territory" the `ZZ` ISO 3316-1-ALPHA-2 code is used

### Manipulating currencies

The `Pcp\Collection` class ease manipulating a collection of `Pcp\Currency` objects.

To instantiate a new `Pcp\Collection` object you need to call its constructor:

``` php
<?php

use Pcp\Collection;

$currencies = new Collection(); //An empty collection is created

$currencies = new Collection($data); // Where $data is an array or an object
                                     // usable by foreach
                                     // containing only Pcp\Currency objects.
```

The `Pcp\Collection` class extends PHP's `ArrayObject` class. Currencies are accessible as `Pcp\Currency` objects by providing their alphabetic code to the `ArrayAccess` interface methods.

``` php
<?php

foreach ($collection as $alphaCode => $currency) {
    //$currency is a Pcp\Currency object
    //$alphaCode === $currency->getAlphaCode();
}

$euro = $collection['EUR']; // $euro is a Pcp\Currency object for the euro currency
isset($collection['US']);   // returns true
count($collection);         // returns the number of Currency available in a given collection
```

Alternatively you can access the currencies using their numeric code using the following methods:

- `getByNumCode` retrieves a `Currency` object by its Numeric Code
- `hasNumCode` tells whether a `Currency` with the given numeric code exists in the collection

``` php
<?php

$euro = $collection->getByNumCode(978); // $euro is a Pcp\Currency object for the euro currency
$collection->hasNumCode(003); // returns a bool, true if the Currency is present in the collection
```

**Of Note:** If the Currency is not found in the collection an `OutOfBoundsException` is thrown by:

- `Pcp\Collection::getOffsetGet`
- `Pcp\Collection::getByNumCode`

You can filter the `Pcp\Collection` using the `filter` method. This method accepted a callback whose first argument is a `Pdp\Currency` object and returns a new `Collection` containing only the objects that validate the predicate.

``` php
<?php

use Pcp\Currency;
use Pcp\PublicListManager;

$collection = (new PublicListManager())->getList();
$predicate = function (Currency $currency) {
    return $currency->isLegalIn('US');
};

$usCurrencies = $collection->filter($predicate);
echo count($currencies); // all the Currencies legal in the US
```

### The Currency object

This `Pcp\Currency` class represents a currency value object.

``` php
<?php

use Pcp\Currency;

$currency = new Currency(
    'Full Currency Name',  // the ISO 4217 Currency Full name
    'XOX',                  // the ISO 4217 Alphabetic Code
    '088',                  // the ISO 4217 Numeric Code
    '2',                    // the ISO 4217 Minor Unit Exponent (an integer or null if not applicable)
    false,                  // is this a Fund Currency
    [
        'BE' => 'BELGIUM',  // An associative array containing the ISO 3166 Alpha-2 country code as
        ...                 // key and its country full name as its associated value
    ]
);
```

Once instantiated, in addition to getting all of its properties through its getter methods. you can also determine using its corresponding ISO 3166 Alpha-2 country code if a the currency is legal in a given country.

``` php
<?php

use Pcp\Currency;

$euro->isLegalIn('FR'); // returns true
$euro->isLegalIn('US'); // returns false
```

The class also:

- implements the `JsonSerializable` interface
- provides a `toArray` method to get an array reprensentation of the currency
- provides a `__toString` method to return the Alphabetic Code

Testing
-------

`PHP Currency Parser` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/). To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Credits
-------

- [Rob Bast](https://github.com/alcohol)
- [Maksim Kotlyar](https://github.com/makasim)
- [Ignace Nyamagana Butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/nyamsprod/php-currency-parser/graphs/contributors)

Source(s)
-------

* [ISO 4217](http://www.currency-iso.org/dam/downloads/lists/list_one.xml)
* [ISO 3166 Country Code](http://data.okfn.org/data/core/country-codes/r/country-codes.json)

This material is licensed by its maintainers under the Public Domain Dedication and License.

Nevertheless, it should be noted that this material is ultimately sourced from ISO and other standards bodies and their rights and licensing policies are somewhat unclear. As this is a short, simple database of facts there is a strong argument that no rights can subsist in this collection. However, ISO state on [their site](http://www.iso.org/iso/home/standards/country_codes.htm):

>    ISO makes the list of alpha-2 country codes available for internal use and non-commercial purposes free of charge.

This carries the implication (though not spelled out) that other uses are not permitted and that, therefore, there may be rights preventing further general use and reuse.

If you intended to use these data in a public or commercial product, please check the original sources for any specific restrictions.


License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/