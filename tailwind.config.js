/**
 * Tailwind configuration tailored for Uma Musume Planner
 * - darkMode: 'class' so app can toggle dark mode via a class on <body>
 * - content includes Blade templates, JS, and Livewire components for PurgeCSS
 * - safelist contains patterns used dynamically by Livewire or generated classes
 *
 * Requirements: 7.3, 8.5, 31.4, 44.1, 44.5
 */
module.exports = {
    darkMode: "class",
    content: [
        // Blade templates
        "./resources/views/**/*.blade.php",
        // JavaScript files
        "./resources/js/**/*.js",
        "./resources/assets/js/**/*.js",
        // PHP view files
        "./resources/views/**/*.php",
        // Vue components (if any)
        "./resources/**/*.vue",
        // Livewire components (server-side classes that may contain Tailwind classes)
        "./app/Livewire/**/*.php",
        // Blade view components
        "./app/View/Components/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                // Brand colors (CSS variable-based for theme switching)
                primary: "var(--color-primary)",
                secondary: "var(--color-secondary)",

                // Stat colors - Game-accurate (Req 7.3)
                // These use CSS variables for consistency with existing style.css
                stat: {
                    speed: "#3399ff", // Speed (Blue) - var(--color-stat-speed)
                    stamina: "#33cc99", // Stamina (Green) - var(--color-stat-stamina)
                    power: "#ff4d4d", // Power (Red) - var(--color-stat-power)
                    guts: "#ffa500", // Guts (Orange) - var(--color-stat-guts)
                    wit: "#9933ff", // Wit/Wisdom (Purple) - var(--color-stat-wisdom)
                },

                // Aptitude grade colors - Game-accurate (Req 8.5)
                grade: {
                    SS: "#e5e7eb", // Platinum/Light Gray
                    S: "#ffd700", // Gold
                    A: "#ef4444", // Red
                    B: "#f97316", // Orange
                    C: "#22c55e", // Green
                    D: "#3b82f6", // Blue
                    E: "#a855f7", // Purple
                    F: "#6b7280", // Gray
                    G: "#9ca3af", // Dark Gray
                },

                // Skill tier colors - Full tier range G- to SS (Req 44.1, 44.5)
                tier: {
                    SS: "#ffd700", // Gold (reserved for max stats 1200)
                    "S-plus": "#ffed4e", // Light Gold
                    S: "#ffd700", // Gold
                    "S-minus": "#e6c200", // Dark Gold
                    "A-plus": "#ff6b6b", // Light Red
                    A: "#ef4444", // Red
                    "A-minus": "#dc2626", // Dark Red
                    "B-plus": "#fb923c", // Light Orange
                    B: "#f97316", // Orange
                    "B-minus": "#ea580c", // Dark Orange
                    "C-plus": "#4ade80", // Light Green
                    C: "#22c55e", // Green
                    "C-minus": "#16a34a", // Dark Green
                    "D-plus": "#60a5fa", // Light Blue
                    D: "#3b82f6", // Blue
                    "D-minus": "#2563eb", // Dark Blue
                    "E-plus": "#c084fc", // Light Purple
                    E: "#a855f7", // Purple
                    "E-minus": "#9333ea", // Dark Purple
                    "F-plus": "#9ca3af", // Light Gray
                    F: "#6b7280", // Gray
                    "F-minus": "#4b5563", // Dark Gray
                    "G-plus": "#d1d5db", // Very Light Gray
                    G: "#9ca3af", // Dark Gray
                    "G-minus": "#6b7280", // Very Dark Gray
                },

                // Mood colors - Game-accurate (Req 31.4)
                mood: {
                    great: "#22c55e", // Green - Great (+4%)
                    good: "#84cc16", // Lime - Good (+2%)
                    normal: "#6b7280", // Gray - Normal (0%)
                    bad: "#f97316", // Orange - Bad (-2%)
                    awful: "#ef4444", // Red - Awful (-4%)
                },

                // Status colors for plan states
                status: {
                    "in-progress": "#3b82f6", // Blue
                    completed: "#22c55e", // Green
                    archived: "#6b7280", // Gray
                },

                // Storage mode colors (Req 56.4 - visual distinction)
                storage: {
                    local: "#f59e0b", // Amber - Local storage
                    account: "#8b5cf6", // Purple - Account/database storage
                },

                // Legacy compatibility - keep existing color names
                "stat-speed": "var(--color-stat-speed)",
                "stat-stamina": "var(--color-stat-stamina)",
                "stat-power": "var(--color-stat-power)",
                "stat-guts": "var(--color-stat-guts)",
                "stat-wisdom": "var(--color-stat-wisdom)",

                // Uma Musume accent colors
                "uma-pink": "#FF69B4",
                "uma-gold": "#FFD700",
                "uma-blue": "#4169E1",
                "uma-green": "#32CD32",
                "uma-purple": "#9370DB",
                "uma-orange": "#FF8C00",

                // Legacy storage mode colors (flat naming)
                "storage-local": "#F59E0B",
                "storage-account": "#8b5cf6",
            },
            ringColor: {
                primary: "var(--color-secondary)",
            },
            // Animation for reduced motion support (FR-14.5)
            animation: {
                "fade-in": "fadeIn 0.3s ease-in-out",
                "slide-up": "slideUp 0.3s ease-out",
                "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                "spin-slow": "spin 2s linear infinite",
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

        // Stat color classes (text-stat-*, bg-stat-*, border-stat-*)
        "text-stat-speed",
        "text-stat-stamina",
        "text-stat-power",
        "text-stat-guts",
        "text-stat-wit",
        "bg-stat-speed",
        "bg-stat-stamina",
        "bg-stat-power",
        "bg-stat-guts",
        "bg-stat-wit",
        "border-stat-speed",
        "border-stat-stamina",
        "border-stat-power",
        "border-stat-guts",
        "border-stat-wit",

        // Grade color classes (text-grade-*, bg-grade-*)
        "text-grade-SS",
        "text-grade-S",
        "text-grade-A",
        "text-grade-B",
        "text-grade-C",
        "text-grade-D",
        "text-grade-E",
        "text-grade-F",
        "text-grade-G",
        "bg-grade-SS",
        "bg-grade-S",
        "bg-grade-A",
        "bg-grade-B",
        "bg-grade-C",
        "bg-grade-D",
        "bg-grade-E",
        "bg-grade-F",
        "bg-grade-G",

        // Tier color classes (text-tier-*, bg-tier-*)
        "text-tier-SS",
        "text-tier-S-plus",
        "text-tier-S",
        "text-tier-S-minus",
        "text-tier-A-plus",
        "text-tier-A",
        "text-tier-A-minus",
        "text-tier-B-plus",
        "text-tier-B",
        "text-tier-B-minus",
        "text-tier-C-plus",
        "text-tier-C",
        "text-tier-C-minus",
        "text-tier-D-plus",
        "text-tier-D",
        "text-tier-D-minus",
        "text-tier-E-plus",
        "text-tier-E",
        "text-tier-E-minus",
        "text-tier-F-plus",
        "text-tier-F",
        "text-tier-F-minus",
        "text-tier-G-plus",
        "text-tier-G",
        "text-tier-G-minus",
        "bg-tier-SS",
        "bg-tier-S-plus",
        "bg-tier-S",
        "bg-tier-S-minus",
        "bg-tier-A-plus",
        "bg-tier-A",
        "bg-tier-A-minus",
        "bg-tier-B-plus",
        "bg-tier-B",
        "bg-tier-B-minus",
        "bg-tier-C-plus",
        "bg-tier-C",
        "bg-tier-C-minus",
        "bg-tier-D-plus",
        "bg-tier-D",
        "bg-tier-D-minus",
        "bg-tier-E-plus",
        "bg-tier-E",
        "bg-tier-E-minus",
        "bg-tier-F-plus",
        "bg-tier-F",
        "bg-tier-F-minus",
        "bg-tier-G-plus",
        "bg-tier-G",
        "bg-tier-G-minus",

        // Mood color classes (text-mood-*, bg-mood-*)
        "text-mood-great",
        "text-mood-good",
        "text-mood-normal",
        "text-mood-bad",
        "text-mood-awful",
        "bg-mood-great",
        "bg-mood-good",
        "bg-mood-normal",
        "bg-mood-bad",
        "bg-mood-awful",

        // Storage mode classes (text-storage-*, bg-storage-*)
        "text-storage-local",
        "text-storage-account",
        "bg-storage-local",
        "bg-storage-account",
        "border-storage-local",
        "border-storage-account",

        // Status classes
        "text-status-in-progress",
        "text-status-completed",
        "text-status-archived",
        "bg-status-in-progress",
        "bg-status-completed",
        "bg-status-archived",

        // Legacy aptitude grade classes (for backward compatibility)
        { pattern: /bg-aptitude-(s|a|b|c|d|e|f|g)/ },
        { pattern: /text-aptitude-(s|a|b|c|d|e|f|g)/ },

        // Uma accent classes
        { pattern: /bg-uma-(pink|gold|blue|green|purple|orange)/ },
        { pattern: /text-uma-(pink|gold|blue|green|purple|orange)/ },

        // Utility classes
        "dark",
        "sr-only",
        "focus:not-sr-only",

        // Motion preference classes
        "motion-safe:animate-fade-in",
        "motion-safe:animate-slide-up",
        "motion-reduce:animate-none",
    ],
};
