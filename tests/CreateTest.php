<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function dataCreate() : array
    {
        return [
            ['0', true],
            ['1', true],
            ['true', true],
            ['false', true],
            ['"string"', true],
            ['"unterminated string', false],
            ['unquoted string', false],
            ['"improperly escaped string\"', false],
            ['[]', true],
            ['[', false],
            ['{}', true],
            ['{', false],
            ['.', false],
        ];
    }

    /** @dataProvider dataCreate */
    public function testCreate($json, $expectSuccess)
    {
        if (!$expectSuccess) {
            $this->expectException(Exception::class);
        }

        $browser = new JsonBrowser($json);
        $this->assertInstanceOf(JsonBrowser::class, $browser);

        if ($expectSuccess) {
            $this->assertEquals(json_decode($json), $browser->getValue());
        }
    }

    public function testExists()
    {
        $browser = new JsonBrowser('{"propertyOne": {"propertyTwo": "valueTwo"}}');

        $this->assertTrue($browser->nodeExists());
        $this->assertTrue($browser->getNodeAt('#/propertyOne')->nodeExists());
        $this->assertFalse($browser->getNodeAt('#/propertyThree')->nodeExists());
        $this->assertTrue($browser->getNodeAt('#/propertyOne/propertyTwo')->nodeExists());
        $this->assertFalse($browser->getNodeAt('#/propertyOne/propertyThree')->nodeExists());
        $this->assertFalse($browser->getNodeAt('#/propertyOne/propertyThree/propertyFour')->nodeExists());
    }
}
