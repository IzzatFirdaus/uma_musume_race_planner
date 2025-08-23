<?php

/**
 * components/copy_to_clipboard.php
 *
 * Generates a formatted plain-text version of a training plan and copies it to clipboard.
 * - Accessibility: non-intrusive, does not auto-execute; exposes a single function on window.
 * - Security: safely injects server-side arrays via json_encode with HEX options.
 * - Robustness: handles empty data, provides fallbacks, and graceful error handling.
 *
 * Uses:
 * - TxtBuilder: modular utility for text-based tables
 * - Clipboard API with fallback error handling
 *
 * Injected server-side:
 * - $moodOptions, $strategyOptions, $conditionOptions
 *
 * Author: extremerazr
 * Updated: August 23, 2025
 */

// Compute base path for assets whether included from /public or root
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
$baseEsc = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
?>
<script type="application/json" id="copy-to-clipboard-options">
<?= json_encode([
  'moodOptions' => $moodOptions ?? [],
  'strategyOptions' => $strategyOptions ?? [],
  'conditionOptions' => $conditionOptions ?? [],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>
<script src="<?= $baseEsc ?>assets/js/copy_to_clipboard.js" defer></script>
