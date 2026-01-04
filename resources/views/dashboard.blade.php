@extends('layouts.app')

@section('content')
    <div class="container" data-testid="dashboard-container">
        <h1 id="dashboard-heading" class="text-2xl font-bold mb-4 visually-hidden">Dashboard</h1>
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
        {{-- Header Banner (Requirement 1.1) --}}
        <livewire:dashboard.header-banner />

        <div id="mainContent" class="row mt-3" role="region" aria-labelledby="dashboard-heading"
            data-testid="dashboard-main-content">
            {{-- Main Column (Req 14.1 - Single column below 768px) --}}
            <div class="col-md-8">
                {{-- Plan List (Requirement 1.3) --}}
                <livewire:dashboard.plan-list />
                {{-- Inline Editor for quick edits (Requirement 76.1, 76.2) --}}
                <livewire:dashboard.inline-editor />
                {{-- Inline Plan Details View: appears below plan list for contextual editing --}}
                <livewire:dashboard.plan-inline-details />
            </div>

            {{-- Sidebar Column (Req 14.1 - Single column below 768px) --}}
            <div class="col-md-4" data-testid="dashboard-sidebar">
                {{-- Stats Panel (Requirement 1.2) --}}
                <livewire:dashboard.stats-panel />
                {{-- Recent Activity (Requirement 1.4) --}}
                <livewire:dashboard.recent-activity />
                <livewire:dashboard.support-card-summary />
            </div>
        </div>

        {{-- Convert Run Modal (Requirement 76.9) - Single run conversion --}}
        <livewire:auth.convert-run-modal />

        {{-- Claim Plans Modal (Requirement 56.7, 56.8) - Bulk conversion on login --}}
        <livewire:auth.claim-plans-modal />
    </div>

    @auth
        @push('scripts')
            <script>
                // Check for local runs on page load and show claim modal if user just logged in
                document.addEventListener('DOMContentLoaded', function() {
                    // Only check if user is authenticated and hasn't dismissed the modal this session
                    const claimModalDismissed = sessionStorage.getItem('claim_modal_dismissed');
                    if (claimModalDismissed) {
                        return;
                    }

                    // Check if this is a fresh login (check for login redirect or session flag)
                    const isNewLogin = {{ session()->has('just_logged_in') ? 'true' : 'false' }};

                    if (isNewLogin || !sessionStorage.getItem('claim_modal_checked')) {
                        sessionStorage.setItem('claim_modal_checked', 'true');

                        // Get local runs from localStorage
                        if (typeof window.localRunStorage !== 'undefined') {
                            const localRuns = window.localRunStorage.getAll();
                            if (localRuns && localRuns.length > 0) {
                                // Dispatch event to open the claim modal
                                Livewire.dispatch('open-claim-plans-modal', [localRuns]);
                            }
                        }
                    }
                });

                // Listen for successful conversion to remove local runs
                Livewire.on('local-runs-converted', (data) => {
                    const uuids = data.uuids || [];
                    if (typeof window.localRunStorage !== 'undefined' && uuids.length > 0) {
                        window.localRunStorage.deleteMany(uuids);
                    }
                });

                // Mark modal as dismissed for this session when skipped
                Livewire.on('claim-modal-skipped', () => {
                    sessionStorage.setItem('claim_modal_dismissed', 'true');
                });
            </script>
        @endpush
    @endauth
@endsection
