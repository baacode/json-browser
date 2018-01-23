<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test attachment to pre-existing document
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class AttachTest extends \PHPUnit\Framework\TestCase
{
    public function testAttach()
    {
        $document = json_decode('{"childOne": "valueOne"}');
        $browser = new JsonBrowser(JsonBrowser::OPT_DEFAULT, $document);
        $browser->getChild('childOne')->setValue('valueTwo');
        $this->assertEquals('valueTwo', $document->childOne);
    }
}
