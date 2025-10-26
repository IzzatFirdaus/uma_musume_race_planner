# SPEC-05-UMA-TRACKER-UI-DESIGN

## 1. Purpose & Audience

Primary users: Uma Musume: Pretty Derby players who want to log career runs, track stats/skills, and plan SP usage.

**Goals:**

- Streamlined logging of career progress (turn-by-turn stats)
- Easy skill planning interface with SP cost & notes
- Exporting data in spreadsheet formats
- Clean, engaging UI styled after the game’s aesthetic

## 2. Visual & UX Inspirations from the Game

### Key UI Elements from Uma Musume

| Feature              | Description                                   |
| -------------------- | --------------------------------------------- |
| Character-Centric UI | Bright, dynamic character icons and colors.   |
| Stat Bars & Icons    | Visual stat meters with color coding.         |
| Tabbed Layout        | Separate views for Training, Race, Live Show. |
| Responsive Design    | Portrait-first UI with adaptive grid layouts. |

**Design takeaway:** Adopt a stat bar style with color-coded strength (Speed = blue, Stamina = green, etc.), and profile-style header cards for each Uma.

## 3. Layout & Component Designs

### 3.1 Dashboard

- Header: Welcome section, personalized greeting.
- Quick cards: Rounded, colored buttons linking to Uma List, Export pages, Form.
- Layout: Mobile-first grid → 1 column; desktop → 3 columns.
- Branding: Use Cygames-style vibrant hues (blue, green) with hover shadows.

### 3.2 Uma List

- Title: “🎴 Uma Musume List”
- Buttons: Preview and Export links styled with matching colored backgrounds.
- Cards: Name, style/track tags, and link to career view.
- Grid: Responsive (1–3 columns).
- Design: Hover transitions, soft shadows, consistent padding.

### 3.3 Career Run Form

**Form Sections:**

- Character info: Name, stage, condition.
- Stats: Inputs with inline color-coded mini-progress bars or placeholders.
- Suitability: Dropdowns with grade icons (A–G).
- Skills section: Dynamic rows with autocomplete skill name & SP cost.
- Interactivity: Alpine.js for add/remove skill rows, inline validation.
- Layout: Collapsibles or tabs to declutter long forms.
- Styling: Group borders, hover on rows, mobile-friendly input stacking.

## 4. Branding & Aesthetic

- **Color system:** Map stat categories to game palette: - Speed = blue, Stamina = green, Power = red, Guts = orange, Wit = purple.
- **Icons:** Use stat icons from game (⚡, 🛡️, 🔥, 💪, 🧠).
- **Typography:** Modern sans-serif (Figtree via Laravel template if present).
- **Animations:** Subtle transitions on buttons, card hovers, toggles.

## 5. Responsive Strategy

- **Mobile-first grid:** Flex layout shifting from single to multi‑column as screen widens.
- **Touch targets:** Ensure buttons and inputs ≥ 44px high.
- **Form UI:** Collapsible skill section or accordion on mobile to reduce scroll.
- **Fixed footer:** on mobile with “Save” call-to-action always visible.

## 6. Component Blueprint

- `x-card.blade.php`: Uma card with name, style badges, link.
- `x-stat-bar.blade.php`: Displays colored stat with value.
- `x-skill-row.blade.php`: Skill name, cost, acquired toggle, notes.
- `x-responsive-grid.blade.php`: Adaptive grid wrapper.

## 7. Iconography & Fonts

- **Custom icons:** Use game‑style visual stat icons (Speed ⚡ etc.).
- **Font pairing:** - Headlines: Figtree (or Inter, Montserrat if present). - Body: System UI or Figtree.
- **Buttons:** Rounded pills with accent background + drop‑shadow.

## 8. Accessibility & UX

- **Color contrast:** Follow WCAG AA; add text labels with colored stat bars.
- **Form labels:** Always visible, with inline error messages.
- **Keyboard usability:** Tab indexes, ARIA-labels on dynamic skill rows.

## 9. Prototyping & Implementation Flow

- **Wireframe mockups in Figma:** - Dashboard
- **Alpine.js prototyping:** - Skill row repeater - Stat bars - Modal previews
- **UI review:** Ensure mobile and desktop are visually consistent and intuitive.

## 10. Example Career Run Form Layout (Mobile)

<!-- markdownlint-disable -->

```text
[Shadowed card with Uma avatar and name]

📅 Career Stage: [Year 1 · Turn 3]
SPEED ⚡ [720 / 1200] ─────────▱────────
...
Turf Grade: [A]  Dirt Grade: [C]

🧱 Growth Rate: +10% Speed, +5% Guts
🌤️ Conditions: Practice Perfect

Skills:
[ Hydrate   | 20 SP | ✅ | "Recover" ]
[ + Add skill row ]

Total SP: 340

[ Submit ]
-------------------------------------------------
| ← Uma Tracker                   [Turn 12/70]  |
-------------------------------------------------
| Stamina ▶■■■■■□□□□□ (60%)                  |
-------------------------------------------------
| Stats:         Speed 850/1200 ●●●●●○○○○○    |
|                Stamina ...                 |
|                ...                          |
-------------------------------------------------
| Growth Rate: +10% Speed                     |
| Conditions: Light Rain + Boost              |
-------------------------------------------------
| Skills [ + Add Skill ]                      |
| ┌────────────────────────────────────────┐   |
| │ Skill Card 1: Hydrate | 20 SP [✓]     │   |
| │ Notes: Mid-race recovery              │   |
| ├────────────────────────────────────────┤   |
| │ Skill Card 2 ...                      │   |
| └────────────────────────────────────────┘   |
-------------------------------------------------
| Total SP: 340                              |
| [ Submit Career Run ]                      |
-------------------------------------------------
```

<!-- markdownlint-enable -->

## 11. Summary

The above design plan centers around:

- Visual harmony with Uma Musume game style,
- Responsive, clean UX across devices,
- Interactive components for easy data entry,
- Accessibility-first structure for all users.

With this foundation, development of a solid Figma prototype followed by
implementation using Blade, Bootstrap 5, custom CSS, and Alpine.js can bring
the Uma‑Tracker system to life with polish and practical elegance.
