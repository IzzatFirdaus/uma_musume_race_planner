@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_256.png') }}"
                                alt="Uma Musume Planner Logo" class="img-fluid rounded-circle shadow mb-3"
                                style="max-width: 80px; aspect-ratio: 1;">
                            <h1 class="h4 mb-2">Sign In</h1>
                            <p class="text-muted">Access your plans across devices</p>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                            <strong>Authentication Coming Soon!</strong><br>
                            This application currently supports local storage only.
                            User authentication will be available in a future update to sync your plans across devices.
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>
                                Continue with Local Storage
                            </a>
                            <a href="{{ route('local-data') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-hdd me-2" aria-hidden="true"></i>
                                Manage Local Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
