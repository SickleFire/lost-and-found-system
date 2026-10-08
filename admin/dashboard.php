<?php
require_once '../db.php';
requireAdminOrStaff();

// Fetch metrics from unified tables matching the system lookup mappings
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'lost' => $pdo->query("SELECT COUNT(*) FROM items WHERE item_type = 'Lost'")->fetchColumn(),
    'found' => $pdo->query("SELECT COUNT(*) FROM items WHERE item_type = 'Found'")->fetchColumn(),
    'claims' => $pdo->query("SELECT COUNT(*) FROM claims")->fetchColumn(),
    // Status ID 1 maps to 'Pending' in your c_status lookup table
    'pending_claims' => $pdo->query("SELECT COUNT(*) FROM claims WHERE status_id = 1")->fetchColumn(),
    // Status ID 3 maps to 'Claimed' / 'Closed' in your item lookup table
    'resolved' => $pdo->query("SELECT COUNT(*) FROM items WHERE status_id = 3")->fetchColumn()
];

// Recent pending claims matching exact structural joins from your ERD chart
$pendingClaims = $pdo->query("SELECT c.*, i.title AS item_title, CONCAT(u.firstname, ' ', u.lastname) AS full_name 
                              FROM claims c 
                              JOIN items i ON c.item_id = i.item_id 
                              JOIN users u ON c.claimer_user_id = u.user_id 
                              WHERE c.status_id = 1 
                              ORDER BY c.created_at DESC LIMIT 5")->fetchAll();

include '../header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-gauge text-warning me-2"></i> Admin & Staff Dashboard</h2>
            <p class="text-muted">Overview of system metrics, statistics, and pending verifications</p>
        </div>
        <div>
            <a href="reports.php" class="btn btn-outline-dark fw-bold me-2"><i class="fa-solid fa-print me-1"></i> Printable Reports</a>
            <a href="manage_items.php" class="btn btn-primary fw-bold"><i class="fa-solid fa-boxes-stacked me-1"></i> Manage Items</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row text-center mb-5">
        <div class="col-md-4 mb-3">
            <div class="card p-4 bg-primary text-white shadow-sm">
                <i class="fa-solid fa-users fa-2x mb-2"></i>
                <h3><?php echo $stats['users']; ?></h3>
                <p class="mb-0 fw-bold">Total Users</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-4 bg-danger text-white shadow-sm">
                <i class="fa-solid fa-search fa-2x mb-2"></i>
                <h3><?php echo $stats['lost']; ?></h3>
                <p class="mb-0 fw-bold">Lost Items Reported</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-4 bg-success text-white shadow-sm">
                <i class="fa-solid fa-bullhorn fa-2x mb-2"></i>
                <h3><?php echo $stats['found']; ?></h3>
                <p class="mb-0 fw-bold">Found Items Reported</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-warning text-dark shadow-sm">
                <h4><?php echo $stats['pending_claims']; ?></h4>
                <p class="mb-0 small fw-bold">Pending Claims</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-info text-white shadow-sm">
                <h4><?php echo $stats['claims']; ?></h4>
                <p class="mb-0 small fw-bold">Total Claims Filed</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-dark text-white shadow-sm">
                <h4><?php echo $stats['resolved']; ?></h4>
                <p class="mb-0 small fw-bold">Resolved / Returned</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-secondary text-white shadow-sm">
                <h4><?php echo round(($stats['resolved'] / max(1, $stats['lost'] + $stats['found'])) * 100); ?>%</h4>
                <p class="mb-0 small fw-bold">Success Recovery Rate</p>
            </div>
        </div>
    </div>
    <!-- Pending Claims Section -->
    <div class="card shadow-sm p-4 mb-4" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0" style="font-size: 1.25rem;"><i class="fa-solid fa-list-check text-warning me-2"></i> Pending Claims Requiring Verification</h4>
            <a href="manage_claims.php" class="btn btn-outline-secondary btn-sm fw-bold bg-white text-dark px-3 py-1.5" style="border-radius: 20px; font-size: 0.85rem; border-color: #ced4da;">View All Claims &rarr;</a>
        </div>
        <?php if (empty($pendingClaims)): ?>
            <div class="alert alert-success" style="border-radius: 8px;"><i class="fa-solid fa-check-circle me-1"></i> No pending claims awaiting review!</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-dark small border-bottom" style="font-weight: 600;">
                            <th class="py-3">Item Title</th>
                            <th class="py-3">Claimant</th>
                            <th class="py-3">Proof Details</th>
                            <th class="py-3">Date Filed</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingClaims as $claim): ?>
                            <tr class="border-bottom border-light-subtle">
                                <td class="py-3 fw-bold">
                                    <a href="../found_detail.php?id=<?php echo $claim['item_id']; ?>" class="text-decoration-none text-primary">
                                        <?php echo htmlspecialchars($claim['item_title']); ?>
                                    </a>
                                </td>
                                <td class="py-3 text-dark"><?php echo htmlspecialchars($claim['full_name']); ?></td>
                                <td class="py-3 text-muted small"><?php echo htmlspecialchars(substr($claim['proof_description'], 0, 60)); ?>...</td>
                                <td class="py-3 text-muted small"><?php echo date('M d, Y', strtotime($claim['created_at'])); ?></td>
                                <td class="py-3">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <!-- FIXED: Pill Approve button matching the exact custom teal background design profile -->
                                        <a href="manage_claims.php?action=approve&id=<?php echo $claim['claim_id']; ?>" 
                                           class="btn btn-sm text-dark px-3 py-1.5 fw-bold d-inline-flex align-items-center" 
                                           style="border-radius: 20px; background-color: #bce5db; font-size: 0.85rem;"
                                           onclick="return confirm('Approve claim? All other requests for this item will be auto-rejected.');">
                                            <i class="fa-solid fa-check me-1.5 small"></i> Approve
                                        </a>
                                        <!-- FIXED: Pill Reject button matching the exact solid crimson design profile -->
                                        <a href="manage_claims.php?action=reject&id=<?php echo $claim['claim_id']; ?>" 
                                           class="btn btn-sm text-white px-3 py-1.5 fw-bold d-inline-flex align-items-center" 
                                           style="border-radius: 20px; background-color: #dc3545; font-size: 0.85rem;"
                                           onclick="return confirm('Reject claim submission record?');">
                                            <i class="fa-solid fa-xmark me-1.5 small"></i> Reject
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Management Links -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card p-3 text-center shadow-sm h-100" style="border-radius: 12px;">
                <i class="fa-solid fa-boxes-stacked text-primary fa-2x mb-2"></i>
                <h5 class="fw-bold">Manage Items</h5>
                <p class="text-muted small">View, edit, or delete lost and found item records.</p>
                <a href="manage_items.php" class="btn btn-outline-primary btn-sm mt-auto fw-bold">Manage Items</a>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-3 text-center shadow-sm h-100" style="border-radius: 12px;">
                <i class="fa-solid fa-handshake text-success fa-2x mb-2"></i>
                <h5 class="fw-bold">Manage Claims</h5>
                <p class="text-muted small">Review claim proofs, approve or reject ownership claims.</p>
                <a href="manage_claims.php" class="btn btn-outline-success btn-sm mt-auto fw-bold">Manage Claims</a>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card p-3 text-center shadow-sm h-100" style="border-radius: 12px;">
                <i class="fa-solid fa-users-gear text-dark fa-2x mb-2"></i>
                <h5 class="fw-bold">Manage Users</h5>
                <p class="text-muted small">View registered community members, staff, and administrators.</p>
                <a href="manage_users.php" class="btn btn-outline-dark btn-sm mt-auto fw-bold">Manage Users</a>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
