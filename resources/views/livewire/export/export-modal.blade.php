{{-- Export Modal Component --}}
{{-- Implements Requirements 15.1, 15.2, 15.3, 22.1, 22.2, 22.3, 24.1, 24.2, 24.3, 24.4, 68.9 --}}
@if ($show)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="exportModalTitle" aria-modal="true"
        x-on:keydown.escape.window="$wire.closeModal()" data-testid="export-modal">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="exportModalTitle">
                        <i class="bi bi-download" aria-hidden="true"></i>
                        Export Plan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal" aria-label="Close"
                        data-testid="export-modal-close"></button>
                </div>
                <div class="modal-body">
                    @if ($this->plan)
                        {{-- Plan Info Summary --}}
                        <div class="alert alert-light d-flex align-items-center gap-3 mb-4"
                            data-testid="export-plan-info">
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
                                <div class="fw-bold">{{ $this->plan->plan_title ?? $this->plan->name }}</div>
                                <small class="text-muted">
                                    {{ $this->plan->skills->count() }} skills •
                                    {{ $this->plan->turns->count() }} turns •
                                    {{ $this->plan->goals->count() }} goals
                                </small>
                            </div>
                        </div>

                        {{-- Format Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold" id="format-selection-label">Export Format</label>
                            <div class="row g-2" role="radiogroup" aria-labelledby="format-selection-label">
                                @foreach ($this->formats as $key => $formatInfo)
                                    <div class="col-md-4">
                                        <div
                                            class="form-check card h-100 {{ $format === $key ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                            <label
                                                class="form-check-label card-body d-flex align-items-start gap-3 cursor-pointer m-0"
                                                for="format-{{ $key }}">
                                                <input class="form-check-input mt-1" type="radio" name="format"
                                                    id="format-{{ $key }}" value="{{ $key }}"
                                                    wire:model.live="format" data-testid="format-{{ $key }}">
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

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-outline-secondary" wire:click="generatePreview"
                                wire:loading.attr="disabled" data-testid="preview-btn">
                                <span wire:loading.remove wire:target="generatePreview">
                                    <i class="bi bi-eye me-1" aria-hidden="true"></i>
                                    Preview
                                </span>
                                <span wire:loading wire:target="generatePreview">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                        aria-hidden="true"></span>
                                    Generating...
                                </span>
                            </button>
                            @if ($showPreview && $previewContent)
                                <button type="button" class="btn btn-outline-primary" wire:click="copyToClipboard"
                                    data-testid="copy-clipboard-btn">
                                    <i class="bi bi-clipboard me-1" aria-hidden="true"></i>
                                    Copy to Clipboard
                                </button>
                            @endif
                        </div>

                        {{-- Preview Panel --}}
                        @if ($showPreview && $previewContent)
                            <div class="card bg-light mb-3" data-testid="export-preview-panel">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <span class="fw-semibold">
                                        <i class="bi bi-file-text me-1" aria-hidden="true"></i>
                                        Preview
                                        <span
                                            class="badge bg-secondary ms-2">{{ $this->formats[$format]['label'] ?? $format }}</span>
                                    </span>
                                    @if ($exportSize)
                                        <small class="text-muted">Size: {{ $exportSize }}</small>
                                    @endif
                                </div>
                                <div class="card-body p-0">
                                    <pre class="mb-0 p-3 small bg-dark text-light rounded-bottom"
                                        style="max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;"
                                        data-testid="preview-content"><code>{{ $previewContent }}</code></pre>
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
                    <button type="button" class="btn btn-secondary" wire:click="closeModal"
                        data-testid="export-cancel-btn">
                        Cancel
                    </button>
                    @if ($this->plan)
                        <button type="button" class="btn btn-primary" wire:click="export" wire:loading.attr="disabled"
                            data-testid="export-download-btn">
                            <span wire:loading.remove wire:target="export">
                                <i class="bi bi-download me-1" aria-hidden="true"></i>
                                Download {{ $this->formats[$format]['label'] ?? $format }}
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
        // Handle copy to clipboard
        Livewire.on('copy-to-clipboard', (data) => {
            const content = data[0]?.content || data.content || '';
            navigator.clipboard.writeText(content).then(() => {
                // Success handled by toast
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        });

        // Handle file download trigger
        Livewire.on('trigger-download', (data) => {
            const params = data[0] || data;
            const content = params.content || '';
            const filename = params.filename || 'export.txt';
            const mimeType = params.mimeType || 'text/plain';

            // Create blob and trigger download
            const blob = new Blob([content], {
                type: mimeType + ';charset=utf-8'
            });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();

            // Cleanup
            setTimeout(() => {
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 100);
        });
    </script>
@endscript

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .form-check.card:hover {
        border-color: var(--bs-primary) !important;
    }

    .form-check.card .form-check-input:checked~div {
        color: var(--bs-primary);
    }
</style>
