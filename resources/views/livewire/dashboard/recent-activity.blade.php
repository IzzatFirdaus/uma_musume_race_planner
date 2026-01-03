{{-- Dashboard Recent Activity Panel - Livewire Component View --}}
{{-- Implements FR-8.2, FR-8.7: User-scoped activity with source indicator --}}
<div class="card recent-activity-card mb-4 shadow-sm border-0 rounded-4 recent-activity-theme"
    data-testid="recent-activity-panel">
    <div class="card-header d-flex align-items-center fw-bold rounded-top-4 recent-activity-header-theme">
        <i class="bi bi-clock-history me-2" aria-hidden="true"></i>
        Recent Activity
        <button type="button" class="btn btn-sm btn-link ms-auto p-0" wire:click="refresh" title="Refresh activity"
            data-testid="recent-activity-refresh">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
            <span class="visually-hidden">Refresh activity</span>
        </button>
    </div>
    <div class="card-body recent-activity-body-theme p-0" id="recentActivity" role="feed"
        aria-label="Recent activity feed">
        <div wire:loading.flex wire:transition class="justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading activities...</span>
            </div>
        </div>
        <div wire:loading.remove>
            @if ($activities && count($activities) > 0)
                <div class="list-group list-group-flush" role="list">
                    @foreach ($activities as $activity)
                        <article class="list-group-item d-flex align-items-center border-0 py-3"
                            wire:key="activity-{{ $activity['id'] ?? $loop->index }}" role="listitem"
                            data-testid="activity-item-{{ $activity['id'] ?? $loop->index }}">
                            {{-- Activity icon --}}
                            <div class="flex-shrink-0 me-3">
                                @if (!empty($activity['icon_file']))
                                    <img src="{{ asset('uploads/activity_icons/' . $activity['icon_file']) }}"
                                        alt="" class="rounded-circle"
                                        style="width: 32px; height: 32px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px;" aria-hidden="true">
                                        <i
                                            class="bi {{ $activity['icon_class'] ?? 'bi-activity' }} text-white small"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1 min-width-0">
                                <div class="activity-description d-flex align-items-center gap-2">
                                    <span>{{ $activity['description'] }}</span>
                                    {{-- Source indicator badge (FR-8.7) --}}
                                    <span
                                        class="badge {{ $activity['source_badge_class'] ?? 'bg-secondary' }} rounded-pill"
                                        style="font-size: 0.65rem;"
                                        title="{{ $activity['source'] === 'local' ? 'Stored locally in browser' : 'Stored in your account' }}"
                                        data-testid="activity-source-badge">
                                        <i class="bi {{ $activity['source'] === 'local' ? 'bi-hdd' : 'bi-cloud' }} me-1"
                                            aria-hidden="true"></i>
                                        {{ $activity['source_label'] ?? 'Account' }}
                                    </span>
                                </div>
                                @if (!empty($activity['plan_name']))
                                    <div class="text-muted small">Plan: {{ $activity['plan_name'] }}</div>
                                @endif
                            </div>

                            <div class="flex-shrink-0 ms-2">
                                <small class="text-muted">
                                    <time datetime="{{ $activity['timestamp'] }}">
                                        {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                    </time>
                                </small>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5" data-testid="recent-activity-empty">
                    <div class="text-muted">
                        <i class="bi bi-inbox display-6 mb-3 d-block" aria-hidden="true"></i>
                        <p class="mb-0">No recent activity</p>
                        <small>Your plan actions will appear here</small>
                    </div>
                </div>
            @endif

            @if ($hasMore)
                <div class="card-footer text-center bg-transparent border-top-0">
                    <button class="btn btn-sm btn-outline-primary" wire:click="loadMore" wire:loading.attr="disabled"
                        data-testid="recent-activity-load-more">
                        <span wire:loading.remove wire:target="loadMore">
                            <i class="bi bi-arrow-down me-1" aria-hidden="true"></i>
                            Load More
                        </span>
                        <span wire:loading wire:target="loadMore">
                            <span class="spinner-border spinner-border-sm me-1" role="status"
                                aria-hidden="true"></span>
                            Loading...
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
