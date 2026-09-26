# Contributing to Uma Musume Career Planner

Thank you for taking the time to contribute.

## How to Contribute

1. Fork the repository and create a feature branch.
2. Make your changes with clear, focused commits.
3. Add or update tests for behavioral changes.
4. Run the relevant checks before opening a pull request.
5. Open a pull request with a short summary of the change and any follow-up notes.

## Development Standards

- Follow the existing Laravel, Livewire, Blade, and Tailwind conventions in the repository.
- Keep changes focused and avoid unrelated refactors.
- Use PSR-12 for PHP code.
- Write tests for new features and bug fixes.
- Update documentation when user-facing behavior changes.

## Suggested Checks

- `vendor/bin/pint --dirty`
- `php artisan test`
- `npm run test`
- `npm run playwright:test` when frontend behavior changes

## Reporting Issues

When reporting a bug, include:

- A short description of the problem
- Steps to reproduce it
- Expected and actual behavior
- Any relevant screenshots, logs, or error messages

## Code of Conduct

Please be respectful and constructive in discussions, issues, and pull requests.