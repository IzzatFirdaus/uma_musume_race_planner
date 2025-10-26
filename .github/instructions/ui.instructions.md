---
applyTo: "**"
---

# shadcn/ui LLM UI Development Instructions (2025)

_Last updated: September 2025_

---

## Overview

This document provides updated instructions for AI assistants and developers working with shadcn/ui components in our codebase. These guidelines incorporate the latest shadcn/ui features and best practices as of September 2025.

---

## Core Principles

- **Open Code Philosophy**: shadcn/ui components are open for modification and extension.
- **Always Fetch Latest**: Use the fetch tool to access current documentation from [https://ui.shadcn.com/docs/components](https://ui.shadcn.com/docs/components).
- **CLI-First Approach**: Use the shadcn CLI for all component operations.
- **Accessibility First**: All components must meet WCAG 2.2 AA accessibility standards.
- **AI-Ready Design**: Maintain clean, consistent code structure for AI compatibility.

---

## Version Compatibility

- **shadcn CLI**: 3.0+
- **React**: 19+
- **Tailwind CSS**: v4+
- **TypeScript**: 5.0+

---

## Quick Reference

### Installation Commands

```bash
# Add components
pnpm dlx shadcn@latest add <component-name>

# Add from specific registry
pnpm dlx shadcn@latest add @registry/namespace/component

# Search components
pnpm dlx shadcn@latest search <component-name>

# View component before installing
pnpm dlx shadcn@latest view @registry/namespace/component
```

### Common Components

```bash
# Basic UI components
pnpm dlx shadcn@latest add button
pnpm dlx shadcn@latest add card
pnpm dlx shadcn@latest add dialog
pnpm dlx shadcn@latest add input
pnpm dlx shadcn@latest add table

# Layout components
pnpm dlx shadcn@latest add sidebar
pnpm dlx shadcn@latest add scroll-area
pnpm dlx shadcn@latest add collapsible
```

---

## CLI 3.0 Features

### Namespaced Registries

```json
// components.json
{
    "registries": [
        "https://registry.company.com/components.json",
        "https://ui.shadcn.com/registries/default.json"
    ]
}
```

Install with namespaced format:

```bash
pnpm dlx shadcn@latest add @company/button
pnpm dlx shadcn@latest add @shadcn/calendar
```

### Private Registries Authentication

```json
// components.json
{
    "registries": [
        {
            "url": "https://registry.company.com/components.json",
            "auth": {
                "type": "bearer",
                "token": "${env.COMPANY_REGISTRY_TOKEN}"
            }
        }
    ]
}
```

### MCP Server Integration

```bash
# Start MCP server
npx shadcn registry:mcp

# Connect to MCP client
npx shadcn mcp:setup
```

---

## Component Management

### Adding Components

```bash
# Standard component addition
pnpm dlx shadcn@latest add button

# Specific variant
pnpm dlx shadcn@latest add button --variant new-york

# With dependencies
pnpm dlx shadcn@latest add calendar --with-dependencies

# Specific registry
pnpm dlx shadcn@latest add @company/special-button
```

### Updating Components

```bash
# Update all components
pnpm dlx shadcn@latest update

# Update specific component
pnpm dlx shadcn@latest update button

# Update to specific version
pnpm dlx shadcn@latest update button --version 1.2.0
```

### Migration Commands

```bash
# Migrate to radix-ui package
pnpm dlx shadcn@latest migrate-radix

# Migrate icons to Lucide
pnpm dlx shadcn@latest migrate-icons
```

---

## Project Structure

```
/src
	/components
		/ui          # shadcn/ui components
			button.tsx
			input.tsx
			...
		/custom      # Custom components
			...
	lib/utils.ts   # Utility functions
```

---

## Implementation Guidelines

### Import Patterns

```typescript
// Correct - import from local UI components
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

// Incorrect - import from package
import { Button } from "shadcn/ui/button";
```

### Component Customization

```typescript
// Extend base components with custom functionality
export const CustomButton = React.forwardRef<
	HTMLButtonElement,
	React.ComponentProps<typeof Button> & {
		customProp?: string
	}
>(({ className, customProp, ...props }, ref) => {
	return (
		<Button
			ref={ref}
			className={cn("custom-class", className)}
			{...props}
		/>
	)
})
```

### Theme Integration

```typescript
// Use CSS variables for theming
:root {
	--background: 0 0% 100%;
	--foreground: 222.2 84% 4.9%;
}

[data-theme="dark"] {
	--background: 222.2 84% 4.9%;
	--foreground: 210 40% 98%;
}
```

---

## AI Development Directives

### Component Generation

- Always use shadcn CLI for component addition
- Prefer existing components from registry before creating custom ones
- Follow established patterns in existing components
- Maintain consistent API surface across components

### Code Quality

```typescript
// Good - Proper typing and documentation
interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    /**
     * Button variant style
     * @default "default"
     */
    variant?:
        | "default"
        | "destructive"
        | "outline"
        | "secondary"
        | "ghost"
        | "link";
    /**
     * Button size
     * @default "default"
     */
    size?: "default" | "sm" | "lg" | "icon";
}

// Bad - Missing documentation and loose typing
interface ButtonProps {
    variant?: string;
    size?: string;
}
```

### Error Handling

- Provide clear error messages for missing dependencies
- Validate component props with TypeScript
- Include error boundaries for complex components
- Use shadcn CLI's improved error messaging

---

## Performance Optimization

### Bundle Size Management

```bash
# Analyze bundle size
pnpm dlx shadcn@latest analyze

# Tree-shake unused components
pnpm dlx shadcn@latest prune
```

### Dynamic Imports

```typescript
// Lazy load complex components
const HeavyComponent = React.lazy(() => import("@/components/ui/heavy-component"))

// With loading state
<Suspense fallback={<LoadingSpinner />}>
	<HeavyComponent />
</Suspense>
```

---

## Testing Guidelines

### Component Testing

```typescript
// Test component rendering and interactions
import { render, screen } from "@testing-library/react"
import userEvent from "@testing-library/user-event"
import { Button } from "@/components/ui/button"

test("button renders correctly", async () => {
	render(<Button>Click me</Button>)
	expect(screen.getByRole("button")).toBeInTheDocument()
})

test("button click handler works", async () => {
	const handleClick = jest.fn()
	render(<Button onClick={handleClick}>Click me</Button>)
	await userEvent.click(screen.getByRole("button"))
	expect(handleClick).toHaveBeenCalled()
})
```

### Accessibility Testing

```bash
# Run accessibility checks
pnpm dlx shadcn@latest check-a11y
```

---

## Registry Management

### Custom Registry Setup

```json
// components.json
{
    "$schema": "https://ui.shadcn.com/schema.json",
    "style": "default",
    "rsc": true,
    "tsx": true,
    "tailwind": {
        "config": "tailwind.config.ts",
        "css": "src/app/globals.css",
        "baseColor": "slate",
        "cssVariables": true
    },
    "registries": [
        {
            "name": "company",
            "url": "https://registry.company.com/components.json",
            "auth": {
                "type": "bearer",
                "token": "${env.REGISTRY_TOKEN}"
            }
        }
    ],
    "aliases": {
        "components": "@/components",
        "utils": "@/lib/utils"
    }
}
```

### Local Development

```bash
# Test local registry items
pnpm dlx shadcn@latest add ./local-components/button.json

# Develop with local registry
pnpm dlx shadcn@latest dev
```

---

## Troubleshooting

### Common Issues

1. **Missing Dependencies**: Use `--with-dependencies` flag
2. **Style Conflicts**: Check Tailwind configuration
3. **Type Errors**: Verify TypeScript version and types
4. **Registry Errors**: Check authentication and network connectivity

### Debug Commands

```bash
# Debug component installation
pnpm dlx shadcn@latest add button --verbose

# Check registry connectivity
pnpm dlx shadcn@latest ping-registry

# Validate component configuration
pnpm dlx shadcn@latest validate
```

---

## Resources

- **Official Documentation**: [https://ui.shadcn.com/docs](https://ui.shadcn.com/docs)
- **Changelog**: [https://ui.shadcn.com/docs/changelog](https://ui.shadcn.com/docs/changelog)
- **Registry Index**: [https://ui.shadcn.com/r/registries.json](https://ui.shadcn.com/r/registries.json)
- **GitHub Repository**: [https://github.com/shadcn/ui](https://github.com/shadcn/ui)
- **MCP Documentation**: [https://ui.shadcn.com/docs/mcp](https://ui.shadcn.com/docs/mcp)

---

## Compliance Checklist

- [ ] Components use local imports (`@/components/ui/`)
- [ ] CLI 3.0+ features are utilized where appropriate
- [ ] Accessibility standards are met (WCAG 2.2 AA)
- [ ] TypeScript types are properly defined
- [ ] Components work with both light and dark themes
- [ ] Error handling is implemented
- [ ] Performance best practices are followed
- [ ] Testing coverage is adequate
- [ ] Documentation is complete and current

---

_This document is automatically validated against the latest shadcn/ui documentation. Last validation: 2025-09-21_
