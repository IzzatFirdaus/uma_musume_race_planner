<?php

// components/footer.php — Displays app metadata, version, and attribution

require_once __DIR__ . '/../includes/env.php';
load_env();
?>

<footer class="text-center small mt-5 v8-footer" role="contentinfo" aria-label="Site footer">
  <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 mb-2 v9-footer-actions">
    <a href="https://github.com/IzzatFirdaus/uma_musume_race_planner" target="_blank" rel="noopener noreferrer" class="v9-action-bubble stamina" style="min-width:44px; min-height:44px;" aria-label="GitHub">
      <i class="bi bi-github v9-action-icon" aria-hidden="true"></i>
      <span class="v9-action-label d-none d-md-block">GitHub</span>
    </a>
    <span class="d-none d-md-inline">|</span>
    <span class="v9-action-label">Last Updated: <?= htmlspecialchars(getenv('LAST_UPDATED') ?: '2025-09-01') ?></span>
  </div>

  <div class="mt-2">
    <!-- Official sites -->
    <a href="https://umamusume.jp/" target="_blank" rel="noopener noreferrer" class="v8-tap-feedback">JP Official Site</a>
    |
    <a href="https://umamusume.com/" target="_blank" rel="noopener noreferrer" class="v8-tap-feedback">Global Site (EN)</a>
    |
    <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/" target="_blank" rel="noopener noreferrer" class="v8-tap-feedback">Steam Page</a>
  </div>

  <div class="mt-2">
    <!-- Social icons -->
    <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer" title="X / Twitter" class="v8-tap-feedback">
      <i class="bi bi-twitter-x me-2" aria-hidden="true"></i>
    </a>
    <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer" title="Facebook" class="v8-tap-feedback">
      <i class="bi bi-facebook me-2" aria-hidden="true"></i>
    </a>
    <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer" title="YouTube" class="v8-tap-feedback">
      <i class="bi bi-youtube me-2" aria-hidden="true"></i>
    </a>
    <a href="https://discord.com/invite/umamusume-eng" target="_blank" rel="noopener noreferrer" title="Discord" class="v8-tap-feedback">
      <i class="bi bi-discord" aria-hidden="true"></i>
    </a>
  </div>

  <!--<div class="mt-2">
    <a href="guide.php">📘 Planner Guide</a>
  </div>-->

  <div class="mt-2">
    <em>This fan-made planner is not affiliated with Cygames or the Uma Musume franchise. All trademarks and rights belong to their respective owners.</em>
  </div>
</footer>
