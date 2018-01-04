<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

class SetTest extends \PHPUnit\Framework\TestCase
{
    public function testSetRoot()
    {
        $root = new JsonBrowser('{"childOne": {"childTwo": ["valueThree", "valueFour"]}}');

        $root->setValue(5);

        $this->assertSame(5, $root->getValue());
        $this->assertFalse($root->childExists('childOne'));
    }

    public function testSetChild()
    {
        $root = new JsonBrowser('{"childOne": "valueOne", "childTwo": ["valueThree", "valueFour"]}');
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
        $root = new JsonBrowser('{"childOne": null, "childTwo": []}');
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
        $root = new JsonBrowser('{"childOne": "valueOne"}');

        $this->expectException(Exception::class);
        $root->getNodeAt('#/childOne/childTwo/childThree')->setValue(5);
    }

    public function testRefresh()
    {
        $root = new JsonBrowser('{"childOne": "valueOne"}');
        $childOne = $root->getChild('childOne');

        $pre = $childOne->getValue();
        $root->setValueAt('#/childOne', 'valueTwo');
        $post = $childOne->getValue();

        $this->assertSame('valueOne', $pre);
        $this->assertSame('valueTwo', $post);
    }
}
