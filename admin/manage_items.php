<?php
require_once '../db.php';
requireAdminOrStaff();

$type = $_GET['type'] ?? 'lost';
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Delete items utilizing your real item_id primary key from the ERD chart
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$id]);
    header("Location: manage_items.php?type=" . urlencode($type));
    exit;
}

$search = trim($_GET['search'] ?? '');
$category_id = intval($_GET['category'] ?? 0);
$status_id = intval($_GET['status'] ?? 0);
$activeTab = $_GET['tab'] ?? ($type === 'found' ? 'found' : 'lost');

// Fetch master configuration system lookups from clean operational tables
$categories = $pdo->query("SELECT * FROM categories ORDER BY category ASC")->fetchAll();
$statuses = $pdo->query("SELECT * FROM i_status ORDER BY status_id ASC")->fetchAll();

// 1. Fetch Lost Items dataset directly from your single, centralized table structure
$lostSql = "SELECT i.*, c.category, s.status, CONCAT(u.firstname, ' ', u.lastname) AS full_name 
            FROM items i 
            JOIN users u ON i.user_id = u.user_id 
            JOIN categories c ON i.category_id = c.category_id 
            JOIN i_status s ON i.status_id = s.status_id 
            WHERE i.item_type = 'Lost'";
$lostParams = [];

if ($search !== '') {
    $lostSql .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $searchWild = "%$search%";
    $lostParams = array_merge($lostParams, [$searchWild, $searchWild, $searchWild, $searchWild, $searchWild]);
}
if ($category_id > 0) { $lostSql .= " AND i.category_id = ?"; $lostParams[] = $category_id; }
if ($status_id > 0) { $lostSql .= " AND i.status_id = ?"; $lostParams[] = $status_id; }
$lostSql .= " ORDER BY i.created_at DESC";

$stmtLost = $pdo->prepare($lostSql);
$stmtLost->execute($lostParams);
$lostItems = $stmtLost->fetchAll();
// 2. Fetch Found Items dataset directly from your single, centralized table structure
$foundSql = "SELECT i.*, c.category, s.status, CONCAT(u.firstname, ' ', u.lastname) AS full_name 
             FROM items i 
             JOIN users u ON i.user_id = u.user_id 
             JOIN categories c ON i.category_id = c.category_id 
             JOIN i_status s ON i.status_id = s.status_id 
             WHERE i.item_type = 'Found'";
$foundParams = [];

if ($search !== '') {
    $foundSql .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $searchWild = "%$search%";
    $foundParams = array_merge($foundParams, [$searchWild, $searchWild, $searchWild, $searchWild, $searchWild]);
}
if ($category_id > 0) { $foundSql .= " AND i.category_id = ?"; $foundParams[] = $category_id; }
if ($status_id > 0) { $foundSql .= " AND i.status_id = ?"; $foundParams[] = $status_id; }
$foundSql .= " ORDER BY i.created_at DESC";

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
        <a href="dashboard.php" class="btn btn-outline-secondary fw-bold">Dashboard</a>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="itemTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link <?php echo $activeTab === 'lost' ? 'active' : ''; ?> fw-bold" id="lost-tab" data-bs-toggle="tab" data-bs-target="#lost-pane" type="button" role="tab" onclick="document.getElementById('tabInput').value='lost';">
                Lost Items (<?php echo count($lostItems); ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?php echo $activeTab === 'found' ? 'active' : ''; ?> fw-bold" id="found-tab" data-bs-toggle="tab" data-bs-target="#found-pane" type="button" role="tab" onclick="document.getElementById('tabInput').value='found';">
                Found Items (<?php echo count($foundItems); ?>)
            </button>
        </li>
    </ul>

    <!-- Filter Control Card Form -->
    <div class="card shadow-sm mb-4 bg-light border-0">
        <div class="card-body">
            <form method="GET" action="manage_items.php" class="row g-3 align-items-end">
                <input type="hidden" name="tab" id="tabInput" value="<?php echo htmlspecialchars($activeTab); ?>">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Search Keyword</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, location...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Category</label>
                    <select class="form-select" name="category">
                        <option value="">All</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c['category_id']; ?>" <?php echo $category_id === intval($c['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?php echo $s['status_id']; ?>" <?php echo $status_id === intval($s['status_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                    <a href="manage_items.php" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-right"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab View Panel Tables -->
    <div class="tab-content" id="itemTabContent">
        <!-- Lost Pane Container -->
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
                                <tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>
                            <?php else: foreach ($lostItems as $item): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                                    <td><span class="badge bg-dark"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                    <td>
                                        <a href="../lost_detail.php?id=<?php echo $item['item_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                        <a href="manage_items.php?type=lost&action=delete&id=<?php echo $item['item_id']; ?>&tab=lost" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete record permanently?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Found Pane Container -->
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
                                <tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>
                            <?php else: foreach ($foundItems as $item): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                                    <td><span class="badge bg-dark"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                    <td>
                                        <a href="../found_detail.php?id=<?php echo $item['item_id']; ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-eye"></i></a>
                                        <a href="manage_items.php?type=found&action=delete&id=<?php echo $item['item_id']; ?>&tab=found" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete record permanently?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
