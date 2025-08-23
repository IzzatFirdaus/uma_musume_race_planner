# UIUX Phase 2 Implementation Plan
## Uma Musume Race Planner - MYDS Alignment

### **Overview**
Phase 2 focuses on implementing Malaysian Government Design System (MYDS) standards while preserving the Uma Musume game aesthetic. This phase ensures accessibility, consistency, and professional government-grade user experience.

---

## **1. Typography System**

### **Implementation Strategy**
- **Headings**: Poppins font family (MYDS standard)
- **Body Text**: Inter font family for readability
- **Rich Text**: Inter for long-form content

### **Typography Hierarchy**
```css
/* Headings - Poppins */
h1 { font-size: 36px; line-height: 44px; font-weight: 500; } /* Heading Medium */
h2 { font-size: 30px; line-height: 38px; font-weight: 500; } /* Heading Small */
h3 { font-size: 24px; line-height: 32px; font-weight: 500; } /* Heading Extra Small */
h4 { font-size: 20px; line-height: 28px; font-weight: 500; } /* Heading 2X Small */
h5 { font-size: 16px; line-height: 24px; font-weight: 500; } /* Heading 3X Small */
h6 { font-size: 14px; line-height: 20px; font-weight: 500; } /* Heading 4X Small */

/* Body Text - Inter */
body { font-size: 16px; line-height: 24px; font-weight: 400; } /* Body Medium */
.text-small { font-size: 14px; line-height: 20px; } /* Body Small */
.text-large { font-size: 18px; line-height: 26px; } /* Body Large */
```

### **Files to Update**
- `assets/css/style.css` - Add typography variables
- `components/header.php` - Update font imports
- All `.php` files - Apply typography classes

## Progress snapshot

Recent commits on `uiux-phase-2` (most recent first):

- `7aad4b7` uiux: inline plan details — form aria, required flags, and MYDS button styles
- `ca535a3` uiux: modals — add focus-trap helpers for keyboard accessibility
- `fd6d4ec` uiux: forms & modals — apply MYDS buttons, aria-required, and dialog semantics
- `8c2d68c` uiux: cards — apply MYDS card roles and accessibility improvements
- `05b9739` uiux: plan-list — MYDS button styles and ARIA improvements

Files already updated (partial):

- `components/plan-list.php` — buttons & ARIA
- `components/stats-panel.php` — roles & accessible canvas fallback
- `components/recent-activity.php` — roles, time element
- `plan_details_modal.php` — dialog aria, focus-trap
- `quick_create_plan_modal.php` — dialog aria, focus-trap
- `components/plan-inline-details.php` — form aria, required flags, button styles


---

## **2. Color System Enhancement**

### **Primitive Colors (Game-Inspired)**
```css
:root {
  /* Uma Musume Stat Colors */
  --color-speed: #3399ff;     /* Blue */
  --color-stamina: #33cc99;   /* Green */
  --color-power: #ff4d4d;     /* Red */
  --color-guts: #ffa500;      /* Orange */
  --color-wit: #9933ff;       /* Purple */
  
  /* MYDS Primary */
  --color-primary: #2563EB;   /* MYDS Blue */
  --color-success: #388E3C;   /* Success Green */
  --color-warning: #FFA000;   /* Warning Orange */
  --color-danger: #D32F2F;    /* Error Red */
}
```

### **Color Tokens (Light/Dark Mode)**
```css
/* Light Mode */
:root {
  --bg-primary: #FFFFFF;
  --bg-secondary: #F8F9FA;
  --bg-tertiary: #E9ECEF;
  --txt-primary: #212529;
  --txt-secondary: #6C757D;
  --txt-tertiary: #ADB5BD;
  --otl-primary: #DEE2E6;
  --otl-secondary: #CED4DA;
  --fr-primary: #0D6EFD;
}

/* Dark Mode */
body.dark-mode {
  --bg-primary: #121212;
  --bg-secondary: #1E1E1E;
  --bg-tertiary: #2D2D2D;
  --txt-primary: #F1F1F1;
  --txt-secondary: #B0B0B0;
  --txt-tertiary: #808080;
  --otl-primary: #404040;
  --otl-secondary: #555555;
  --fr-primary: #4285F4;
}
```

