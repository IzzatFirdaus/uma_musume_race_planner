# Git practices for this repository

This project follows common Git and GitHub best practices. Use this as a quick reminder.

1) Branches
- Create a feature branch for changes: feature/short-description
- Use bugfix/ or hotfix/ prefixes for fixes. Keep `main` stable.

2) Commits
- Make small, focused commits. One logical change per commit.
- Use present-tense short subject (<50 chars) and optional body.
  - Example: "fix(api): consistent JSON responses for get_plans"

3) Sensitive files
- Never commit `.env` or credentials. Use `.env.example` as template.

4) Large files / docs
- The `UMAMUSUME PLANS/` folder contains large markdown files. Consider:
  - Moving them to a separate `docs` repo or branch, or
  - Using Git LFS if you need versioning for very large files.

5) .gitignore
- The repository contains a recommended `.gitignore`. Review and adjust if you
  intentionally want to track additional folders like `vendor/`.

6) Before pushing
- Run `git pull --rebase origin main` (or your team's flow), run linters/tests, then push.
