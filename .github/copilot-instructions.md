# Copilot Instructions for Attendance SPA (Nuxt.js 2)

## Project Overview

This is a Nuxt.js 2 Single Page Application for attendance management with JWT-based authentication and role-based access control.

## Architecture & Key Patterns

### Authentication Flow

- **Store**: `store/auth.js` manages JWT tokens and user state using Vuex
- **Middleware**: Layer-based protection with specific execution order:
  - `middleware/auth.js` - Verifies JWT token validity
  - `middleware/guest.js` - Redirects authenticated users from login/register
  - `middleware/admin.js` - Admin role verification
  - `middleware/verified.js` - Email verification check
- **Layouts**: `layouts/auth.vue` for login/register, `layouts/default.vue` for authenticated pages

### Route Protection Pattern

Pages use middleware arrays for layered security:

```javascript
// Example: Admin-only page
middleware: ["auth", "verified", "admin"];
```

### State Management

- JWT tokens stored in Vuex (`store/auth.js`)
- User authentication state managed centrally
- No persistent storage configured (tokens lost on refresh)

## Key Files & Directories

### Core Authentication

- `store/auth.js` - Vuex store for authentication state
- `middleware/auth.js` - Primary authentication guard
- `layouts/auth.vue` - Layout for unauthenticated pages
- `pages/login.vue` & `pages/register.vue` - Authentication pages

### Configuration

- `nuxt.config.js` - Main Nuxt configuration with middleware and module setup
- `jsconfig.json` - IDE support for Nuxt aliases (@, ~~, @assets, @static)

## Development Patterns

### Middleware Usage

- Always use `middleware: ['auth']` for protected pages
- Chain middleware for specific requirements: `['auth', 'verified', 'admin']`
- Guest-only pages (login/register) use `middleware: ['guest']`

### Layout Selection

- Use `layout: 'auth'` for login/register pages
- Default layout includes authentication checks

### API Integration

- JWT tokens should be attached to API requests
- Handle token expiration in auth middleware
- Redirect to login on authentication failures

## Common Tasks

### Adding Protected Pages

1. Create page in `pages/`
2. Add appropriate middleware array
3. Use default layout (or specify custom)

### Adding Admin Features

- Use `middleware: ['auth', 'verified', 'admin']`
- Check user role in `store/auth.js`

### Debugging Authentication

- Check Vuex state in browser dev tools
- Verify middleware execution order
- Check JWT token presence and validity

## Important Notes

- This is Nuxt.js 2 (not Nuxt 3) - use appropriate syntax
- No SSR configuration visible - likely SPA mode
- Token persistence not implemented (consider adding localStorage/sessionStorage)
- Email verification system is referenced but implementation not visible
