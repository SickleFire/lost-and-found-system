<?php
require_once 'db.php';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

$query = "SELECT f.*, u.full_name FROM found_items f JOIN users u ON f.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (f.title LIKE ? OR f.description LIKE ? OR f.location_found LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND f.category = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $query .= " AND f.status = ?";
    $params[] = $status;
}

if ($sort === 'oldest') {
    $query .= " ORDER BY f.date_found ASC";
} else {
    $query .= " ORDER BY f.date_found DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Fetch distinct categories for filter dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM found_items")->fetchAll(PDO::FETCH_COLUMN);

include 'header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-bullhorn text-success me-2"></i> Found Items Catalog</h2>
            <p class="text-muted">Browse items found and reported by community members or staff</p>
        </div>
        <?php if (isLoggedIn()): ?>
            <a href="post_found.php" class="btn btn-success fw-bold"><i class="fa-solid fa-plus me-1"></i> Post Found Item</a>
        <?php endif; ?>
    </div>

    <!-- Search, Filter & Sort Form -->
    <div class="card p-4 mb-4 shadow-sm bg-light">
        <form method="GET" action="found_items.php" class="row g-3">
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
                    <option value="Unclaimed" <?php echo $status === 'Unclaimed' ? 'selected' : ''; ?>>Unclaimed</option>
                    <option value="Pending Verification" <?php echo $status === 'Pending Verification' ? 'selected' : ''; ?>>Pending Verification</option>
                    <option value="Claimed" <?php echo $status === 'Claimed' ? 'selected' : ''; ?>>Claimed</option>
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
                    <h5>No found items found matching your criteria.</h5>
                    <a href="found_items.php" class="btn btn-outline-success btn-sm mt-2">Reset Filters</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-success"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="badge bg-<?php echo $item['status'] === 'Unclaimed' ? 'primary' : ($item['status'] === 'Pending Verification' ? 'warning text-dark' : 'secondary'); ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                            </div>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted small mb-2"><i class="fa-solid fa-map-marker-alt me-1 text-success"></i> <?php echo htmlspecialchars($item['location_found']); ?></p>
                            <p class="card-text text-muted small mb-3"><i class="fa-solid fa-calendar me-1 text-primary"></i> Found Date: <?php echo $item['date_found']; ?></p>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 120))); ?>...</p>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pb-3">
                            <small class="text-muted">Posted by: <?php echo htmlspecialchars($item['full_name']); ?></small>
                            <a href="found_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-success btn-sm fw-bold">View & Claim</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
