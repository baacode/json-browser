JSON Browser
============

[![Build Status](https://travis-ci.org/baacode/json-browser.svg?branch=master)](https://travis-ci.org/baacode/json-browser)
[![Coverage Status](https://coveralls.io/repos/github/baacode/json-browser/badge.svg?branch=master)](https://coveralls.io/github/baacode/json-browser?branch=master)
[![Latest Stable Version](https://poser.pugx.org/baacode/json-browser/version)](https://packagist.org/packages/baacode/json-browser)
[![License](https://poser.pugx.org/baacode/json-browser/license)](https://packagist.org/packages/baacode/json-browser)
[![Maintainability](https://api.codeclimate.com/v1/badges/066738ab622644a2ab55/maintainability)](https://codeclimate.com/github/baacode/json-browser/maintainability)

Usage
-----

```php
use JsonBrowser\JsonBrowser;

// returns a new JsonBrowser, or throws an exception if the JSON syntax is invalid
$browser = new JsonBrowser();

// load document as JSON string
$browser->loadJSON($json);

// attach to existing document
$browser->attach($document);

// check for child node
$childExists = $browser->childExists('childName');

// get child node
$child = $browser->getChild('childName');
$child = $browser->childName; // dynamic __get() alias

// iterate through child nodes
foreach ($browser as $childName => $childNode) {
    // $childName is the index key of the child
    // $childNode is another JsonBrowser object (equivalent to $browser->getChild($childName))
}

// check for sibling node
$siblingExists = $child->siblingExists('siblingName');

// get sibling node
$child = $browser->getSibling('siblingName');

// get arbitrary node by path
$node = $browser->getNodeAt('#/childName/grandchildName/4');

// get arbitrary node value by path
$value = $browser->getValueAt('#/childName/grandchildName/4');

// set arbitrary node value by path
$browser->setValueAt('#/childName/grandchildName/4', 'myValue')

// delete arbitrary node value by path
$browser->deleteValueAt('#/childName/grandchildName/4')

// get root node
$root = $node->getRoot();

// test whether a node is the root
$nodeIsRoot = $node->isRoot();

// get parent node
$parent = $node->getParent();

// get node value
$value = $node->getValue();
$value = $parent->node; // __get() alias method when OPT_GET_VALUE is set

// set node value
$node->setValue('myValue');
$parent->node = 'myValue'; // __set() alias method

// delete node value
$node->deleteValue();

// get node type
$type = $node->getType();

// check whether the node exists
$nodeExists = $node->nodeExists();

// test whether the node is at least one of the given types
$isType = $node->isType(JsonBrowser::TYPE_STRING | JsonBrowser::TYPE_NUMBER);

// test whether the node is *not* any of the given types
$isNotType = $node->isNotType(JsonBrowser::TYPE_NULL | JsonBrowser::TYPE_INTEGER);

// test for equality
$isEqual = $node->isEqualTo("myValue");

// get node path
$path = $node->getPath();

// get JSON source for node
$json = $node->getJSON();

// get a node as the root of a subtree
$root = $node->asRoot();

// set named annotation on a node
$node->setAnnotation('myAnnotation', 'myValue'); // append to existing values
$node->setAnnotation('myOtherAnnotation', 'myOtherValue', true); // overwrite previous values

// get latest value for a named annotation, or null if not set
$annotation = $node->getAnnotation('myAnnotation');

// get array of values for a named annotation, empty array if not set
$annotations = $node->getAnnotations('myAnnotation');

// get an associative array of all annotations on a node, empty array if none set
$annotations = $node->getAnnotations();

```

Configuration Options
---------------------

| Name                       | Description                                                        |
|----------------------------|--------------------------------------------------------------------|
| OPT_DEFAULT                | Use the default options set (no user-configurable options enabled) |
| OPT_NONEXISTENT_EXCEPTIONS | Throw an exception when attempting to read a nonexistent value     |
| OPT_GET_VALUE              | Get values, rather than node objects, when using `__get()`         |
| OPT_DECODE                 | Decode the document passed to the constructor as a JSON string     |

Documentation
-------------

Comprehensive API documentation is available [here](https://baacode.github.io/json-browser/).

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
