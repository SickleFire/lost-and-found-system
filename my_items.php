<?php
require_once 'db.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch user's lost items
$myLost = $pdo->prepare("SELECT * FROM lost_items WHERE user_id = ? ORDER BY created_at DESC");
$myLost->execute([$user_id]);
$lostItems = $myLost->fetchAll();

// Fetch user's found items
$myFound = $pdo->prepare("SELECT * FROM found_items WHERE user_id = ? ORDER BY created_at DESC");
$myFound->execute([$user_id]);
$foundItems = $myFound->fetchAll();

// Fetch user's claims
$myClaims = $pdo->prepare("SELECT c.*, f.title as item_title, f.category, f.status as item_status FROM claims c JOIN found_items f ON c.found_item_id = f.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$myClaims->execute([$user_id]);
$claims = $myClaims->fetchAll();

include 'header.php';
?>

<div class="container my-5">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-folder-open text-primary me-2"></i> My Dashboard & Activity</h2>
        <p class="text-muted">Manage your posted lost items, reported found items, and claim tracking status</p>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="lost-tab" data-bs-toggle="tab" data-bs-target="#lost-pane" type="button" role="tab">
                <i class="fa-solid fa-search me-1"></i> My Lost Items (<?php echo count($lostItems); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="found-tab" data-bs-toggle="tab" data-bs-target="#found-pane" type="button" role="tab">
                <i class="fa-solid fa-bullhorn me-1"></i> My Found Items (<?php echo count($foundItems); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims-pane" type="button" role="tab">
                <i class="fa-solid fa-handshake me-1"></i> My Claims (<?php echo count($claims); ?>)
            </button>
        </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content" id="myTabContent">
        <!-- Lost Items Pane -->
        <div class="tab-pane fade show active" id="lost-pane" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold">Items You Reported Lost</h4>
                <a href="post_lost.php" class="btn btn-primary btn-sm fw-bold"><i class="fa-solid fa-plus me-1"></i> Post Lost Item</a>
            </div>
            <?php if (empty($lostItems)): ?>
                <div class="alert alert-info">You haven't reported any lost items yet.</div>
            <?php else: ?>
                <div class="table-responsive bg-white rounded shadow-sm p-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location Lost</th>
                                <th>Date Lost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lostItems as $item): ?>
                                <tr>
                                    <td class="fw-bold"><a href="lost_detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></td>
                                    <td><span class="badge bg-danger"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['location_lost']); ?></td>
                                    <td><?php echo $item['date_lost']; ?></td>
                                    <td><span class="badge bg-<?php echo $item['status'] === 'Open' ? 'warning text-dark' : 'secondary'; ?>"><?php echo $item['status']; ?></span></td>
                                    <td>
                                        <a href="lost_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Found Items Pane -->
        <div class="tab-pane fade" id="found-pane" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold">Items You Reported Found</h4>
                <a href="post_found.php" class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-plus me-1"></i> Post Found Item</a>
            </div>
            <?php if (empty($foundItems)): ?>
                <div class="alert alert-info">You haven't reported any found items yet.</div>
            <?php else: ?>
                <div class="table-responsive bg-white rounded shadow-sm p-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location Found</th>
                                <th>Date Found</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($foundItems as $item): ?>
                                <tr>
                                    <td class="fw-bold"><a href="found_detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['location_found']); ?></td>
                                    <td><?php echo $item['date_found']; ?></td>
                                    <td><span class="badge bg-<?php echo $item['status'] === 'Unclaimed' ? 'primary' : ($item['status'] === 'Pending Verification' ? 'warning text-dark' : 'secondary'); ?>"><?php echo $item['status']; ?></span></td>
                                    <td>
                                        <a href="found_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Claims Pane -->
        <div class="tab-pane fade" id="claims-pane" role="tabpanel">
            <div class="mb-3">
                <h4 class="fw-bold">Your Claim Tracking Status</h4>
            </div>
            <?php if (empty($claims)): ?>
                <div class="alert alert-info">You haven't filed any claims on found items yet.</div>
            <?php else: ?>
                <div class="table-responsive bg-white rounded shadow-sm p-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item Title</th>
                                <th>Category</th>
                                <th>Proof / Description</th>
                                <th>Claim Status</th>
                                <th>Admin Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claims as $claim): ?>
                                <tr>
                                    <td class="fw-bold"><a href="found_detail.php?id=<?php echo $claim['found_item_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($claim['item_title']); ?></a></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($claim['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($claim['proof_description'], 0, 50)); ?>...</td>
                                    <td><span class="badge bg-<?php echo $claim['status'] === 'Approved' ? 'success' : ($claim['status'] === 'Rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo $claim['status']; ?></span></td>
                                    <td><?php echo htmlspecialchars($claim['admin_notes'] ?? 'None'); ?></td>
                                    <td>
                                        <a href="found_detail.php?id=<?php echo $claim['found_item_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
