@extends('layouts.app')

@section('content')
    {{-- Navbar provided by layouts.app (Livewire) --}}
    {{-- Sticky sub-navigation for the guide page (Livewire) --}}
    <livewire:guide-sticky-nav />

    <main class="container my-4" data-testid="guide-page">
        {{-- The main banner is included via the app layout, but we add the card structure here. --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h3 mb-0"><i class="bi bi-book-fill me-2"></i>Application Guide</h1>
            </div>
            <div class="card-body p-lg-5">

                <section class="mb-5 p-4 p-md-5 rounded shadow-sm section-highlight" id="welcome">
                    <h2>Welcome to Uma Musume Career Planner</h2>
                    <p class="lead">This planner is designed to mirror the authentic mechanics of Uma Musume: Pretty Derby
                        as seen on the Global English server. Track your trainee's journey from early training to URA
                        Finals, leveraging support cards, veteran legacies, and strategic skill selection for true
                        competitive depth.</p>
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                        <strong>New to the app?</strong> Start by creating your first plan from the Dashboard, then use this
                        guide to learn about each feature.
                    </div>
                </section>

                <section class="mb-5" id="dashboard">
                    <h3>Dashboard &amp; Plan Management</h3>
                    <p>The Dashboard is your central hub for managing all career plans. Here you can:</p>
                    <ul>
                        <li><strong>View all plans</strong> — See your Local and Account plans in one unified list</li>
                        <li><strong>Filter and search</strong> — Filter by status, strategy, or storage mode</li>
                        <li><strong>Quick actions</strong> — View, edit, duplicate, or delete plans directly from the list
                        </li>
                        <li><strong>Create new plans</strong> — Click the "Create Plan" button to start a new career run
                        </li>
                    </ul>
                    <h5 class="mt-4">Storage Modes</h5>
                    <ul>
                        <li><strong>Local Storage</strong> <span class="badge bg-warning text-dark">Local</span> — Plans
                            stored in your browser. Works offline, no account needed.</li>
                        <li><strong>Account Storage</strong> <span class="badge bg-purple text-white">Account</span> — Plans
                            stored on the server. Requires sign-in, accessible from any device.</li>
                    </ul>
                    <figure class="text-center mt-4">
                        <img src="{{ asset('uploads/screenshot/Homepage.png') }}" class="img-fluid shadow-sm rounded"
                            alt="Career Mode Dashboard showing plan list and statistics" loading="lazy">
                        <figcaption class="text-muted small mt-2">Dashboard with plan list and quick actions</figcaption>
                    </figure>
                </section>

                <section class="mb-5" id="plan-editor">
                    <h3>Plan Editor</h3>
                    <p>The Plan Editor provides a comprehensive tabbed interface for managing all aspects of your career
                        run:</p>

                    <h5 class="mt-4"><i class="bi bi-gear me-2" aria-hidden="true"></i>General Tab</h5>
                    <ul>
                        <li>Set plan title and select your trainee character</li>
                        <li>Track career stage (Junior, Classic, Senior year)</li>
                        <li>Monitor mood and conditions affecting training</li>
                        <li>Upload a custom trainee image</li>
                    </ul>

                    <h5 class="mt-4"><i class="bi bi-bar-chart me-2" aria-hidden="true"></i>Attributes Tab</h5>
                    <ul>
                        <li>Track all five core stats: <span class="text-primary">Speed</span>, <span
                                class="text-success">Stamina</span>, <span class="text-danger">Power</span>, <span
                                class="text-warning">Guts</span>, <span class="text-purple">Wit</span></li>
                        <li>View growth rate bonuses from your character</li>
                        <li>Stats are capped at 1200 (hard maximum)</li>
                    </ul>

                    <h5 class="mt-4"><i class="bi bi-star me-2" aria-hidden="true"></i>Aptitude Grades Tab</h5>
                    <ul>
                        <li><strong>Terrain:</strong> Turf (芝) and Dirt (ダート) suitability</li>
                        <li><strong>Distance:</strong> Sprint, Mile, Medium, Long race preferences</li>
                        <li><strong>Style:</strong> Front Runner, Pace Chaser, Late Surger, End Closer strategies</li>
                        <li>Grades range from SS (120% effectiveness) to G (40% effectiveness)</li>
                    </ul>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <img src="{{ asset('uploads/screenshot/001_GENERAL Edit Plan.png') }}"
                                class="img-fluid rounded shadow-sm" alt="General Tab showing plan details" loading="lazy">
                            <p class="text-muted small text-center mt-2">General Tab</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <img src="{{ asset('uploads/screenshot/002_ATTRIBUTES Edit Plan.png') }}"
                                class="img-fluid rounded shadow-sm" alt="Attributes Tab showing stat values" loading="lazy">
                            <p class="text-muted small text-center mt-2">Attributes Tab</p>
                        </div>
                    </div>
                </section>

                <section class="mb-5" id="skills">
                    <h3>Skills Management</h3>
                    <p>Skills are abilities your Uma Musume can acquire during training. Effective skill management is key
                        to race success.</p>

                    <h5 class="mt-4">Skill Status</h5>
                    <ul>
                        <li><span class="badge bg-success">Acquired</span> — Skill has been purchased with SP</li>
                        <li><span class="badge bg-secondary">Skipped</span> — Decided not to purchase this skill</li>
                        <li><span class="badge bg-info">Suggested</span> — Recommended but not yet decided</li>
                    </ul>

                    <h5 class="mt-4">Skill Tiers</h5>
                    <p>Skills are ranked from G- (lowest) to SS (highest). Higher tier skills are more powerful but cost
                        more SP:</p>
                    <ul>
                        <li><strong>G-tier to F-tier:</strong> Basic skills, low SP cost</li>
                        <li><strong>E-tier to C-tier:</strong> Intermediate skills, moderate SP cost</li>
                        <li><strong>B-tier to A-tier:</strong> Advanced skills, higher SP cost</li>
                        <li><strong>S-tier to SS-tier:</strong> Elite skills, SS requires max stats (1200)</li>
                    </ul>

                    <h5 class="mt-4">Skill Categories</h5>
                    <ul>
                        <li><strong>Speed:</strong> Increase maximum running speed</li>
                        <li><strong>Stamina:</strong> Improve endurance and HP recovery</li>
                        <li><strong>Power:</strong> Boost acceleration and lane changes</li>
                        <li><strong>Guts:</strong> Enhance last spurt performance</li>
                        <li><strong>Wit:</strong> Improve skill activation rate</li>
                        <li><strong>Debuff:</strong> Hinder opponents during races</li>
                    </ul>

                    <figure class="text-center mt-4">
                        <img src="{{ asset('uploads/screenshot/004_SKILLS Edit Plan.png') }}"
                            class="img-fluid shadow-sm rounded" alt="Skills Tab showing skill list and SP totals"
                            loading="lazy">
                        <figcaption class="text-muted small mt-2">Skills Tab with autocomplete and SP tracking</figcaption>
                    </figure>
                </section>

                <section class="mb-5" id="races">
                    <h3>Race Predictions &amp; Snapshots</h3>
                    <p>Plan your race schedule and track outcomes throughout your career run.</p>

                    <h5 class="mt-4">Race Predictions</h5>
                    <ul>
                        <li>Add races you plan to enter with venue, distance, and track type</li>
                        <li>Set predicted placements and track actual results</li>
                        <li>View recommended stamina thresholds by distance:
                            <ul>
                                <li>Sprint (1000-1400m): 350+ stamina</li>
                                <li>Mile (1401-1800m): 400+ stamina</li>
                                <li>Medium (1801-2400m): 500+ stamina</li>
                                <li>Long (2401-3600m): 600+ stamina</li>
                            </ul>
                        </li>
                    </ul>

                    <h5 class="mt-4">Race Snapshots</h5>
                    <p>Capture your character's state before important races:</p>
                    <ul>
                        <li>Stats, skills, aptitudes, mood, and conditions are recorded</li>
                        <li>Compare snapshots to current stats to see progression</li>
                        <li>Up to 20 snapshots per plan</li>
                    </ul>

                    <figure class="text-center mt-4">
                        <img src="{{ asset('uploads/screenshot/005_RACE PREDICTIONS Edit Plan.png') }}"
                            class="img-fluid shadow-sm rounded" alt="Race Predictions Tab showing race schedule"
                            loading="lazy">
                        <figcaption class="text-muted small mt-2">Race Predictions with stamina recommendations</figcaption>
                    </figure>
                </section>

                <section class="mb-5" id="goals">
                    <h3>Goals &amp; Turn Tracking</h3>

                    <h5 class="mt-4">Goals</h5>
                    <p>Set training objectives and track your progress:</p>
                    <ul>
                        <li>Add custom goals for your career run</li>
                        <li>Mark goals as complete when achieved</li>
                        <li>Completed goals are visually distinguished</li>
                    </ul>

                    <h5 class="mt-4">Turn-by-Turn Tracking</h5>
                    <p>Careers span approximately 70-72 turns across three years:</p>
                    <ul>
                        <li><strong>Junior Year:</strong> Early training and foundation building</li>
                        <li><strong>Classic Year:</strong> Mid-career development and key races</li>
                        <li><strong>Senior Year:</strong> Final push toward URA Finals</li>
                    </ul>
                    <p>Each turn represents half a month. Log your stats at each turn to track progression and identify
                        training patterns.</p>

                    <figure class="text-center mt-4">
                        <img src="{{ asset('uploads/screenshot/006_GOALS Edit Plan.png') }}"
                            class="img-fluid shadow-sm rounded" alt="Goals Tab showing goal checklist" loading="lazy">
                        <figcaption class="text-muted small mt-2">Goals Tab with completion tracking</figcaption>
                    </figure>
                </section>

                <section class="mb-5" id="export-import">
                    <h3>Export &amp; Import</h3>

                    <h5 class="mt-4">Exporting Plans</h5>
                    <p>Back up or share your plans in multiple formats:</p>
                    <ul>
                        <li><strong>JSON:</strong> Full data export for backup and transfer</li>
                        <li><strong>Text/Markdown:</strong> Human-readable format for sharing in forums or chat</li>
                        <li><strong>Copy to Clipboard:</strong> Quick sharing without downloading</li>
                    </ul>

                    <h5 class="mt-4">Importing Plans</h5>
                    <p>Restore plans from backup files:</p>
                    <ul>
                        <li>Upload JSON files exported from this app</li>
                        <li>Preview imports before confirming</li>
                        <li>Handle conflicts: Skip, Overwrite, or Import as Copy</li>
                        <li>Choose destination: Local or Account storage</li>
                    </ul>
                </section>

                <section class="mb-5" id="local-data">
                    <h3>Local Data Management</h3>
                    <p>Manage your locally-stored plans from the <a href="{{ route('local-data') }}">Local Data Manager</a>:
                    </p>
                    <ul>
                        <li><strong>Storage Stats:</strong> See how much browser storage you're using</li>
                        <li><strong>Export All:</strong> Download all local plans as a single backup file</li>
                        <li><strong>Import:</strong> Restore plans from a backup file</li>
                        <li><strong>Purge All:</strong> Clear all local data (requires double confirmation)</li>
                        <li><strong>Convert to Account:</strong> Move local plans to your account (requires sign-in)</li>
                    </ul>
                    <div class="alert alert-warning mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                        <strong>Storage Limit:</strong> Browser localStorage is limited to ~5-10MB. Export your data
                        regularly to avoid data loss.
                    </div>
                </section>

                <section class="mb-5" id="characters">
                    <h3>Character Roster</h3>
                    <p>Browse the Uma Musume character database from the <a href="{{ route('characters') }}">Characters
                            page</a>:</p>
                    <ul>
                        <li>View all available Uma Musume characters</li>
                        <li>Filter by name, team, rarity, or specialty distance</li>
                        <li>See base stats, growth rates, and default aptitudes</li>
                        <li>Select a character when creating a plan to auto-populate growth rates</li>
                    </ul>
                </section>

                <section class="mb-5" id="keyboard">
                    <h3>Keyboard Shortcuts</h3>
                    <p>Speed up your workflow with these keyboard shortcuts:</p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Shortcut</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><kbd>Ctrl</kbd> + <kbd>S</kbd></td>
                                <td>Save current plan</td>
                            </tr>
                            <tr>
                                <td><kbd>N</kbd></td>
                                <td>Create new plan (from Dashboard)</td>
                            </tr>
                            <tr>
                                <td><kbd>Escape</kbd></td>
                                <td>Close modal or cancel action</td>
                            </tr>
                            <tr>
                                <td><kbd>?</kbd></td>
                                <td>Show keyboard shortcuts help</td>
                            </tr>
                            <tr>
                                <td><kbd>Tab</kbd></td>
                                <td>Navigate between form fields</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="mb-5" id="glossary">
                    <h3>Glossary of Terms</h3>
                    <dl class="row">
                        <dt class="col-sm-3">Trainee</dt>
                        <dd class="col-sm-9">The Uma Musume you select to train in Career Mode.</dd>

                        <dt class="col-sm-3">Support Card</dt>
                        <dd class="col-sm-9">Cards that provide stat boosts, skill assistance, and unlock Rainbow Training.
                        </dd>

                        <dt class="col-sm-3">Legacy / Veteran</dt>
                        <dd class="col-sm-9">Uma Musume who have completed a career and can be used to boost new trainees
                            via inheritance.</dd>

                        <dt class="col-sm-3">Rainbow Training</dt>
                        <dd class="col-sm-9">A special training event triggered by high bond with support cards, giving
                            major stat boosts.</dd>

                        <dt class="col-sm-3">SP (Skill Points)</dt>
                        <dd class="col-sm-9">Currency earned from races, used to purchase skills.</dd>

                        <dt class="col-sm-3">Aptitude Grade</dt>
                        <dd class="col-sm-9">Rating (SS to G) indicating suitability for terrain, distance, or running
                            style.</dd>

                        <dt class="col-sm-3">Energy</dt>
                        <dd class="col-sm-9">Resource spent on training and racing; low energy increases risk of failed
                            training.</dd>

                        <dt class="col-sm-3">Mood</dt>
                        <dd class="col-sm-9">Affects training and race performance; Great mood gives ~20% training bonus
                            and ~4% race bonus.</dd>

                        <dt class="col-sm-3">URA Finals</dt>
                        <dd class="col-sm-9">The final race series at the end of Senior Year, the ultimate goal of Career
                            Mode.</dd>

                        <dt class="col-sm-3">Turn</dt>
                        <dd class="col-sm-9">A single progression point representing half a month; careers span ~70-72
                            turns.</dd>
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
