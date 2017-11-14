JSON Browser
============

[![Build Status](https://travis-ci.org/baacode/json-browser.svg?branch=master)](https://travis-ci.org/baacode/json-browser)
[![Coverage Status](https://coveralls.io/repos/github/baacode/json-browser/badge.svg?branch=master)](https://coveralls.io/github/baacode/json-browser?branch=master)
[![Latest Stable Version](https://poser.pugx.org/baacode/json-browser/version)](https://packagist.org/packages/baacode/json-browser)
[![License](https://poser.pugx.org/baacode/json-browser/license)](https://packagist.org/packages/baacode/json-browser)

Usage
-----

```php
use JsonBrowser\JsonBrowser;

// returns a new JsonBrowser, or throws an exception if the JSON syntax is invalid
$browser = new JsonBrowser($json);
```

Installation
------------

To install via composer, use:

```bash
$ composer require baacode/json-browser
```

Requirements
------------

 - PHP >= 7.0
 - PHP's native JSON extension
 - seld/jsonlint >= 1.0
