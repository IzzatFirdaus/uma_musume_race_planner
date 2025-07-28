<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide - Uma Musume Race Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h3 mb-0"><i class="bi bi-book-fill me-2"></i>Application Guide</h1>
            </div>
            <div class="card-body p-lg-5">
                <div class="mb-5">
                    <h2>Welcome to the Race Planner!</h2>
                    <p class="lead">This planner is your new best friend for guiding your favorite umamusume to victory. Think of it as a digital notebook where you can track everything about a character's training, from their first race to the URA Finals. It helps you stay organized and make smarter training decisions.</p>
                </div>

                <div class="mb-5">
                    <h3>Getting Started: Your Dashboard</h3>
                    <p>When you open the planner, you'll see your main dashboard. It has several key areas:</p>
                    <ul>
                        <li><strong>Your Race Plans:</strong> This is the main list where every plan you create will appear. You can quickly see a plan's status (Active, Planning, etc.) and a set of mini stat bars that give you a quick visual summary of the character's strengths.</li>
                        <li><strong>Filter Controls:</strong> Above the list, you can use buttons to filter your plans by status, making it easy to find exactly what you're looking for.</li>
                        <li><strong>Quick Stats:</strong> This gives you a simple count of how many total plans you have, how many are currently active, and how many you've completed.</li>
                        <li><strong>Recent Activity:</strong> This is a small feed that shows your latest actions, like when you create or update a plan.</li>
                    </ul>
                </div>

                <div class="mb-5">
                    <h3>Creating and Editing Your Plan</h3>
                    <p>You have two main ways to start a new plan.</p>
                    <h5>1. Quick Create</h5>
                    <ol>
                        <li>Click the <strong>"Create New"</strong> or <strong>"New Plan"</strong> button.</li>
                        <li>A small window will pop up. Just fill in your character's name, their next important race, and their current career stage and class.</li>
                        <li>Click <strong>"Create Plan."</strong> A new plan will be added to your list with default stats, ready for you to edit.</li>
                    </ol>

                    <h5 class="mt-4">2. Editing the Details</h5>
                    <p>This is where you'll spend most of your time. To open the editor, click the <strong>"Edit"</strong> button next to any plan. A large window will appear with several tabs to organize all your information.</p>
                    <ul>
                        <li><strong>General & Attributes:</strong> Set the plan's title, status, and input stat values using the interactive sliders and number fields.</li>
                        <li><strong>Skills:</strong> This tab now features **skill autocomplete**. Just start typing a skill's name, and a list of suggestions will appear. When you select one, its information will be displayed for you.</li>
                        <li><strong>Progress Chart (New!):</strong> This tab provides a visual line graph of your character's stat growth over each turn of the plan, helping you see their progress at a glance.</li>
                    </ul>
                    <p class="mt-3">Remember to click <strong>"Save Changes"</strong> when you are done!</p>
                </div>

                <div>
                    <h3>Using Your Plan with an AI Assistant</h3>
                    <p>This planner works wonderfully with an AI chat assistant (like ChatGPT, Gemini, etc.) to help you create powerful training builds.</p>
                    <ol>
                        <li><strong>Ask for a Plan:</strong> Go to your favorite AI chat and ask it something like, <em>"Create a training plan for Haru Urara in Uma Musume, focusing on winning dirt mile races."</em></li>
                        <li><strong>Enter the Data:</strong> Use the AI's response to fill out your plan in the planner. The new skill autocomplete feature makes this step faster than ever.</li>
                        <li><strong>Track Your Progress:</strong> As you play the game, come back to the planner to update your stats and check off the skills you've acquired.</li>
                        <li><strong>Get Advice on the Fly:</strong> If you're unsure what to do next, use the <strong>"Export Plan"</strong> button inside the editor. This copies a clean summary of your entire plan. Paste this summary into your AI chat and ask a follow-up question like, <em>"This is my current progress. What skill should I learn next?"</em></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Basic dark mode persistence for guide page
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const darkModeToggle = document.getElementById('darkModeToggle');
            function setDarkMode(isDarkMode) {
                body.classList.toggle('dark-mode', isDarkMode);
                if (darkModeToggle) darkModeToggle.checked = isDarkMode;
            }
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'enabled') { setDarkMode(true); }
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', () => {
                    localStorage.setItem('darkMode', darkModeToggle.checked ? 'enabled' : 'disabled');
                    setDarkMode(darkModeToggle.checked);
                });
            }
        });
    </script>
</body>
</html>