### **Files to Update**
- `assets/css/style.css` - Implement color tokens
- All component files - Apply semantic colors

---

## **3. Grid System Implementation**

### **MYDS 12-8-4 Grid System**
```css
/* Container */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 24px;
}

/* Desktop (≥1024px) - 12 columns */
@media (min-width: 1024px) {
  .grid-12 {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 24px;
  }
  .col-span-1 { grid-column: span 1; }
  .col-span-2 { grid-column: span 2; }
  /* ... up to col-span-12 */
}

/* Tablet (768px-1023px) - 8 columns */
@media (min-width: 768px) and (max-width: 1023px) {
  .grid-8 {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 24px;
  }
}

/* Mobile (≤767px) - 4 columns */
@media (max-width: 767px) {
  .grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
  }
  .container {
    padding: 0 18px;
  }
}
```

### **Files to Update**
- `assets/css/style.css` - Add grid system
- `public/index.php` - Apply grid layout
- `components/plan-list.php` - Responsive cards
- `plan_details_modal.php` - Form layout

---

## **4. Component Enhancement**

### **4.1 Button System**
```css
/* Primary Button */
.btn-primary {
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-weight: 500;
  font-size: 16px;
  line-height: 24px;
  transition: all 0.2s ease;
  min-height: 48px; /* Touch target */
}

.btn-primary:hover {
  background: #1D4ED8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Secondary Button */
.btn-secondary {
  background: transparent;
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  /* ... similar properties */
}

/* Stat-specific buttons */
.btn-speed { background: var(--color-speed); }
.btn-stamina { background: var(--color-stamina); }
.btn-power { background: var(--color-power); }
.btn-guts { background: var(--color-guts); }
.btn-wit { background: var(--color-wit); }
```

### **4.2 Card System**
```css
.card {
  background: var(--bg-primary);
  border: 1px solid var(--otl-primary);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 24px;
  transition: all 0.2s ease;
}

.card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.card-header {
  border-bottom: 1px solid var(--otl-secondary);
  padding-bottom: 16px;
  margin-bottom: 24px;
}
```

### **4.3 Form Controls**
```css
.form-control {
  background: var(--bg-primary);
  border: 2px solid var(--otl-primary);
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 16px;
  line-height: 24px;
  color: var(--txt-primary);
  transition: all 0.2s ease;
  min-height: 48px;
}

.form-control:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  outline: none;
}

.form-label {
  font-weight: 500;
  color: var(--txt-primary);
  margin-bottom: 8px;
  display: block;
}
```

### **Files to Update**
- `assets/css/style.css` - Component styles
- All form components - Apply new classes
- Button elements - Update styling

---

## **5. Accessibility Implementation**

### **5.1 ARIA Labels and Roles**
```html
<!-- Navigation -->
<nav role="navigation" aria-label="Main navigation">
  <ul role="menubar">
    <li role="none">
      <a role="menuitem" href="#" aria-current="page">Dashboard</a>
    </li>
  </ul>
</nav>

<!-- Forms -->
<label for="plan-name" class="form-label">Plan Name</label>
<input 
  id="plan-name" 
  type="text" 
  class="form-control"
  aria-describedby="plan-name-help"
  aria-required="true"
>
<div id="plan-name-help" class="form-help">
  Enter a descriptive name for your training plan
</div>

<!-- Buttons -->
<button 
  type="button" 
  class="btn-primary"
  aria-label="Save training plan"
  aria-describedby="save-status"
>
  Save Plan
</button>
```

### **5.2 Focus Management**
```css
/* Focus indicators */
.focus-visible:focus {
  outline: 3px solid var(--fr-primary);
  outline-offset: 2px;
}

/* Skip link */
.skip-link {
  position: absolute;
  top: -40px;
  left: 6px;
  background: var(--color-primary);
  color: white;
  padding: 8px;
  text-decoration: none;
  border-radius: 4px;
  z-index: 1000;
}

.skip-link:focus {
  top: 6px;
}
```

