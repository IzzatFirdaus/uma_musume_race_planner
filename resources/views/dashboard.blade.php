@extends('layouts.app')

@section('content')

    {{-- Header Banner (from header.php) --}}
    @include('dashboard.partials.header-banner')

    <div id="mainContent" class="row">
        {{-- Main Column --}}
        <div class="col-lg-8">
            @include('dashboard.partials.plan-list')
        </div>

        {{-- Sidebar Column --}}
        <div class="col-lg-4">
            @include('dashboard.partials.stats-panel')
            @include('dashboard.partials.recent-activity')
        </div>
    </div>

    {{-- Inline Plan Details View --}}
    @include('dashboard.partials.plan-inline-details')

@endsection

@push('scripts')
{{--
    Include the script for the "Copy to Clipboard" functionality.
    This partial requires $moodOptions, $strategyOptions, and $conditionOptions
    to be passed from the controller to this view.
--}}
@include('plans.partials.copy-script')

<script>
    // Pass initial data from the controller to a global JavaScript object.
    // This makes all server-side data cleanly available to all client-side scripts.
    window.plannerData = {
        plans: @json($plans ?? []),
        stats: @json($stats ?? []),
        activities: @json($activities ?? []),
        predictionIcons: @json($predictionIcons ?? []),
        attributeGradeOptions: @json($attributeGradeOptions ?? []),
        skillTagOptions: @json($skillTagOptions ?? []),
        // Add lookup options for use by the copy script and other UI components
        moodOptions: @json($moodOptions ?? []),
        strategyOptions: @json($strategyOptions ?? []),
        conditionOptions: @json($conditionOptions ?? [])
    };

    // Initial render calls based on the passed data.
    // These functions would live in a file like 'main.js' and use the window.plannerData object.
    document.addEventListener('DOMContentLoaded', () => {
        // Example:
        // if (window.plannerData.plans) {
        //     renderPlanTable(window.plannerData.plans);
        // }
        // if (window.plannerData.stats) {
        //     renderStats(window.plannerData.stats);
        // }
        // if (window.plannerData.activities) {
        //     renderActivity(window.plannerData.activities);
        // }
    });
</script>
@endpush
