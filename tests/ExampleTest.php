<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testTrueIsTrue(): void
    {
        $this->assertTrue(true);
    }

    public function testAddition(): void
    {
        $this->assertEquals(4, 2 + 2);
    }

    public function testStringContains(): void
    {
        $this->assertStringContainsString('musume', 'uma musume race planner');
    }

    public function testArrayHasKey(): void
    {
        $arr = ['foo' => 1, 'bar' => 2];
        $this->assertArrayHasKey('foo', $arr);
    }
}
