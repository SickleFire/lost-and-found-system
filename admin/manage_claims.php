<?php
require_once '../db.php';
requireAdminOrStaff();

$action = $_GET['action'] ?? '';
$claim_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';

// Handle status updates (Approve or Reject Claim Requests)
if (!empty($action) && $claim_id > 0) {
    if ($action === 'approve') {
        $stmtClaim = $pdo->prepare("SELECT item_id FROM claims WHERE claim_id = ?");
        $stmtClaim->execute([$claim_id]);
        $claimData = $stmtClaim->fetch();

        if ($claimData) {
            $item_id = $claimData['item_id'];

            // Approve this claim (status_id 2 = Approved)
            $stmtApprove = $pdo->prepare("UPDATE claims SET status_id = 2, updated_at = NOW() WHERE claim_id = ?");
            $stmtApprove->execute([$claim_id]);

            // Reject all other open claims for this specific item (status_id 3 = Rejected)
            $stmtRejectOthers = $pdo->prepare("UPDATE claims SET status_id = 3, admin_notes = 'Item awarded to another claimant.' WHERE item_id = ? AND claim_id != ?");
            $stmtRejectOthers->execute([$item_id, $claim_id]);

            // Update parent item status to Closed/Claimed (status_id 3 = Claimed/Closed)
            $stmtCloseItem = $pdo->prepare("UPDATE items SET status_id = 3 WHERE item_id = ?");
            $stmtCloseItem->execute([$item_id]);

            $success = 'Claim has been approved successfully. The item is now marked as closed.';
        }
    } elseif ($action === 'reject') {
        // Reject this claim request (status_id 3 = Rejected)
        $stmtClaim = $pdo->prepare("SELECT item_id FROM claims WHERE claim_id = ?");
        $stmtClaim->execute([$claim_id]);
        $claimData = $stmtClaim->fetch();

        $stmtReject = $pdo->prepare("UPDATE claims SET status_id = 3, updated_at = NOW() WHERE claim_id = ?");
        if ($claimData && $stmtReject->execute([$claim_id])) {
            $stmtPendingClaims = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE item_id = ? AND status_id = 1");
            $stmtPendingClaims->execute([$claimData['item_id']]);
            if ((int) $stmtPendingClaims->fetchColumn() === 0) {
                $stmtReopenItem = $pdo->prepare("UPDATE items SET status_id = 1 WHERE item_id = ? AND status_id = 2");
                $stmtReopenItem->execute([$claimData['item_id']]);
            }
            $success = 'Claim request has been rejected successfully.';
        }
    }
    
    header("Location: manage_claims.php?success=" . urlencode($success));
    exit;
}

$success = $_GET['success'] ?? '';
$search = trim($_GET['search'] ?? '');
$status_id = intval($_GET['status'] ?? 0);

// Fetch system claim statuses for filter choices
$claimStatuses = $pdo->query("SELECT * FROM c_status ORDER BY status_id ASC")->fetchAll();

// Build dynamic claims verification ledger query
$query = "SELECT c.*, i.title AS item_title, i.item_type, cs.status AS claim_status, 
                 CONCAT(u.firstname, ' ', u.lastname) AS claimant_name 
          FROM claims c 
          JOIN items i ON c.item_id = i.item_id 
          JOIN users u ON c.claimer_user_id = u.user_id 
          JOIN c_status cs ON c.status_id = cs.status_id 
          WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (i.title LIKE ? OR c.proof_description LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $searchWild = "%$search%";
    $params = array_merge($params, [$searchWild, $searchWild, $searchWild, $searchWild]);
}

if ($status_id > 0) {
    $query .= " AND c.status_id = ?";
    $params[] = $status_id;
}

$query .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$claims = $stmt->fetchAll();

