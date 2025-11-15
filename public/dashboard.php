<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_login();

$user_id = $_SESSION['user_id'];

// ⚠ Basic query – later you'll secure with prepared statements
$sql = "SELECT id, title, category, status, created_at FROM tickets WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);
$ticket_count = $result ? $result->num_rows : 0;

function status_badge_class($status) {
    switch ($status) {
        case 'In Progress': return 'badge-status-progress';
        case 'Resolved':    return 'badge-status-resolved';
        case 'Closed':      return 'badge-status-closed';
        default:            return 'badge-status-open';
    }
}

render_header("Dashboard - Security System");
?>

<div class="row mb-4">
  <div class="col-md-8">
    <h2 class="app-section-title mb-1">My Tickets</h2>
    <p class="text-muted mb-0">
      You currently have <strong><?= $ticket_count ?></strong> ticket<?= $ticket_count === 1 ? '' : 's' ?>.
    </p>
  </div>
  <div class="col-md-4 text-md-end mt-3 mt-md-0">
    <a href="ticket_create.php" class="btn btn-primary btn-pill">
      Create New Ticket
    </a>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive app-card p-3">
        <table class="table table-hover mb-0 align-middle">
          <thead>
            <tr>
              <th style="width: 70px;">ID</th>
              <th>Title</th>
              <th style="width: 160px;">Category</th>
              <th style="width: 140px;">Status</th>
              <th style="width: 200px;">Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <?php
                $status = $row['status'];
                $badgeClass = status_badge_class($status);
              ?>
              <tr>
                <td class="fw-semibold">#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td>
                  <?= $row['category'] !== '' ? htmlspecialchars($row['category']) : '<span class="text-muted">—</span>' ?>
                </td>
                <td>
                  <span class="badge <?= $badgeClass ?> px-3 py-2">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td><span class="text-muted small"><?= htmlspecialchars($row['created_at']) ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="app-card p-4 text-center">
        <p class="mb-2 fw-semibold">No tickets yet.</p>
        <p class="text-muted mb-3">Create your first ticket to get support.</p>
        <a href="ticket_create.php" class="btn btn-primary btn-pill">
          Create a Ticket
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
render_footer();
