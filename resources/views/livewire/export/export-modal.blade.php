{{-- Export Modal Component --}}
{{-- Implements FR-6.1, FR-6.2, FR-6.3, FR-13.1, FR-13.2 --}}
@if ($show)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="exportModalTitle" aria-modal="true"
        x-on:keydown.escape.window="$wire.closeModal()" data-testid="export-modal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="exportModalTitle">
                        <i class="bi bi-download" aria-hidden="true"></i>
                        Export Plan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($this->plan)
                        {{-- Plan Info --}}
                        <div class="alert alert-light d-flex align-items-center gap-3 mb-4">
                            @if ($this->plan->trainee_image_path)
                                <img src="{{ asset($this->plan->trainee_image_path) }}" alt="{{ $this->plan->name }}"
                                    class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                            @else
                                <div class="rounded bg-secondary d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="bi bi-person text-white" aria-hidden="true"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold">{{ $this->plan->name }}</div>
                                <small class="text-muted">
                                    {{ $this->plan->skills->count() }} skills •
                                    {{ $this->plan->turns->count() }} turns logged
                                </small>
                            </div>
                        </div>

                        {{-- Format Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Export Format</label>
                            <div class="row g-2">
                                @foreach ($this->formats as $key => $formatInfo)
                                    <div class="col-md-6">
                                        <div
                                            class="form-check card h-100 {{ $format === $key ? 'border-primary' : '' }}">
                                            <label
                                                class="form-check-label card-body d-flex align-items-start gap-3 cursor-pointer m-0"
                                                for="format-{{ $key }}">
                                                <input class="form-check-input mt-1" type="radio" name="format"
                                                    id="format-{{ $key }}" value="{{ $key }}"
                                                    wire:model="format" data-testid="format-{{ $key }}">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi {{ $formatInfo['icon'] }}" aria-hidden="true"></i>
                                                        <span class="fw-semibold">{{ $formatInfo['label'] }}</span>
                                                    </div>
                                                    <small class="text-muted">{{ $formatInfo['description'] }}</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Preview Button --}}
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary" wire:click="generatePreview"
                                wire:loading.attr="disabled" data-testid="preview-btn">
                                <span wire:loading.remove wire:target="generatePreview">
                                    <i class="bi bi-eye me-1" aria-hidden="true"></i>
                                    Preview Export
                                </span>
                                <span wire:loading wire:target="generatePreview">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                        aria-hidden="true"></span>
                                    Generating...
                                </span>
                            </button>
                        </div>

                        {{-- Preview Panel (FR-13.1, FR-13.2) --}}
                        @if ($showPreview && $previewContent)
                            <div class="card bg-light mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">
                                        <i class="bi bi-file-text me-1" aria-hidden="true"></i>
                                        Preview
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        wire:click="copyToClipboard" data-testid="copy-preview-btn">
                                        <i class="bi bi-clipboard me-1" aria-hidden="true"></i>
                                        Copy
                                    </button>
                                </div>
                                <div class="card-body">
                                    <pre class="mb-0 small" style="max-height: 200px; overflow-y: auto;" data-testid="preview-content"><code>{{ $previewContent }}</code></pre>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle display-4 mb-3 d-block" aria-hidden="true"></i>
                            <p>No plan selected for export</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">
                        Cancel
                    </button>
                    @if ($this->plan)
                        <button type="button" class="btn btn-primary" wire:click="export" wire:loading.attr="disabled"
                            data-testid="export-btn">
                            <span wire:loading.remove wire:target="export">
                                <i class="bi bi-download me-1" aria-hidden="true"></i>
                                Export as {{ $this->formats[$format]['label'] ?? $format }}
                            </span>
                            <span wire:loading wire:target="export">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Exporting...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

@script
    <script>
        Livewire.on('copy-to-clipboard', (data) => {
            navigator.clipboard.writeText(data[0].content).then(() => {
                // Success handled by toast
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        });
    </script>
@endscript

<style>
    .cursor-pointer {
        cursor: pointer;
    }
</style>
