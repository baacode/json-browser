<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test setting node values
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class SetTest extends \PHPUnit\Framework\TestCase
{
    public function testSetRoot()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": {"childTwo": ["valueThree", "valueFour"]}}');

        $root->setValue(5);

        $this->assertSame(5, $root->getValue());
        $this->assertFalse($root->childExists('childOne'));
    }

    public function testSetChild()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": "valueOne", "childTwo": ["valueThree", "valueFour"]}');
        $childOne = $root->getChild('childOne');
        $childFour = $root->getChild('childTwo')->getChild(1);
        $childFive = $root->getChild('childTwo')->getChild(2);

        $root->getChild('childOne')->setValue(5);
        $this->assertSame(5, $childOne->getValue());

        $childFive->setValue(5);
        $this->assertSame(5, $childFive->getValue());
        $this->assertSame(5, $root->getChild('childTwo')->getChild(2)->getValue());

        $root->setValueAt('#/childTwo/1', 4);
        $this->assertSame(4, $childFour->getValue());

        $this->assertSame(
            '{"childOne":5,"childTwo":["valueThree",4,5]}',
            $root->getJSON(0)
        );
    }

    public function testPromote()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": null, "childTwo": []}');
        $gcOne = $root->getChild('childOne')->getChild('gcOne');
        $gcTwo = $root->getChild('childTwo')->getChild('gcTwo');
        $gcThree = $root->getChild('childThree')->getChild(3);
        $gcFour = $root->getChild('childFour')->getChild(4);

        $gcOne->setValue('valueOne');
        $gcTwo->setValue('valueTwo');
        $gcThree->setValue('valueThree', true);
        $gcFour->setValue('valueFour', false);

        $this->assertSame('valueOne', $root->getValueAt('#/childOne/gcOne'));
        $this->assertSame('valueTwo', $root->getValueAt('#/childTwo/gcTwo'));
        $this->assertSame('valueThree', $root->getValueAt('#/childThree/3'));
        $this->assertSame('valueFour', $root->getValueAt('#/childFour/4'));

        $this->assertSame(
            '{"childOne":{"gcOne":"valueOne"},"childTwo":{"gcTwo":"valueTwo"},' .
            '"childThree":[null,null,null,"valueThree"],"childFour":{"4":"valueFour"}}',
            $root->getJSON(0)
        );
    }

    public function testInvalidContainer()
    {
        $root = new JsonBrowser();
        $root->loadJSON('{"childOne": "valueOne"}');

        $this->expectException(Exception::class);
        $root->getNodeAt('#/childOne/childTwo/childThree')->setValue(5);
    }

    public function testDeleteRoot()
    {
        $root = new JsonBrowser();
        $root->deleteValue();
        $this->assertEquals('null', $root->getJSON(0));
    }

    public function testDeleteChild()
    {
        $root = new JsonBrowser(JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS);
        $root->loadJSON('{"childOne": ["valueOne"], "childTwo": {"childThree": "valueThree"}}');
        $childOne = $root->getChild('childOne');
        $gcOne = $childOne->getChild(0);
        $childTwo = $root->getChild('childOne');
        $childThree = $childTwo->getNodeAt('#/childTwo/childThree');

        $this->assertTrue($gcOne->nodeExists());
        $childOne->setValue('valueOne');
        $this->assertFalse($gcOne->nodeExists());

        $this->assertTrue($childThree->nodeExists());
        $childThree->deleteValue();
        $this->assertFalse($childThree->nodeExists());

        $this->assertTrue($childTwo->nodeExists());
        $childTwo->deleteValue();
        $this->assertFalse($childTwo->nodeExists());

        $this->expectException(Exception::class);
        $childTwo->getValue();
    }

    public function testDeleteEmptyContainers()
    {
        $root = new JsonBrowser();
        $root->loadJSON('[[[[["valueOne", "valueTwo"]], "valueThree"]]]');

        $root->deleteValueAt('#/0/0/0/0/1', true);
        $this->assertEquals('[[[[["valueOne"]],"valueThree"]]]', $root->getJSON(0));

        $root->deleteValueAt('#/0/0/0/0/0', false);
        $this->assertEquals('[[[[[]],"valueThree"]]]', $root->getJSON(0));

        $root->deleteValueAt('#/0/0/0/0', true);
        $this->assertEquals('[[{"1":"valueThree"}]]', $root->getJSON(0));

        $root->deleteValueAt('#/0/0/1', true);
        $this->assertEquals('null', $root->getJSON(0));
    }

    public function testDeleteChildOfInvalidContainer()
    {
        $browser = new JsonBrowser();
        $browser->loadJSON('"this is a string"');
        $browser->deleteValueAt('#/non/existent/path');
        $this->assertEquals('"this is a string"', $browser->getJSON());
    }
}
