/**
 * Tailwind configuration tailored for Uma Musume Planner
 * - darkMode: 'class' so app can toggle dark mode via a class on <body>
 * - content includes Blade templates and JS so PurgeCSS can find classes
 * - safelist contains patterns used dynamically by Livewire or generated classes
 */
module.exports = {
    darkMode: "class",
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./resources/assets/js/**/*.js",
        "./resources/views/**/*.php",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: "var(--color-primary)",
                secondary: "var(--color-secondary)",
                // Stat colors (FR-3.3)
                "stat-speed": "var(--color-stat-speed)",
                "stat-stamina": "var(--color-stat-stamina)",
                "stat-power": "var(--color-stat-power)",
                "stat-guts": "var(--color-stat-guts)",
                "stat-wisdom": "var(--color-stat-wisdom)",
                // Aptitude grade colors (Task 4.1.1)
                "aptitude-s": "#FFD700", // Gold
                "aptitude-a": "#FF6B6B", // Coral Red
                "aptitude-b": "#4ECDC4", // Teal
                "aptitude-c": "#45B7D1", // Sky Blue
                "aptitude-d": "#96CEB4", // Sage Green
                "aptitude-e": "#FFEAA7", // Pale Yellow
                "aptitude-f": "#DFE6E9", // Light Gray
                "aptitude-g": "#B2BEC3", // Gray
                // Uma Musume accent colors (Task 4.1.2)
                "uma-pink": "#FF69B4", // Hot Pink (primary accent)
                "uma-gold": "#FFD700", // Gold (achievements)
                "uma-blue": "#4169E1", // Royal Blue (racing)
                "uma-green": "#32CD32", // Lime Green (success)
                "uma-purple": "#9370DB", // Medium Purple (special)
                "uma-orange": "#FF8C00", // Dark Orange (energy)
                // Storage mode colors
                "storage-local": "#F59E0B", // Amber
                "storage-account": "#3B82F6", // Blue
            },
            ringColor: {
                primary: "var(--color-secondary)",
            },
            // Animation for reduced motion support (FR-14.5)
            animation: {
                "fade-in": "fadeIn 0.3s ease-in-out",
                "slide-up": "slideUp 0.3s ease-out",
                "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { transform: "translateY(10px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
            },
        },
    },
    plugins: [],
    safelist: [
        // Dynamic classes frequently used by Livewire or generated templates
        {
            pattern:
                /bg-(blue|red|green|amber|slate|primary|secondary)-(\d{3})?/,
        },
        {
            pattern:
                /text-(blue|red|green|amber|slate|primary|secondary)-(\d{3})?/,
        },
        // Aptitude grade classes
        {
            pattern: /bg-aptitude-(s|a|b|c|d|e|f|g)/,
        },
        {
            pattern: /text-aptitude-(s|a|b|c|d|e|f|g)/,
        },
        // Uma accent classes
        {
            pattern: /bg-uma-(pink|gold|blue|green|purple|orange)/,
        },
        {
            pattern: /text-uma-(pink|gold|blue|green|purple|orange)/,
        },
        // Storage mode classes
        "bg-storage-local",
        "bg-storage-account",
        "text-storage-local",
        "text-storage-account",
        "dark",
        "sr-only",
        "focus:not-sr-only",
        // Motion preference
        "motion-safe:animate-fade-in",
        "motion-safe:animate-slide-up",
        "motion-reduce:animate-none",
    ],
};
