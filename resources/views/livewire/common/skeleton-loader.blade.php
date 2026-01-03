{{-- Skeleton Loader Component --}}
{{-- Implements NFR-1.6: Skeleton loaders during async operations --}}
<div class="skeleton-loader" style="width: {{ $width }};" role="status" aria-label="Loading content"
    data-testid="skeleton-loader-{{ $type }}">
    <span class="visually-hidden">Loading...</span>

    @switch($type)
        @case('text')
            @for ($i = 0; $i < $lines; $i++)
                <div class="skeleton-line mb-2" style="height: 1rem; width: {{ $i === $lines - 1 ? '60%' : '100%' }};"></div>
            @endfor
        @break

        @case('card')
            <div class="card border-0">
                <div class="skeleton-image" style="height: 150px;"></div>
                <div class="card-body">
                    <div class="skeleton-line mb-2" style="height: 1.25rem; width: 70%;"></div>
                    <div class="skeleton-line mb-2" style="height: 0.875rem; width: 100%;"></div>
                    <div class="skeleton-line" style="height: 0.875rem; width: 80%;"></div>
                </div>
            </div>
        @break

        @case('table')
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            @for ($i = 0; $i < 4; $i++)
                                <th>
                                    <div class="skeleton-line" style="height: 1rem; width: 80%;"></div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @for ($row = 0; $row < $lines; $row++)
                            <tr>
                                @for ($col = 0; $col < 4; $col++)
                                    <td>
                                        <div class="skeleton-line" style="height: 1rem; width: {{ rand(60, 100) }}%;">
                                        </div>
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @break

        @case('avatar')
            <div class="d-flex align-items-center gap-3">
                <div class="skeleton-circle" style="width: 48px; height: 48px;"></div>
                <div class="flex-grow-1">
                    <div class="skeleton-line mb-2" style="height: 1rem; width: 40%;"></div>
                    <div class="skeleton-line" style="height: 0.75rem; width: 60%;"></div>
                </div>
            </div>
        @break

        @case('button')
            <div class="skeleton-button" style="width: 100px; height: 38px;"></div>
        @break

        @case('stat')
            <div class="text-center">
                <div class="skeleton-line mx-auto mb-2" style="height: 2.5rem; width: 60%;"></div>
                <div class="skeleton-line mx-auto" style="height: 0.875rem; width: 80%;"></div>
            </div>
        @break

        @default
            <div class="skeleton-line" style="height: {{ $height }}; width: {{ $width }};"></div>
    @endswitch
</div>

<style>
    .skeleton-loader .skeleton-line,
    .skeleton-loader .skeleton-image,
    .skeleton-loader .skeleton-circle,
    .skeleton-loader .skeleton-button {
        background: linear-gradient(90deg,
                var(--bs-secondary-bg, #e9ecef) 25%,
                var(--bs-tertiary-bg, #f8f9fa) 50%,
                var(--bs-secondary-bg, #e9ecef) 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
        border-radius: 0.25rem;
    }

    .skeleton-loader .skeleton-circle {
        border-radius: 50%;
    }

    .skeleton-loader .skeleton-image {
        border-radius: 0.5rem 0.5rem 0 0;
    }

    @keyframes skeleton-shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Respect reduced motion preference */
    @media (prefers-reduced-motion: reduce) {

        .skeleton-loader .skeleton-line,
        .skeleton-loader .skeleton-image,
        .skeleton-loader .skeleton-circle,
        .skeleton-loader .skeleton-button {
            animation: none;
            background: var(--bs-secondary-bg, #e9ecef);
        }
    }
</style>
