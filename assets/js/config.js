// Centralized frontend config (General Best Practices: single source of truth, safe defaults)
// APP_CONFIG.API_BASE should point to the site-root API folder (no trailing slash).
// Backward compatibility: preserves window.APP_API_BASE

/* eslint-env browser */
(() => {
    'use strict';

  // NOTE: must match project folder name under htdocs (uma_musume_race_planner)
  const DEFAULT_API_BASE = '/uma_musume_race_planner/api';

  // If server-side sets window.APP_API_BASE, use it; otherwise use default
    const legacyBase = (typeof window.APP_API_BASE === 'string' && window.APP_API_BASE) || DEFAULT_API_BASE;

  // Normalize to remove trailing slash
    const normalized = legacyBase.replace(/\/+$/, '');

  // Modern config object
    window.APP_CONFIG = Object.freeze({
        API_BASE: normalized,
    });

  // Keep legacy global for existing code paths
  window.APP_API_BASE = normalized;
})();