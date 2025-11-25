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
    $user_id     = $_SESSION['user_id'];
    $title       = $_POST['title'] ?? '';
    $category    = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';

    // Minimal validation (intentionally weak)
    if ($title === '') {
        $errors[] = "Title is required.";
    }
    if ($description === '') {
        $errors[] = "Description is required.";
    }

    if (empty($errors)) {
        // ✅ SECURE: Use prepared statements within a try-catch block for graceful failure.
        try {
            $sql = "
            INSERT INTO tickets (user_id, title, category, description, status)
            VALUES (?, ?, ?, ?, 'Open')
          ";

          $stmt = $conn->prepare($sql);

          if ($stmt === false) {
            $errors[] = "Database error: " . $conn->error;
          } else {
            $stmt->bind_param('isss', $user_id, $title, $category, $description);

            if ($stmt-> execute()) {
              redirect('/security_system/public/dashboard.php');
            } else {
              $errors[] = "Error creating ticket: " . $conn->error;
            }
          }

          $stmt->close();
          
        } catch (mysqli_sql_exception $e) {
            // Catches the specific database exception (like the syntax error you saw)
            // 🛑 SECURITY FIX: Log the detailed error internally and show a generic message.
            error_log("Database Exception for user $user_id: " . $e->getMessage());
            $errors[] = "We encountered a problem submitting your ticket. Please check your inputs and try again.";

        } catch (Exception $e) {
            // Catches general errors (like the custom exceptions thrown above)
            error_log("General Error during ticket creation for user $user_id: " . $e->getMessage());
            $errors[] = "An unexpected server error occurred. Please try again later.";
    }
}

render_header("Create Ticket - Security System");
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="app-card p-4">
      <h2 class="app-section-title mb-3">Create Support Ticket</h2>
      <p class="text-muted mb-4">
        Describe your issue as clearly as possible so the support team can assist you.
      </p>

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
          <label class="form-label fw-semibold">Title</label>
          <input
            type="text"
            name="title"
            class="form-control"
            value="<?= htmlspecialchars($title) ?>"
            placeholder="e.g. Laptop not booting"
          >
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Category <span class="text-muted">(optional)</span></label>
          <input
            type="text"
            name="category"
            class="form-control"
            value="<?= htmlspecialchars($category) ?>"
            placeholder="e.g. Laptop, Email, Network"
          >
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Description</label>
          <textarea
            name="description"
            rows="4"
            class="form-control"
            placeholder="Give more details about the problem..."
          ><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="dashboard.php" class="btn btn-outline-secondary btn-pill">Back to Dashboard</a>
          <button type="submit" class="btn btn-primary btn-pill">
            Submit Ticket
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
render_footer();
