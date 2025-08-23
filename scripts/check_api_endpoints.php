<?php

// scripts/check_api_endpoints.php
// Run from project root: php scripts/check_api_endpoints.php

$base = 'http://localhost/uma_musume_race_planner';
$urls = [
    "$base/api/stats.php?action=get",
    "$base/api/plan.php?action=list",
    "$base/api/plan.php?action=get&id=1",
    "$base/api/plan_section.php?type=attributes&id=1",
    "$base/api/plan_section.php?type=skills&id=1",
    "$base/api/progress.php?action=chart&plan_id=1",
    "$base/get_stats.php",
    "$base/get_plans.php",
    "$base/get_plans.php?id=1&type=attributes",
    "$base/fetch_plan_details.php?id=1",
];

$results = [];
foreach ($urls as $u) {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $entry = ['url' => $u, 'status' => null, 'isJson' => false, 'snippet' => null];
    try {
        $content = @file_get_contents($u, false, $ctx);
        $meta = $http_response_header ?? [];
        $status = null;
        foreach ($meta as $m) {
            if (preg_match('#^HTTP/\d+\.\d+\s+(\d+)#', $m, $m2)) {
                $status = intval($m2[1]);
                break;
            }
        }
        $entry['status'] = $status ?? 'UNKNOWN';
        if ($content !== false && $content !== '') {
            $trim = ltrim($content);
            if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
                $entry['isJson'] = true;
            }
            $entry['snippet'] = mb_substr($content, 0, 512);
        } else {
            $entry['snippet'] = 'NO_CONTENT';
        }
    } catch (Throwable $ex) {
        $entry['status'] = 'ERROR';
        $entry['snippet'] = $ex->getMessage();
    }

    // If this appears to be non-JSON or errored, try sensible param variations for legacy endpoints
    if (!$entry['isJson'] && in_array(basename(parse_url($u, PHP_URL_PATH)), ['get_plans.php', 'get_stats.php', 'fetch_plan_details.php'], true)) {
        // try with id=1&type=attributes if not already present
        if (strpos($u, 'id=') === false) {
            $try = $u . (strpos($u, '?') === false ? '?' : '&') . 'id=1&type=attributes';
            try {
                $c2 = @file_get_contents($try, false, $ctx);
                $meta2 = $http_response_header ?? [];
                $status2 = null;
                foreach ($meta2 as $m) {
                    if (preg_match('#^HTTP/\d+\.\d+\s+(\d+)#', $m, $m2)) {
                        $status2 = intval($m2[1]);
                        break;
                    }
                }
                $entry['tried_with'] = $try;
                $entry['tried_status'] = $status2 ?? 'UNKNOWN';
                $entry['tried_snippet'] = $c2 !== false && $c2 !== '' ? mb_substr($c2, 0, 512) : 'NO_CONTENT';
                if ($c2 !== false) {
                    $ttrim = ltrim($c2);
                    if ($ttrim !== '' && ($ttrim[0] === '{' || $ttrim[0] === '[')) {
                        $entry['tried_isJson'] = true;
                    }
                }
            } catch (Throwable $ex) {
                $entry['tried_status'] = 'ERROR';
                $entry['tried_snippet'] = $ex->getMessage();
            }
        }
    }

    $results[] = $entry;
}

$ts = date('Ymd_His');
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$out = $logDir . "/api_check_php_$ts.json";
file_put_contents($out, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Saved API check to: $out\n";
foreach ($results as $r) {
    echo sprintf("%s -> %s (json=%s)\n", $r['url'], $r['status'], $r['isJson'] ? 'yes' : 'no');
}

return 0;
