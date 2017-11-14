<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

class JSONTest extends \PHPUnit\Framework\TestCase
{
    public function testGetJSON()
    {
        $browser = new JsonBrowser('{"childOne": {"childTwo": ["valueThree", "valueFour"]}}');
        $childTwo = $browser->getNodeAt('#/childOne/childTwo');
        $this->assertEquals("[\n    \"valueThree\",\n    \"valueFour\"\n]", $childTwo->getJSON());
    }
}
