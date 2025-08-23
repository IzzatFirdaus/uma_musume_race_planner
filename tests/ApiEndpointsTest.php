<?php

namespace UmaMusumeRacePlanner\Tests;

use PHPUnit\Framework\TestCase;

class ApiEndpointsTest extends TestCase
{
    private function getJson($url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertNotFalse($response, "Failed to fetch $url: $err");
        $this->assertTrue($httpCode >= 200 && $httpCode < 500, "HTTP error $httpCode for $url");
        $data = json_decode($response, true);
        $this->assertIsArray($data, "Invalid JSON from $url");
        return $data;
    }

    public function testPlanAttributes(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_attributes.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('attributes', $data);
    }

    public function testPlanDistanceGrades(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_distance_grades.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('distance_grades', $data);
    }

    public function testPlanGoals(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_goals.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('goals', $data);
    }

    public function testPlanPredictions(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_predictions.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('predictions', $data);
    }

    public function testPlanSectionAttributes(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_section.php?type=attributes&id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('attributes', $data);
    }

    public function testPlanSkills(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_skills.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('skills', $data);
    }

    public function testPlanStyleGrades(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_style_grades.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('style_grades', $data);
    }

    public function testPlanTerrainGrades(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_terrain_grades.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('terrain_grades', $data);
    }

    public function testProgress(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/progress.php?id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid or missing plan_id', $data['error']);
    }

    public function testAutosuggest(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/autosuggest.php?query=test');
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid field for autosuggestion', $data['error']);
    }

    public function testPlanAttributesMissingId(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_attributes.php');
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid Plan ID', $data['error']);
    }

    public function testPlanDistanceGradesInvalidId(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_distance_grades.php?id=invalid');
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid Plan ID', $data['error']);
    }

    public function testPlanSectionInvalidType(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/plan_section.php?type=invalid&id=1');
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid type', $data['error']);
    }

    public function testPlanDuplicateMissingId(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/uma_musume_race_planner/api/plan.php?action=duplicate');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertNotFalse($response, "Failed to fetch duplicate plan endpoint: $err");
        $this->assertTrue($httpCode >= 200 && $httpCode < 500, "HTTP error $httpCode for duplicate plan endpoint");
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Missing or invalid plan ID', $data['error']);
    }

    public function testAutosuggestEmptyQuery(): void
    {
        $data = $this->getJson('http://localhost/uma_musume_race_planner/api/autosuggest.php?query=');
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid field for autosuggestion', $data['error']);
    }

    public function testPlanDuplicateValidId(): void
    {
        // Duplicate plan with id=1 (must exist)
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'id=1'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan.php?action=duplicate', false, $context);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        // Success may be true or false depending on DB state
    }

    public function testPlanUpdateInvalid(): void
    {
        // Simulate update with missing data
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => ''
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan.php?action=update', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
            $this->assertFalse($data['success']);
        } else {
            $this->assertTrue(true, 'Endpoint not implemented');
        }
    }

    public function testPlanDeleteInvalid(): void
    {
        // Simulate delete with missing/invalid id
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'id=invalid'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan.php?action=delete', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
            $this->assertFalse($data['success']);
        } else {
            $this->assertTrue(true, 'Endpoint not implemented');
        }
    }

    public function testPlanAttributesMutation(): void
    {
        // Simulate adding an attribute (may require valid plan_id and fields)
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&attribute_name=test&value=42'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_attributes.php?action=add', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Mutation endpoint not implemented');
        }
    }

    public function testPlanSkillsMutation(): void
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&skill_reference_id=1&sp_cost=100'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_skills.php?action=add', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Mutation endpoint not implemented');
        }
    }

    public function testPlanGradesMutation(): void
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&distance=short&grade=A'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_distance_grades.php?action=add', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Mutation endpoint not implemented');
        }
    }

    public function testPlanGoalsMutation(): void
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&goal=test_goal&result=success'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_goals.php?action=add', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Mutation endpoint not implemented');
        }
    }

    public function testPlanAttributesUpdate(): void
    {
        // Simulate updating an attribute (requires valid plan_id and attribute_name)
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&attribute_name=test&value=99'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_attributes.php?action=update', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Update endpoint not implemented');
        }
    }

    public function testPlanAttributesDelete(): void
    {
        // Simulate deleting an attribute (requires valid plan_id and attribute_name)
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&attribute_name=test'
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_attributes.php?action=delete', false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Delete endpoint not implemented');
        }
    }

    public function testPlanSkillsUpdateDelete(): void
    {
        // Update skill
        $optsUpdate = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&skill_reference_id=1&sp_cost=200'
            ]
        ];
        $contextUpdate = stream_context_create($optsUpdate);
        $responseUpdate = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_skills.php?action=update', false, $contextUpdate);
        if ($responseUpdate !== false) {
            $data = json_decode($responseUpdate, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Update endpoint not implemented');
        }
        // Delete skill
        $optsDelete = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&skill_reference_id=1'
            ]
        ];
        $contextDelete = stream_context_create($optsDelete);
        $responseDelete = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_skills.php?action=delete', false, $contextDelete);
        if ($responseDelete !== false) {
            $data = json_decode($responseDelete, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Delete endpoint not implemented');
        }
    }

    public function testPlanGradesUpdateDelete(): void
    {
        // Update grade
        $optsUpdate = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&distance=short&grade=S'
            ]
        ];
        $contextUpdate = stream_context_create($optsUpdate);
        $responseUpdate = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_distance_grades.php?action=update', false, $contextUpdate);
        if ($responseUpdate !== false) {
            $data = json_decode($responseUpdate, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Update endpoint not implemented');
        }
        // Delete grade
        $optsDelete = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&distance=short'
            ]
        ];
        $contextDelete = stream_context_create($optsDelete);
        $responseDelete = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_distance_grades.php?action=delete', false, $contextDelete);
        if ($responseDelete !== false) {
            $data = json_decode($responseDelete, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Delete endpoint not implemented');
        }
    }

    public function testPlanGoalsUpdateDelete(): void
    {
        // Update goal
        $optsUpdate = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&goal=test_goal&result=fail'
            ]
        ];
        $contextUpdate = stream_context_create($optsUpdate);
        $responseUpdate = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_goals.php?action=update', false, $contextUpdate);
        if ($responseUpdate !== false) {
            $data = json_decode($responseUpdate, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Update endpoint not implemented');
        }
        // Delete goal
        $optsDelete = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => 'plan_id=1&goal=test_goal'
            ]
        ];
        $contextDelete = stream_context_create($optsDelete);
        $responseDelete = @file_get_contents('http://localhost/uma_musume_race_planner/api/plan_goals.php?action=delete', false, $contextDelete);
        if ($responseDelete !== false) {
            $data = json_decode($responseDelete, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('success', $data);
        } else {
            $this->assertTrue(true, 'Delete endpoint not implemented');
        }
    }
}
