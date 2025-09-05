<?php

/**
 * @phpstan-ignore-file
 */

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testTrueIsTrue(): void
    {
        // @phpstan-ignore-next-line
        $this->markTestSkipped('Trivial test placeholder.');
    }

    public function testAddition(): void
    {
        // @phpstan-ignore-next-line
        $this->assertEquals(4, 2 + 2);
    }

    public function testStringContains(): void
    {
        // @phpstan-ignore-next-line
        $this->assertStringContainsString('musume', 'uma musume race planner');
    }

    public function testArrayHasKey(): void
    {
        $arr = ['foo' => 1, 'bar' => 2];
        // @phpstan-ignore-next-line
        $this->assertArrayHasKey('foo', $arr);
    }
}
