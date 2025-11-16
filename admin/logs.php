<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_admin();

$sql = "SELECT id, user_id, action, details, created_at FROM audit_logs ORDER BY created_at DESC";
$result = $conn->query($sql);

render_header("Audit Logs - Security System");
?>

<div class="row mb-3">
  <div class="col-md-8">
    <h2 class="app-section-title mb-1">Audit Logs</h2>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive app-card p-3">
        <table class="table table-sm table-striped mb-0 align-middle">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th style="width: 80px;">User ID</th>
              <th style="width: 170px;">Action</th>
              <th>Details</th>
              <th style="width: 200px;">Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><span class="fw-semibold"><?= htmlspecialchars($row['action']) ?></span></td>
                <td class="small"><?= htmlspecialchars($row['details']) ?></td>
                <td><span class="text-muted small"><?= htmlspecialchars($row['created_at']) ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="app-card p-4 text-center">
        <p class="mb-2 fw-semibold">No logs yet.</p>
        <p class="text-muted mb-0">Once users start logging in and creating tickets, events will appear here.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
render_footer();
