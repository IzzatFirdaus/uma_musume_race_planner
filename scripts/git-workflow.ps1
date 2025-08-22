# Lightweight git workflow automation for Windows PowerShell.
# Creates a branch, stages (optional) and commits changes, pushes the branch,
# and optionally merges the branch into the main branch (locally + pushes).

param(
    [Parameter(Mandatory = $true)]
    [string]$BranchName,

    [string]$Message = "chore: work in progress",

    [switch]$StageAll,

    [switch]$MergeToMain,

    [switch]$DryRun,

    [switch]$Interactive,

    [string]$Remote = "origin",

    [string]$MainBranch = "main"
)

function Exec([string]$cmd) {
    if ($DryRun) {
        Write-Host "DRYRUN: $cmd"
        return
    }

    Write-Host "> $cmd"
    $result = cmd /c $cmd
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed: $cmd`n$result"
    }
    return $result
}

# Ensure we're inside a git repo
try {
    Exec "git rev-parse --is-inside-work-tree"
} catch {
    Write-Error "This script must be run from within a git repository root or subfolder."
    exit 1
}

if ($StageAll) {
    Exec "git add -A"
}

# Check for staged changes
$staged = git diff --name-only --cached
if (-not $staged) {
    Write-Warning "No staged changes found. If you intended to commit, run with -StageAll or stage files first." 
}


# Create and switch to branch
Exec "git checkout -b $BranchName"

# Push staged files to branch before commit
Exec "git push -u $Remote $BranchName"

# Commit if there are staged changes
$stagedNow = git diff --name-only --cached
if ($stagedNow) {
    $commitMsg = $Message
    if ($Interactive) {
        $tmp = Join-Path $env:TEMP ([System.Guid]::NewGuid().ToString() + '.txt')
        Set-Content -Path $tmp -Value $Message -Encoding UTF8
        Write-Host "Opening Notepad for commit message editing: $tmp"
        if (-not $DryRun) { Start-Process notepad.exe $tmp -Wait }
        $commitMsg = Get-Content -Path $tmp -Raw
        Remove-Item $tmp -ErrorAction SilentlyContinue
    }

    # Escape double quotes in commit message
    $escapedMsg = $commitMsg -replace '"', '""'
    Exec "git commit -m \"$escapedMsg\""
} else {
    Write-Host "No staged changes to commit. Proceeding without commit."
}

if ($MergeToMain) {
    Write-Host "Merging $BranchName into $MainBranch..."

    # Checkout main, pull latest, merge, push
    Exec "git checkout $MainBranch"
    Exec "git pull $Remote $MainBranch"

    # Create a merge commit (no-ff) to preserve feature branch record
    Exec "git merge --no-ff $BranchName -m \"Merge branch '$BranchName': $Message\""

    Exec "git push $Remote $MainBranch"

    Write-Host "Merge complete. Checking out $BranchName again."
    Exec "git checkout $BranchName"
}

Write-Host "Workflow finished. Branch: $BranchName (pushed to $Remote)."
