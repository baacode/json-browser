<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

class RootTest extends \PHPUnit\Framework\TestCase
{
    public function testChangeRoot()
    {
        $root = new JsonBrowser('{"childOne": {"childTwo": "valueTwo"}}');
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
