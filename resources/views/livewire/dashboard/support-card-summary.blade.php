{{-- Dashboard Support Card Summary Panel - Livewire Component View (Placeholder) --}}
<div class="card mb-4 shadow-sm border-0 rounded-4 support-card-summary-theme">
    <div class="card-header d-flex align-items-center fw-bold rounded-top-4 support-card-summary-header-theme">
        <i class="bi bi-collection me-2" aria-hidden="true"></i>
        Support Card Summary
    </div>
    <div class="card-body support-card-summary-body-theme">
        @if(isset($supportCards) && count($supportCards) > 0)
            {{-- Future implementation: display support cards --}}
            <div class="row g-2">
                @foreach($supportCards as $card)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-sm">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center">
                                    @if($card['image_path'] ?? null)
                                        <img src="{{ asset($card['image_path']) }}"
                                             alt="{{ $card['name'] }}"
                                             class="rounded me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px;">
                                            <i class="bi bi-card-image text-white small" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="fw-semibold small">{{ $card['name'] }}</div>
                                        <div class="text-muted very-small">{{ $card['effect'] ?? 'No effect' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Placeholder state --}}
            <div class="text-center py-4">
                <div class="text-muted">
                    <i class="bi bi-collection display-6 mb-3 d-block" aria-hidden="true"></i>
                    <p class="mb-2">Support Card Summary</p>
                    <small>This panel will show your selected support cards, their effects, and synergy for your current training plan.</small>
                </div>
                <div class="mt-3">
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-wrench me-1" aria-hidden="true"></i>
                        Coming Soon
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>
