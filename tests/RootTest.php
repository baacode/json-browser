<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test getting and using a node as the root of a subtree
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class RootTest extends \PHPUnit\Framework\TestCase
{
    public function testChangeRoot()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": {"childTwo": "valueTwo"}}');
        $childOne = $root->getChild('childOne')->asRoot();

        $this->assertEquals('{"childTwo":"valueTwo"}', $childOne->getJSON(0));
        $this->assertEquals($childOne, $childOne->getRoot());
        $this->assertEquals($childOne, $childOne->getNodeAt('#/'));

        $childTwo = $childOne->getChild('childTwo')->asRoot();
        $this->assertEquals('valueTwo', $childTwo->getValue());

        $childTwo->setValue('valueThree');
        $this->assertEquals('{"childOne":{"childTwo":"valueThree"}}', $root->getJSON(0));
    }
}
