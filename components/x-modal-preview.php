<?php
// components/x-modal-preview.php
// Usage: include and provide $modalId and optionally $title
if (!isset($modalId)) $modalId = 'exportPreviewModal';
if (!isset($title)) $title = 'Preview';
?>
<div class="modal fade v8-modal" id="<?= htmlspecialchars($modalId) ?>" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($modalId) ?>Label">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content v8-modal-content">
      <div class="modal-header v8-gradient-header">
        <h5 class="modal-title v8-gradient-text" id="<?= htmlspecialchars($modalId) ?>Label"><?= htmlspecialchars($title) ?></h5>
        <button type="button" class="btn-close v8-tap-feedback" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <pre id="exportPreviewContent" class="p-3" style="white-space: pre-wrap; background: #f8f9fa; border-radius: .5rem;">Loading…</pre>
      </div>
      <div class="modal-footer v8-gradient-footer">
        <button type="button" class="btn btn-secondary v8-animated-pill v8-tap-feedback" data-bs-dismiss="modal">Close</button>
        <a id="exportPreviewDownload" class="btn btn-primary v8-animated-pill v8-tap-feedback" href="#">Download</a>
      </div>
    </div>
  </div>
</div>
