<?php
require_once 'db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$item_id = intval($_GET['item_id'] ?? $_POST['item_id'] ?? 0);
$error = '';
$success = '';

// Fetch found item details
$stmt = $pdo->prepare("SELECT i.*, c.category, s.status, CONCAT(u.firstname, ' ', u.lastname) AS finder_name 
                       FROM items i 
                       JOIN users u ON i.user_id = u.user_id 
                       JOIN categories c ON i.category_id = c.category_id 
                       JOIN i_status s ON i.status_id = s.status_id 
                       WHERE i.item_id = ? AND i.item_type = 'Found'");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: found_items.php");
    exit;
}

// Validation: User cannot claim their own posted item
if ($item['user_id'] == $user_id) {
    $error = "You cannot file a claim on an item that you reported found.";
}

// Items remain claimable while claims are pending (status_id 2 = Pending Verification).
if (!in_array((int) $item['status_id'], [1, 2], true)) {
    $error = "This item is currently " . strtolower($item['status']) . " and is not open for new claims.";
}

// Check if user has already submitted a pending/active claim for this item
$chkClaim = $pdo->prepare("SELECT claim_id FROM claims WHERE item_id = ? AND claimer_user_id = ?");
$chkClaim->execute([$item_id, $user_id]);
$existingClaim = $chkClaim->fetch();

if ($existingClaim) {
    $error = "You have already submitted a claim request for this item. You can track your claim status in your dashboard.";
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $proof_description = trim($_POST['proof_description'] ?? '');

    if (empty($proof_description)) {
        $error = "Please provide detailed ownership proof (serial number, distinctive marks, contents, etc.).";
    } else {
        // Insert claim record (c_status id 1 = Pending in c_status table)
        $stmtInsert = $pdo->prepare("INSERT INTO claims (item_id, claimer_user_id, proof_description, status_id, created_at) VALUES (?, ?, ?, 1, NOW())");
        if ($stmtInsert->execute([$item_id, $user_id, $proof_description])) {
            // Keep the item available for additional claims until one is approved.
            $stmtUpdateItem = $pdo->prepare("UPDATE items SET status_id = 2 WHERE item_id = ?");
            $stmtUpdateItem->execute([$item_id]);

            header("Location: my_items.php?success=" . urlencode("Claim request successfully submitted! Track your claim status below."));
            exit;
        } else {
            $error = "Failed to submit claim request. Please try again.";
        }
    }
}

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small"><a href="found_items.php" class="text-decoration-none">Found Items</a></li>
                    <li class="breadcrumb-item small"><a href="found_detail.php?id=<?php echo $item_id; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></li>
                    <li class="breadcrumb-item small text-muted active" aria-current="page">Claim Item</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0 p-4 p-md-5 bg-white" style="border-radius: 16px;">
                <div class="mb-4">
                    <h2 class="fw-bold text-dark"><i class="fa-solid fa-handshake text-success me-2"></i> File Ownership Claim</h2>
                    <p class="text-muted">Provide detailed proof and verification information to claim this found item.</p>
                </div>

                <!-- Item Summary Box -->
                <div class="p-3 mb-4 rounded border border-light-subtle" style="background-color: #bce5db; border-radius: 12px;">
                    <div class="small text-muted mb-1">Item Being Claimed:</div>
                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                    <div class="d-flex gap-3 text-secondary small">
                        <span><i class="fa-solid fa-tag me-1"></i> <?php echo htmlspecialchars($item['category']); ?></span>
                        <span><i class="fa-solid fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                        <span><i class="fa-solid fa-calendar me-1"></i> <?php echo htmlspecialchars($item['event_date']); ?></span>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="mt-3">
                        <a href="found_detail.php?id=<?php echo $item_id; ?>" class="btn btn-secondary px-4 fw-bold" style="border-radius: 20px;">Back to Item Details</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="claim_item.php?item_id=<?php echo $item_id; ?>">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        
                        <div class="mb-4">
                            <label for="proof_description" class="form-label fw-bold text-dark">Proof of Ownership & Verification Details <span class="text-danger">*</span></label>
                            <textarea class="form-control bg-light border-0 p-3" id="proof_description" name="proof_description" rows="5" placeholder="Describe unique identifying marks, contents, serial numbers, passwords, or where/when you lost it to prove ownership..." required style="border-radius: 12px; font-size: 0.95rem;"></textarea>
                            <div class="form-text text-muted mt-2">Your claim will be reviewed by administrators or the finder. Honest and accurate descriptions speed up approval.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="found_detail.php?id=<?php echo $item_id; ?>" class="btn btn-outline-secondary px-4 py-2 fw-bold" style="border-radius: 20px;">Cancel</a>
                            <button type="submit" class="btn btn-success px-4 py-2 fw-bold shadow-sm" style="border-radius: 20px; background-color: #198754;">Submit Claim Request</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
