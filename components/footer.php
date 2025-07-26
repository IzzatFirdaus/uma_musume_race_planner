<?php

// This component reads metadata from the environment, which is loaded by db.php.
?>
<footer class="text-center py-3 mt-5">
  <div class="text-muted">
    Uma Musume Planner <?= htmlspecialchars(getenv('APP_VERSION') ?: '1.0.0') ?> | Last Updated: <?= htmlspecialchars(getenv('LAST_UPDATED') ?: '2025') ?>
  </div>
</footer>