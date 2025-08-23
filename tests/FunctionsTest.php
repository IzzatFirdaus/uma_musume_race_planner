<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/functions.php';

class FunctionsTest extends TestCase
{
    public function testSanitizeInput(): void
    {
        $input = " <b>hello</b> ";
        $expected = "&lt;b&gt;hello&lt;/b&gt;";
        $this->assertEquals($expected, sanitize_input($input));
    }

    public function testSanitizeInputSpecialChars(): void
    {
        $input = "<script>alert('x')</script>";
        $expected = "&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;";
        $this->assertEquals($expected, sanitize_input($input));
    }

    public function testSanitizeInputLongString(): void
    {
        $input = str_repeat('A', 10000);
        $this->assertEquals(str_repeat('A', 10000), sanitize_input($input));
    }

    public function testSanitizeInputInvalidUtf8(): void
    {
        $input = "\xC3\x28"; // Invalid UTF-8
        $this->assertIsString(sanitize_input($input));
    }

    public function testValidateWhitelist(): void
    {
        $allowed = ['foo', 'bar', 'baz'];
        $this->assertTrue(validate_whitelist('bar', $allowed));
        $this->assertFalse(validate_whitelist('qux', $allowed));
    }

    public function testValidateWhitelistMixedTypes(): void
    {
        $allowed = [1, '2', 3.0];
        $this->assertTrue(validate_whitelist(1, $allowed));
        $this->assertTrue(validate_whitelist('2', $allowed));
        $this->assertFalse(validate_whitelist('3', $allowed));
    }

    public function testValidateWhitelistEmptyArray(): void
    {
        $this->assertFalse(validate_whitelist('foo', []));
    }

    public function testComputeHash(): void
    {
        $hash = compute_hash('md5', 'abc');
        $this->assertEquals(md5('abc'), $hash);
    }

    public function testComputeHashUnsupportedAlgo(): void
    {
        $hash = compute_hash('unsupported', 'abc');
        $this->assertEquals('', $hash);
    }

    public function testComputeHashRawOutput(): void
    {
        $hash = compute_hash('md5', 'abc', true);
        $this->assertEquals(md5('abc', true), $hash);
    }

    public function testTimingSafeEquals(): void
    {
        $this->assertTrue(timing_safe_equals('abc', 'abc'));
        $this->assertFalse(timing_safe_equals('abc', 'def'));
    }

    public function testTimingSafeEqualsDifferentLengths(): void
    {
        $this->assertFalse(timing_safe_equals('abc', 'abcd'));
    }

    public function testTimingSafeEqualsBinary(): void
    {
        $this->assertTrue(timing_safe_equals("\x00\x01", "\x00\x01"));
        $this->assertFalse(timing_safe_equals("\x00\x01", "\x00\x02"));
    }

    public function testValidateId(): void
    {
        $this->assertEquals(123, validate_id('123'));
        $this->assertEquals(123, validate_id(123));
        $this->assertFalse(validate_id('-1'));
        $this->assertFalse(validate_id('abc'));
    }

    public function testValidateIdFloat(): void
    {
        $this->assertFalse(validate_id(1.5));
    }

    public function testValidateIdNegative(): void
    {
        $this->assertFalse(validate_id(-10));
    }

    public function testValidateIdLeadingZeros(): void
    {
        $this->assertEquals(10, validate_id('010'));
    }

    public function testFormatDate(): void
    {
        $date = '2025-08-24';
        $expected = date('M d, Y', strtotime($date));
        $this->assertEquals($expected, format_date($date));
    }

    public function testFormatDateInvalid(): void
    {
        $this->assertEquals('', format_date('not-a-date'));
    }

    public function testFormatDateTimestamp(): void
    {
        $ts = time();
        $expected = date('M d, Y', $ts);
        $this->assertEquals($expected, format_date($ts));
    }
}
