<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_admin();

// ⚠ For now, get ALL logs, no paging, no filtering
$sql = "SELECT id, user_id, action, details, created_at FROM audit_logs ORDER BY created_at DESC";
$result = $conn->query($sql);

render_header("Audit Logs - Security System");
?>

<div class="row">
  <div class="col-12">
    <h2 class="mb-3">Audit Logs (Insecure Baseline)</h2>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-sm table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>User ID</th>
              <th>Action</th>
              <th>Details</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['action']) ?></td>
                <td><?= htmlspecialchars($row['details']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        No logs yet.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
render_footer();
