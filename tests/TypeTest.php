<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

class TypeTest extends \PHPUnit\Framework\TestCase
{
    public function dataType() : array
    {
        return [
            ['null', JsonBrowser::TYPE_NULL],
            ['true', JsonBrowser::TYPE_BOOLEAN],
            ['false', JsonBrowser::TYPE_BOOLEAN],
            ['1', JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER],
            ['1.000', JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER],
            ['1.000000001', JsonBrowser::TYPE_NUMBER],
            ['"stringValue"', JsonBrowser::TYPE_STRING],
            ['[]', JsonBrowser::TYPE_ARRAY],
            ['{}', JsonBrowser::TYPE_OBJECT],
        ];
    }

    /** @dataProvider dataType */
    public function testType(string $json, int $type)
    {
        $browser = new JsonBrowser($json);
        $this->assertEquals($type, $browser->getType());
    }
}
