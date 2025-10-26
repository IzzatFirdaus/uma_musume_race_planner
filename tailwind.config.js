/**
 * Tailwind configuration tailored for Uma Musume Planner
 * - darkMode: 'class' so app can toggle dark mode via a class on <body>
 * - content includes Blade templates and JS so PurgeCSS can find classes
 * - safelist contains patterns used dynamically by Livewire or generated classes
 */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/assets/js/**/*.js',
    './resources/views/**/*.php',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary)',
        secondary: 'var(--color-secondary)',
        'stat-speed': 'var(--color-stat-speed)',
        'stat-stamina': 'var(--color-stat-stamina)',
        'stat-power': 'var(--color-stat-power)',
        'stat-guts': 'var(--color-stat-guts)',
        'stat-wisdom': 'var(--color-stat-wisdom)',
      },
      ringColor: {
        primary: 'var(--color-secondary)'
      }
    },
  },
  plugins: [],
  safelist: [
    // Dynamic classes frequently used by Livewire or generated templates
    {
      pattern: /bg-(blue|red|green|amber|slate|primary|secondary)-(\d{3})?/, // e.g. bg-blue-600
    },
    {
      pattern: /text-(blue|red|green|amber|slate|primary|secondary)-(\d{3})?/, // e.g. text-slate-700
    },
    'dark',
    'sr-only',
    'focus:not-sr-only'
  ],
};
