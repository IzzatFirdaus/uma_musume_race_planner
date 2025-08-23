(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const mainNavbar = document.querySelector('.navbar');
        const guideNavbar = document.querySelector('.guide-sticky-nav');

      // Compute sticky offset height dynamically, but lazily to avoid layout reads
      // during DOMContentLoaded which may run before stylesheets are applied.
        function getStickyOffset()
        {
          // Read layout values only when actually needed; keep this local so callers
          // can invoke inside requestAnimationFrame to avoid forced synchronous layouts.
            const mainH = mainNavbar ? mainNavbar.offsetHeight : 0;
            const guideH = guideNavbar ? guideNavbar.offsetHeight : 0;
            return mainH + guideH;
        }

      // Event delegation for smooth scroll on guide links
    document.querySelector('.guide-sticky-nav')?.addEventListener('click', function (e) {
            const a = e.target.closest('.nav-link[href^="#"]');
            if (!a) {
                return;
            }
            const targetId = a.getAttribute('href') || '';
            if (!targetId.startsWith('#')) {
                return;
            }
            const target = document.querySelector(targetId);
            if (!target) {
                return;
            }

            e.preventDefault();
            document.querySelectorAll('.guide-sticky-nav .nav-link').forEach(l => l.classList.remove('active'));
            a.classList.add('active');

          // Defer layout reads to the next animation frame to avoid forcing layout
          // while stylesheets may still be loading. This prevents FOUC / console warnings.
            requestAnimationFrame(() => {
                const rectTop = target.getBoundingClientRect().top;
                const top = rectTop + window.pageYOffset - getStickyOffset();
                window.scrollTo({ top, behavior: reducedMotion ? 'auto' : 'smooth' });
              // Ensure focus for accessibility after the scroll is initiated
                if (!target.hasAttribute('tabindex')) {
                    target.setAttribute('tabindex', '-1');
                }
                target.focus({ preventScroll: true });
            });
        });

      // Active link highlight with IntersectionObserver (more efficient than scroll handlers)
        const links = Array.from(document.querySelectorAll('.guide-sticky-nav .nav-link'));
        const sections = Array.from(document.querySelectorAll('section[id]'));
        if ('IntersectionObserver' in window && sections.length && links.length) {
            const offset = getStickyOffset();
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        links.forEach(link => {
                              link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                        });
                    }
                });
            }, {
                root: null,
                rootMargin: ` - ${offset + 20}px 0px - 60 % 0px`, // top margin to account for sticky nav
                threshold: 0.1
            });
            sections.forEach(sec => observer.observe(sec));
        }
    });
})();
