(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const mainNavbar = document.querySelector('.navbar');
        const guideNavbar = document.querySelector('.guide-sticky-nav');

        function getStickyOffset() {
            const mainH = mainNavbar && Number.isFinite(mainNavbar.offsetHeight) ? mainNavbar.offsetHeight : 0;
            const guideH = guideNavbar && Number.isFinite(guideNavbar.offsetHeight) ? guideNavbar.offsetHeight : 0;
            const offset = mainH + guideH;
            return Number.isFinite(offset) && offset >= 0 ? offset : 0;
        }

        guideNavbar?.addEventListener('click', function (e) {
            const a = e.target.closest('.nav-link[href^="#"]');
            if (!a) return;
            const targetId = a.getAttribute('href') || '';
            if (!targetId.startsWith('#')) return;
            const target = document.querySelector(targetId);
            if (!target) return;

            e.preventDefault();
            document.querySelectorAll('.guide-sticky-nav .nav-link').forEach(l => l.classList.remove('active'));
            a.classList.add('active');

            requestAnimationFrame(() => {
                const rectTop = target.getBoundingClientRect().top;
                const top = rectTop + window.pageYOffset - getStickyOffset();
                window.scrollTo({ top, behavior: reducedMotion ? 'auto' : 'smooth' });
                if (!target.hasAttribute('tabindex')) {
                    target.setAttribute('tabindex', '-1');
                }
                target.focus({ preventScroll: true });
            });
        });

        // Active link highlight with IntersectionObserver (efficient)
        const links = Array.from(document.querySelectorAll('.guide-sticky-nav .nav-link'));
        const sections = Array.from(document.querySelectorAll('section[id]'));

        if ('IntersectionObserver' in window && sections.length && links.length) {
            // Get sticky offset and ensure it's a valid number
            const offset = getStickyOffset();
            // Calculate rootMargin value (always ensure it's a valid number)
            let rootMarginValue = -20; // Default fallback
            if (Number.isFinite(offset) && offset >= 0) {
                rootMarginValue = -(offset + 20);
            }
            // Construct a valid rootMargin string (guaranteed to be valid)
            const rootMargin = `${rootMarginValue}px 0px 0px 0px`;
            // For debugging
            console.log(`[guide_inline.js] Using rootMargin: ${rootMargin}`);

            // Create observer with validated rootMargin
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
                rootMargin: rootMargin,
                threshold: 0.1
            });
            // Observe all sections
            sections.forEach(sec => observer.observe(sec));
        }
        }); // end DOMContentLoaded
})(); // end main IIFE
