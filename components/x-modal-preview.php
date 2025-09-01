<?php
// components/x-modal-preview.php
// Usage: include and provide $modalId and optionally $title
if (!isset($modalId)) $modalId = 'exportPreviewModal';
if (!isset($title)) $title = 'Preview';
?>
<div class="modal fade" id="<?= htmlspecialchars($modalId) ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= htmlspecialchars($title) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <pre id="exportPreviewContent" class="p-3" style="white-space: pre-wrap; background: #f8f9fa; border-radius: .5rem;">Loading…</pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a id="exportPreviewDownload" class="btn btn-primary" href="#">Download</a>
      </div>
    </div>
  </div>
</div>
