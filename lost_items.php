<?php
require_once 'db.php';

$search = trim($_GET['search'] ?? '');
$category_id = intval($_GET['category'] ?? 0);
$status_id = intval($_GET['status'] ?? 0);
$sort = trim($_GET['sort'] ?? 'newest');

$query = "SELECT i.*, CONCAT(u.firstname, ' ', u.lastname) AS full_name, c.category, s.status 
          FROM items i 
          JOIN users u ON i.user_id = u.user_id 
          JOIN categories c ON i.category_id = c.category_id
          JOIN i_status s ON i.status_id = s.status_id
          WHERE i.item_type = 'Lost'";
$params = [];

if (!empty($search)) {
    $query .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $searchParam = "%$search%";
    array_push($params, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
}

if ($category_id > 0) {
    $query .= " AND i.category_id = ?";
    $params[] = $category_id;
}

if ($status_id > 0) {
    $query .= " AND i.status_id = ?";
    $params[] = $status_id;
}

if ($sort === 'oldest') {
    $query .= " ORDER BY i.event_date ASC";
} else {
    $query .= " ORDER BY i.event_date DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Fetch system categories and statuses for filters
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$statuses = $pdo->query("SELECT * FROM i_status")->fetchAll();

include 'header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-search text-danger me-2"></i> Lost Items Catalog</h2>
            <p class="text-muted">Browse items reported lost by community members</p>
        </div>
        <?php if (isLoggedIn()): ?>
            <a href="post_lost.php" class="btn btn-primary fw-bold"><i class="fa-solid fa-plus me-1"></i> Post Lost Item</a>
        <?php endif; ?>
    </div>

    <!-- Search, Filter & Sort Form -->
    <div class="card p-4 mb-4 shadow-sm bg-light">
        <form method="GET" action="lost_items.php" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label fw-bold small">Search Keyword</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Item name, reporter, location...">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label fw-bold small">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_id === intval($cat['category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label fw-bold small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $stat): ?>
                        <option value="<?php echo $stat['status_id']; ?>" <?php echo $status_id === intval($stat['status_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($stat['status']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort" class="form-label fw-bold small">Sort By Date</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-dark w-100 fw-bold"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button>
            </div>
        </form>
    </div>

    <!-- Results Grid -->
    <div class="row">
        <?php if (empty($items)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-4">
                    <i class="fa-solid fa-info-circle fa-2x mb-2"></i>
                    <h5>No lost items found matching your criteria.</h5>
                    <a href="lost_items.php" class="btn btn-outline-primary btn-sm mt-2">Reset Filters</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <?php
                $statusClass = in_array($item['status'], ['Closed', 'Claimed', 'Resolved'], true)
                    ? 'item-status-closed'
                    : ($item['status'] === 'Open' ? 'item-status-open' : 'item-status-pending');
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge item-category"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                            </div>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text item-location small mb-2"><i class="fa-solid fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($item['location']); ?></p>
                            <p class="card-text item-date small mb-3"><i class="fa-solid fa-calendar me-1"></i> Lost Date: <?php echo $item['event_date']; ?></p>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 120))); ?>...</p>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pb-3">
                            <small class="text-muted">By: <?php echo htmlspecialchars($item['full_name']); ?></small>
                            <a href="lost_detail.php?id=<?php echo $item['item_id']; ?>" class="btn btn-outline-primary btn-sm fw-bold">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>