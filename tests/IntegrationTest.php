<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $pdo = @require __DIR__ . '/../includes/db.php';
        $this->assertInstanceOf(\PDO::class, $pdo);
        $stmt = $pdo->query('SELECT 1');
        $this->assertEquals(1, $stmt->fetchColumn());
    }
}
