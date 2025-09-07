@extends('layouts.app')

@section('content')
    <main class="container">
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
        @livewire('dashboard.header-banner')

        <div id="mainContent" class="row mt-3">
            {{-- Main Column --}}
            <div class="col-lg-8">
                @livewire('dashboard.plan-list')
                {{-- Inline Plan Details View: appears below plan list for contextual editing --}}
                @livewire('dashboard.plan-inline-details')
            </div>

            {{-- Sidebar Column --}}
            <div class="col-lg-4">
                @livewire('dashboard.stats-panel')
                @livewire('dashboard.recent-activity')
                @livewire('dashboard.support-card-summary')
            </div>
        </div>
    </main>

@endsection


