<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_admin();

// ⚠ Insecure status update via GET parameters (no CSRF, no validation)
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

// ⚠ Still insecure: no prepared statements
$sql = "
    SELECT t.id, t.title, t.category, t.status, t.created_at, t.description,
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
                $status     = $row['status'];
                $badgeClass = status_badge_class($status);
              ?>
              <tr>
                <td class="fw-semibold">
                  <!-- Clickable ID opens admin ticket modal -->
                  <button
                    type="button"
                    class="btn btn-link p-0 text-decoration-none admin-ticket-view"
                    data-bs-toggle="modal"
                    data-bs-target="#adminTicketModal"
                    data-id="<?= $row['id'] ?>"
                    data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
                    data-category="<?= htmlspecialchars($row['category'], ENT_QUOTES) ?>"
                    data-status="<?= htmlspecialchars($row['status'], ENT_QUOTES) ?>"
                    data-created="<?= htmlspecialchars($row['created_at'], ENT_QUOTES) ?>"
                    data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                    data-username="<?= htmlspecialchars($row['user_name'], ENT_QUOTES) ?>"
                    data-useremail="<?= htmlspecialchars($row['user_email'], ENT_QUOTES) ?>"
                  >
                    #<?= $row['id'] ?>
                  </button>
                </td>
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

<!-- Admin ticket details modal -->
<div class="modal fade" id="adminTicketModal" tabindex="-1" aria-labelledby="adminTicketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminTicketModalLabel">
          Ticket #<span id="adminModalTicketId"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">User</dt>
          <dd class="col-sm-9" id="adminModalUser"></dd>

          <dt class="col-sm-3">Title</dt>
          <dd class="col-sm-9" id="adminModalTicketTitle"></dd>

          <dt class="col-sm-3">Category</dt>
          <dd class="col-sm-9" id="adminModalTicketCategory"></dd>

          <dt class="col-sm-3">Status</dt>
          <dd class="col-sm-9" id="adminModalTicketStatus"></dd>

          <dt class="col-sm-3">Created At</dt>
          <dd class="col-sm-9" id="adminModalTicketCreated"></dd>

          <dt class="col-sm-3">Description</dt>
          <dd class="col-sm-9" id="adminModalTicketDescription"></dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-pill" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Fill admin modal when clicking on a ticket ID
document.addEventListener('DOMContentLoaded', function () {
  const idEl        = document.getElementById('adminModalTicketId');
  const userEl      = document.getElementById('adminModalUser');
  const titleEl     = document.getElementById('adminModalTicketTitle');
  const catEl       = document.getElementById('adminModalTicketCategory');
  const statusEl    = document.getElementById('adminModalTicketStatus');
  const createdEl   = document.getElementById('adminModalTicketCreated');
  const descEl      = document.getElementById('adminModalTicketDescription');

  document.querySelectorAll('.admin-ticket-view').forEach(function (btn) {
    btn.addEventListener('click', function () {
      idEl.textContent      = btn.dataset.id || '';
      userEl.textContent    = (btn.dataset.username || '') + ' <' + (btn.dataset.useremail || '') + '>';
      titleEl.textContent   = btn.dataset.title || '—';
      catEl.textContent     = btn.dataset.category || '—';
      statusEl.textContent  = btn.dataset.status || '—';
      createdEl.textContent = btn.dataset.created || '—';
      descEl.textContent    = btn.dataset.description || '—';
    });
  });
});
</script>

<?php
render_footer();
