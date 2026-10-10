<?php
require_once 'db.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
$lost_item_id = intval($_GET['lost_item_id'] ?? 0);
$isCreatingFromLostItem = $id === 0 && $lost_item_id > 0;
$error = '';
$success = '';

if ($isCreatingFromLostItem) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND item_type = 'Lost'");
    $stmt->execute([$lost_item_id]);
    $lostItem = $stmt->fetch();

    if (!$lostItem) {
        header("Location: lost_items.php");
        exit;
    }

    $item = [
        'title' => $lostItem['title'],
        'category_id' => $lostItem['category_id'],
        'location' => $lostItem['location'],
        'event_date' => $lostItem['event_date'],
        'status_id' => 1,
        'description' => $lostItem['description'],
    ];
} else {
    // Fetch the existing found item to edit.
    $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND item_type = 'Found'");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
        header("Location: found_items.php");
        exit;
    }

    // Lock edits to the post creator or an admin/staff account.
    if ($_SESSION['user_id'] != $item['user_id'] && !isAdminOrStaff()) {
        header("Location: found_items.php");
        exit;
    }
}

// 3. Process Post data form submission adjustments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $status_id = $isCreatingFromLostItem ? 1 : intval($_POST['status_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || $category_id <= 0 || empty($location) || empty($event_date) || (!$isCreatingFromLostItem && $status_id <= 0) || empty($description)) {
        $error = 'Please fill in all required fields completely.';
    } elseif ($isCreatingFromLostItem) {
        $stmtInsert = $pdo->prepare("INSERT INTO items (user_id, status_id, item_type, related_lost_item_id, category_id, title, description, location, event_date) VALUES (?, 1, 'Found', ?, ?, ?, ?, ?, ?)");
        if ($stmtInsert->execute([$_SESSION['user_id'], $lost_item_id, $category_id, $title, $description, $location, $event_date])) {
            header("Location: found_detail.php?id=" . $pdo->lastInsertId());
            exit;
        }
        $error = 'System error occurred. Failed to create the linked found item report.';
    } else {
        // Enforce changes onto your real schema table column values
        $stmtUpdate = $pdo->prepare("UPDATE items SET title = ?, category_id = ?, location = ?, event_date = ?, status_id = ?, description = ?, updated_at = NOW() WHERE item_id = ?");
        if ($stmtUpdate->execute([$title, $category_id, $location, $event_date, $status_id, $description, $id])) {
            $success = 'Found item listing has been successfully updated.';
            
            // Re-sync row array targets locally for field persistence
            $item['title'] = $title;
            $item['category_id'] = $category_id;
            $item['location'] = $location;
            $item['event_date'] = $event_date;
            $item['status_id'] = $status_id;
            $item['description'] = $description;
        } else {
            $error = 'System error occurred. Failed to apply changes onto the database catalog.';
        }
    }
}

// 4. Extract lookups directly from operational schema master tables
$categories = $pdo->query("SELECT * FROM categories ORDER BY category ASC")->fetchAll();
$statuses = $pdo->query("SELECT * FROM i_status ORDER BY status_id ASC")->fetchAll();

include 'header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-4" style="border-radius: 12px;">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-edit text-success fa-3x mb-2"></i>
                    <h3 class="fw-bold"><?php echo $isCreatingFromLostItem ? 'Report Found Item' : 'Edit Found Item Listing'; ?></h3>
                    <p class="text-muted"><?php echo $isCreatingFromLostItem ? 'Details are copied from the lost item. Update the location and date to where and when you found it.' : 'Modify the details of your posted found item entry'; ?></p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-1"></i> <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="edit_found.php?<?php echo $isCreatingFromLostItem ? 'lost_item_id=' . $lost_item_id : 'id=' . $id; ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Item Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-bold">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($item['category_id']) && intval($item['category_id']) === intval($cat['category_id'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label fw-bold">Date Found *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required value="<?php echo htmlspecialchars($item['event_date'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="location" class="form-label fw-bold">Location Found *</label>
                            <input type="text" class="form-control" id="location" name="location" required value="<?php echo htmlspecialchars($item['location'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <?php if ($isCreatingFromLostItem): ?>
                                <label for="status" class="form-label fw-bold">Item Status</label>
                                <input type="text" id="status" class="form-control" value="Open" readonly>
                            <?php else: ?>
                                <label for="status_id" class="form-label fw-bold">Item Status *</label>
                                <select class="form-select" id="status_id" name="status_id" required>
                                    <option value="">Select Status</option>
                                    <?php foreach ($statuses as $stat): ?>
                                        <option value="<?php echo $stat['status_id']; ?>" <?php echo (isset($item['status_id']) && intval($item['status_id']) === intval($stat['status_id'])) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($stat['status']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Item Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo $isCreatingFromLostItem ? 'lost_detail.php?id=' . $lost_item_id : 'found_detail.php?id=' . $id; ?>" class="btn btn-outline-secondary fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-success rounded-pill fw-bold px-4"><i class="fa-solid fa-save me-1"></i> <?php echo $isCreatingFromLostItem ? 'Create Found Report' : 'Save Changes'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
