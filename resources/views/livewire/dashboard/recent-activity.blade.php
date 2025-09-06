
<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    {{--
            Recent activity panel.
            No direct image usage, but if activity icons use uploaded .ico files,
            reference them with asset() helper.
    --}}
    <div class="card recent-activity-card mb-4 shadow-sm border-0 rounded-4 recent-activity-theme">
        <div class="card-header fw-bold rounded-top-4 recent-activity-header-theme">Recent Activity</div>
        <div class="card-body recent-activity-body-theme" id="recentActivity">
            <ul class="list-group list-group-flush">
                @forelse($activities as $activity)
                        <li class="list-group-item d-flex align-items-center">
                            {{-- If you want to show favicon or uploaded icon, use asset() helper --}}
                            @if(!empty($activity['icon_file']))
                                <img src="{{ asset('uploads/app_logo/' . $activity['icon_file']) }}"
                                         alt="Icon" class="me-2" style="width: 20px; height: 20px;">
                            @else
                                <i class="bi {{ $activity['icon_class'] }} me-2" aria-hidden="true"></i>
                            @endif
                            <span>{{ $activity['description'] }}</span>
                            <small class="text-muted ms-auto">
                                    {{-- Using Laravel's Carbon for cleaner date formatting --}}
                                    {{ \Carbon\Carbon::parse($activity['timestamp'])->format('M d, H:i') }}
                            </small>
                        </li>
                @empty
                    <li class="list-group-item text-muted text-center">
                        No recent activity.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
