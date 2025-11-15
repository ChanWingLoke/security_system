<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_login();

$user_id = $_SESSION['user_id'];

// ⚠ Basic query – later you'll secure with prepared statements
$sql = "SELECT id, title, category, status, created_at FROM tickets WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);

render_header("Dashboard - Security System");
?>

<div class="row">
  <div class="col-md-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">My Tickets</h2>
      <a href="ticket_create.php" class="btn btn-primary">Create New Ticket</a>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        You have not created any tickets yet.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
render_footer();
