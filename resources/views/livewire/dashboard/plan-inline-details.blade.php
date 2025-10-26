
{{-- Dashboard Plan Inline Details Editor - Livewire Component View --}}
<div>
    {{-- Inline card view for editing the full details of a selected plan --}}
    <div id="planInlineDetails"
         class="card mb-4 border-0 shadow-sm rounded-4 plan-inline-details-theme"
         @style(['display: block' => $isVisible, 'display: none' => !$isVisible])
         role="dialog"
         aria-labelledby="planInlineDetailsLabel"
         aria-hidden="{{ $isVisible ? 'false' : 'true' }}">

        <div class="card-header d-flex justify-content-between align-items-center rounded-top-4 plan-inline-details-header-theme">
            <h5 class="mb-0 d-flex align-items-center" id="planInlineDetailsLabel">
                <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>
                @if($plan_title)
                    Plan Details: {{ $plan_title }}
                @else
                    Plan Details
                @endif
            </h5>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    wire:click="closePlan"
                    aria-label="Close plan details">
                <i class="bi bi-x" aria-hidden="true"></i>
                <span class="d-none d-sm-inline ms-1">Close</span>
            </button>
        </div>

        @if($isLoading)
            <div class="loading-overlay position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75 rounded-4"
                 style="z-index: 10;"
                 id="planInlineDetailsLoadingOverlay"
                 role="status"
                 aria-label="Loading plan details">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        @endif

        {{-- Form for updating the plan --}}
        <form id="planDetailsFormInline"
              wire:submit.prevent="save"
              novalidate>
            @csrf
            <input type="hidden" wire:model.defer="planId" id="planIdInline" name="planId" aria-hidden="true" aria-label="plan id inline">

            <div class="card-body plan-inline-details-body-theme">
                {{-- Form validation messages --}}
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Success message --}}
                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-check-circle me-2" aria-hidden="true"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Reuse the reactive FormTabs component --}}
                <livewire:form-tabs
                    :id_suffix="'_inline'"
                    :planId="$planId"
                    :wire:key="'form-tabs-inline-' . ($planId ?? 'new')" />
            </div>

            <div class="card-footer bg-light rounded-bottom-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div class="d-flex gap-2 order-2 order-sm-1">
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="downloadTxtInline"
                            title="Export plan as text file">
                        <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>
                        <span class="d-none d-md-inline">Export TXT</span>
                    </button>
                    <button type="button"
                            class="btn btn-outline-info btn-sm"
                            id="exportPlanBtnInline"
                            title="Copy plan to clipboard">
                        <i class="bi bi-clipboard me-1" aria-hidden="true"></i>
                        <span class="d-none d-md-inline">Copy</span>
                    </button>
                </div>

                <div class="d-flex gap-2 order-1 order-sm-2">
            <button type="button"
                class="btn btn-secondary"
                wire:click="closePlan">
                        Cancel
                    </button>
                    <button type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                            Save Changes
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
