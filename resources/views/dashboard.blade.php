@extends('layouts.app')

@section('content')
    <main class="container">
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
            </div>
        </div>
    </main>

@endsection


