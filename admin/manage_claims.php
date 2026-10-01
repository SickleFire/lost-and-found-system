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
            $stmtUpdateClaim = $pdo->prepare("UPDATE claims SET status = 'Approved', admin_notes = 'Claim verified and approved by staff.' WHERE id = ?");
            $stmtUpdateClaim->execute([$id]);
            
            // Update found item status to Claimed
            $stmtUpdateFound = $pdo->prepare("UPDATE found_items SET status = 'Claimed' WHERE id = ?");
            $stmtUpdateFound->execute([$claim['found_item_id']]);
            
            // Reject other pending claims for this item
            $stmtRejectOther = $pdo->prepare("UPDATE claims SET status = 'Rejected', admin_notes = 'Another claim was approved for this item.' WHERE found_item_id = ? AND id != ? AND status = 'Pending'");
            $stmtRejectOther->execute([$claim['found_item_id'], $id]);
        }
    } elseif ($action === 'reject') {
        $stmtReject = $pdo->prepare("UPDATE claims SET status = 'Rejected', admin_notes = 'Claim verification proof was insufficient or rejected by staff.' WHERE id = ?");
        $stmtReject->execute([$id]);
    }
    header("Location: manage_claims.php");
    exit;
}

// Fetch filter parameters
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build query with parameters
$query = "SELECT c.*, f.title as item_title, f.category, u.full_name, u.email, u.phone FROM claims c JOIN found_items f ON c.found_item_id = f.id JOIN users u ON c.user_id = u.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR f.title LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($status_filter !== '') {
    $query .= " AND c.status = ?";
    $params[] = $status_filter;
}

if ($category_filter !== '') {
    $query .= " AND f.category = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$claims = $stmt->fetchAll();

// Get unique categories for dropdown filter
$categories = $pdo->query("SELECT DISTINCT category FROM found_items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

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

    <!-- Search & Filter Bar -->
    <div class="card shadow-sm p-3 mb-4">
        <form method="GET" action="manage_claims.php" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search claimant or item...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid gap-2 d-flex">
                <button type="submit" class="btn btn-success flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="manage_claims.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm p-4">
        <?php if (empty($claims)): ?>
            <div class="alert alert-info mb-0">No claims match your search/filter criteria.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Found Item</th>
                            <th>Category</th>
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
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($claim['category']); ?></span></td>
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
