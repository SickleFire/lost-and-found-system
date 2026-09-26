<?php
require_once 'db.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location_lost = trim($_POST['location_lost'] ?? '');
    $date_lost = $_POST['date_lost'] ?? '';

    if (empty($title) || empty($category) || empty($description) || empty($location_lost) || empty($date_lost)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO lost_items (user_id, title, category, description, location_lost, date_lost, status) VALUES (?, ?, ?, ?, ?, ?, 'Open')");
        if ($stmt->execute([$_SESSION['user_id'], $title, $category, $description, $location_lost, $date_lost])) {
            header("Location: lost_items.php");
            exit;
        } else {
            $error = 'Failed to post lost item. Please try again.';
        }
    }
}

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-search text-primary fa-3x mb-2"></i>
                    <h3 class="fw-bold">Post Lost Item</h3>
                    <p class="text-muted">Provide details about the item you lost so the community can help</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="post_lost.php">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Item Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. iPhone 13 Pro, Brown Leather Wallet" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-bold">Category *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Accessories">Accessories</option>
                                <option value="Documents / ID">Documents / ID</option>
                                <option value="Keys">Keys</option>
                                <option value="Clothing">Clothing</option>
                                <option value="Pets">Pets</option>
                                <option value="Bags / Luggage">Bags / Luggage</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_lost" class="form-label fw-bold">Date Lost *</label>
                            <input type="date" class="form-control" id="date_lost" name="date_lost" required value="<?php echo htmlspecialchars($_POST['date_lost'] ?? date('Y-m-d')); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location_lost" class="form-label fw-bold">Location Lost *</label>
                        <input type="text" class="form-control" id="location_lost" name="location_lost" required placeholder="e.g. Central Park near fountain, Main Street Cafe" value="<?php echo htmlspecialchars($_POST['location_lost'] ?? ''); ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Detailed Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Provide color, brand, unique marks, contents, or serial numbers..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="lost_items.php" class="btn btn-outline-secondary fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fa-solid fa-paper-plane me-1"></i> Submit Lost Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
