<?php

// components/footer.php — Displays app metadata, version, and attribution

require_once __DIR__ . '/../includes/env.php';
load_env();
?>

<footer class="text-center small mt-5">
  <div class="font-medium">
    <a href="https://github.com/IzzatFirdaus/uma_musume_race_planner" target="_blank" rel="noopener noreferrer">
      <i class="bi bi-github me-1" aria-hidden="true"></i> Uma Musume Planner <?= htmlspecialchars(getenv('APP_VERSION') ?: '1.4.0') ?>
    </a>
    <span class="text-muted small"> | Last Updated: <?= htmlspecialchars(getenv('LAST_UPDATED') ?: '2025') ?></span>
  </div>

  <div class="mt-2">
    <!-- Official sites -->
    <a href="https://umamusume.jp/" target="_blank" rel="noopener noreferrer">JP Official Site</a>
    |
    <a href="https://umamusume.com/" target="_blank" rel="noopener noreferrer">Global Site (EN)</a>
    |
    <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/" target="_blank" rel="noopener noreferrer">Steam Page</a>
  </div>

  <div class="mt-2">
    <!-- Social icons -->
    <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer" title="X / Twitter">
      <i class="bi bi-twitter-x me-2" aria-hidden="true"></i>
    </a>
    <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer" title="Facebook">
      <i class="bi bi-facebook me-2" aria-hidden="true"></i>
    </a>
    <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer" title="YouTube">
      <i class="bi bi-youtube me-2" aria-hidden="true"></i>
    </a>
    <a href="https://discord.com/invite/umamusume-eng" target="_blank" rel="noopener noreferrer" title="Discord">
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