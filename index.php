<?php
require_once 'db.php';
// Fetch summary statistics for the homepage
$stats = [
    'lost' => $pdo->query("SELECT COUNT(*) FROM lost_items")->fetchColumn(),
    'found' => $pdo->query("SELECT COUNT(*) FROM found_items")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM lost_items WHERE status='Resolved'")->fetchColumn() + 
                  $pdo->query("SELECT COUNT(*) FROM found_items WHERE status='Claimed'")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()
];

// Fetch recent lost items
$recentLost = $pdo->query("SELECT l.*, u.full_name FROM lost_items l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 3")->fetchAll();

// Fetch recent found items
$recentFound = $pdo->query("SELECT f.*, u.full_name FROM found_items f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 3")->fetchAll();

include 'header.php';
?>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3"><i class="fa-solid fa-hands-holding-child me-2"></i>Community Lost & Found</h1>
        <p class="lead mb-4">Did you lose something valuable in the community? Or did you find an item? Post it here and let us help reconnect owners with their belongings.</p>
        <div class="d-flex justify-content-center gap-3">
            <?php if (isLoggedIn()): ?>
                <a href="post_lost.php" class="btn btn-light btn-lg fw-bold text-primary shadow-sm"><i class="fa-solid fa-search me-2"></i> Post Lost Item</a>
                <a href="post_found.php" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm"><i class="fa-solid fa-bullhorn me-2"></i> Post Found Item</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-lg fw-bold text-primary shadow-sm"><i class="fa-solid fa-sign-in-alt me-2"></i> Login to Post Items</a>
                <a href="register.php" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm"><i class="fa-solid fa-user-plus me-2"></i> Register Account</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container my-4">
    <!-- Statistics Summary Bar -->
    <div class="row text-center mb-5">
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-primary text-white shadow-sm">
                <h3><?php echo $stats['lost']; ?></h3>
                <p class="mb-0"><i class="fa-solid fa-search me-1"></i> Total Lost Items</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-success text-white shadow-sm">
                <h3><?php echo $stats['found']; ?></h3>
                <p class="mb-0"><i class="fa-solid fa-bullhorn me-1"></i> Total Found Items</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-info text-white shadow-sm">
                <h3><?php echo $stats['resolved']; ?></h3>
                <p class="mb-0"><i class="fa-solid fa-circle-check me-1"></i> Successfully Returned</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card p-3 bg-dark text-white shadow-sm">
                <h3><?php echo $stats['users']; ?></h3>
                <p class="mb-0"><i class="fa-solid fa-users me-1"></i> Community Members</p>
            </div>
        </div>
    </div>

    <!-- Recent Lost Items -->
    <div class="row mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold"><i class="fa-solid fa-search text-danger me-2"></i> Recent Lost Items</h3>
            <a href="lost_items.php" class="btn btn-outline-primary btn-sm fw-bold">View All Lost Items &rarr;</a>
        </div>
        <?php if (empty($recentLost)): ?>
            <div class="col-12"><p class="text-muted">No lost items reported recently.</p></div>
        <?php else: ?>
            <?php foreach ($recentLost as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-danger mb-2"><?php echo htmlspecialchars($item['category']); ?></span>
                            <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($item['status']); ?></span>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted small mb-2"><i class="fa-solid fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($item['location_lost']); ?></p>
                            <p class="card-text text-muted small mb-3"><i class="fa-solid fa-calendar me-1"></i> Lost on: <?php echo $item['date_lost']; ?></p>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <a href="lost_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary btn-sm w-100 fw-bold">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recent Found Items -->
    <div class="row mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold"><i class="fa-solid fa-bullhorn text-success me-2"></i> Recent Found Items</h3>
            <a href="found_items.php" class="btn btn-outline-success btn-sm fw-bold">View All Found Items &rarr;</a>
        </div>
        <?php if (empty($recentFound)): ?>
            <div class="col-12"><p class="text-muted">No found items reported recently.</p></div>
        <?php else: ?>
            <?php foreach ($recentFound as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-success mb-2"><?php echo htmlspecialchars($item['category']); ?></span>
                            <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($item['status']); ?></span>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted small mb-2"><i class="fa-solid fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($item['location_found']); ?></p>
                            <p class="card-text text-muted small mb-3"><i class="fa-solid fa-calendar me-1"></i> Found on: <?php echo $item['date_found']; ?></p>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <a href="found_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-success btn-sm w-100 fw-bold">View Details & Claim</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
