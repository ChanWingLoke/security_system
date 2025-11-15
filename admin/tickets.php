<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

// Only admin should access this page (basic RBAC)
require_admin();

// ⚠ Insecure status update via GET parameters (no CSRF, no validation)
if (isset($_GET['update_id']) && isset($_GET['status'])) {
    $ticket_id = $_GET['update_id'];   // not cast, no validation
    $new_status = $_GET['status'];     // could be anything

    // ⚠ Insecure SQL: no escaping, no prepared statement
    $sql_update = "
        UPDATE tickets
        SET status = '$new_status'
        WHERE id = $ticket_id
    ";

    $conn->query($sql_update);
    // No error handling, no feedback

     // log status change (no validation of status yet)
    $admin_id = $_SESSION['user_id'] ?? null;
    log_event(
      $admin_id,
      'TICKET_STATUS_CHANGED',
      "Admin changed ticket #$ticket_id status to $new_status"
    );
}

// ⚠ Basic query to fetch ALL tickets (admin can see everything)
$sql = "
    SELECT t.id, t.title, t.category, t.status, t.created_at,
           u.name AS user_name, u.email AS user_email
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
";
$result = $conn->query($sql);

render_header("Admin Tickets - Security System");
?>

<div class="row">
  <div class="col-12">
    <h2 class="mb-3">All Tickets (Admin)</h2>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th>Created At</th>
              <th>Actions (Insecure)</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td>
                  <?= htmlspecialchars($row['user_name']) ?><br>
                  <small class="text-muted"><?= htmlspecialchars($row['user_email']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <!-- ⚠ ALL of these links are insecure on purpose -->
                  <a href="tickets.php?update_id=<?= $row['id'] ?>&status=Open" class="btn btn-sm btn-outline-secondary mb-1">Open</a>
                  <a href="tickets.php?update_id=<?= $row['id'] ?>&status=In%20Progress" class="btn btn-sm btn-outline-primary mb-1">In Progress</a>
                  <a href="tickets.php?update_id=<?= $row['id'] ?>&status=Resolved" class="btn btn-sm btn-outline-success mb-1">Resolved</a>
                  <a href="tickets.php?update_id=<?= $row['id'] ?>&status=Closed" class="btn btn-sm btn-outline-dark mb-1">Closed</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        No tickets found.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
render_footer();
