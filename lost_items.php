<?php
require_once 'db.php';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

$query = "SELECT l.*, u.full_name FROM lost_items l JOIN users u ON l.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (l.title LIKE ? OR l.description LIKE ? OR l.location_lost LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND l.category = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $query .= " AND l.status = ?";
    $params[] = $status;
}

if ($sort === 'oldest') {
    $query .= " ORDER BY l.date_lost ASC";
} else {
    $query .= " ORDER BY l.date_lost DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Fetch distinct categories for filter dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM lost_items")->fetchAll(PDO::FETCH_COLUMN);

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
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, description, location...">
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
            <div class="col-md-2">
                <label for="status" class="form-label fw-bold small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Open" <?php echo $status === 'Open' ? 'selected' : ''; ?>>Open</option>
                    <option value="Resolved" <?php echo $status === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="Closed" <?php echo $status === 'Closed' ? 'selected' : ''; ?>>Closed</option>
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
                <button type="submit" class="btn btn-dark w-100 fw-bold"><i class="fa-solid fa-filter"></i></button>
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
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-danger"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="badge bg-<?php echo $item['status'] === 'Open' ? 'warning text-dark' : 'secondary'; ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                            </div>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted small mb-2"><i class="fa-solid fa-map-marker-alt me-1 text-danger"></i> <?php echo htmlspecialchars($item['location_lost']); ?></p>
                            <p class="card-text text-muted small mb-3"><i class="fa-solid fa-calendar me-1 text-primary"></i> Lost Date: <?php echo $item['date_lost']; ?></p>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 120))); ?>...</p>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pb-3">
                            <small class="text-muted">By: <?php echo htmlspecialchars($item['full_name']); ?></small>
                            <a href="lost_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary btn-sm fw-bold">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
