@extends('layouts.app')

@section('title', 'Register')

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
                            <h1 class="h4 mb-2">Create Account</h1>
                            <p class="text-muted">Sync your plans across devices</p>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                            <strong>Registration Coming Soon!</strong><br>
                            User registration will be available in a future update.
                            For now, you can use the app with local storage to track your plans.
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>
                                Continue with Local Storage
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>
                                Already have an account?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
