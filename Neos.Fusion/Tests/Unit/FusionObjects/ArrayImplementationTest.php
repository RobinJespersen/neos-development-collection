<?php
namespace Neos\Fusion\Tests\Unit\FusionObjects;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Tests\UnitTestCase;
use Neos\Fusion\Core\Runtime;
use Neos\Fusion\FusionObjects\ArrayImplementation;

/**
 * Testcase for the Fusion Array object
 */
class ArrayImplementationTest extends UnitTestCase
{
    /**
     * @test
     */
    public function evaluateWithEmptyArrayRendersNull()
    {
        $mockRuntime = $this->getMockBuilder(Runtime::class)->disableOriginalConstructor()->getMock();
        $path = 'array/test';
        $fusionObjectName = 'Neos.Fusion:Array';
        $renderer = new ArrayImplementation($mockRuntime, $path, $fusionObjectName);
        $result = $renderer->evaluate();
        self::assertNull($result);
    }

    /**
     * @return array
     */
    public function positionalSubElements()
    {
        return [
            [
                'Position end should put element to end',
                ['second' => ['__meta' => ['position' => 'end']], 'first' => []],
                ['/first', '/second']
            ],
            [
                'Position start should put element to start',
                ['second' => [], 'first' => ['__meta' => ['position' => 'start']]],
                ['/first', '/second']
            ],
            [
                'Position start should respect priority',
                ['second' => ['__meta' => ['position' => 'start 50']], 'first' => ['__meta' => ['position' => 'start 52']]],
                ['/first', '/second']
            ],
            [
                'Position end should respect priority',
                ['second' => ['__meta' => ['position' => 'end 17']], 'first' => ['__meta' => ['position' => 'end']]],
                ['/first', '/second']
            ],
            [
                'Positional numbers are in the middle',
                ['last' => ['__meta' => ['position' => 'end']], 'second' => ['__meta' => ['position' => '17']], 'first' => ['__meta' => ['position' => '5']], 'third' => ['__meta' => ['position' => '18']]],
                ['/first', '/second', '/third', '/last']
            ],
            [
                'Position before adds before named element if present',
                ['second' => [], 'first' => ['__meta' => ['position' => 'before second']]],
                ['/first', '/second']
            ],
            [
                'Position before adds after start if named element not present',
                ['third' => [], 'second' => ['__meta' => ['position' => 'before third']], 'first' => ['__meta' => ['position' => 'before unknown']]],
                ['/first', '/second', '/third']
            ],
            [
                'Position before uses priority when referencing the same element; The higher the priority the closer before the element gets added.',
                ['third' => [], 'second' => ['__meta' => ['position' => 'before third 12']], 'first' => ['__meta' => ['position' => 'before third']]],
                ['/first', '/second', '/third']
            ],
            [
                'Position before works recursively',
                ['third' => [], 'second' => ['__meta' => ['position' => 'before third']], 'first' => ['__meta' => ['position' => 'before second']]],
                ['/first', '/second', '/third']
            ],
            [
                'Position after adds after named element if present',
                ['second' => ['__meta' => ['position' => 'after first']], 'first' => []],
                ['/first', '/second']
            ],
            [
                'Position after adds before end if named element not present',
                ['second' => ['__meta' => ['position' => 'after unknown']], 'third' => ['__meta' => ['position' => 'end']], 'first' => []],
                ['/first', '/second', '/third']
            ],
            [
                'Position after uses priority when referencing the same element; The higher the priority the closer after the element gets added.',
                ['third' => ['__meta' => ['position' => 'after first']], 'second' => ['__meta' => ['position' => 'after first 12']], 'first' => []],
                ['/first', '/second', '/third']
            ],
            [
                'Position after works recursively',
                ['third' => ['__meta' => ['position' => 'after second']], 'second' => ['__meta' => ['position' => 'after first']], 'first' => []],
                ['/first', '/second', '/third']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider positionalSubElements
     *
     * @param string $message
     * @param array $subElements
     * @param array $expectedKeyOrder
     */
    public function evaluateRendersKeysSortedByPositionMetaProperty($message, $subElements, $expectedKeyOrder)
    {
        $mockRuntime = $this->getMockBuilder(Runtime::class)->disableOriginalConstructor()->getMock();

        $mockRuntime->expects($this->any())->method('evaluate')->will($this->returnCallback(function ($path) use (&$renderedPaths) {
            $renderedPaths[] = $path;
        }));

        $path = '';
        $fusionObjectName = 'Neos.Fusion:Array';
        $renderer = new ArrayImplementation($mockRuntime, $path, $fusionObjectName);
        foreach ($subElements as $key => $value) {
            $renderer[$key] = $value;
        }
        $renderer->evaluate();

        self::assertSame($expectedKeyOrder, $renderedPaths, $message);
    }
}
