<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class ApiPlanTest extends TestCase
{
    public function testListPlansEndpoint(): void
    {
        $url = 'http://localhost/uma_musume_race_planner/api/plan.php?action=list';
        $response = file_get_contents($url);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('plans', $data);
        $this->assertIsArray($data['plans']);
    }

    public function testGetPlanEndpoint(): void
    {
        $url = 'http://localhost/uma_musume_race_planner/api/plan.php?action=get&id=1';
        $response = file_get_contents($url);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        if ($data['success']) {
            $this->assertArrayHasKey('plan', $data);
            $this->assertIsArray($data['plan']);
        }
    }
}
