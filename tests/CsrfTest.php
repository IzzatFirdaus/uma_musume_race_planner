<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/functions.php';

class CsrfTest extends TestCase
{
    public function testGenerateAndValidateCsrfToken(): void
    {
        $token = generate_csrf_token();
        $this->assertIsString($token);
        $this->assertTrue(validate_csrf_token($token));
        $this->assertFalse(validate_csrf_token('invalid_token'));
    }
}
