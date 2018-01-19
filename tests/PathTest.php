<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

class PathTest extends \PHPUnit\Framework\TestCase
{
    public function testGetPath()
    {
        $root = new JsonBrowser('{"child~/One": {"child%Two": ["valueThree", "valueFour"]}}');
        $childFour = $root->getChild('child~/One')->getChild('child%Two')->getChild(1);
        $childFive = $root->getChild('child~/One')->getChild(5);

        $this->assertEquals('#/child~0~1One/child%25Two/1', $childFour->getPath());
        $this->assertEquals('#/child~0~1One/5', $childFive->getPath());

        $childThree = $root->getNodeAt('#/child~0~1One/child%25Two/0');
        $this->assertEquals('valueThree', $childThree->getValue());

        $this->assertEquals('valueFour', $root->getValueAt('#/child~0~1One/child%25Two/1'));

        $this->assertEquals($root, $root->getNodeAt('#/'));
    }

    public function testNodeByPathException()
    {
        $root = new JsonBrowser('{}', JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS);

        $this->expectException(Exception::class);
        $root->getNodeAt('#/this/path/does/not/exist');
    }

    public function testValueByPathException()
    {
        $root = new JsonBrowser('{}', JsonBrowser::OPT_NONEXISTENT_EXCEPTIONS);

        $this->expectException(Exception::class);
        $root->getValueAt('#/this/path/does/not/exist');
    }
}
