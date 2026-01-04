# Accessibility (WCAG 2.2) Guidelines

This document outlines the accessibility standards and best practices implemented in the Uma Musume Planner Laravel application. All components and pages must adhere to **WCAG 2.2 Level AA** compliance.

## Standards & References

This project implements accessibility guidelines from:

- **WCAG 2.2 Level AA**: Web Content Accessibility Guidelines (<https://www.w3.org/WAI/WCAG22/quickref/>)
- **ARIA Authoring Practices Guide (APG)**: <https://www.w3.org/WAI/ARIA/apg/>
- **MDN Web Docs**: Accessibility <https://developer.mozilla.org/en-US/docs/Web/Accessibility>
- **Tailwind CSS**: Accessibility utilities and dark mode support
- **Blade Components**: Semantic HTML and Livewire integration

## Core Accessibility Principles

### 1. Semantic HTML

Use native HTML elements for their intended purpose. Avoid divs when semantic elements are appropriate:

```blade
<!-- ❌ Bad: divs without semantics -->
<div class="header">Site Header</div>
<div role="navigation">Menu</div>

<!-- ✅ Good: semantic HTML -->
<header role="banner">Site Header</header>
<nav role="navigation" aria-label="Main navigation">Menu</nav>
```

**Required Landmarks:**

- `<header role="banner">` — Page header
- `<nav role="navigation">` with `aria-label` — Navigation regions
- `<main id="main">` — Main content area
- `<footer role="contentinfo">` — Page footer

### 2. Keyboard Navigation

All interactive elements must be keyboard accessible without relying on mouse:

```blade
<!-- ✅ Native elements are keyboard accessible by default -->
<button>Click me</button>
<a href="/page">Link</a>
<input type="text" />

<!-- ⚠️ If using divs, add tabindex and keyboard handlers -->
<div role="button" tabindex="0" @keydown.enter="action()">Custom Button</div>
```

**Tab Order:**

1. Skip link (first focusable element)
2. Header navigation
3. Main content (form fields, buttons, links)
4. Footer navigation
5. Use `tabindex="0"` only to make elements focusable in order
6. Avoid `tabindex="-1"` on interactive elements
7. Never use `tabindex > 0` (breaks logical tab order)

### 3. Focus Management

All interactive elements must have visible focus indicators:

```blade
<!-- Blade component includes focus-visible styles -->
<x-button type="primary">Click Me</x-button>

<!-- ✅ CSS: Focus visible indicator (Tailwind utility) -->
<!-- focus-visible:outline-2 focus-visible:outline-offset-2 -->
```

**Verification:**

- Tab through the page with keyboard
- All interactive elements should show clear focus ring
- Focus outline should have minimum 2px width
- Focus should be clearly visible in both light and dark modes

### 4. Form Labels & Descriptions

All form inputs must have associated labels:

```blade
<!-- ✅ Correct: Input with associated label -->
<label for="email">Email Address</label>
<input type="email" id="email" name="email" />

<!-- ✅ Error descriptions linked via aria-describedby -->
<label for="password">Password</label>
<input
  type="password"
  id="password"
  name="password"
  aria-describedby="password-error"
/>
<div id="password-error" role="alert">
  Password must be at least 8 characters
</div>
```

**Component Usage:**

```blade
<!-- Use the accessible input component -->
<x-input
  name="email"
  type="email"
  label="Email Address"
  error="{{ $errors->first('email') }}"
/>
```

### 5. Color Contrast

All text must meet WCAG AA contrast ratios:

- **Normal text**: Minimum 4.5:1 ratio
- **Large text** (18pt+ or 14pt+ bold): Minimum 3:1 ratio

**Tailwind Classes:**

```blade
<!-- ✅ Good contrast combinations -->
<p class="text-gray-900 dark:text-white">Dark text on light, light on dark</p>
<button class="bg-blue-600 text-white dark:bg-blue-500">High contrast</button>

<!-- ❌ Avoid low contrast -->
<p class="text-gray-400 bg-gray-100">Too light - low contrast</p>
```

Test with: [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

### 6. Skip Links

Every page must include a skip link (first focusable element):

```blade
<!-- In layout.blade.php -->
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-0 focus:left-0 focus:z-50 focus:bg-blue-600 focus:text-white focus:p-2">
  Skip to main content
</a>
```

**Behavior:**

- Hidden visually (`sr-only`) until focused
- First tab on page shows it
- Clicking navigates to `<main id="main">`

### 7. Accessible Images

All images must have descriptive alt text or be marked as decorative:

```blade
<!-- ✅ Descriptive alt text -->
<img src="hero.jpg" alt="Team members collaborating on project plan" />

<!-- ✅ Decorative image (marked as such) -->
<img src="separator.png" alt="" role="presentation" />

<!-- ❌ Avoid generic alt text -->
<img src="photo.jpg" alt="image" />
```

### 8. Status Messages & Alerts

Status messages must be announced to screen readers:

```blade
<!-- ✅ Using accessible alert component -->
<x-alert type="success" dismissible>
  Your plan has been saved successfully!
</x-alert>

<!-- Component includes: -->
<!-- role="status" aria-live="polite" aria-atomic="true" -->
```

**Alert Types:**

- `type="success"` — Polite announcement for positive feedback
- `type="error"` — Assertive announcement for errors
- `type="warning"` — Assertive announcement for warnings
- `type="info"` — Polite announcement for information

### 9. Accessible Names

All buttons, links, and icons must have accessible names:

```blade
<!-- ✅ Button with visible text (best) -->
<button>Save Changes</button>

<!-- ✅ Icon button with aria-label -->
<button aria-label="Close dialog">
  <svg><!-- Close icon --></svg>
</button>

<!-- ✅ Link with clear text or aria-label -->
<a href="/settings">Settings</a>
<a href="/profile" aria-label="User Profile">👤</a>

<!-- ❌ Avoid -->
<button>Click here</button>  <!-- Unclear -->
<a href="/page">→</a>  <!-- No accessible name -->
```

### 10. Text Sizing & Spacing

Users must be able to resize text and adjust spacing without loss of functionality:

- Text should reflow at 200% zoom without horizontal scrolling
- Support line-height increase for dyslexia-friendly reading
- Don't rely on visual layout that breaks with larger text

```blade
<!-- ✅ Flexible layouts -->
<div class="flex flex-wrap gap-4">
  <!-- Uses gap instead of fixed margins -->
</div>

<!-- ✅ Responsive text sizing -->
<h1 class="text-2xl sm:text-3xl md:text-4xl">Heading</h1>
```

## Livewire Status Region

Every page must include a global status region for Livewire announcements (included in layout.blade.php):

```blade
<!-- In resources/views/components/layout.blade.php -->
<div id="livewire-status" class="sr-only" aria-live="polite" aria-atomic="true" role="status"></div>
```

When Livewire components update page state, write to this region:

```blade
<!-- Livewire component -->
<div wire:model="skillCount">
  <p>Skills: {{ $skillCount }}</p>
</div>

<!-- Script to announce updates -->
<script>
document.addEventListener('livewire:updated', () => {
  const statusRegion = document.getElementById('livewire-status');
  if (statusRegion) {
    statusRegion.textContent = `Skills count updated to ${skillCount}`;
  }
});
</script>
```

### Running Accessibility Tests

**1. Automated Axe Tests (Playwright):**

```bash
# Run all accessibility tests
npm run test:a11y

# Run with verbose output
npx playwright test tests/playwright/accessibility.spec.ts --reporter=list

# Run single test
npx playwright test --grep "skip link"

# Debug test
npx playwright test --debug tests/playwright/accessibility.spec.ts
```

**Test Coverage:**

- ✅ No critical axe violations
- ✅ Keyboard navigation (Tab, Escape)
- ✅ Focus indicators visible
- ✅ Semantic landmarks present
- ✅ Form labels associated
- ✅ No keyboard traps
- ✅ Button accessible names
- ✅ Image alt text present
- ✅ Text resizable without scrolling
- ✅ Skip link functional

**2. Manual Testing:**

```bash
# Keyboard only testing
1. Start dev server: npm run dev
2. Navigate to http://localhost:5173
3. Press Tab repeatedly - verify all interactive elements are reachable
4. Press Enter/Space on buttons - verify they work
5. Press Escape - verify modals close
6. Verify focus ring is visible at each step
```

**3. Screen Reader Testing:**

Recommended free screen readers:

- **Windows**: NVDA (<https://www.nvaccess.org/>)
- **macOS**: VoiceOver (built-in, press Cmd+F5 to enable)
- **Browser**: Enable browser accessibility inspector

```bash
# NVDA testing
1. Install NVDA from https://www.nvaccess.org/download/
2. Start NVDA (Ctrl+Alt+N)
3. Navigate page with arrow keys
4. Listen for landmark announcements: "navigation", "main", "contentinfo"
5. Fill form fields - verify labels are read
```

**4. Lighthouse CI (Performance + Accessibility):**

```bash
# Install Lighthouse CI
npm install -g @lhci/cli@latest

# Run Lighthouse checks
npm run lighthouse:ci

# View results
npx lhci open
```

Baseline targets:

- **Performance**: LCP < 2.5s, CLS < 0.1, TBT < 200ms
- **Accessibility**: 95+ score
- **Best Practices**: 90+ score

### Accessibility Audits with Axe DevTools

**Browser Extension:**

1. Install Axe DevTools (Chrome, Firefox, Edge)
2. Open DevTools (F12)
3. Go to Axe DevTools tab
4. Click "Scan ALL of my page"
5. Review violations and best practice items

**Command-line (via Playwright):**

```bash
npx playwright test tests/playwright/accessibility.spec.ts --grep "axe violations"
```

## Component Reference

### Button Component (`x-button`)

```blade
<x-button
  type="button"          <!-- button|submit|reset -->
  variant="primary"      <!-- primary|secondary|danger|ghost -->
  size="md"              <!-- sm|md|lg|icon -->
  disabled
  aria-label="Actions"   <!-- Optional: for icon-only buttons -->
>
  Save Changes
</x-button>
```

**Accessibility Features:**

- ✅ Native `<button>` element (keyboard accessible)
- ✅ Focus visible (outline-2, offset)
- ✅ Hover and active states
- ✅ Disabled state with visual indicator
- ✅ Supports aria-label for icon buttons

### Input Component (`x-input`)

```blade
<x-input
  name="email"
  type="email"
  label="Email Address"
  placeholder="user@example.com"
  error="{{ $errors->first('email') }}"
  required
  aria-label="Email Address"
/>
```

**Accessibility Features:**

- ✅ Associated label with `for` attribute
- ✅ Error messages in `aria-describedby`
- ✅ Errors announced with `role="alert"`
- ✅ Required indicator
- ✅ Focus-visible styles

### Alert Component (`x-alert`)

```blade
<x-alert type="success" dismissible>
  Your changes have been saved!
</x-alert>
```

**Alert Types:**

- `type="success"` — `role="status"` aria-live="polite"
- `type="error"` — `role="alert"` aria-live="assertive"
- `type="warning"` — `role="alert"` aria-live="assertive"
- `type="info"` — `role="status"` aria-live="polite"

**Accessibility Features:**

- ✅ Correct ARIA role and aria-live region
- ✅ aria-atomic="true" (announce entire message)
- ✅ Dismissible button with aria-label
- ✅ Icon with color (not color-only indicator)

### Layout Component (`x-layout`)

```blade
<x-layout title="Page Title">
  <!-- Content -->
</x-layout>
```

**Accessibility Features:**

- ✅ Skip link (first focusable)
- ✅ Semantic landmarks (header, nav, main, footer)
- ✅ Page title in `<title>` and `<h1>`
- ✅ Livewire integration
- ✅ Dark mode support

## Common Accessibility Issues & Fixes

### Issue: Focus not visible on custom components

**❌ Problem:**

```blade
<div class="custom-button">Click me</div>
```

**✅ Solution:**

```blade
<button class="custom-button">Click me</button>
<!-- OR if must use div: -->
<div
  class="custom-button"
  role="button"
  tabindex="0"
  @keydown.enter="action()"
  @keydown.space="action()"
>
  Click me
</div>
```

### Issue: Form labels not associated

**❌ Problem:**

```blade
<label>Email</label>
<input type="email" />
```

**✅ Solution:**

```blade
<label for="email">Email</label>
<input type="email" id="email" />
```

### Issue: Images without alt text

**❌ Problem:**

```blade
<img src="hero.jpg" />
```

**✅ Solution:**

```blade
<!-- Informative image -->
<img src="hero.jpg" alt="Two team members discussing project timeline" />

<!-- Decorative image -->
<img src="divider.png" alt="" role="presentation" />
```

### Issue: Color-only status indicators

**❌ Problem:**

```blade
<div class="text-red-600">Error occurred</div> <!-- Red color only -->
```

**✅ Solution:**

```blade
<div class="flex items-center gap-2 text-red-600">
  <svg class="w-4 h-4" aria-hidden="true"><!-- Error icon --></svg>
  <span>Error occurred</span>
</div>
```

## Resources & Tools

**Standards & Guidelines:**

- WCAG 2.2: <https://www.w3.org/WAI/WCAG22/quickref/>
- ARIA APG: <https://www.w3.org/WAI/ARIA/apg/>
- MDN Accessibility: <https://developer.mozilla.org/en-US/docs/Web/Accessibility>

**Testing Tools:**

- Axe DevTools: <https://www.deque.com/axe/devtools/>
- WAVE Browser Extension: <https://wave.webaim.org/extension/>
- Lighthouse: Chrome DevTools (built-in, F12)
- NVDA Screen Reader: <https://www.nvaccess.org/>
- WebAIM Contrast Checker: <https://webaim.org/resources/contrastchecker/>

**Learning Resources:**

- WebAIM Articles: <https://webaim.org/articles/>
- Deque University: <https://dequeuniversity.com/>
- A11ycasts (Google): <https://www.youtube.com/playlist?list=PLNYkxOF6rcICWx0C9Xc-RgEzwLvePng7V>
- Tailwind Accessibility: <https://tailwindcss.com/docs/responsive-design#accessibility>

## Contributing

When adding new components or pages:

1. ✅ Use semantic HTML elements
2. ✅ Test with keyboard only (Tab, Enter, Escape)
3. ✅ Verify focus indicators are visible
4. ✅ Run `npm run test:a11y` before committing
5. ✅ Check color contrast (WCAG AA minimum)
6. ✅ Add descriptive alt text to images
7. ✅ Test with screen reader (NVDA or VoiceOver)
8. ✅ Update this documentation if adding new patterns

## Maintenance

**Regular Accessibility Audits:**

```bash
# Weekly during development
npm run test:a11y

# Before releases
npm run lighthouse:ci
npx axe-core your-page-url  # Manual check

# Regression prevention
git pre-commit hook runs: npm run test:a11y
```

**Tracking Issues:**

- Label GitHub issues with `accessibility` or `a11y`
- Reference specific WCAG criterion (e.g., "WCAG 2.2 1.4.3 Contrast")
- Include steps to reproduce with assistive technology
- Add screenshots or video recordings

---

**Last Updated:** 2025

**Maintained By:** Development Team

**Questions?** Check WCAG 2.2 quickref or create an issue tagged `accessibility`.
