@extends('layouts.app')

@section('content')
    {{-- Navbar provided by layouts.app (Livewire) --}}
    {{-- Sticky sub-navigation for the guide page --}}
    <nav class="sticky-top py-2 guide-sticky-nav">
        <div class="container">
            <ul class="nav nav-pills justify-content-center">
                <li class="nav-item"><a class="nav-link" href="#welcome">Welcome</a></li>
                <li class="nav-item"><a class="nav-link" href="#dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#create-edit">Plan Editor</a></li>
                <li class="nav-item"><a class="nav-link" href="#ai-help">AI Assistant</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="#glossary">Glossary</a></li>
            </ul>
        </div>
    </nav>

    <main class="container my-4">
        {{-- The main banner is included via the app layout, but we add the card structure here. --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h3 mb-0"><i class="bi bi-book-fill me-2"></i>Application Guide</h1>
            </div>
            <div class="card-body p-lg-5">

                <section class="mb-5 p-4 p-md-5 rounded shadow-sm section-highlight" id="welcome">
                    <h2>Welcome to the Race Planner!</h2>
                    <p class="lead">This planner helps guide your favorite Umamusume to victory! Track everything from early training to URA Finals performance. Save your plans and iterate with AI help, screenshots, and stat charts.</p>
                </section>

                <section class="mb-5" id="dashboard">
                    <h3>Getting Started: Your Dashboard</h3>
                    <ul>
                        <li><strong>Race Plans:</strong> View saved plans with thumbnails and statuses.</li>
                        <li><strong>Filters:</strong> Filter by Planning, Active, Draft, and more.</li>
                        <li><strong>Stats Summary:</strong> See total plan count, active training, and completions.</li>
                        <li><strong>Recent Activity:</strong> Timeline of your last 5 edits or creations.</li>
                    </ul>
                    <figure class="text-center mt-4">
                        {{-- UPDATED: Image path now uses the asset() helper --}}
                        <img src="{{ asset('uploads/screenshot/Homepage.png') }}" class="img-fluid shadow-sm rounded" alt="Dashboard Screenshot" loading="lazy">
                        <figcaption class="text-muted small mt-2">Dashboard layout example</figcaption>
                    </figure>
                </section>

                <section class="mb-5" id="create-edit">
                    <h3>Creating and Editing Plans</h3>
                    <h5>1. Quick Create</h5>
                    <ol>
                        <li>Click <strong>"Create New"</strong> or <strong>"New Plan"</strong>.</li>
                        <li>Fill in the trainee name, career stage, and race info.</li>
                        <li>Click <strong>"Create Plan"</strong>. It appears instantly on your dashboard.</li>
                    </ol>

                    <h5 class="mt-4">2. Editing a Plan</h5>
                    <ul>
                        <li><strong>General / Attributes:</strong> Update status, energy, time of day, or mood.</li>
                        <li><strong>Skills:</strong> Autocomplete search + inline skill editing with notes.</li>
                        <li><strong>Progress Chart:</strong> Graph your stat growth across turns.</li>
                        <li><strong>Upload Image:</strong> Add trainee thumbnail with image upload support.</li>
                    </ul>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <img src="{{ asset('uploads/screenshot/001_GENERAL Edit Plan.png') }}" class="img-fluid rounded shadow-sm" alt="General Tab" loading="lazy">
                        </div>
                        <div class="col-md-6 mb-3">
                            <img src="{{ asset('uploads/screenshot/004_SKILLS Edit Plan.png') }}" class="img-fluid rounded shadow-sm" alt="Skills Tab" loading="lazy">
                        </div>
                    </div>
                    <p>Always click <strong>"Save Changes"</strong> before closing the plan!</p>
                </section>

                <section class="mb-5" id="ai-help">
                    <h3>Using with AI Assistants</h3>
                    <p>Use tools like ChatGPT or Gemini to brainstorm training strategies:</p>
                    <ol>
                        <li><strong>Ask:</strong> "Give me a plan for Daiwa Scarlet with long-distance stamina focus."</li>
                        <li><strong>Input:</strong> Enter suggestions into your plan manually.</li>
                        <li><strong>Progress:</strong> Track turn-by-turn stats in the chart tab.</li>
                        <li><strong>Coach:</strong> Use <strong>Export Plan</strong> to copy a clean summary and ask your AI assistant what to do next.</li>
                    </ol>
                </section>

                <section class="mb-5" id="faq">
                    <h3>Frequently Asked Questions</h3>
                    <div class="accordion" id="faqAccordion">
                        {{-- FAQ Items --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">What is a “Turn Before” value?</button></h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">It shows how many training turns remain before the next scheduled race, helping you pace training goals effectively.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Can I plan for more than one Uma at a time?</button></h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes. Each plan is stored separately and can track a different trainee and strategy.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Does this tool work on mobile?</button></h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes! The planner is fully responsive and optimized for touch controls and smaller screens.</div></div>
                        </div>
                         <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Can I share my plan with others?</button></h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes. Use "Export Plan" to copy your data as plain text and share it via message, Discord, or AI platforms.</div></div>
                        </div>
                    </div>
                </section>

                <section class="mb-5" id="glossary">
                    <h3>Glossary of Terms</h3>
                    <dl class="row">
                        <dt class="col-sm-3">SP</dt><dd class="col-sm-9">Skill Points — earned from training or races. Used to purchase skills.</dd>
                        <dt class="col-sm-3">Aptitude</dt><dd class="col-sm-9">Grade rating for track (Turf/Dirt), distance, or strategy (Front, Pace, etc).</dd>
                        <dt class="col-sm-3">Wit</dt><dd class="col-sm-9">Also known as Intelligence. Improves skill activation rate and positioning decisions.</dd>
                        <dt class="col-sm-3">Guts</dt><dd class="col-sm-9">Represents endurance and comeback power during the final sprint.</dd>
                        <dt class="col-sm-3">Support Cards</dt><dd class="col-sm-9">In-game cards that influence growth rate and skill acquisition.</dd>
                        <dt class="col-sm-3">Skill Tag</dt><dd class="col-sm-9">A category label (Start Dash, Recovery, Debuff, etc) that helps group skills.</dd>
                    </dl>
                </section>

            </div>
        </div>
    </main>
    {{-- Footer provided by layouts.app (Livewire) --}}
@endsection

@push('scripts')
<script>
    // This script is specific to the guide page for smooth scrolling and nav highlighting.
    document.addEventListener('DOMContentLoaded', function () {
        const guideNav = document.querySelector('.guide-sticky-nav');
        if (!guideNav) return;

        const mainNavbar = document.querySelector('.navbar.sticky-top');
        const totalStickyHeight = (mainNavbar?.offsetHeight || 0) + guideNav.offsetHeight;

        // Smooth scrolling for guide navigation
        guideNav.querySelectorAll('.nav-link').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                    const offsetPosition = elementPosition - totalStickyHeight;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });
                }
            });
        });

        // Highlight active nav link on scroll
        const sections = document.querySelectorAll('main section[id]');
        const guideNavLinks = guideNav.querySelectorAll('.nav-link');
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

                guideNavLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + currentActiveSectionId) {
                        link.classList.add('active');
                    }
                });
            }, 100);
        }

        window.addEventListener('scroll', highlightNavOnScroll);
        highlightNavOnScroll(); // Call on load
    });
</script>
@endpush
