<?php
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Uma Musume Race Planner</title>
		<!-- Bootstrap 5 + Icons CDN -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<!-- Chart.js CDN -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<!-- Custom CSS -->
		<link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../assets/css/theme.css">
  <link rel="stylesheet" href="../assets/css/accessibility.css">
</head>
<body>
  <a href="#main" class="skip-link">Skip to main content</a>
  <?php include '../components/navbar.php'; ?>
  <main id="main" class="container my-4" role="main">
    <!-- Example: Quick Stats Chart Card -->
    <section aria-labelledby="stats-heading" class="mb-4">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h2 id="stats-heading" class="h5 mb-0">Quick Stats</h2>
          <div class="ms-auto text-muted small" id="stats-status" aria-live="polite"></div>
        </div>
        <div class="card-body">
          <div class="ratio ratio-16x9">
            <canvas id="statsRadar" aria-label="Overall stats radar chart" role="img"></canvas>
          </div>
        </div>
      </div>
    </section>
    <!-- ...existing content... -->
  </main>
  <!-- ...existing footer... -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
  <script src="../assets/js/app.js" defer></script>
  <script src="../assets/js/dashboard.js" defer></script>
</body>
</html>