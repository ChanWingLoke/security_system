<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_admin();

if (isset($_GET['update_id']) && isset($_GET['status'])) {
    $ticket_id  = $_GET['update_id'];   // not cast, no validation
    $new_status = $_GET['status'];      // could be anything

    $sql_update = "
        UPDATE tickets
        SET status = '$new_status'
        WHERE id = $ticket_id
    ";

    $conn->query($sql_update);

    $admin_id = $_SESSION['user_id'] ?? null;
    log_event(
        $admin_id,
        'TICKET_STATUS_CHANGED',
        "Admin changed ticket #$ticket_id status to $new_status"
    );
}

$sql = "
    SELECT t.id, t.title, t.category, t.status, t.created_at,
           u.name AS user_name, u.email AS user_email
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
";
$result = $conn->query($sql);

function status_badge_class($status) {
    switch ($status) {
        case 'In Progress': return 'badge-status-progress';
        case 'Resolved':    return 'badge-status-resolved';
        case 'Closed':      return 'badge-status-closed';
        default:            return 'badge-status-open';
    }
}

render_header("Admin Tickets - Security System");
?>

<div class="row mb-3">
  <div class="col-md-8">
    <h2 class="app-section-title mb-1">All Tickets</h2>
    <p class="text-muted mb-0">
      As an admin, you can view and update the status of every ticket in the system.
    </p>
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
              <th>User</th>
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th style="width: 200px;">Created</th>
              <th style="width: 260px;">Actions (Insecure)</th>
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
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars($row['user_name']) ?></div>
                  <div class="text-muted small"><?= htmlspecialchars($row['user_email']) ?></div>
                </td>
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
                <td>
                  <!-- ⚠ ALL of these links remain insecure on purpose -->
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="tickets.php?update_id=<?= $row['id'] ?>&status=Open" class="btn btn-outline-secondary">Open</a>
                    <a href="tickets.php?update_id=<?= $row['id'] ?>&status=In%20Progress" class="btn btn-outline-primary">In Progress</a>
                    <a href="tickets.php?update_id=<?= $row['id'] ?>&status=Resolved" class="btn btn-outline-success">Resolved</a>
                    <a href="tickets.php?update_id=<?= $row['id'] ?>&status=Closed" class="btn btn-outline-dark">Closed</a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="app-card p-4 text-center">
        <p class="mb-2 fw-semibold">No tickets found.</p>
        <p class="text-muted mb-0">Tickets created by users will appear here.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
render_footer();
