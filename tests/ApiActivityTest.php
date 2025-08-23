<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class ApiActivityTest extends TestCase
{
    public function testGetActivityEndpoint(): void
    {
        $url = 'http://localhost/uma_musume_race_planner/api/activity.php?action=get';
        $response = file_get_contents($url);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('activities', $data);
        $this->assertIsArray($data['activities']);
    }
}
