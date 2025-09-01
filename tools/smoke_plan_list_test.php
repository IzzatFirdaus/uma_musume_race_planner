<?php
// Simple smoke test: render components/plan-list.php with a fake environment
$plans = [
    ['id'=>1,'plan_title'=>'Test Plan A','name'=>'Trainer A','status'=>'Active','race_name'=>'Derby','trainee_image_path'=>'uploads/app_logo/uma_musume_race_planner_logo_128.png','stats'=>['speed'=>800,'stamina'=>600,'power'=>450,'guts'=>300,'wit'=>200]],
    ['id'=>2,'plan_title'=>'Test Plan B','name'=>'Trainer B','status'=>'Planning','race_name'=>'Sprint','trainee_image_path'=>'','stats'=>['speed'=>500,'stamina'=>700,'power'=>300,'guts'=>400,'wit'=>350]]
];
// Emulate get_plans.php output
ob_start();
require __DIR__ . '/../components/plan-list.php';
$html = ob_get_clean();
file_put_contents(__DIR__ . '/smoke_plan_list_output.html', $html);
echo "Smoke output written to tools/smoke_plan_list_output.html\n";
