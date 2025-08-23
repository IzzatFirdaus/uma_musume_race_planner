<?php

declare(strict_types=1);

/**
 * Comprehensive Test Script for Uma Musume Race Planner
 * Tests all major functionalities to ensure the application is working correctly
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

class ComprehensiveTest
{
    private $results = [];
    private $db;

    public function __construct()
    {
        $this->db = $this->getDbConnection();
    }

    private function getDbConnection()
    {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'uma_musume_planner';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $username, $password, $options);
        } catch (Exception $e) {
            $this->log('ERROR', 'Database connection failed: ' . $e->getMessage());
            return null;
        }
    }

    private function log($level, $message)
    {
        echo "[" . date('Y-m-d H:i:s') . "] [$level] $message\n";
    }

    public function runAllTests()
    {
        $this->log('INFO', 'Starting comprehensive testing...');

        $this->testDatabaseConnection();
        $this->testApiEndpoints();
        $this->testValidationFunctions();
        $this->testFileStructure();
        $this->testPermissions();

        $this->generateReport();
    }

    private function testDatabaseConnection()
    {
        $this->log('INFO', 'Testing database connection...');

        if ($this->db === null) {
            $this->results['database'] = ['status' => 'FAIL', 'message' => 'Cannot connect to database'];
            return;
        }

        try {
            // Test basic query
            $stmt = $this->db->query("SELECT 1 as test");
            $result = $stmt->fetch();

            if ($result['test'] === 1) {
                $this->results['database'] = ['status' => 'PASS', 'message' => 'Database connection successful'];
            } else {
                $this->results['database'] = ['status' => 'FAIL', 'message' => 'Database query failed'];
            }
        } catch (Exception $e) {
            $this->results['database'] = ['status' => 'FAIL', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function testApiEndpoints()
    {
        $this->log('INFO', 'Testing API endpoints...');

        $endpoints = [
            'plan_list' => 'http://localhost/uma_musume_race_planner/api/plan.php?action=list',
            'stats' => 'http://localhost/uma_musume_race_planner/api/stats.php',
            'activity' => 'http://localhost/uma_musume_race_planner/api/activity.php'
        ];

        foreach ($endpoints as $name => $url) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'method' => 'GET'
                    ]
                ]);

                $response = file_get_contents($url, false, $context);
                if ($response === false) {
                    $this->results["api_$name"] = ['status' => 'FAIL', 'message' => 'HTTP request failed'];
                    continue;
                }

                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->results["api_$name"] = ['status' => 'FAIL', 'message' => 'Invalid JSON response'];
                    continue;
                }

                if (isset($data['success']) && $data['success'] === true) {
                    $this->results["api_$name"] = ['status' => 'PASS', 'message' => 'API endpoint working correctly'];
                } else {
                    $this->results["api_$name"] = ['status' => 'FAIL', 'message' => 'API returned error or unexpected format'];
                }
            } catch (Exception $e) {
                $this->results["api_$name"] = ['status' => 'FAIL', 'message' => 'Exception: ' . $e->getMessage()];
            }
        }
    }

    private function testValidationFunctions()
    {
        $this->log('INFO', 'Testing validation functions...');

        try {
            // Test validate_id function
            if (function_exists('validate_id')) {
                $validTests = [
                    validate_id('123') === 123,
                    validate_id('0') === 0,
                    validate_id('999') === 999
                ];

                $invalidTests = [
                    validate_id('abc') === false,
                    validate_id('-1') === false,
                    validate_id('12.34') === false,
                    validate_id('') === false
                ];

                if (array_sum($validTests) === count($validTests) && array_sum($invalidTests) === count($invalidTests)) {
                    $this->results['validation'] = ['status' => 'PASS', 'message' => 'Validation functions working correctly'];
                } else {
                    $this->results['validation'] = ['status' => 'FAIL', 'message' => 'Validation function tests failed'];
                }
            } else {
                $this->results['validation'] = ['status' => 'FAIL', 'message' => 'validate_id function not found'];
            }
        } catch (Exception $e) {
            $this->results['validation'] = ['status' => 'FAIL', 'message' => 'Validation test exception: ' . $e->getMessage()];
        }
    }

    private function testFileStructure()
    {
        $this->log('INFO', 'Testing file structure...');

        $requiredFiles = [
            'api/plan.php',
            'api/stats.php',
            'api/activity.php',
            'includes/db.php',
            'includes/functions.php',
            'includes/logger.php',
            'components/trainee_image_handler.php',
            'public/index.php',
            'public/guide.php'
        ];

        $requiredDirs = [
            'assets/css',
            'assets/js',
            'assets/images',
            'uploads/trainee_images',
            'logs'
        ];

        $missingFiles = [];
        $missingDirs = [];

        foreach ($requiredFiles as $file) {
            if (!file_exists(__DIR__ . '/../' . $file)) {
                $missingFiles[] = $file;
            }
        }

        foreach ($requiredDirs as $dir) {
            if (!is_dir(__DIR__ . '/../' . $dir)) {
                $missingDirs[] = $dir;
            }
        }

        if (empty($missingFiles) && empty($missingDirs)) {
            $this->results['file_structure'] = ['status' => 'PASS', 'message' => 'All required files and directories present'];
        } else {
            $message = 'Missing files: ' . implode(', ', $missingFiles) . ' | Missing dirs: ' . implode(', ', $missingDirs);
            $this->results['file_structure'] = ['status' => 'FAIL', 'message' => $message];
        }
    }

    private function testPermissions()
    {
        $this->log('INFO', 'Testing file permissions...');

        $writableDirs = [
            'uploads/trainee_images',
            'logs'
        ];

        $permissionIssues = [];

        foreach ($writableDirs as $dir) {
            $fullPath = __DIR__ . '/../' . $dir;
            if (!is_writable($fullPath)) {
                $permissionIssues[] = $dir;
            }
        }

        if (empty($permissionIssues)) {
            $this->results['permissions'] = ['status' => 'PASS', 'message' => 'All required directories are writable'];
        } else {
            $message = 'Non-writable directories: ' . implode(', ', $permissionIssues);
            $this->results['permissions'] = ['status' => 'FAIL', 'message' => $message];
        }
    }

    private function generateReport()
    {
        $this->log('INFO', 'Generating test report...');

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "COMPREHENSIVE TEST REPORT\n";
        echo str_repeat('=', 80) . "\n\n";

        $passed = 0;
        $failed = 0;

        foreach ($this->results as $testName => $result) {
            $status = $result['status'];
            $message = $result['message'];

            echo sprintf("%-20s [%s] %s\n", strtoupper($testName), $status, $message);

            if ($status === 'PASS') {
                $passed++;
            } else {
                $failed++;
            }
        }

        echo "\n" . str_repeat('-', 80) . "\n";
        echo sprintf("SUMMARY: %d PASSED, %d FAILED\n", $passed, $failed);
        echo str_repeat('=', 80) . "\n";

        if ($failed === 0) {
            echo "🎉 ALL TESTS PASSED! The application is working correctly.\n";
            exit(0);
        } else {
            echo "❌ Some tests failed. Please review the issues above.\n";
            exit(1);
        }
    }
}

// Run the tests
$tester = new ComprehensiveTest();
$tester->runAllTests();
