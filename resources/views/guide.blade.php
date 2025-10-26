@extends('layouts.app')

@section('content')
    {{-- Navbar provided by layouts.app (Livewire) --}}
    {{-- Sticky sub-navigation for the guide page (Livewire) --}}
    <livewire:guide-sticky-nav />

    <main class="container my-4">
        {{-- The main banner is included via the app layout, but we add the card structure here. --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h3 mb-0"><i class="bi bi-book-fill me-2"></i>Application Guide</h1>
            </div>
            <div class="card-body p-lg-5">

                <section class="mb-5 p-4 p-md-5 rounded shadow-sm section-highlight" id="welcome">
                    <h2>Welcome to Umamusume: Pretty Derby — Umamusume Planner</h2>
                    <p class="lead">This planner is designed to mirror the authentic mechanics of Umamusume: Pretty Derby as seen on the Global English server. Track your trainee’s journey from early training to URA Finals, leveraging support cards, veteran legacies, and strategic skill selection for true competitive depth.</p>
                </section>

                <section class="mb-5" id="dashboard">
                    <h3>Career Mode Overview</h3>
                    <ul>
                        <li>Careers span approximately <strong>70 turns</strong> (each half a month across three years), including training, rest, recreation, races, and pivotal events like Summer Camp or New Year’s rejuvenation.</li>
                        <li>Trainers select a <strong>trainee</strong> Uma Musume and build her using <strong>support cards</strong> and <strong>veteran legacies</strong>, setting up long-term growth via inheritance and synergy.</li>
                        <li>Key attributes—<strong>Speed</strong>, <strong>Stamina</strong>, <strong>Power</strong>, <strong>Guts</strong>, and <strong>Wit</strong>—define performance and strategy.</li>
                        <li>Support cards provide stat boosts, skill assistance, and trigger <strong>Rainbow Training</strong> when bond levels reach orange tier.</li>
                        <li>Borrow <strong>legacies</strong> (veteran Uma Musume) for inherited stats and Sparks to strengthen new runs.</li>
                    </ul>
                    <figure class="text-center mt-4">
                        <img src="{{ asset('uploads/screenshot/Homepage.png') }}" class="img-fluid shadow-sm rounded" alt="Career Mode Dashboard" loading="lazy">
                        <figcaption class="text-muted small mt-2">Career Mode dashboard example</figcaption>
                    </figure>
                </section>

                <section class="mb-5" id="create-edit">
                    <h3>Training, Skills & Legacy Setup</h3>
                    <p>Every turn in Career Mode represents half a month. Train attributes (<strong>Speed, Stamina, Power, Guts, Wit</strong>), manage <strong>Energy</strong> and <strong>Mood</strong>, or enter races for <strong>Skill Points (SP)</strong> and fans. Build bond with support cards to unlock <strong>Rainbow Training</strong> for major stat boosts. After each run, convert your Uma Musume to a <strong>Veteran</strong> to pass on strength to new trainees.</p>
                    <h5 class="mt-4">Skill Acquisition & Activation</h5>
                    <ul>
                        <li>Skills fall into categories: <strong>Speed</strong>, <strong>Acceleration</strong>, <strong>Recovery</strong>, <strong>Passive</strong>, <strong>Debuff</strong>, <strong>Starting Gate</strong>, <strong>Lane Change</strong>, <strong>Observation</strong>, etc.</li>
                        <li>Each skill has specific activation conditions tied to running style, position, timing, and distance.</li>
                        <li>Unique skills require particular setups (e.g., being in the back at the final corner).</li>
                        <li>Effective strategy includes focusing on high-value gold skills and building around your Uma’s specialty (Sprint vs. Long distance).</li>
                        <li>Skill activation probability is influenced by <strong>Wit</strong> stat.</li>
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
                    <h3>Turn Management, Energy & Mood</h3>
                    <ul>
                        <li>Managing <strong>Energy</strong> prevents failed training, which can lower mood and cost turns. Rest or recreation is wise when energy is low.</li>
                        <li>A <strong>Great mood</strong> enhances training effectiveness by ~20% and gives a ~4% race performance bonus.</li>
                        <li>Use <strong>Summer Camp</strong> and <strong>New Year's bonus</strong> turns strategically to optimize training output.</li>
                    </ul>
                </section>

                <section class="mb-5" id="faq">
                    <h3>Gacha Elements & Currencies</h3>
                    <ul>
                        <li>Dual gacha: <strong>Character banners</strong> and <strong>Support card banners</strong>. Support cards are more impactful—prioritize them over characters.</li>
                        <li>Pulling from character banners awards <strong>Goddess Statues</strong>, which can be exchanged for <strong>Star Pieces</strong> to upgrade trainee rarity (SSR, etc.).</li>
                        <li>Use modes like <strong>Daily Races</strong>, <strong>Daily Missions</strong>, and <strong>Events</strong> for steady revenue of "Monies" and other rewards.</li>
                        <li>Global version launched June 26, 2025 on <strong>PC (Steam)</strong>, <strong>iOS</strong>, and <strong>Android</strong>, with cross-platform linking enabled.</li>
                        <li>Daily resets for missions and scouting adhere to <strong>JST midnight</strong> timing.</li>
                    </ul>
                </section>

                <section class="mb-5" id="glossary">
                    <h3>Glossary of Terms</h3>
                    <dl class="row">
                        <dt class="col-sm-3">Trainee</dt><dd class="col-sm-9">The Uma Musume you select to train in Career Mode.</dd>
                        <dt class="col-sm-3">Support Card</dt><dd class="col-sm-9">Cards that provide stat boosts, skill assistance, and unlock Rainbow Training.</dd>
                        <dt class="col-sm-3">Legacy / Veteran</dt><dd class="col-sm-9">Uma Musume who have completed a career and can be used to boost new trainees via inheritance.</dd>
                        <dt class="col-sm-3">Rainbow Training</dt><dd class="col-sm-9">A special training event triggered by high bond with support cards, giving major stat boosts.</dd>
                        <dt class="col-sm-3">Skill Categories</dt><dd class="col-sm-9">Acceleration, Starting Gate, Lane Change, Recovery, Passive, Debuff, Observation, etc.</dd>
                        <dt class="col-sm-3">Energy</dt><dd class="col-sm-9">Resource spent on training and racing; low energy increases risk of failed training.</dd>
                        <dt class="col-sm-3">Mood</dt><dd class="col-sm-9">Affects training and race performance; Great mood gives bonuses.</dd>
                        <dt class="col-sm-3">Fan Milestones</dt><dd class="col-sm-9">Goals for accumulating fans, unlocking new races and rewards.</dd>
                        <dt class="col-sm-3">Goddess Statue & Star Pieces</dt><dd class="col-sm-9">Gacha currency for upgrading trainee rarity and exchanging for rewards.</dd>
                    </dl>
                </section>

            </div>
        </div>
    </main>
    {{-- Footer provided by layouts.app (Livewire) --}}
@endsection

@push('scripts')
    {{-- Extra page-specific JS can be pushed here if needed. --}}
@endpush
