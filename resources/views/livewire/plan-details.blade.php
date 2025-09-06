
<div class="modal fade" id="planDetailsModal" tabindex="-1" aria-labelledby="planDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planDetailsModalLabel">Plan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="loading-overlay" id="planDetailsLoadingOverlay" style="display: none;">
                <div class="spinner-border text-uma" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <form id="planDetailsForm" method="POST" enctype="multipart/form-data" wire:submit.prevent="save">
                @method('PUT')
                @csrf
                <div class="modal-body">
                    @livewire('form-tabs')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="exportPlanBtn">Copy to Clipboard</button>
                    <a href="#" id="downloadTxtLink" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-text"></i> Export as TXT</a>
                    <button type="submit" class="btn btn-uma">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
