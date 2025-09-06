

<div>
    {{-- Be like water. --}}
    {{--
        Inline card view for editing the full details of a selected plan.
        No direct image/background usage here, but ensure any referenced partials
        (like form-tabs) also use `asset()` for uploaded images.
    --}}
    <div id="planInlineDetails" class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="planInlineDetailsLabel">Plan Details</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="closeInlineDetailsBtn">
                <i class="bi bi-x"></i> Close
            </button>
        </div>
        <div class="loading-overlay" id="planInlineDetailsLoadingOverlay" style="display: none;">
            <div class="spinner-border text-uma" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        {{-- This form would submit to a Laravel route for updating the plan --}}
        <form id="planDetailsFormInline" enctype="multipart/form-data" wire:submit.prevent="save">
            @method('PUT')
            @csrf
            <div class="card-body">
                {{-- Use the Livewire form-tabs to match element IDs expected by JS --}}

                @livewire('form-tabs', ['id_suffix' => '_inline'])
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="downloadTxtInline">
                    <i class="bi bi-file-earmark-text"></i> Export as TXT
                </button>
                <button type="button" class="btn btn-info me-2" id="exportPlanBtnInline">Copy to Clipboard</button>
                <button type="submit" class="btn btn-uma">Save Changes</button>
            </div>
        </form>
    </div>
</div>
