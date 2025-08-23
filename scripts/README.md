# git-workflow.ps1

Usage examples (PowerShell):


## Create branch, stage all, commit, push

PS> .\git-workflow.ps1 -BranchName feature/add-stats -Message "feat(stats): add stats endpoint" -StageAll


## Create branch, push, and merge into main (after pushing)

PS> .\git-workflow.ps1 -BranchName fix/logger-fallback -Message "fix(logger): fallback" -StageAll -MergeToMain


Notes:

- Script runs `git` commands and will throw on failures.
- Run from repository root or any subfolder inside the repo.
- For safety, review staged files before running with `git status`.
