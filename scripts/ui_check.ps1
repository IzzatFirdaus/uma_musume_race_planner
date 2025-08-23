$base = 'http://localhost/uma_musume_race_planner'
$urls = @(
    "$base/",
    "$base/api/plan_section.php?type=goals&id=1",
    "$base/api/plan_section.php?type=predictions&id=1",
    "$base/api/plan_section.php?type=distance_grades&id=1",
    "$base/api/plan_section.php?type=style_grades&id=1",
    "$base/api/plan_section.php?type=terrain_grades&id=1",
    "$base/api/autosuggest.php?action=get&field=name&query=a",
    "$base/api/autosuggest.php?action=get&field=skill_name&query=a",
    "$base/api/progress.php?action=chart&plan_id=1"
)

foreach ($u in $urls) {
    try {
        $r = Invoke-WebRequest -UseBasicParsing -Uri $u -TimeoutSec 10
        $content = $r.Content
        $isJson = $false
        $count = ''
        if ($content) {
            $trim = $content.TrimStart()
            if ($trim.StartsWith('{') -or $trim.StartsWith('[')) {
                $isJson = $true
                try { $j = $content | ConvertFrom-Json -ErrorAction Stop } catch { $j = $null }
                if ($null -ne $j) {
                    if ($j.psobject.Properties.Name -contains 'attributes') { $count = ( ($j.attributes) | Measure-Object ).Count }
                    elseif ($j.psobject.Properties.Name -contains 'skills') { $count = ( ($j.skills) | Measure-Object ).Count }
                    elseif ($j.psobject.Properties.Name -contains 'goals') { $count = ( ($j.goals) | Measure-Object ).Count }
                    elseif ($j.psobject.Properties.Name -contains 'predictions') { $count = ( ($j.predictions) | Measure-Object ).Count }
                    elseif ($j.psobject.Properties.Name -contains 'suggestions') { $count = ( ($j.suggestions) | Measure-Object ).Count }
                    elseif ($j.psobject.Properties.Name -contains 'data') { $count = ( ($j.data) | Measure-Object ).Count }
                }
            }
        }
        Write-Output "$u -> $($r.StatusCode) isJson=$isJson count=$count len=$($r.RawContentLength)"
    }
    catch {
        Write-Output "$u -> ERROR: $($_.Exception.Message)"
    }
}
