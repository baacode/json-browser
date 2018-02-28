<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test counting node children
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class CountTest extends \PHPUnit\Framework\TestCase
{
    public function dataCount() : array
    {
        return [
            [2, '[1,2]'],
            [2, '{"propertyOne": "valueOne", "propertyTwo": "valueTwo"}'],
            [0, '5'],
            [0, '5.8'],
            [0, 'true'],
            [0, 'false'],
            [0, '"valueOne"'],
            [0, 'null'],
        ];
    }

    /** @dataProvider dataCount */
    public function testCount(int $count, string $json)
    {
        $browser = new JsonBrowser();
        $browser->loadJSON($json);

        $this->assertSame($count, count($browser));
    }
}
