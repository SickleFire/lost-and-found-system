<?php
require_once 'db.php';

// 1. Fetch live metrics directly from your unified table structure
$stats = [
    'lost'     => $pdo->query("SELECT COUNT(*) FROM items WHERE item_type = 'Lost'")->fetchColumn(),
    'found'    => $pdo->query("SELECT COUNT(*) FROM items WHERE item_type = 'Found'")->fetchColumn(),
    'returned' => $pdo->query("SELECT COUNT(*) FROM items WHERE status_id = 3")->fetchColumn(), // status_id 3 = Claimed/Resolved
    'members'  => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()
];

// 2. Fetch the 3 most recent lost items joined with categories and statuses
$recentLost = $pdo->query("SELECT i.*, c.category, s.status FROM items i 
                           JOIN categories c ON i.category_id = c.category_id 
                           JOIN i_status s ON i.status_id = s.status_id
                           WHERE i.item_type = 'Lost' ORDER BY i.created_at DESC LIMIT 3")->fetchAll();

// 3. Fetch the 3 most recent found items joined with categories and statuses
$recentFound = $pdo->query("SELECT i.*, c.category, s.status FROM items i 
                            JOIN categories c ON i.category_id = c.category_id 
                            JOIN i_status s ON i.status_id = s.status_id
                            WHERE i.item_type = 'Found' ORDER BY i.created_at DESC LIMIT 3")->fetchAll();

include 'header.php';
?>

<!-- Hero Banner Layout Block -->
<div class="py-5 mb-4">
    <div class="container py-2">
        <h1 class="display-4 fw-bold mb-3 text-dark">Community Lost & Found</h1>
        <p class="fs-5 text-muted mb-4">Lost something in the community, or found something that isn't yours?<br>Post it here and help it get back to its owner.</p>
        <div class="d-flex gap-2">
            <?php if (!isLoggedIn()): ?>
                <a href="login.php" class="btn btn-dark fw-bold px-4 py-2" style="border-radius: 8px; background-color: #343a40;">Log in to post</a>
                <a href="register.php" class="btn btn-outline-dark fw-bold px-4 py-2" style="border-radius: 8px;">Create account</a>
            <?php else: ?>
                <a href="post_lost.php" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 8px;">Report Lost Item</a>
                <a href="post_found.php" class="btn btn-success fw-bold px-4 py-2" style="border-radius: 8px;">Report Found Item</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Dynamic Counters Section - Fixed Muted Palette Styling -->
<div class="container mb-5">
    <div class="row text-center g-3">
        <?php foreach ([['lost', 'Lost items'], ['found', 'Found items'], ['returned', 'Returned to owners'], ['members', 'Members']] as $item): ?>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded h-100 text-start" style="background-color: #bce5db;">
                    <h2 class="display-5 fw-normal mb-0 text-dark"><?php echo $stats[$item[0]]; ?></h2>
                    <span class="text-muted small"><?php echo $item[1]; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Main Feed Layout Area -->
<div class="container my-5">
    
    <!-- Section A: Recently Lost Panel -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark m-0">Recently lost</h3>
        <a href="lost_items.php" class="text-muted small text-decoration-none">View all lost items</a>
    </div>

    <div class="row g-4 mb-5">
        <?php if (empty($recentLost)): ?>
            <div class="col-12"><div class="alert alert-light border text-center text-muted py-4">No open lost item reports at the moment.</div></div>
        <?php else: ?>
            <?php foreach ($recentLost as $item): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border border-light-subtle" style="border-radius: 12px;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex gap-1 mb-3 flex-wrap">
                                <span class="badge bg-light text-muted border text-uppercase font-monospace px-2.5 py-1 small"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="badge <?php echo ($item['status'] === 'Resolved') ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis'; ?> px-2.5 py-1 small"><?php echo htmlspecialchars($item['status']); ?></span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <div class="text-muted small mb-1"><i class="fa-solid fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($item['location']); ?></div>
                            <div class="text-muted small mb-3"><i class="fa-solid fa-calendar me-1"></i> Lost on <?php echo $item['event_date']; ?></div>
                            <p class="card-text text-muted mb-4 flex-grow-1" style="font-size: 0.9rem; line-height: 1.5;"><?php echo htmlspecialchars($item['description']); ?></p>
                            <a href="lost_detail.php?id=<?php echo $item['item_id']; ?>" class="btn btn-outline-secondary w-100 mt-auto py-2" style="border-radius: 20px; font-size: 0.9rem; border-color: #ced4da; color: #495057;">View details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Section B: Recently Found Panel -->
    <div class="d-flex justify-content-between align-items-center mb-4 pt-2">
        <h3 class="fw-bold text-dark m-0">Recently found</h3>
        <a href="found_items.php" class="text-muted small text-decoration-none">View all found items</a>
    </div>

    <div class="row g-4 mb-4">
        <?php if (empty($recentFound)): ?>
            <div class="col-12"><div class="alert alert-light border text-center text-muted py-4">No found items reported recently.</div></div>
        <?php else: ?>
            <?php foreach ($recentFound as $item): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border border-light-subtle" style="border-radius: 12px;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex gap-1 mb-3 flex-wrap">
                                <span class="badge bg-light text-muted border text-uppercase font-monospace px-2.5 py-1 small"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="badge <?php echo ($item['status'] === 'Claimed') ? 'bg-success-subtle text-success-emphasis' : (($item['status'] === 'Pending Verification') ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis'); ?> px-2.5 py-1 small"><?php echo htmlspecialchars($item['status']); ?></span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <div class="text-muted small mb-1"><i class="fa-solid fa-map-marker-alt text-success me-1"></i> <?php echo htmlspecialchars($item['location']); ?></div>
                            <div class="text-muted small mb-3"><i class="fa-solid fa-calendar me-1"></i> Found on <?php echo $item['event_date']; ?></div>
                            <p class="card-text text-muted mb-4 flex-grow-1" style="font-size: 0.9rem; line-height: 1.5"><?php echo htmlspecialchars($item['description']); ?></p>
                            <a href="found_detail.php?id=<?php echo $item['item_id']; ?>" class="btn btn-outline-secondary w-100 mt-auto py-2" style="border-radius: 20px; font-size: 0.9rem; border-color: #ced4da; color: #495057;">View details & claim</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
