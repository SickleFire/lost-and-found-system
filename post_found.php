<?php
require_once 'db.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $event_date = $_POST['event_date'] ?? '';

    if (empty($title) || $category_id <= 0 || empty($description) || empty($location) || empty($event_date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO items (user_id, status_id, item_type, category_id, title, description, location, event_date) VALUES (?, 1, 'Found', ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $location, $event_date])) {
            header("Location: found_items.php");
            exit;
        } else {
            $error = 'Failed to post found item. Please try again.';
        }
    }
}

// Fetch categories for input lookup dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY category ASC")->fetchAll();

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-bullhorn text-success fa-3x mb-2"></i>
                    <h3 class="fw-bold">Post Found Item</h3>
                    <p class="text-muted">Report an item you found so the rightful owner can claim it</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="post_found.php">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Item Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Silver Toyota Car Keys" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-bold">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo (intval($_POST['category_id'] ?? 0) === intval($cat['category_id'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label fw-bold">Date Found *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required value="<?php echo htmlspecialchars($_POST['event_date'] ?? date('Y-m-d')); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label fw-bold">Location Found *</label>
                        <input type="text" class="form-control" id="location" name="location" required placeholder="e.g. Community Center Parking Lot, Bay 3" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Detailed Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Provide general description without revealing secret identifying marks so owners can verify..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="found_items.php" class="btn btn-outline-secondary fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-paper-plane me-1"></i> Submit Found Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>