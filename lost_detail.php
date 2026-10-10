<?php
require_once 'db.php';
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT i.*, CONCAT(u.firstname, ' ', u.lastname) AS full_name, u.email, u.phone, c.category, s.status 
                       FROM items i 
                       JOIN users u ON i.user_id = u.user_id 
                       JOIN categories c ON i.category_id = c.category_id
                       JOIN i_status s ON i.status_id = s.status_id
                       WHERE i.item_id = ? AND i.item_type = 'Lost'");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: lost_items.php");
    exit;
}

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="lost_items.php">Lost Items</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['title']); ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger mb-2"><?php echo htmlspecialchars($item['category']); ?></span>
                        <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($item['status']); ?></span>
                        <h2 class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></h2>
                    </div>
                    <span class="text-muted small">Posted: <?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted fw-bold small uppercase"><i class="fa-solid fa-map-marker-alt text-danger me-1"></i> Location Lost</h6>
                        <p class="fs-6"><?php echo htmlspecialchars($item['location']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted fw-bold small uppercase"><i class="fa-solid fa-calendar text-primary me-1"></i> Date Lost</h6>
                        <p class="fs-6"><?php echo htmlspecialchars($item['event_date']); ?></p>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold mb-2">Item Description</h5>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    </div>
                </div>

                <div class="card bg-light border-0 p-3 mb-4">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-user-circle me-1"></i> Contact Reporter</h6>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($item['full_name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($item['email']); ?></p>
                    <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($item['phone'] ?? 'Not provided'); ?></p>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="lost_items.php" class="btn btn-outline-success fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Back to Catalog</a>
                    <div class="d-flex gap-2">
                        <?php if (isLoggedIn()): ?>
                            <a href="edit_found.php?lost_item_id=<?php echo $item['item_id']; ?>" class="btn btn-success fw-bold"><i class="fa-solid fa-box-open me-1"></i> Report Found Item</a>
                            <?php if ($_SESSION['user_id'] == $item['user_id'] || isAdminOrStaff()): ?>
                                <a href="edit_lost.php?id=<?php echo $item['item_id']; ?>" class="btn btn-warning fw-bold"><i class="fa-solid fa-edit me-1"></i> Edit Item</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-success fw-bold"><i class="fa-solid fa-box-open me-1"></i> Log in to Report Found Item</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>