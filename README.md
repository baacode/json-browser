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

// check for child node
$childExists = $browser->childExists('childName');

// get child node
$child = $browser->getChild('childName');

// check for sibling node
$siblingExists = $child->siblingExists('siblingName');

// get sibling node
$child = $browser->getSibling('siblingName');

// get arbitrary node by path
$node = $browser->getNodeAt('#/childName/grandchildName/4');

// get root node
$root = $node->getRoot();

// get parent node
$parent = $node->getParent();

// get node value
$value = $node->getValue();

// get node type
$type = $node->getType();

// get node path
$path = $node->getPath();
```

Comprehensive API documentation is available [here](https://coveralls.io/github/baacode/json-browser).

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
