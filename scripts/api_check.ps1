# Automated API checker - saves responses to logs/api_check_TIMESTAMP.json
# Usage: Open PowerShell in the project root (or run this file) and execute:
#   .\scripts\api_check.ps1

$base = 'http://localhost/uma_musume_race_planner'
$urls = @(
    "$base/api/stats.php?action=get",
    "$base/api/plan.php?action=list",
    "$base/api/plan.php?action=get&id=1",
    "$base/api/plan_section.php?type=attributes&id=1",
    "$base/api/plan_section.php?type=skills&id=1",
    "$base/api/progress.php?action=chart&plan_id=1",
    "$base/get_stats.php",
    "$base/get_plans.php",
    "$base/fetch_plan_details.php?id=1"
)

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$logDir = Join-Path $scriptDir "..\logs"
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Force -Path $logDir | Out-Null }

$results = @()
foreach ($u in $urls) {
    try {
        $r = Invoke-WebRequest -UseBasicParsing -Uri $u -TimeoutSec 10 -ErrorAction Stop
        $content = $r.Content
        $isJson = $false
        if ($content) {
            $trim = $content.TrimStart()
            if ($trim.StartsWith('{') -or $trim.StartsWith('[')) { $isJson = $true }
        }
        $results += [pscustomobject]@{
            url    = $u
            status = $r.StatusCode
            isJson = $isJson
            body   = $content
        }
    }
    catch {
        $results += [pscustomobject]@{
            url    = $u
            status = 'ERROR'
            isJson = $false
            body   = $_.Exception.Message
        }
    }
}

$outFile = Join-Path $logDir ("api_check_$timestamp.json")
$results | ConvertTo-Json -Depth 10 | Out-File -FilePath $outFile -Encoding utf8
Write-Output "Saved API check to $outFile"
