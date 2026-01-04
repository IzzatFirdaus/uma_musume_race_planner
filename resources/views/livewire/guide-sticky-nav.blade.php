<nav class="sticky-top py-2 guide-sticky-nav" data-testid="guide-sticky-nav">
    <div class="container">
        <ul class="nav nav-pills justify-content-center">
            @foreach ($sections as $s)
                <li class="nav-item">
                    <a class="nav-link {{ $active === $s['id'] ? 'active' : '' }}" href="#{{ $s['id'] }}" wire:ignore
                        data-testid="guide-nav-{{ $s['id'] }}">
                        {{ $s['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const guideNav = document.querySelector('.guide-sticky-nav');
            if (!guideNav) return;

            const mainNavbar = document.querySelector('.navbar.sticky-top');
            const totalStickyHeight = (mainNavbar?.offsetHeight || 0) + guideNav.offsetHeight;

            // Smooth scrolling for guide navigation
            guideNav.querySelectorAll('.nav-link').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        const elementPosition = targetElement.getBoundingClientRect().top + window
                            .pageYOffset;
                        const offsetPosition = elementPosition - totalStickyHeight;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: "smooth"
                        });
                    }
                });
            });

            // Highlight active nav link on scroll and emit to Livewire
            const sections = document.querySelectorAll('main section[id]');
            let scrollTimeout;

            function highlightNavOnScroll() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    let currentActiveSectionId = '';
                    sections.forEach(section => {
                        const sectionTop = section.offsetTop - totalStickyHeight - 50;
                        if (window.scrollY >= sectionTop) {
                            currentActiveSectionId = section.getAttribute('id');
                        }
                    });

                    // Emit event so Livewire state updates
                    if (window.Livewire) {
                        window.Livewire.dispatch('guide:sectionChanged', [currentActiveSectionId]);
                    }

                    // Also toggle classes for immediate feedback
                    guideNav.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.toggle('active', link.getAttribute('href') === '#' +
                            currentActiveSectionId);
                    });
                }, 100);
            }

            window.addEventListener('scroll', highlightNavOnScroll);
            highlightNavOnScroll();
        });
    </script>
@endpush