include '../header.php';
?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-handshake text-success me-2"></i> Manage Claims Verification</h2>
            <p class="text-muted">Review validation details, proof records, and manage item return status</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary fw-bold" style="border-radius: 8px;"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
            <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Control Card matching your custom light teal palette -->
    <div class="card shadow-sm mb-5 border-0" style="background-color: #bce5db; border-radius: 16px;">
        <div class="card-body p-4">
            <form method="GET" action="manage_claims.php" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-dark">Search Title / Claimant / Proof</label>
                    <input type="text" class="form-control bg-white border-0 py-2" style="border-radius: 8px;" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Item name, claimant name, verification details...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-dark">Claim Status</label>
                    <select class="form-select bg-white border-0 py-2" style="border-radius: 8px;" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($claimStatuses as $stat): ?>
                            <option value="<?php echo $stat['status_id']; ?>" <?php echo $status_id === intval($stat['status_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($stat['status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2 px-3" style="border-radius: 20px; background-color: #495057;">Filter</button>
                    <a href="manage_claims.php" class="btn btn-outline-dark bg-white border-0 p-2 d-inline-flex align-items-center justify-content-center" style="border-radius: 50%; width: 38px; height: 32px; background-color: #fff;"><i class="fa-solid fa-rotate-right"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Claims Records Grid Table -->
    <div class="card shadow-sm p-4 border border-light-subtle" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-dark small border-bottom" style="font-weight: 600;">
                        <th class="py-3">Item Title</th>
                        <th class="py-3">Item Type</th>
                        <th class="py-3">Claimant Name</th>
                        <th class="py-3">Verification Proof Details</th>
                        <th class="py-3">Date Filed</th>
                        <th class="py-3">Claim Status</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No records found matching your filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claims as $claim): ?>
                            <tr class="border-bottom border-light-subtle">
                                <td class="py-3 fw-bold text-dark"><?php echo htmlspecialchars($claim['item_title']); ?></td>
                                <td class="py-3">
                                    <span class="badge rounded-pill px-2.5 py-1 text-xs" style="background-color: #e2e3e5; color: #41464b; font-weight: 500;">
                                        <?php echo htmlspecialchars($claim['item_type']); ?>
                                    </span>
                                </td>
                                <td class="py-3 text-dark"><?php echo htmlspecialchars($claim['claimant_name']); ?></td>
                                <td class="py-3 text-muted small">
                                    <div class="text-wrap" style="max-width: 320px; line-height: 1.5;">
                                        <?php echo nl2br(htmlspecialchars($claim['proof_description'])); ?>
                                    </div>
                                </td>
                                <td class="py-3 text-muted small"><?php echo date('M d, Y', strtotime($claim['created_at'])); ?></td>
                                <td class="py-3">
                                    <?php
                                    $badgeStyle = 'background-color: #f8d7da; color: #842029;'; // Pending/Default Muted Red
                                    if ($claim['claim_status'] === 'Approved') $badgeStyle = 'background-color: #d1e7dd; color: #0f5132;'; // Green Muted
                                    if ($claim['claim_status'] === 'Rejected') $badgeStyle = 'background-color: #f8d7da; color: #842029;';
                                    if ($claim['claim_status'] === 'Pending') $badgeStyle = 'background-color: #ffe6d5; color: #b25e24;'; // Light orange custom
                                    ?>
                                    <span class="badge rounded-pill px-3 py-1.5 small" style="<?php echo $badgeStyle; ?> font-weight: 600;">
                                        <?php echo htmlspecialchars($claim['claim_status']); ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-center">
                                        <?php if ($claim['claim_status'] === 'Pending'): ?>
                                            <div class="d-flex gap-2">
                                                <a href="manage_claims.php?action=approve&id=<?php echo $claim['claim_id']; ?>" 
                                                   class="btn btn-sm text-dark px-3 py-1.5 fw-bold d-inline-flex align-items-center" 
                                                   style="border-radius: 20px; background-color: #bce5db; font-size: 0.85rem;"
                                                   onclick="return confirm('Approve claim? All other requests for this item will be auto-rejected.');">
                                                    <i class="fa-solid fa-check me-1.5 small"></i> Approve
                                                </a>
                                                <a href="manage_claims.php?action=reject&id=<?php echo $claim['claim_id']; ?>" 
                                                   class="btn btn-sm text-white px-3 py-1.5 fw-bold d-inline-flex align-items-center" 
                                                   style="border-radius: 20px; background-color: #dc3545; font-size: 0.85rem;"
                                                   onclick="return confirm('Reject claim submission record?');">
                                                    <i class="fa-solid fa-xmark me-1.5 small"></i> Reject
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <!-- Circular gray badge representing non-reversible locked operations context -->
                                            <span class="badge rounded-pill px-3 py-2 text-muted border border-light-subtle bg-white small" style="font-weight: 500; font-size: 0.8rem; background-color: #f8f9fa !important; border-color: #e9ecef !important; color: #6c757d !important;">
                                                Processed
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
