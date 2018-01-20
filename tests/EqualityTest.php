<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test equality checks
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class EqualityTest extends \PHPUnit\Framework\TestCase
{
    public function dataIsEqual() : array
    {
        return [
            ['{"propertyOne": 5, "propertyTwo": "6"}', (object)['propertyOne' => 5, 'propertyTwo' => '6'], true],
            ['{"propertyOne": "5", "propertyTwo": 6}', (object)['propertyOne' => 5, 'propertyTwo' => '6'], false],
            ['{"propertyOne": 5}', (object)['propertyOne' => 5, 'propertyTwo' => 6], false],
            ['{"propertyOne": 5, "propertyTwo": "6"}', 5, false],
            ['5', 5, true],
            ['5', 5.000, true],
            ['5.000', 5, true],
            ['"5"', 5, false],
            ['5', '5', false],
            ['5', 4, false],
        ];
    }

    /** @dataProvider dataIsEqual */
    public function testIsEqual(string $json, $compareTo, bool $isEqual)
    {
        $browser = new JsonBrowser();
        $browser->loadJSON($json);
        $this->assertTrue($browser->isEqualTo($browser));
        $this->assertEquals($isEqual, $browser->isEqualTo($compareTo));
    }
}
