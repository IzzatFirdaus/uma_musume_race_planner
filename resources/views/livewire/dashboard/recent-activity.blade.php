
{{-- Dashboard Recent Activity Panel - Livewire Component View --}}
<div class="card recent-activity-card mb-4 shadow-sm border-0 rounded-4 recent-activity-theme">
    <div class="card-header d-flex align-items-center fw-bold rounded-top-4 recent-activity-header-theme">
        <i class="bi bi-clock-history me-2" aria-hidden="true"></i>
        Recent Activity
    </div>
    <div class="card-body recent-activity-body-theme p-0" id="recentActivity">
            <div wire:loading.flex wire:transition class="justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div wire:loading.remove>
                @if($activities && count($activities) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($activities as $activity)
                            <div class="list-group-item d-flex align-items-center border-0 py-3" wire:key="activity-{{ $activity['id'] ?? $loop->index }}">
                                {{-- Activity icon or user uploaded icon --}}
                                <div class="flex-shrink-0 me-3">
                                    @if(!empty($activity['icon_file']))
                                        <img src="{{ asset('uploads/activity_icons/' . $activity['icon_file']) }}"
                                             alt="Activity icon" class="rounded-circle"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px;">
                                            <i class="bi {{ $activity['icon_class'] ?? 'bi-activity' }} text-white small" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-grow-1 min-width-0">
                                    <div class="activity-description">{{ $activity['description'] }}</div>
                                    @if(!empty($activity['plan_name']))
                                        <div class="text-muted small">Plan: {{ $activity['plan_name'] }}</div>
                                    @endif
                                </div>

                                <div class="flex-shrink-0 ms-2">
                                    <small class="text-muted">
                                        {{-- Using Laravel's Carbon for cleaner date formatting --}}
                                        <time datetime="{{ $activity['timestamp'] }}">
                                            {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                        </time>
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox display-6 mb-3 d-block" aria-hidden="true"></i>
                            <p class="mb-0">No recent activity</p>
                            <small>Your plan actions will appear here</small>
                        </div>
                    </div>
                @endif

                @if($activities && count($activities) >= 5)
                    <div class="card-footer text-center bg-transparent border-top-0">
                        <button class="btn btn-sm btn-outline-primary" wire:click="loadMore">
                            <i class="bi bi-arrow-down me-1" aria-hidden="true"></i>
                            Load More
                        </button>
                    </div>
                @endif
            </div>
    </div>
</div>
