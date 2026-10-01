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

$lostItems = $pdo->query("SELECT l.*, u.full_name FROM lost_items l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC")->fetchAll();
$foundItems = $pdo->query("SELECT f.*, u.full_name FROM found_items f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC")->fetchAll();

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
            <button class="nav-link active fw-bold" id="lost-tab" data-bs-toggle="tab" data-bs-target="#lost-pane" type="button" role="tab">
                <i class="fa-solid fa-search me-1"></i> Lost Items (<?php echo count($lostItems); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="found-tab" data-bs-toggle="tab" data-bs-target="#found-pane" type="button" role="tab">
                <i class="fa-solid fa-bullhorn me-1"></i> Found Items (<?php echo count($foundItems); ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="itemTabContent">
        <!-- Lost Pane -->
        <div class="tab-pane fade show active" id="lost-pane" role="tabpanel">
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
                            <?php foreach ($lostItems as $item): ?>
                                <tr>
                                    <td class="fw-bold"><a href="../lost_detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></td>
                                    <td><span class="badge bg-danger"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['location_lost']); ?></td>
                                    <td><span class="badge bg-<?php echo $item['status'] === 'Open' ? 'warning text-dark' : 'secondary'; ?>"><?php echo $item['status']; ?></span></td>
                                    <td>
                                        <a href="../lost_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                        <a href="manage_items.php?type=lost&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this lost item?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Found Pane -->
        <div class="tab-pane fade" id="found-pane" role="tabpanel">
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
                            <?php foreach ($foundItems as $item): ?>
                                <tr>
                                    <td class="fw-bold"><a href="../found_detail.php?id=<?php echo $item['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($item['title']); ?></a></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['location_found']); ?></td>
                                    <td><span class="badge bg-<?php echo $item['status'] === 'Unclaimed' ? 'primary' : ($item['status'] === 'Pending Verification' ? 'warning text-dark' : 'secondary'); ?>"><?php echo $item['status']; ?></span></td>
                                    <td>
                                        <a href="../found_detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-eye"></i></a>
                                        <a href="manage_items.php?type=found&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this found item?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
