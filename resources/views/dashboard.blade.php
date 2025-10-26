@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 id="dashboard-heading" class="text-2xl font-bold mb-4">Dashboard</h1>
        <script>
            window.plannerData = {
                plans: @json($plans ?? []),
                stats: @json($stats ?? []),
                activities: @json($activities ?? []),
                motivationOptions: @json($motivationOptions ?? []),
                runningStyleOptions: @json($runningStyleOptions ?? []),
                trackConditionOptions: @json($trackConditionOptions ?? []),
                skillTagOptions: @json($skillTagOptions ?? []),
                racePhaseOptions: @json($racePhaseOptions ?? []),
                raceClassOptions: @json($raceClassOptions ?? []),
                attributeGradeOptions: @json($attributeGradeOptions ?? []),
                predictionIcons: @json($predictionIcons ?? []),
                distanceAptitudeOptions: @json($distanceAptitudeOptions ?? []),
                trackTypeOptions: @json($trackTypeOptions ?? []),
            };
        </script>
        {{-- Header Banner --}}
    <livewire:dashboard.header-banner />

    <div id="mainContent" class="row mt-3" role="region" aria-labelledby="dashboard-heading">
            {{-- Main Column --}}
            <div class="col-lg-8">
                <livewire:dashboard.plan-list />
                {{-- Inline Plan Details View: appears below plan list for contextual editing --}}
                <livewire:dashboard.plan-inline-details />
            </div>

            {{-- Sidebar Column --}}
            <div class="col-lg-4">
                <livewire:dashboard.stats-panel />
                <livewire:dashboard.recent-activity />
                <livewire:dashboard.support-card-summary />
            </div>
        </div>
    </div>

@endsection


