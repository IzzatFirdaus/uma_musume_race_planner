<?php
// components/v9-action-row.php
// Sticky bottom row with color-coded action bubbles for training mechanics
?>
<div class="v9-sticky-actions d-md-none" role="toolbar" aria-label="Training actions">
  <div class="container d-flex justify-content-between align-items-center py-2">
    <button type="button" class="v9-action-bubble speed v8-tap-feedback" aria-label="Speed training" data-action="speed" title="Speed training" tabindex="0">
      <span class="v9-action-icon" aria-hidden="true">⚡</span>
    </button>
    <button type="button" class="v9-action-bubble stamina v8-tap-feedback" aria-label="Stamina / Rest" data-action="stamina">
      <span class="v9-action-icon" aria-hidden="true">🛡️</span>
    </button>
    <button type="button" class="v9-action-bubble power v8-tap-feedback" aria-label="Power training" data-action="power">
      <span class="v9-action-icon" aria-hidden="true">🔥</span>
    </button>
    <button type="button" class="v9-action-bubble guts v8-tap-feedback" aria-label="Guts training" data-action="guts">
      <span class="v9-action-icon" aria-hidden="true">💪</span>
    </button>
    <button type="button" class="v9-action-bubble wit v8-tap-feedback" aria-label="Wit / Intelligence" data-action="wit">
      <span class="v9-action-icon" aria-hidden="true">🧠</span>
    </button>
  </div>
</div>

<script>
  // Dispatch a custom event on action for app logic to hook into
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.v9-sticky-actions .v9-action-bubble').forEach(function(btn){
      // Click handler
      btn.addEventListener('click', function(){
        const type = btn.getAttribute('data-action');
        document.dispatchEvent(new CustomEvent('v9:action', { detail: { type } }));
      });
      // Keyboard activation for accessibility (Enter/Space)
      btn.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const type = btn.getAttribute('data-action');
          document.dispatchEvent(new CustomEvent('v9:action', { detail: { type } }));
        }
      });
    });
  });
</script>
