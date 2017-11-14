<?php

namespace JsonBrowser\Tests;

use JsonBrowser\Exception;

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
}