### **5.3 Keyboard Navigation**
```javascript
// Enhanced keyboard navigation
document.addEventListener('keydown', (e) => {
  // Tab navigation for modals
  if (e.key === 'Tab' && document.querySelector('.modal.show')) {
    trapFocus(e, '.modal.show');
  }
  
  // Escape to close modals
  if (e.key === 'Escape') {
    closeActiveModal();
  }
  
  // Arrow keys for tab navigation
  if (['ArrowLeft', 'ArrowRight'].includes(e.key)) {
    navigateTabs(e);
  }
});
```

### **Files to Update**
- All `.php` files - Add ARIA attributes
- `assets/js/` files - Enhance keyboard support
- `assets/css/style.css` - Focus styles

---

## **6. Motion System**

### **MYDS Motion Implementation**
```css
/* Easing curves */
:root {
  --ease-out: cubic-bezier(0, 0, 0.58, 1);
  --ease-out-back: cubic-bezier(0.34, 1.56, 0.64, 1);
  --duration-fast: 200ms;
  --duration-normal: 300ms;
  --duration-slow: 500ms;
}

/* Transitions */
.transition-smooth {
  transition: all var(--duration-normal) var(--ease-out);
}

.transition-bounce {
  transition: transform var(--duration-normal) var(--ease-out-back);
}

/* Animations */
@keyframes slideInUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slideInUp var(--duration-normal) var(--ease-out);
}
```

---

## **7. Implementation Timeline**

### **Week 1: Foundation**
- [ ] Setup typography system (Poppins + Inter)
- [ ] Implement color tokens and variables
- [ ] Create base grid system

### **Week 2: Components**
- [ ] Update button system
- [ ] Enhance card components
- [ ] Improve form controls

### **Week 3: Layout & Navigation**
- [ ] Apply grid system to main layouts
- [ ] Enhance navigation with MYDS patterns
- [ ] Improve responsive behavior

### **Week 4: Accessibility & Polish**
- [ ] Add ARIA labels and roles
- [ ] Implement keyboard navigation
- [ ] Add motion system
- [ ] Testing and refinement

---

## **8. Testing Checklist**

### **Accessibility Testing**
- [ ] Lighthouse accessibility score ≥90
- [ ] Keyboard navigation works completely
- [ ] Screen reader compatibility (NVDA/JAWS)
- [ ] Color contrast ratios meet WCAG 2.1 AA

### **Responsive Testing**
- [ ] Mobile (320px-767px)
- [ ] Tablet (768px-1023px)
- [ ] Desktop (1024px+)
- [ ] Touch targets ≥48px

### **Cross-browser Testing**
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### **Performance Testing**
- [ ] Page load time <3s
- [ ] First contentful paint <1.5s
- [ ] Cumulative layout shift <0.1

---

## **9. File Structure**

```
assets/
├── css/
│   ├── myds-base.css          # MYDS foundation styles
│   ├── myds-components.css    # Component library
│   ├── myds-utilities.css     # Utility classes
│   └── style.css              # Enhanced main styles
├── js/
│   ├── accessibility.js       # A11y enhancements
│   ├── keyboard-nav.js       # Keyboard navigation
│   └── motion.js             # Animation system
└── fonts/
    ├── poppins/              # Poppins font files
    └── inter/                # Inter font files
```

---

## **10. Success Metrics**

### **User Experience**
- Reduced time to complete tasks
- Improved user satisfaction scores
- Better mobile engagement

### **Technical**
- Lighthouse scores: Performance >90, Accessibility >95
- WCAG 2.1 AA compliance
- Cross-browser compatibility

### **Government Standards**
- MYDS compliance checklist 100%
- MyGovEA principle alignment
- PDPA compliance maintained

---

## **Next Steps**

1. **Create new branch**: `git checkout -b uiux-phase-2`
2. **Setup foundation**: Typography and color systems
3. **Component enhancement**: Systematic component updates
4. **Testing**: Continuous accessibility and responsive testing
5. **Documentation**: Update style guide and component docs

This plan ensures the Uma Musume Race Planner meets Malaysian government digital service standards while maintaining its game-inspired appeal and enhanced user experience.
