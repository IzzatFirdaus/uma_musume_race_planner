<!-- components/modal-import.php -->
<form method="POST" action="/import_plan.php" enctype="multipart/form-data" id="importForm" role="form" aria-label="Import JSON Plan">
  <div class="mb-3">
    <label for="planJsonFile" class="form-label fw-semibold">Choose Plan JSON File</label>
    <input
      type="file"
      id="planJsonFile"
      name="plan_json"
      accept="application/json"
      class="form-control"
      required
      aria-describedby="planFileHelp"
    />
    <div id="planFileHelp" class="form-text">
      Upload a <strong>.json</strong> export from <strong>Uma Musume Planner</strong>. Only valid formats will be accepted.
    </div>
  </div>

  <!-- Live JSON preview -->
  <div id="jsonPreview" class="border p-3 rounded bg-light d-none" aria-live="polite" aria-label="JSON Preview"></div>

  <button type="submit" class="btn btn-success w-100 mt-3" aria-label="Submit JSON import">
    <i class="bi bi-upload me-1"></i> Import Plan
  </button>
</form>

<script>
  // Optional preview logic (if not already in script.js)
  document.getElementById('planJsonFile')?.addEventListener('change', function (e) {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
      try {
        const json = JSON.parse(event.target.result);
        const pretty = JSON.stringify(json, null, 2);
        const preview = document.getElementById('jsonPreview');
        preview.textContent = pretty;
        preview.classList.remove('d-none');
      } catch (err) {
        alert("Invalid JSON file.");
      }
    };
    reader.readAsText(file);
  });
</script>
