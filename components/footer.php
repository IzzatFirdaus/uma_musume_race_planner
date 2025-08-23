<?php

// components/footer.php — Displays app metadata, version, and attribution
require_once __DIR__ . '/../includes/env.php';
load_env();
$appVersion = htmlspecialchars(getenv('APP_VERSION') ?: '1.4.0', ENT_QUOTES, 'UTF-8');
$lastUpdated = htmlspecialchars(getenv('LAST_UPDATED') ?: date('Y'), ENT_QUOTES, 'UTF-8');
?>
<footer class="text-center small mt-5" role="contentinfo" aria-label="Site footer">
  <div>
    <a href="https://github.com/IzzatFirdaus/uma_musume_race_planner"
       target="_blank" rel="noopener noreferrer"
       aria-label="Open Uma Musume Planner repository on GitHub in a new tab">
      <i class="bi bi-github me-1" aria-hidden="true"></i>
      <span class="visually-hidden">GitHub Repository:</span>
      Uma Musume Planner <?= $appVersion ?>
    </a>
    <span aria-hidden="true"> | </span>
    <span>Last Updated: <?= $lastUpdated ?></span>
  </div>

  <nav class="mt-2" aria-label="Official links">
    <a href="https://umamusume.jp/" target="_blank" rel="noopener noreferrer" hreflang="ja">JP Official Site</a>
    <span aria-hidden="true"> | </span>
    <a href="https://umamusume.com/" target="_blank" rel="noopener noreferrer" hreflang="en">Global Site (EN)</a>
    <span aria-hidden="true"> | </span>
    <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/"
       target="_blank" rel="noopener noreferrer">Steam Page</a>
  </nav>

  <div class="mt-2" aria-label="Social links">
    <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer" title="X / Twitter" aria-label="X / Twitter">
      <i class="bi bi-twitter-x me-2" aria-hidden="true"></i><span class="visually-hidden">X / Twitter</span>
    </a>
    <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer" title="Facebook" aria-label="Facebook">
      <i class="bi bi-facebook me-2" aria-hidden="true"></i><span class="visually-hidden">Facebook</span>
    </a>
    <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer" title="YouTube" aria-label="YouTube">
      <i class="bi bi-youtube me-2" aria-hidden="true"></i><span class="visually-hidden">YouTube</span>
    </a>
    <a href="https://discord.com/invite/umamusume-eng" target="_blank" rel="noopener noreferrer" title="Discord" aria-label="Discord">
      <i class="bi bi-discord" aria-hidden="true"></i><span class="visually-hidden">Discord</span>
    </a>
  </div>

  <div class="mt-2">
    <em>This fan-made planner is not affiliated with Cygames or the Uma Musume franchise. All trademarks and rights belong to their respective owners.</em>
  </div>
</footer>
