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

      <!-- Google Font (clean & modern) -->
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

      <!-- Bootstrap -->
      <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
      >

      <style>
        :root {
          --app-bg: #f3f4f6;
          --app-card-bg: #ffffff;
          --app-accent: #2563eb;
          --app-accent-soft: #e0edff;
        }

        body {
          font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
          background: var(--app-bg);
          color: #111827;
        }

        .navbar {
          box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
        }

        .navbar-brand {
          font-weight: 600;
          letter-spacing: 0.03em;
        }

        .navbar-dark .navbar-nav .nav-link {
          font-weight: 500;
          opacity: 0.9;
        }

        .navbar-dark .navbar-nav .nav-link:hover {
          opacity: 1;
        }

        .app-container {
          padding-top: 2rem;
          padding-bottom: 2.5rem;
        }

        .app-card {
          background: var(--app-card-bg);
          border-radius: 1rem;
          box-shadow: 0 15px 35px rgba(15, 23, 42, 0.10);
          border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .app-card-soft {
          background: linear-gradient(135deg, #ffffff, #eef2ff);
          border-radius: 1.25rem;
          box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
          border: 1px solid rgba(129, 140, 248, 0.4);
        }

        .app-section-title {
          font-size: 1.6rem;
          font-weight: 600;
          letter-spacing: 0.02em;
        }

        .table thead th {
          font-size: 0.78rem;
          text-transform: uppercase;
          letter-spacing: 0.08em;
          border-bottom-width: 1px;
          color: #6b7280;
          background: #f9fafb;
        }

        .badge-status-open {
          background-color: #e0f2fe;
          color: #0369a1;
        }
        .badge-status-progress {
          background-color: #fef3c7;
          color: #92400e;
        }
        .badge-status-resolved {
          background-color: #dcfce7;
          color: #166534;
        }
        .badge-status-closed {
          background-color: #e5e7eb;
          color: #374151;
        }

        .btn-pill {
          border-radius: 999px;
        }

        .hero-kicker {
          text-transform: uppercase;
          font-size: 0.8rem;
          letter-spacing: 0.14em;
          font-weight: 600;
          color: #6b7280;
        }

        .hero-title {
          font-size: 2rem;
          font-weight: 700;
          letter-spacing: 0.03em;
          margin-bottom: 0.6rem;
        }

        .hero-subtitle {
          color: #4b5563;
          font-size: 0.98rem;
        }

        .table td {
          vertical-align: middle;
        }
      </style>
    </head>
    <body>
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">

          <?php
            // If user is logged in, send them to dashboard; otherwise, go to landing page
            $homeUrl = !empty($_SESSION['user_id'])
              ? '/security_system/public/dashboard.php'
              : '/security_system/public/index.php';
          ?>
          <a class="navbar-brand" href="<?= $homeUrl ?>">
            Ticket Service System
          </a>

          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-center">
              <?php if (!empty($_SESSION['user_id'])): ?>
                <li class="nav-item me-2">
                  <span class="navbar-text small text-light">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    <span class="text-secondary">
                      (<?= htmlspecialchars($_SESSION['user_role'] ?? 'user') ?>)
                    </span>
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
                <li class="nav-item">
                  <a class="nav-link" href="/security_system/public/login.php">Login</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/security_system/public/register.php">Register</a>
                </li>               
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </nav>

      <div class="container app-container">
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
