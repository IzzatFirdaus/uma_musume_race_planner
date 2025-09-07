
<div>
    {{-- Be like water. --}}
    {{--
        Inline card view for editing the full details of a selected plan.
        No direct image/background usage here, but ensure any referenced partials
        (like form-tabs) also use `asset()` for uploaded images.
    --}}
    <div id="planInlineDetails" class="card mb-4" @style(['display: block' => $isVisible, 'display: none' => !$isVisible])>
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="planInlineDetailsLabel">
                @if($plan_title)
                    Plan Details: {{ $plan_title }}
                @else
                    Plan Details
                @endif
            </h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closePlan">
                <i class="bi bi-x"></i> Close
            </button>
        </div>

        @if($isLoading)
        <div class="loading-overlay" id="planInlineDetailsLoadingOverlay" style="display: flex;">
            <div class="spinner-border text-uma" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        @endif

        {{-- This form would submit to a Laravel route for updating the plan --}}
        <form id="planDetailsFormInline" enctype="multipart/form-data" wire:submit.prevent="save">
            @method('PUT')
            @csrf
            <input type="hidden" wire:model="planId" id="planIdInline" name="planId">

            <div class="card-body">
                {{-- Reuse the new reactive FormTabs component --}}
                <livewire:form-tabs :id_suffix="'_inline'" :planId="$planId" :wire:key="'form-tabs-inline-' . ($planId ?? 'new')" />
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
