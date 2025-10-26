# Livewire Frontend Standardization & Conversion Guide

**Date:** October 26, 2025  
**Purpose:** Reference guide for converting non-Livewire components to Livewire and standardizing frontend implementation.  
**Scope:** Uma Musume Planner Laravel application (Laravel 12, Livewire v3.6, PHP 8.2+)

---

## Quick Start for Automated Agent

If you are an automated agent executing the **STANDARDIZE_LIVEWIRE_PROMPT.md**, follow these steps:

### 1. Verify Environment (RULE #0)

```powershell
# Check Laravel version
php -r "echo json_decode(file_get_contents('composer.json'))->require->{'laravel/framework'};"

# Check PHP version
php -v

# Check Livewire version
php -r "echo json_decode(file_get_contents('composer.json'))->require->{'livewire/livewire'};"

# Count existing Livewire components
(Get-ChildItem app/Livewire -Recurse -Filter *.php).Count

# Verify directories exist
Test-Path app/Livewire
Test-Path resources/views/livewire
```

**Expected Output:**

```
laravel/framework: ^12.0
PHP: 8.2.12 or higher
livewire/livewire: ^3.6
Livewire components: ~7 (may vary)
Directories: True (both should exist)
```

### 2. Review Conversion Mapping

Open `docs/livewire-conversion-mapping.md` to see:

- Technology stack verification
- Existing Livewire components (reference patterns)
- Conversion candidates and priorities
- Assessment template for new candidates

### 3. Start with HIGH-Priority Conversions

**Recommended First 3 Conversions:**

1. **Skill Editor** — Add/remove skill rows (server state, validation)
2. **Character Manager** — CRUD operations (form submission, state management)
3. **Stat Logger** — Turn-by-turn stat input (validation, persistence)

For each, create:

- Livewire class: `php artisan make:livewire <feature>/<Name>`
- View: `resources/views/livewire/<feature>/<name>.blade.php`
- Test: `php artisan make:test Feature/Livewire/<Name>Test`

### 4. Execute Conversion Pattern

**Pattern for Each Component:**

```
Step 1: Create Artisan files
  php artisan make:livewire Plans/SkillEditor --no-interaction
  php artisan make:test Feature/Livewire/SkillEditorTest --no-interaction

Step 2: Update Livewire class
  - Add typed properties with constructor property promotion
  - Add validation rules (or create FormRequest)
  - Add action methods (save, delete, etc.)
  - Use wire:model.live for reactivity

Step 3: Create view
  - Use Blade + Tailwind CSS
  - Add wire:key in loops
  - Add wire:loading states
  - Keep accessibility (role, aria-*, tab order)

Step 4: Update parent Blade/Controller
  - Replace @component('old.path') with @livewire('feature.name')
  - Pass initial data as props if needed

Step 5: Add tests
  - Test rendering with Livewire::test()
  - Test action calls and state changes
  - Test validation failures and edge cases
  - Test accessibility

Step 6: Format & verify
  - vendor/bin/pint --dirty
  - npm run build (if style changes)
  - php artisan test --filter=<ComponentName>
```

### 5. Verify After Each Batch (3 components)

```powershell
# Format code
vendor/bin/pint --dirty

# Count updated Livewire components
(Get-ChildItem app/Livewire -Recurse -Filter *.php).Count

# Run Livewire tests
php artisan test --filter=Livewire

# Run all tests
php artisan test

# Build frontend
npm run build
```

### 6. Create Output JSON

After completing 3+ conversions, generate:

```json
{
  "verified": {
    "laravel_version": "12.x",
    "php_version": "8.2.x",
    "livewire_version": "3.6",
    "livewire_component_count": 10,
    "view_livewire_count": 10
  },
  "inventory": [
    {
      "source_path": "resources/views/plans/skill-editor.blade.php",
      "suggested_livewire_class": "app/Livewire/Plans/SkillEditor.php",
      "suggested_livewire_view": "resources/views/livewire/plans/skill-editor.blade.php",
      "category": "Form/List Editor"
    }
  ],
  "changes": [
    {
      "file_added": "app/Livewire/Plans/SkillEditor.php",
      "file_removed": null,
      "file_modified": "resources/views/plans/plan-details.blade.php",
      "test_added": "tests/Feature/Livewire/SkillEditorTest.php"
    }
  ],
  "commands_run": [
    "php artisan make:livewire Plans/SkillEditor --no-interaction",
    "php artisan make:test Feature/Livewire/SkillEditorTest --no-interaction",
    "vendor/bin/pint --dirty",
    "npm run build",
    "php artisan test"
  ],
  "tests": {
    "run_status": "PASS",
    "failures": []
  },
  "notes": "Successfully converted 3 high-priority components. All tests passing. Ready for next batch."
}
```

