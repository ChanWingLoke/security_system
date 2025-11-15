<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

require_login();

$errors = [];
$title = '';
$category = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $title      = $_POST['title'] ?? '';
    $category   = $_POST['category'] ?? '';
    $description= $_POST['description'] ?? '';

    // Minimal validation (intentionally weak)
    if ($title === '') {
        $errors[] = "Title is required.";
    }
    if ($description === '') {
        $errors[] = "Description is required.";
    }

    if (empty($errors)) {
        $sql = "
            INSERT INTO tickets (user_id, title, category, description, status)
            VALUES ($user_id, '$title', '$category', '$description', 'Open')
        ";

        if ($conn->query($sql) === TRUE) {
            $new_ticket_id = $conn->insert_id;

            // log ticket creation (again, details are quite verbose on purpose)
            log_event(
                $user_id,
                'TICKET_CREATED',
                "Ticket #$new_ticket_id created with title: $title"
            );

            redirect('/security_system/public/dashboard.php');
        } else {
            $errors[] = "Error creating ticket: " . $conn->error;
        }
    }

}

render_header("Create Ticket - Security System");
?>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="mb-4 text-center">Create Support Ticket</h2>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input
              type="text"
              name="title"
              class="form-control"
              value="<?= htmlspecialchars($title) ?>"
            >
          </div>

          <div class="mb-3">
            <label class="form-label">Category (optional)</label>
            <input
              type="text"
              name="category"
              class="form-control"
              value="<?= htmlspecialchars($category) ?>"
            >
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea
              name="description"
              rows="4"
              class="form-control"
            ><?= htmlspecialchars($description) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            Submit Ticket
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
render_footer();
