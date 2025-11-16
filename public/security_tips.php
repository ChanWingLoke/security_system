<?php
// Allow both normal full-page view and popup view
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if this page is opened as a popup (from register page)
$is_popup = isset($_GET['popup']) && $_GET['popup'] == '1';

if (!$is_popup) {
    // Normal mode: use full layout with navbar, etc.
    require_once __DIR__ . '/../includes/layout.php';
    render_header("Data Privacy & Security Policy");
} else {
    // Popup mode: minimal HTML, no navbar
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>Data Privacy & Security Policy</title>
      <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
      >
    </head>
    <body class="bg-white p-3">
    <?php
}
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h2 class="mb-3">Data Privacy & Security Policy</h2>
    <p class="text-muted">
      Last updated: <?= date("F j, Y") ?>
    </p>

    <p>
      This policy explains how personal data is collected, processed, and protected within the
      <strong>Security System</strong>. By using this system, you acknowledge and agree to the
      practices described below.
    </p>

    <hr>

    <h4>1️⃣ Data We Collect</h4>
    <p>The following data is collected to provide user authentication and system functionality:</p>
    <ul>
      <li><strong>Account Information</strong>: Full name, email address, password (securely stored using hashing techniques).</li>
      <li><strong>Support Ticket Information</strong>: Ticket title, category, and description submitted by the user.</li>
      <li><strong>Security &amp; Audit Logs</strong>: Login activity, ticket creation, and administrative actions.</li>
    </ul>

    <hr>

    <h4>2️⃣ How We Use Your Data</h4>
    <ul>
      <li>To authenticate your identity and manage account access.</li>
      <li>To provide support ticket functionality.</li>
      <li>To ensure accountability and traceability through security logs.</li>
      <li>To protect the system against unauthorized access and misuse.</li>
    </ul>

    <hr>

    <h4>3️⃣ Protection of Your Data</h4>
    <p>We apply various technical and procedural measures to safeguard your personal information:</p>
    <ul>
      <li>Passwords are stored using secure cryptographic hashing.</li>
      <li>Only authorized personnel (administrators) have operational access to tickets and system logs.</li>
      <li>User roles determine access level for enhanced security (RBAC enforcement).</li>
      <li>Security logs are maintained for auditing and incident response.</li>
    </ul>

    <p>
      <em>Note: Some security controls are currently being improved as part of ongoing system development.</em>
    </p>

    <hr>

    <h4>4️⃣ Your Privacy Rights</h4>
    <ul>
      <li>You have the right to access and manage your own ticket information.</li>
      <li>Your account will not be shared with or sold to third parties.</li>
      <li>You can request account removal by contacting the system administrator.</li>
    </ul>

    <hr>

    <h4>5️⃣ Data Retention</h4>
    <p>
      Account and ticket information is retained only for operational purposes.
      Audit logs are preserved to ensure system integrity and accountability.
    </p>

    <hr>

    <h4>6️⃣ Responsible Use &amp; Security Awareness</h4>
    <p>To help protect your own data, we recommend the following best practices:</p>
    <ul>
      <li>Use a strong and unique password for your account.</li>
      <li>Do not share your login credentials with anyone.</li>
      <li>Remember to log out after using a shared or public device.</li>
      <li>Be cautious when clicking unknown links or downloading attachments.</li>
    </ul>

    <p class="text-muted">
      For any concerns regarding your privacy or data protection, please contact the system administrator.
    </p>
  </div>
</div>

<?php if (empty($_SESSION['user_id']) && !$is_popup): ?>
  <div class="text-center mt-4">
    <a href="/security_system/public/register.php" class="btn btn-primary">
      Back to Register
    </a>
  </div>
<?php endif; ?>

<?php
// Close page depending on mode
if (!$is_popup) {
    render_footer();
} else {
    ?>
    </body>
    </html>
    <?php
}
?>
