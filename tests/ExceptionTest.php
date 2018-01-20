<?php

namespace JsonBrowser\Tests;

use JsonBrowser\Exception;
use JsonBrowser\JsonBrowser;

/*
 * Test exceptions
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testWrapError()
    {
        $this->expectException(Exception::class);
        try {
            Exception::wrap(function () {
                trigger_error('message text', E_USER_ERROR);
            }, 2, 'Caught error: %s');
        } catch (Exception $e) {
            $this->assertEquals(2, $e->getCode());
            $this->assertEquals('Caught error: message text', $e->getMessage());
            throw $e; // re-throw to prove that the catch block actually ran
        }
    }

    public function testWrapException()
    {
        $this->expectException(Exception::class);
        try {
            Exception::wrap(function () {
                throw new \Exception('message text', 5);
            }, 2, 'Caught error: %s');
        } catch (Exception $e) {
            $this->assertEquals(2, $e->getCode());
            $this->assertEquals('Caught error: message text', $e->getMessage());
            $this->assertEquals(5, $e->getPrevious()->getCode());
            $this->AssertEquals('message text', $e->getPrevious()->getMessage());
            $this->assertInstanceOf(\Exception::class, $e->getPrevious());
            throw $e; // re-throw to prove that the catch block actually ran
        }
    }

    public function getExceptionBrowser()
    {
        return new JsonBrowser(
            '{"childOne": "valueOne", "childTwo": ["valueTwo"]}',
            JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS
        );
    }

    public function testNoExceptionOnValidNode()
    {
        $browser = $this->getExceptionBrowser();
        $this->assertInstanceOf(JsonBrowser::class, $browser->getChild('childOne'));
        $this->assertInstanceOf(JsonBrowser::class, $browser->childOne);
        $this->assertInstanceOf(JsonBrowser::class, $browser->getNodeAt('#/childOne'));
        $this->assertInstanceOf(JsonBrowser::class, $browser->childOne->getSibling('childTwo'));
    }

    public function testNoExceptionOnExists()
    {
        $browser = $this->getExceptionBrowser();
        $childOne = $browser->getChild('childOne');
        $childOne->deleteValue();
        $this->assertFalse($childOne->nodeExists());
    }

    public function testDeletedNodeValueException()
    {
        $browser = $this->getExceptionBrowser();
        $childOne = $browser->getChild('childOne');
        $childOne->deleteValue();
        $this->expectException(Exception::class);
        $childOne->getValue();
    }

    public function testNonContainerChildException()
    {
        $browser = $this->getExceptionBrowser();
        $this->expectException(Exception::class);
        $browser->getChild('childOne')->getChild('childThree');
    }

    public function testNonExistentChildException()
    {
        $browser = $this->getExceptionBrowser();
        $this->expectException(Exception::class);
        $browser->getChild('childThree');
    }

    public function testNonExistentNodeException()
    {
        $browser = $this->getExceptionBrowser();
        $this->expectException(Exception::class);
        $browser->getNodeAt('#/childThree');
    }

    public function testNonExistentChildValueException()
    {
        $browser = new JsonBrowser('{}', JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS | JsonBrowser::OPT_GET_VALUE);
        $this->expectException(Exception::class);
        $browser->childThree;
    }

    public function testNonExistentNodeValueException()
    {
        $browser = $this->getExceptionBrowser();
        $this->expectException(Exception::class);
        $browser->getValueAt('#/childThree');
    }
}