---

## Code Example: Converting a Component

### Before (Blade + Alpine)

**File:** `resources/views/components/partials/skill-row.blade.php`

```blade
<div class="skill-row" x-data="skillForm()" x-init="init()">
    <input type="text" x-model="skill.name" placeholder="Skill name" />
    <input type="number" x-model="skill.sp_cost" placeholder="SP cost" />
    <button @click="saveSkill()">Save</button>
    <button @click="removeSkill()" class="text-red-600">Remove</button>
    <div x-show="errors.length" class="text-red-500">
        <template x-for="error in errors">
            <p x-text="error"></p>
        </template>
    </div>
</div>

<script>
function skillForm() {
    return {
        skill: { name: '', sp_cost: 0 },
        errors: [],
        init() { /* ... */ },
        saveSkill() { /* API call */ },
        removeSkill() { /* API call */ }
    }
}
</script>
```

### After (Livewire)

**File:** `app/Livewire/Plans/SkillRow.php`

```php
<?php

namespace App\Livewire\Plans;

use App\Models\Skill;
use Livewire\Component;

class SkillRow extends Component
{
    public Skill $skill;
    public string $name = '';
    public int $spCost = 0;

    public function mount(Skill $skill): void
    {
        $this->skill = $skill;
        $this->name = $skill->name;
        $this->spCost = $skill->sp_cost;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'spCost' => 'required|integer|min:0|max:999',
        ];
    }

    public function saveSkill(): void
    {
        $this->validate();
        $this->skill->update([
            'name' => $this->name,
            'sp_cost' => $this->spCost,
        ]);
        $this->dispatch('skillUpdated', skillId: $this->skill->id);
    }

    public function removeSkill(): void
    {
        $this->skill->delete();
        $this->dispatch('skillRemoved');
    }

    public function render()
    {
        return view('livewire.plans.skill-row');
    }
}
```

**File:** `resources/views/livewire/plans/skill-row.blade.php`

```blade
<div class="skill-row space-y-2">
    <div class="flex gap-2">
        <input 
            type="text" 
            wire:model.live="name" 
            placeholder="Skill name"
            class="flex-1 px-3 py-2 border rounded"
        />
        <input 
            type="number" 
            wire:model.live="spCost" 
            placeholder="SP cost"
            class="w-20 px-3 py-2 border rounded"
        />
        <button 
            wire:click="saveSkill"
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
            <span wire:loading.remove>Save</span>
            <span wire:loading>Saving...</span>
        </button>
        <button 
            wire:click="removeSkill"
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
        >
            Remove
        </button>
    </div>

    @error('name')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    @error('spCost')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

**File:** `tests/Feature/Livewire/SkillRowTest.php`

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Plans\SkillRow;
use App\Models\Skill;
use Livewire\Livewire;
use Tests\TestCase;

class SkillRowTest extends TestCase
{
    public function test_skill_row_renders(): void
    {
        $skill = Skill::factory()->create();

        Livewire::test(SkillRow::class, ['skill' => $skill])
            ->assertSee($skill->name);
    }

    public function test_can_save_skill(): void
    {
        $skill = Skill::factory()->create(['name' => 'Old Name']);

        Livewire::test(SkillRow::class, ['skill' => $skill])
            ->set('name', 'New Name')
            ->call('saveSkill')
            ->assertDispatched('skillUpdated');

        $this->assertEquals('New Name', $skill->refresh()->name);
    }

    public function test_validation_fails_with_empty_name(): void
    {
        $skill = Skill::factory()->create();

        Livewire::test(SkillRow::class, ['skill' => $skill])
            ->set('name', '')
            ->call('saveSkill')
            ->assertHasErrors(['name']);
    }

    public function test_can_remove_skill(): void
    {
        $skill = Skill::factory()->create();

        Livewire::test(SkillRow::class, ['skill' => $skill])
            ->call('removeSkill')
            ->assertDispatched('skillRemoved');

        $this->assertModelMissing($skill);
    }
}
```

