<?php

namespace JsonBrowser\Tests;

/*
 * Test subclass identity when initial instantiation is a subclass
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class SubclassTest extends \PHPUnit\Framework\TestCase
{
    private function getBrowser() : TestBrowser
    {
        $json = '{"childOne": {"childTwo": "valueTwo"}}';
        return new TestBrowser(TestBrowser::OPT_DECODE, $json);
    }

    public function testCreate()
    {
        $this->assertInstanceOf(TestBrowser::class, $this->getBrowser());
    }

    public function testChild()
    {
        $this->assertInstanceOf(TestBrowser::class, $this->getBrowser()->getChild('childOne'));
    }

    public function testPath()
    {
        $this->assertInstanceOf(TestBrowser::class, $this->getBrowser()->getNodeAt('#/childOne/childTwo'));
    }

    public function testUnset()
    {
        $this->assertInstanceOf(TestBrowser::class, $this->getBrowser()->getChild('childThree'));
    }

    public function testAsRoot()
    {
        $root = $this->getBrowser()->getNodeAt('#/childOne/childTwo')->asRoot();
        $this->assertInstanceOf(TestBrowser::class, $root);
    }
}
