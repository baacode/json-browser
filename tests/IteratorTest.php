<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test iterator
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class IteratorTest extends \PHPUnit\Framework\TestCase
{
    public function testEmpty()
    {
        $browser = new JsonBrowser();
        $browser->loadJSON('"notAContainer"');

        foreach ($browser as $child) {
            $this->assertTrue(false); // should never execute
        }
        $this->assertNull($child ?? null);
    }

    public function testArray()
    {
        $browser = new JsonBrowser();
        $browser->loadJSON('["valueOne", "valueTwo"]');
        $count = 0;
        foreach ($browser as $key => $child) {
            $this->assertEquals($count++, $key);
            $this->assertInstanceOf(JsonBrowser::class, $child);
            if ($count == 1) {
                $this->assertEquals('valueOne', $child->getValue());
            } else {
                $this->assertEquals('valueTwo', $child->getValue());
            }
        }
        $this->assertEquals(2, $count);
    }

    public function testObject()
    {
        $browser = new JsonBrowser();
        $browser->loadJSON('{"childOne": "valueOne", "childTwo": {"childThree": "valueThree"}}');

        $count = 0;
        foreach ($browser as $key => $child) {
            $this->assertInstanceOf(JsonBrowser::class, $child);
            if (++$count == 1) {
                $this->assertEquals('childOne', $key);
                $this->assertEquals('valueOne', $child->getValue());
            } else {
                $this->assertEquals('childTwo', $key);
                $this->assertEquals((object)['childThree' => 'valueThree'], $child->getValue());
            }
        }
        $this->assertEquals(2, $count);
    }
}
