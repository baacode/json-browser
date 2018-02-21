<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test manipulation of sibling nodes
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class SiblingTest extends \PHPUnit\Framework\TestCase
{
    public function testRootSiblingExists()
    {
        $root = new JsonBrowser();
        $this->assertFalse($root->siblingExists('siblingOne'));
    }

    public function testSiblingExists()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": "valueOne", "childTwo": "valueTwo"}');
        $childOne = $root->getChild('childOne');
        $childTwo = $root->getChild('childTwo');
        $childThree = $root->getChild('childThree');

        $this->assertTrue($childOne->siblingExists('childTwo'));
        $this->assertTrue($childTwo->siblingExists('childOne'));
        $this->assertFalse($childOne->siblingExists('childThree'));
        $this->assertTrue($childThree->siblingExists('childTwo'));
    }

    public function testGetSibling()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": "valueOne", "childTwo": "valueTwo"}');
        $childOne = $root->getChild('childOne');
        $childThree = $childOne->getSibling('childThree');
        $childTwo = $childThree->getSibling('childTwo');

        $this->assertEquals('valueOne', $childOne->getValue());
        $this->assertEquals('valueTwo', $childTwo->getValue());
        $this->assertNull($childThree->getValue());

        $root = new JsonBrowser(JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS);
        $root->loadJSON('{"childOne": "valueOne"}');
        $childOne = $root->getChild('childOne');
        $this->expectException(Exception::class);
        $childTwo = $childOne->getSibling('childTwo');
    }
}
