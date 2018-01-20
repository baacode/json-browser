<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test manipulation of child nodes
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class ChildTest extends \PHPUnit\Framework\TestCase
{
    public function testChildExists()
    {
        $browser = new JsonBrowser();
        $browser->loadJSON('{"childOne": "valueOne", "2": "valueTwo"}');
        $this->assertTrue($browser->childExists('childOne'));
        $this->assertTrue($browser->childExists('2'));
        $this->assertTrue($browser->childExists(2)); // array_key_exists() allows sloppy typing
        $this->assertFalse($browser->childExists('childThree'));
        $this->assertFalse($browser->childExists(3));

        $browser = new JsonBrowser();
        $browser->loadJSON('["valueOne", "valueTwo"]');
        $this->assertTrue($browser->childExists(0));
        $this->assertTrue($browser->childExists('1'));
        $this->assertFalse($browser->childExists(2));
        $this->assertFalse($browser->childExists("childThree"));

        $browser = new JsonBrowser();
        $browser->loadJSON('"stringValue"');
        $this->assertFalse($browser->childExists('childOne'));
    }

    public function testGetChild()
    {
        $array = new JsonBrowser();
        $array->loadJSON('["valueOne"]');
        $this->assertEquals('valueOne', $array->getChild(0)->getValue());
        $this->assertNull($array->getChild(1)->getValue());

        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": "valueOne", "childTwo": {"childThree": "valueThree"}}');
        $childOne = $root->getChild('childOne');
        $childTwo = $root->getChild('childTwo');
        $childThree = $childTwo->getChild('childThree');
        $childFour = $root->getChild('childFour');
        $childFive = $childFour->getChild('childFive');
        $this->assertEquals('childTwo', $childTwo->getKey());
        $this->assertEquals('childSix', $root->getChild('childSix')->getKey());
        $this->assertNull($root->getKey());

        $this->assertEquals('valueOne', $childOne->getValue());
        $this->assertInstanceOf(\StdClass::class, $childTwo->getValue());
        $this->assertEquals('valueThree', $childThree->getValue());
        $this->assertNull($childFour->getValue());
        $this->assertNull($childFive->getValue());

        $root = new JsonBrowser(JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS);
        $root->loadJSON('{"childOne": "valueOne"}');
        $this->assertEquals('valueOne', $root->getChild('childOne')->getValue());
        $this->expectException(Exception::class);
        $root->getChild('childTwo');
    }

    public function testGetRoot()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": {"childTwo": "valueTwo"}}');
        $childOne = $root->getChild('childOne');
        $childTwo = $childOne->getChild('childTwo');

        $this->assertEquals($root, $root->getRoot());
        $this->assertEquals($root, $childOne->getRoot());
        $this->assertEquals($root, $childTwo->getRoot());
    }

    public function testGetParent()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": {"childTwo": "valueTwo"}}');
        $childOne = $root->getChild('childOne');
        $childTwo = $childOne->getChild('childTwo');

        $this->assertNull($root->getParent());
        $this->assertEquals($root, $childOne->getParent());
        $this->assertEquals($childOne, $childTwo->getParent());
    }

    public function testGetSet()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": "valueOne"}');
        $this->assertEquals('valueOne', $root->childOne->getValue());

        $root->childTwo = 'valueTwo';
        $this->assertEquals('{"childOne":"valueOne","childTwo":"valueTwo"}', $root->getJSON(0));
    }

    public function testDynamicGetValue()
    {
        $root = new JsonBrowser(JsonBrowser::OPT_GET_VALUE);
        $root->loadJSON('{"childOne": "valueOne"}');
        $this->assertEquals('valueOne', $root->childOne);
    }
}
