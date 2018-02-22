<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Util;
use JsonBrowser\Exception;

/*
 * Test type casting
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class CastTest extends \PHPUnit\Framework\TestCase
{
    public function dataCast() : array
    {
        $arrayJSON = '["valueOne", "valueTwo"]';
        $objectJSON = '{"propertyOne": "valueOne", "propertyTwo": "valueTwo"}';
        $stringJSON = '"valueOne"';
        $numberJSON = '5.01';
        $integerJSON = '5';
        $booleanJSON = 'true';

        return [
            // array casting
            [JsonBrowser::TYPE_OBJECT, false, (object)['valueOne', 'valueTwo'], $arrayJSON],
            [JsonBrowser::TYPE_ARRAY, true, ['valueOne', 'valueTwo'], $arrayJSON],
            [JsonBrowser::TYPE_STRING, false, "[\n    \"valueOne\",\n    \"valueTwo\"\n]", $arrayJSON],
            [JsonBrowser::TYPE_NUMBER, false, 2, $arrayJSON],
            [JsonBrowser::TYPE_INTEGER, false, 2, $arrayJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, true, $arrayJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, false, '[]'],
            [JsonBrowser::TYPE_NULL, false, null, $arrayJSON],

            // object casting
            [
                JsonBrowser::TYPE_OBJECT,
                true,
                (object)['propertyOne' => 'valueOne', 'propertyTwo' => 'valueTwo'],
                $objectJSON
            ],
            [JsonBrowser::TYPE_ARRAY, false, ['propertyOne' => 'valueOne', 'propertyTwo' => 'valueTwo'], $objectJSON],
            [
                JsonBrowser::TYPE_STRING,
                false,
                "{\n    \"propertyOne\": \"valueOne\",\n    \"propertyTwo\": \"valueTwo\"\n}",
                $objectJSON
            ],
            [JsonBrowser::TYPE_NUMBER, false, 2, $objectJSON],
            [JsonBrowser::TYPE_INTEGER, false, 2, $objectJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, true, $objectJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, false, '{}'],
            [JsonBrowser::TYPE_NULL, false, null, $objectJSON],

            // string casting
            [JsonBrowser::TYPE_OBJECT, false, (object)['v', 'a', 'l', 'u', 'e', 'O', 'n', 'e'], $stringJSON],
            [JsonBrowser::TYPE_ARRAY, false, ['v', 'a', 'l', 'u', 'e', 'O', 'n', 'e'], $stringJSON],
            [JsonBrowser::TYPE_STRING, true, 'valueOne', $stringJSON],
            [JsonBrowser::TYPE_NUMBER, false, 8, $stringJSON],
            [JsonBrowser::TYPE_INTEGER, false, 8, $stringJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, true, $stringJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, false, '""'],
            [JsonBrowser::TYPE_NULL, false, null, $stringJSON],

            // number casting
            [JsonBrowser::TYPE_OBJECT, false, (object)['value' => 5.01], $numberJSON],
            [JsonBrowser::TYPE_ARRAY, false, [5.01], $numberJSON],
            [JsonBrowser::TYPE_STRING, false, '5.01', $numberJSON],
            [JsonBrowser::TYPE_NUMBER, true, 5.01, $numberJSON],
            [JsonBrowser::TYPE_INTEGER, false, 5, $numberJSON],
            [JsonBrowser::TYPE_INTEGER, false, -5, '-5.01'],
            [JsonBrowser::TYPE_BOOLEAN, false, true, $numberJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, false, '0.00'],
            [JsonBrowser::TYPE_NULL, false, null, $numberJSON],

            // integer casting
            [JsonBrowser::TYPE_OBJECT, false, (object)['value' => 5], $integerJSON],
            [JsonBrowser::TYPE_ARRAY, false, [5], $integerJSON],
            [JsonBrowser::TYPE_STRING, false, '5', $integerJSON],
            [JsonBrowser::TYPE_NUMBER, true, 5, $integerJSON],
            [JsonBrowser::TYPE_INTEGER, true, 5, $integerJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, true, $integerJSON],
            [JsonBrowser::TYPE_BOOLEAN, false, false, '0'],
            [JsonBrowser::TYPE_NULL, false, null, $integerJSON],

            // boolean casting
            [JsonBrowser::TYPE_OBJECT, false, (object)['value' => true], 'true'],
            [JsonBrowser::TYPE_ARRAY, false, [true], 'true'],
            [JsonBrowser::TYPE_STRING, false, 'true', 'true'],
            [JsonBrowser::TYPE_NUMBER, false, 1, 'true'],
            [JsonBrowser::TYPE_NUMBER, false, 0, 'false'],
            [JsonBrowser::TYPE_INTEGER, false, 1, 'true'],
            [JsonBrowser::TYPE_INTEGER, false, 0, 'false'],
            [JsonBrowser::TYPE_BOOLEAN, true, true, 'true'],
            [JsonBrowser::TYPE_NULL, false, null, 'true'],

            // null casting
            [JsonBrowser::TYPE_OBJECT, false, new \stdClass(), 'null'],
            [JsonBrowser::TYPE_ARRAY, false, [], 'null'],
            [JsonBrowser::TYPE_STRING, false, '', 'null'],
            [JsonBrowser::TYPE_NUMBER, false, 0, 'null'],
            [JsonBrowser::TYPE_INTEGER, false, 0, 'null'],
            [JsonBrowser::TYPE_BOOLEAN, false, false, 'null'],
            [JsonBrowser::TYPE_NULL, true, null, 'null'],

            // priority tests
            [JsonBrowser::TYPE_STRING | JsonBrowser::TYPE_NUMBER, false, "[\n    \"valueOne\"\n]", '["valueOne"]'],
            [JsonBrowser::TYPE_BOOLEAN | JsonBrowser::TYPE_INTEGER, false, 5, '5.01'],
            [JsonBrowser::TYPE_NULL | JsonBrowser::TYPE_BOOLEAN, false, true, '5.01'],
        ];
    }

    /** @dataProvider dataCast */
    public function testCast(int $asType, bool $alreadyValid, $finalValue, string $json)
    {
        $browser = new JsonBrowser(JsonBrowser::OPT_DECODE | JsonBrowser::OPT_CAST, $json);

        if (is_object($finalValue)) {
            $this->assertSame(
                get_object_vars($finalValue),
                get_object_vars($browser->getValue($asType))
            );
        } else {
            $this->assertSame($finalValue, $browser->getValue($asType));
        }

        if (!$alreadyValid) {
            $this->expectException(Exception::class);
            $browser->getValue($asType, false);
        }
    }

    public function testGetValueAt()
    {
        $json = '5.01';
        $browser = new JsonBrowser(JsonBrowser::OPT_DECODE | JsonBrowser::OPT_CAST, $json);

        $this->assertSame(5, $browser->getValueAt('#/', JsonBrowser::TYPE_INTEGER));

        $this->expectException(Exception::class);
        $browser->getValueAt('#/', JsonBrowser::TYPE_INTEGER, false);
    }

    public function testUnknownType()
    {
        $json = 'null';
        $browser = new JsonBrowser(JsonBrowser::OPT_DECODE | JsonBrowser::OPT_CAST, $json);

        $this->expectException(Exception::class);
        $browser->getValue(1 << 31); // there is no valid type associated with this bit
    }
}