---

## Best Practices

### 1. Use Constructor Property Promotion

```php
public function __construct(
    public User $user,
    public Plan $plan,
) {}
```

### 2. Type Properties Explicitly

```php
public ?string $name = null;
public int $count = 0;
public Collection $items;
```

### 3. Use `wire:model.live` for Real-Time Updates

```blade
<input wire:model.live="name" />
<!-- Updates component state as user types -->
```

### 4. Add `wire:key` in Loops

```blade
@foreach($skills as $skill)
    <div wire:key="skill-{{ $skill->id }}">
        @livewire('plans.skill-row', ['skill' => $skill])
    </div>
@endforeach
```

### 5. Use `wire:loading` for UX Feedback

```blade
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

### 6. Prefer FormRequest Over Inline Validation

```php
// In component
$this->authorize('create', Plan::class);
$validated = $this->validate(with: CreatePlanRequest::class);
```

### 7. Eager-Load Related Data

```php
public function mount(): void
{
    $this->plans = Plan::with(['skills', 'statProgresses'])->get();
}
```

### 8. Test Happy Path + Failure Cases

```php
public function test_happy_path(): void { /* ... */ }
public function test_validation_fails(): void { /* ... */ }
public function test_unauthorized_fails(): void { /* ... */ }
```

---

## Accessibility Checklist

Ensure converted components maintain accessibility:

- [ ] Use semantic HTML (`<button>`, `<input>`, `<form>`)
- [ ] Add `role` attributes where needed (e.g., `role="alert"`)
- [ ] Add `aria-*` attributes for state (e.g., `aria-busy`, `aria-disabled`)
- [ ] Maintain keyboard navigation (Tab, Enter, Esc)
- [ ] Test with screen reader
- [ ] Color should not be the only means of conveying information
- [ ] Contrast ratio ≥ 4.5:1 for normal text

---

## Testing Checklist

For each converted component:

- [ ] **Rendering**: Component renders without errors
- [ ] **Happy Path**: Core actions work (save, delete, etc.)
- [ ] **Validation**: Invalid inputs show error messages
- [ ] **State Update**: `wire:model` updates reflect correctly
- [ ] **Events**: Custom events are dispatched correctly
- [ ] **Authorization**: Only authorized users can access/modify
- [ ] **Edge Cases**: Boundary values, null states, empty lists

---

## Troubleshooting

### Issue: Vite Manifest Error

**Error:** `Unable to locate file in Vite manifest`

**Solution:** Rebuild frontend assets:

```powershell
npm run build
# or for development with auto-rebuild
npm run dev
```

### Issue: Tests Failing After Conversion

**Steps:**

1. Check Livewire component exists in correct path
2. Verify view file uses correct namespace
3. Ensure FormRequest (if used) exists and has rules
4. Check test imports and class references
5. Run `php artisan test --verbose` for detailed output

### Issue: Component State Not Updating

**Check:**

1. Properties are public
2. Using `wire:model` or `wire:click` with action name
3. Method exists on component class
4. No typos in property/method names

---

## Files to Review as Patterns

Reference these existing Livewire components for style/pattern guidance:

- `app/Livewire/Dashboard/` — Page-level component
- `app/Livewire/FormTabs.php` — Tab/form navigation
- `app/Livewire/QuickCreatePlan.php` — Modal form with submission
- `app/Livewire/TraineeImageHandler.php` — File upload handling

---

## Next Steps

1. **Agent:** Execute RULE #0 verification and document results
2. **Agent:** Create conversion mapping with priorities
3. **Agent:** Convert first 3 HIGH-priority components
4. **Agent:** Generate output JSON and update mapping doc
5. **Team:** Review converted components and test manually
6. **Team:** Plan next batch of conversions (MEDIUM priority)

---

**End of Guide**
