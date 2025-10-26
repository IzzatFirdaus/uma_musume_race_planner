{{-- Header Partial - Page header with breadcrumbs and actions --}}
@php
    $pageTitle = $pageTitle ?? config('app.name');
    $breadcrumbs = $breadcrumbs ?? [];
    $headerActions = $headerActions ?? [];
@endphp

<header class="page-header mb-4 page-header-theme" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                {{-- Page Title --}}
                <h1 class="h3 mb-2 fw-bold">{{ $pageTitle }}</h1>

                {{-- Breadcrumbs --}}
                @if(count($breadcrumbs) > 0)
                    <nav aria-label="Breadcrumb">
                        <ol class="breadcrumb mb-0">
                            @foreach($breadcrumbs as $breadcrumb)
                                @if($loop->last)
                                    <li class="breadcrumb-item active" aria-current="page">
                                        {{ $breadcrumb['title'] }}
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                @endif
            </div>

            {{-- Header Actions --}}
            @if(count($headerActions) > 0)
                <div class="col-md-4 text-end">
                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                        @foreach($headerActions as $action)
                            @if($action['type'] === 'button')
                                <button type="button"
                                        class="btn {{ $action['class'] ?? 'btn-primary' }}"
                                        @if(isset($action['id'])) id="{{ $action['id'] }}" @endif
                                        @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif>
                                    @if(isset($action['icon']))
                                        <i class="bi {{ $action['icon'] }} me-1" aria-hidden="true"></i>
                                    @endif
                                    {{ $action['text'] }}
                                </button>
                            @elseif($action['type'] === 'link')
                                <a href="{{ $action['url'] }}"
                                   class="btn {{ $action['class'] ?? 'btn-outline-primary' }}"
                                   @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                                    @if(isset($action['icon']))
                                        <i class="bi {{ $action['icon'] }} me-1" aria-hidden="true"></i>
                                    @endif
                                    {{ $action['text'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>

@push('styles')
<style>
    .page-header-theme {
        background: transparent;
        border-bottom: 1px solid var(--bs-border-color);
        padding: 1.5rem 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--bs-secondary);
    }

    /* Dark mode support */
    body.dark-mode .page-header-theme {
        border-bottom-color: var(--bs-border-color-translucent);
    }
</style>
@endpush
