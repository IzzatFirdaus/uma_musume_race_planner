<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class ApiStatsTest extends TestCase
{
    public function testStatsEndpoint(): void
    {
        $url = 'http://localhost/uma_musume_race_planner/api/stats.php';
        $response = file_get_contents($url);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('stats', $data);
        $this->assertIsArray($data['stats']);
    }
}
