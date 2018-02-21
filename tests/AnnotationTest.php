<?php

namespace JsonBrowser\Tests;

use JsonBrowser\JsonBrowser;
use JsonBrowser\Exception;

/*
 * Test node annotations
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class AnnotationTest extends \PHPUnit\Framework\TestCase
{
    public function testAnnotations()
    {
        $browser = new JsonBrowser();
        $browser->loadJSON('{"childOne": {"childTwo": "valueTwo"}}');
        $childOne = $browser->getChild('childOne');
        $childTwo = $childOne->getChild('childTwo');

        // no annotations present, getting returns an empty array both with and without an annotation name
        $this->assertSame([], $childOne->getAnnotations());
        $this->assertSame([], $childOne->getAnnotations('aOne'));

        $childOne->setAnnotation('aOne', 'aOneValueOne');
        $childTwo->setAnnotation('aOne', 'aOneValueTwo');
        $childTwo->setAnnotation('aTwo', 'aTwoValueOne');
        $childTwo->setAnnotation('aTwo', 'aTwoValueTwo');

        // annotations are applied to the correct node and append without overwriting by default
        $this->assertSame(['aOne' => ['aOneValueOne']], $childOne->getAnnotations());
        $this->assertSame(
            ['aOne' => ['aOneValueTwo'], 'aTwo' => ['aTwoValueOne', 'aTwoValueTwo']],
            $childTwo->getAnnotations()
        );
        $this->assertSame(['aTwoValueOne', 'aTwoValueTwo'], $childTwo->getAnnotations('aTwo'));

        // fetching a single annotation returns the latest-set, or null if not set
        $this->assertSame('aTwoValueTwo', $childTwo->getAnnotation('aTwo'));
        $this->assertNull($childTwo->getAnnotation('aThree'));

        // existing annotations are cleared prior to setting the new value
        $childTwo->setAnnotation('aTwo', 'aTwoValueThree', true);
        $this->assertSame(['aTwoValueThree'], $childTwo->getAnnotations('aTwo'));

        // annotations still work with a subtree root
        $childTwoRoot = $childTwo->asRoot();
        $childTwoRoot->setAnnotation('aThree', 'aThreeValueOne');
        $this->assertSame(
            ['aOne' => ['aOneValueTwo'], 'aTwo' => ['aTwoValueThree'], 'aThree' => ['aThreeValueOne']],
            $childTwoRoot->getAnnotations()
        );
        $this->assertSame($childTwo->getAnnotations(), $childTwoRoot->getAnnotations());
    }
}
