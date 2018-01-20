<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test JSON encoding
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class JSONTest extends \PHPUnit\Framework\TestCase
{
    public function testGetJSON()
    {
        $browser = new JsonBrowser('{"childOne": {"childTwo": ["valueThree", "valueFour"]}}');
        $childTwo = $browser->getNodeAt('#/childOne/childTwo');
        $this->assertEquals("[\n    \"valueThree\",\n    \"valueFour\"\n]", $childTwo->getJSON());
    }
}
