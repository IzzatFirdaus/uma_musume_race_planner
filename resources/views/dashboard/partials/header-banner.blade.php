{{--
	Dashboard header/banner partial.
	- Prefer Livewire component for dynamic content if available.
	- Provide a static fallback that uses asset() so the banner renders even without Livewire or JS.
--}}
@php
	$appName = config('app.name', 'Uma Musume Race Planner');
	$logoPath = asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico');
@endphp

@if (class_exists(\Livewire\Livewire::class))
	{{-- Use Livewire component when available for reactive content --}}
	<livewire:dashboard.header-banner />
@else
	{{-- Static fallback banner --}}
	<div class="card shadow-sm mb-4 rounded-4 border-0 header-banner-theme">
		<div class="card-body text-center py-4">
			<div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-3">
				<img src="{{ $logoPath }}" alt="{{ $appName }} logo" class="logo" style="height:64px;width:64px;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
				<div>
					<h1 class="display-5 fw-bold mb-1">{{ $appName }}</h1>
					<p class="lead mb-0">Welcome to your dashboard — manage plans, view stats, and track activity.</p>
				</div>
			</div>
		</div>
	</div>
@endif

{{-- noscript fallback for users with JS disabled --}}
<noscript>
	<div class="card shadow-sm mb-4 rounded-4 border-0 header-banner-theme">
		<div class="card-body text-center py-4">
			<div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-3">
				<img src="{{ $logoPath }}" alt="{{ $appName }} logo" class="logo" style="height:48px;width:48px;border-radius:12px;">
				<div>
					<h2 class="h4 mb-0">{{ $appName }}</h2>
					<p class="small text-muted mb-0">Dashboard (static fallback)</p>
				</div>
			</div>
		</div>
	</div>
</noscript>
