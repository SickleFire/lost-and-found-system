<?php
require_once '../db.php';
requireAdminOrStaff();

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($action && $id) {
    if ($action === 'approve') {
        // Get claim and found item id
        $stmtClaim = $pdo->prepare("SELECT * FROM claims WHERE id = ?");
        $stmtClaim->execute([$id]);
        $claim = $stmtClaim->fetch();
        if ($claim) {
            // Update claim status
            $pdo->prepare("UPDATE claims SET status = 'Approved', admin_notes = 'Claim verified and approved by staff.' WHERE id = ?")->execute([$id]);
            // Update found item status to Claimed
            $pdo->prepare("UPDATE found_items SET status = 'Claimed' WHERE id = ?")->execute([$claim['found_item_id']]);
            // Reject other pending claims for this item
            $pdo->prepare("UPDATE claims SET status = 'Rejected', admin_notes = 'Another claim was approved for this item.' WHERE found_item_id = ? AND id != ? AND status = 'Pending'")->execute([$claim['found_item_id'], $id]);
        }
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE claims SET status = 'Rejected', admin_notes = 'Claim verification proof was insufficient or rejected by staff.' WHERE id = ?")->execute([$id]);
    }
    header("Location: manage_claims.php");
    exit;
}

// Fetch all claims
$claims = $pdo->query("SELECT c.*, f.title as item_title, f.category, u.full_name, u.email, u.phone FROM claims c JOIN found_items f ON c.found_item_id = f.id JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC")->fetchAll();

include '../header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-handshake text-success me-2"></i> Manage Claims & Verifications</h2>
            <p class="text-muted">Review ownership proofs submitted by community members</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <div class="card shadow-sm p-4">
        <?php if (empty($claims)): ?>
            <div class="alert alert-info">No claims have been submitted yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Found Item</th>
                            <th>Claimant</th>
                            <th>Proof Details</th>
                            <th>Status</th>
                            <th>Admin Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td class="fw-bold"><a href="../found_detail.php?id=<?php echo $claim['found_item_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($claim['item_title']); ?></a></td>
                                <td>
                                    <?php echo htmlspecialchars($claim['full_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($claim['email']); ?></small>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($claim['proof_description'])); ?></td>
                                <td><span class="badge bg-<?php echo $claim['status'] === 'Approved' ? 'success' : ($claim['status'] === 'Rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo $claim['status']; ?></span></td>
                                <td><?php echo htmlspecialchars($claim['admin_notes'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($claim['status'] === 'Pending'): ?>
                                        <a href="manage_claims.php?action=approve&id=<?php echo $claim['id']; ?>" class="btn btn-success btn-sm mb-1"><i class="fa-solid fa-check"></i> Approve</a>
                                        <a href="manage_claims.php?action=reject&id=<?php echo $claim['id']; ?>" class="btn btn-danger btn-sm mb-1"><i class="fa-solid fa-times"></i> Reject</a>
                                    <?php else: ?>
                                        <span class="text-muted small">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
