<?php
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);

// Fetch item details utilizing centralized items table structure metrics
$stmt = $pdo->prepare("SELECT i.*, c.category, s.status, CONCAT(u.firstname, ' ', u.lastname) AS finder_name, u.email, u.phone,
                              lost.item_id AS linked_lost_item_id, lost.title AS linked_lost_title
                       FROM items i 
                       JOIN users u ON i.user_id = u.user_id 
                       JOIN categories c ON i.category_id = c.category_id 
                       JOIN i_status s ON i.status_id = s.status_id 
                       LEFT JOIN items lost ON i.related_lost_item_id = lost.item_id AND lost.item_type = 'Lost'
                       WHERE i.item_id = ? AND i.item_type = 'Found'");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: found_items.php");
    exit;
}

$isClaimable = in_array((int) $item['status_id'], [1, 2], true);

// Security evaluation check tracking administrative profile access credentials
$canEdit = false;
if (isLoggedIn()) {
    if ($_SESSION['user_id'] == $item['user_id'] || isAdminOrStaff()) {
        $canEdit = true;
    }
}

include 'header.php';
?>

<!-- Breadcrumb path alignment navigation elements matching lost view styles layout -->
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small"><a href="found_items.php" class="text-decoration-none text-primary">Found Items</a></li>
                    <li class="breadcrumb-item small text-muted active" aria-current="page"><?php echo htmlspecialchars($item['title']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Primary Component Interface Body - FIXED: Added structural width constraints mapping col-md-9 layout -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border border-light-subtle p-4 p-md-5 bg-white" style="border-radius: 16px;">
                
                <!-- Badge row layout section header component block details -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <!-- Category Badge - FIXED: Solid custom background hex override variables mapping your lost template style perfectly -->
                        <span class="badge text-white px-2.5 py-1.5 small text-capitalize" style="border-radius: 20px; font-weight: 700; font-size: 0.75rem; background-color: #5c636a !important; padding-left: 12px !important; padding-right: 12px !important; border: none !important; opacity: 1 !important;">
                            <?php echo htmlspecialchars($item['category']); ?>
                        </span>
                        
                        <!-- Status Badge - FIXED: Solid high-contrast pastel matching your target schema profiles -->
                        <?php
                        $statusStyle = "background-color: #c5e8e1 !important; color: #0f5132 !important;"; // Default Open Clean Style Hex
                        if ($item['status'] === 'Claimed' || $item['status'] === 'Closed') {
                            $statusStyle = "background-color: #d1e7dd !important; color: #0f5132 !important;";
                        } elseif ($item['status'] === 'Pending Verification') {
                            $statusStyle = "background-color: #ffe6d5 !important; color: #b25e24 !important;";
                        }
                        ?>
                        <span class="badge px-2.5 py-1.5 small text-capitalize" style="border-radius: 20px; font-weight: 500; font-size: 0.75rem; <?php echo $statusStyle; ?> padding-left: 12px !important; padding-right: 12px !important;">
                            <?php echo htmlspecialchars($item['status']); ?>
                        </span>
                    </div>
                    <div class="text-muted small">Posted: <?php echo date('M d, Y', strtotime($item['created_at'])); ?></div>
                </div>

                <!-- Primary Master Title Entry Summary Header Text Section -->
                <h2 class="fw-bold text-dark mb-4" style="font-size: 2.1rem; letter-spacing: -0.5px;"><?php echo htmlspecialchars($item['title']); ?></h2>
                <hr class="text-light-subtle mb-4">
                
                <!-- Tracking Parameters Coordinate Split Row Panels -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="text-muted small mb-1"><i class="fa-solid fa-map-marker-alt text-danger me-1"></i> Location Found</div>
                        <div class="text-dark fs-6" style="font-weight: 500;"><?php echo htmlspecialchars($item['location']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1"><i class="fa-solid fa-calendar text-primary me-1"></i> Date Found</div>
                        <div class="text-dark fs-6" style="font-weight: 500;"><?php echo htmlspecialchars($item['event_date']); ?></div>
                    </div>
                </div>

                <!-- System Description Details Box Panel Component Area -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark mb-3" style="font-size: 1.15rem;">Item Description</h5>
                    <div class="p-3 border border-light-subtle rounded text-dark" style="background-color: #bce5db; line-height: 1.6; font-size: 0.95rem;">
                        <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                    </div>
                </div>

                <?php if ($item['linked_lost_item_id']): ?>
                    <div class="alert alert-info mb-4">
                        <i class="fa-solid fa-link me-1"></i> Reported in response to lost item:
                        <a href="lost_detail.php?id=<?php echo (int) $item['linked_lost_item_id']; ?>" class="alert-link"><?php echo htmlspecialchars($item['linked_lost_title']); ?></a>
                    </div>
                <?php endif; ?>

                <!-- Finder Contact Credentials Summary Panel -->
                <div class="p-4 mb-4 border border-light-subtle rounded" style="background-color: #bce5db; border-radius: 8px;">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center"><i class="fa-solid fa-user-check me-2 text-secondary"></i> Finder / Reporter Contact</h6>
                    <div class="text-dark mb-1.5" style="font-size: 0.95rem;"><strong>Name:</strong> <?php echo htmlspecialchars($item['finder_name']); ?></div>
                    <div class="text-dark mb-1.5" style="font-size: 0.95rem;"><strong>Email:</strong> <?php echo htmlspecialchars($item['email']); ?></div>
                    <div class="text-dark mb-0" style="font-size: 0.95rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($item['phone'] ?? 'Not provided'); ?></div>
                </div>

                <!-- Conditional Status Actions Panel Area Block -->
                <?php if (!$isClaimable): ?>
                    <div class="alert border-0 text-secondary p-3 d-flex align-items-center justify-content-center mb-4 small" style="border-radius: 8px; font-weight: 500; background-color: #e2e3e5 !important; color: #41464b !important;">
                        <i class="fa-solid fa-lock me-2 text-secondary"></i> This item is no longer available for claims.
                    </div>
                <?php endif; ?>

                <!-- Button Navigation Row Section containing your clean action button layouts -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                    <a href="found_items.php" class="btn btn-outline-success px-3 py-1.5 fw-bold" style="border-radius: 20px; font-size: 0.85rem;">&larr; Back to Found Catalog</a>
                    
                    <div class="d-flex align-items-center gap-2">
                        <?php if (isLoggedIn()): ?>
                            <?php if ($_SESSION['user_id'] != $item['user_id'] && $isClaimable): ?>
                                <a href="claim_item.php?item_id=<?php echo $id; ?>" class="btn btn-success fw-bold px-4 py-1.5 d-inline-flex align-items-center" style="border-radius: 20px; font-size: 0.85rem;">
                                    <i class="fa-solid fa-handshake me-1.5 small"></i> Claim This Item
                                </a>
                            <?php elseif ($_SESSION['user_id'] == $item['user_id']): ?>
                                <span class="text-muted small italic me-2">This is an item you reported found.</span>
                            <?php endif; ?>
                        <?php elseif ($isClaimable): ?>
                            <a href="login.php" class="btn btn-outline-success px-3 py-1.5 fw-bold" style="border-radius: 20px; font-size: 0.85rem;">Log in to Claim</a>
                        <?php endif; ?>

                        <?php if ($canEdit): ?>
                            <a href="edit_found.php?id=<?php echo $id; ?>" class="btn fw-bold px-4 py-1.5 d-inline-flex align-items-center text-dark border-0" style="background-color: #fecba1; border-radius: 20px; font-size: 0.85rem;">
                                <i class="fa-regular fa-pen-to-square me-1.5 small"></i> Edit Item
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
