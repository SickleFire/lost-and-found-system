<?php
require_once 'db.php';
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT f.*, u.full_name, u.email, u.phone FROM found_items f JOIN users u ON f.user_id = u.id WHERE f.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: found_items.php");
    exit;
}

$error = '';
$success = '';

// Handle claim submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $proof_description = trim($_POST['proof_description'] ?? '');

    if (empty($proof_description)) {
        $error = 'Please provide verification details/proof to support your claim.';
    } else {
        // Check if user already claimed this item
        $stmtCheck = $pdo->prepare("SELECT id FROM claims WHERE found_item_id = ? AND user_id = ?");
        $stmtCheck->execute([$id, $_SESSION['user_id']]);
        if ($stmtCheck->fetch()) {
            $error = 'You have already submitted a claim for this found item.';
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO claims (found_item_id, user_id, proof_description, status) VALUES (?, ?, ?, 'Pending')");
            if ($stmtInsert->execute([$id, $_SESSION['user_id']])) {
                // Update found item status to Pending Verification
                $pdo->prepare("UPDATE found_items SET status = 'Pending Verification' WHERE id = ?")->execute([$id]);
                $success = 'Your claim has been successfully submitted and is pending staff verification.';
                // Refresh item status
                $item['status'] = 'Pending Verification';
            } else {
                $error = 'Failed to submit claim. Please try again.';
            }
        }
    }
}

// Fetch existing claims if admin/staff or owner
$claims = [];
if (isLoggedIn() && (isAdminOrStaff() || $_SESSION['user_id'] == $item['user_id'])) {
    $stmtClaims = $pdo->prepare("SELECT c.*, u.full_name, u.email, u.phone FROM claims c JOIN users u ON c.user_id = u.id WHERE c.found_item_id = ?");
    $stmtClaims->execute([$id]);
    $claims = $stmtClaims->fetchAll();
}

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="found_items.php">Found Items</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['title']); ?></li>
                </ol>
            </nav>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><i class="fa-solid fa-check-circle me-1"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-success mb-2"><?php echo htmlspecialchars($item['category']); ?></span>
                        <span class="badge bg-<?php echo $item['status'] === 'Unclaimed' ? 'primary' : ($item['status'] === 'Pending Verification' ? 'warning text-dark' : 'secondary'); ?> mb-2"><?php echo htmlspecialchars($item['status']); ?></span>
                        <h2 class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></h2>
                    </div>
                    <span class="text-muted small">Posted: <?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted fw-bold small uppercase"><i class="fa-solid fa-map-marker-alt text-success me-1"></i> Location Found</h6>
                        <p class="fs-6"><?php echo htmlspecialchars($item['location_found']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted fw-bold small uppercase"><i class="fa-solid fa-calendar text-primary me-1"></i> Date Found</h6>
                        <p class="fs-6"><?php echo htmlspecialchars($item['date_found']); ?></p>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold mb-2">Item Description</h5>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    </div>
                </div>

                <div class="card bg-light border-0 p-3 mb-4">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-user-circle me-1"></i> Finder / Reporter Contact</h6>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($item['full_name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($item['email']); ?></p>
                    <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($item['phone'] ?? 'Not provided'); ?></p>
                </div>

                <!-- Claim Form / Section -->
                <?php if ($item['status'] === 'Claimed'): ?>
                    <div class="alert alert-secondary text-center">
                        <i class="fa-solid fa-lock me-1"></i> This item has already been successfully claimed and returned.
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fa-solid fa-info-circle me-1"></i> Please <a href="login.php" class="fw-bold">Login</a> to file a claim for this found item.
                    </div>
                <?php else: ?>
                    <div class="card border-success p-3">
                        <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-handshake me-1"></i> Claim This Item</h5>
                        <form method="POST" action="found_detail.php?id=<?php echo $item['id']; ?>">
                            <div class="mb-3">
                                <label for="proof_description" class="form-label fw-bold">Verification Details & Proof *</label>
                                <textarea class="form-control" id="proof_description" name="proof_description" rows="3" required placeholder="Describe unique identifying marks, contents, serial numbers, or details that prove ownership..."></textarea>
                                <div class="form-text">Your claim will be verified by community staff before approval.</div>
                            </div>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-paper-plane me-1"></i> Submit Claim Verification</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Claims list for staff or item poster -->
                <?php if (!empty($claims)): ?>
                    <hr class="my-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check me-1"></i> Claims Filed for this Item</h5>
                    <?php foreach ($claims as $claim): ?>
                        <div class="card bg-light border p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <strong class="text-primary"><?php echo htmlspecialchars($claim['full_name']); ?> (<?php echo htmlspecialchars($claim['email']); ?>)</strong>
                                <span class="badge bg-<?php echo $claim['status'] === 'Approved' ? 'success' : ($claim['status'] === 'Rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo htmlspecialchars($claim['status']); ?></span>
                            </div>
                            <p class="mb-1"><strong>Proof:</strong> <?php echo nl2br(htmlspecialchars($claim['proof_description'])); ?></p>
                            <?php if (!empty($claim['admin_notes'])): ?>
                                <p class="mb-0 text-muted small"><strong>Admin Notes:</strong> <?php echo htmlspecialchars($claim['admin_notes']); ?></p>
                            <?php endif; ?>
                            <?php if (isAdminOrStaff()): ?>
                                <div class="mt-2">
                                    <a href="admin/manage_claims.php?action=approve&id=<?php echo $claim['id']; ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i> Approve</a>
                                    <a href="admin/manage_claims.php?action=reject&id=<?php echo $claim['id']; ?>" class="btn btn-danger btn-sm"><i class="fa-solid fa-times me-1"></i> Reject</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="found_items.php" class="btn btn-outline-secondary fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Back to Found Catalog</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
