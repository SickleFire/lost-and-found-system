<?php
require_once '../db.php';
requireAdminOrStaff();

$type = $_GET['type'] ?? 'lost';
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    if ($type === 'lost') {
        $stmt = $pdo->prepare("DELETE FROM lost_items WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM found_items WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: manage_items.php?type=" . urlencode($type));
    exit;
}

// Search and filter parameters
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');
$activeTab = $_GET['tab'] ?? ($type === 'found' ? 'found' : 'lost');

// Fetch categories for filter dropdowns (distinct from both tables or standard list)
$categories = $pdo->query("SELECT DISTINCT category FROM (SELECT category FROM lost_items UNION SELECT category FROM found_items) c ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Build Lost Items query with filters
$lostSql = "SELECT l.*, u.full_name FROM lost_items l JOIN users u ON l.user_id = u.id WHERE 1=1";
$lostParams = [];

if ($search !== '') {
    $lostSql .= " AND (l.title LIKE ? OR l.description LIKE ? OR l.location_lost LIKE ? OR u.full_name LIKE ?)";
    $searchWild = "%$search%";
    $lostParams = array_merge($lostParams, [$searchWild, $searchWild, $searchWild, $searchWild]);
}
if ($category !== '') {
    $lostSql .= " AND l.category = ?";
    $lostParams[] = $category;
}
if ($status !== '') {
    $lostSql .= " AND l.status = ?";
    $lostParams[] = $status;
}
$lostSql .= " ORDER BY l.created_at DESC";

$stmtLost = $pdo->prepare($lostSql);
$stmtLost->execute($lostParams);
$lostItems = $stmtLost->fetchAll();

// Build Found Items query with filters
$foundSql = "SELECT f.*, u.full_name FROM found_items f JOIN users u ON f.user_id = u.id WHERE 1=1";
$foundParams = [];

if ($search !== '') {
    $foundSql .= " AND (f.title LIKE ? OR f.description LIKE ? OR f.location_found LIKE ? OR u.full_name LIKE ?)";
    $searchWild = "%$search%";
    $foundParams = array_merge($foundParams, [$searchWild, $searchWild, $searchWild, $searchWild]);
}
if ($category !== '') {
    $foundSql .= " AND f.category = ?";
    $foundParams[] = $category;
}
if ($status !== '') {
    $foundSql .= " AND f.status = ?";
    $foundParams[] = $status;
}
$foundSql .= " ORDER BY f.created_at DESC";

$stmtFound = $pdo->prepare($foundSql);
$stmtFound->execute($foundParams);
$foundItems = $stmtFound->fetchAll();

include '../header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Manage Items Catalog</h2>
            <p class="text-muted">Administrators can view, update, or remove any lost or found item record</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="itemTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $activeTab === 'lost' ? 'active' : ''; ?> fw-bold" id="lost-tab" data-bs-toggle="tab" data-bs-target="#lost-pane" type="button" role="tab" onclick="document.getElementById('tabInput').value='lost';">
                <i class="fa-solid fa-search me-1"></i> Lost Items (<?php echo count($lostItems); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $activeTab === 'found' ? 'active' : ''; ?> fw-bold" id="found-tab" data-bs-toggle="tab" data-bs-target="#found-pane" type="button" role="tab" onclick="document.getElementById('tabInput').value='found';">
                <i class="fa-solid fa-bullhorn me-1"></i> Found Items (<?php echo count($foundItems); ?>)
            </button>
        </li>
    </ul>

    <!-- Search & Filter Card -->
    <div class="card shadow-sm mb-4 bg-light border-0">
        <div class="card-body">
            <form method="GET" action="manage_items.php" class="row g-3 align-items-end">
                <input type="hidden" name="tab" id="tabInput" value="<?php echo htmlspecialchars($activeTab); ?>">
                <div class="col-md-4">
                    <label for="search" class="form-label fw-bold small">Search Name / Keyword</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, description, location, reporter...">
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label fw-bold small">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label fw-bold small">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Open" <?php echo $status === 'Open' ? 'selected' : ''; ?>>Open (Lost)</option>
                        <option value="Resolved" <?php echo $status === 'Resolved' ? 'selected' : ''; ?>>Resolved (Lost)</option>
                        <option value="Unclaimed" <?php echo $status === 'Unclaimed' ? 'selected' : ''; ?>>Unclaimed (Found)</option>
                        <option value="Pending Verification" <?php echo $status === 'Pending Verification' ? 'selected' : ''; ?>>Pending Verification (Found)</option>
                        <option value="Claimed" <?php echo $status === 'Claimed' ? 'selected' : ''; ?>>Claimed (Found)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <a href="manage_items.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="tab-content" id="itemTabContent">
        <!-- Lost Pane -->
        <div class="tab-pane fade <?php echo $activeTab === 'lost' ? 'show active' : ''; ?>" id="lost-pane" role="tabpanel">
            <div class="card shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Reporter</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lostItems)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No lost items found matching your filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lostItems as $item): ?>
                                    <tr>
                                        <td class="fw-bold"><a href="../lost_detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></td>
                                        <td><span class="badge bg-danger"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                        <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['location_lost']); ?></td>
                                        <td><span class="badge bg-<?php echo $item['status'] === 'Open' ? 'warning text-dark' : 'secondary'; ?>"><?php echo $item['status']; ?></span></td>
                                        <td>
                                            <a href="../lost_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                            <a href="manage_items.php?type=lost&action=delete&id=<?php echo $item['id']; ?>&tab=lost" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this lost item?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Found Pane -->
        <div class="tab-pane fade <?php echo $activeTab === 'found' ? 'show active' : ''; ?>" id="found-pane" role="tabpanel">
            <div class="card shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Finder</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($foundItems)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No found items found matching your filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($foundItems as $item): ?>
                                    <tr>
                                        <td class="fw-bold"><a href="../found_detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></td>
                                        <td><span class="badge bg-success"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                        <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['location_found']); ?></td>
                                        <td><span class="badge bg-<?php echo $item['status'] === 'Unclaimed' ? 'primary' : ($item['status'] === 'Pending Verification' ? 'warning text-dark' : 'secondary'); ?>"><?php echo $item['status']; ?></span></td>
                                        <td>
                                            <a href="../found_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-eye"></i></a>
                                            <a href="manage_items.php?type=found&action=delete&id=<?php echo $item['id']; ?>&tab=found" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this found item?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
