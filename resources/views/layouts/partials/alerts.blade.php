{{-- Alerts Partial - Global alert messages with Bootstrap styling --}}
@if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" aria-live="polite" aria-atomic="true">
        <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
    </div>
@endif

@if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" aria-live="assertive" aria-atomic="true">
        <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
    </div>
@endif

@if(session()->has('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert" aria-live="assertive" aria-atomic="true">
        <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
        <strong>Warning!</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
    </div>
@endif

@if(session()->has('info'))
    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" aria-live="polite" aria-atomic="true">
        <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
        <strong>Info:</strong> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" aria-live="assertive" aria-atomic="true">
        <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2" role="list">
            @foreach($errors->all() as $error)
                <li role="listitem">{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
    </div>
@endif
