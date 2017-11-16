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

    public function dataIsType() : array
    {
        $tests = array_merge(
            array_map(function ($value) {
                $value[] = true;
                $value[] = true;
                return $value;
            }, $this->dataType()),
            [
                ['null', JsonBrowser::TYPE_NULL | JsonBrowser::TYPE_STRING, false, true],
                ['null', JsonBrowser::TYPE_ARRAY | JsonBrowser::TYPE_STRING, false, false],
                ['null', JsonBrowser::TYPE_STRING, true, false],
                ['null', JsonBrowser::TYPE_STRING, false, false],
                ['1.000', JsonBrowser::TYPE_INTEGER, true, true],
                ['1.000', JsonBrowser::TYPE_NUMBER, true, true],
                ['1.000', JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER, true, true],
            ]
        );

        return $tests;
    }

    /** @dataProvider dataIsType */
    public function testIsType(string $json, int $type, bool $all, bool $isMatch)
    {
        $browser = new JsonBrowser($json);
        $this->assertEquals($isMatch, $browser->isType($type, $all));
    }

    public function testIsNotType()
    {
        $browser = new JsonBrowser('1.000000001');
        $this->assertTrue($browser->isNotType(JsonBrowser::TYPE_INTEGER));
    }
}
