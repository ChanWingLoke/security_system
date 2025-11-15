<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function render_header($title = "Security System") {
  ?>
  <!doctype html>
  <html lang="en">
    <head>
      <meta charset="utf-8">
      <title><?= htmlspecialchars($title) ?></title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
      >
    </head>
    <body class="bg-light">
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
          <a class="navbar-brand" href="/security_system/public/index.php">
            Security System
          </a>
          <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
              <?php if (!empty($_SESSION['user_id'])): ?>
                <li class="nav-item">
                  <span class="navbar-text me-3">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    (<?= htmlspecialchars($_SESSION['user_role'] ?? 'user') ?>)
                  </span>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/security_system/public/dashboard.php">Dashboard</a>
                </li>

                <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                  <li class="nav-item">
                    <a class="nav-link" href="/security_system/admin/tickets.php">Admin Tickets</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="/security_system/admin/logs.php">Audit Logs</a>
                  </li>
                <?php endif; ?>

                <li class="nav-item">
                  <a class="nav-link" href="/security_system/public/logout.php">Logout</a>
                </li>
              <?php else: ?>
                <!-- login/register links -->
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </nav>
      <div class="container mb-5">
  <?php
}

function render_footer() {
  ?>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
  </html>
  <?php
}
