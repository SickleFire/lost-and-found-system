<?php
require_once '../db.php';
requireAdminOrStaff();

$lostItems = $pdo->query("SELECT l.*, u.full_name FROM lost_items l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC")->fetchAll();
$foundItems = $pdo->query("SELECT f.*, u.full_name FROM found_items f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC")->fetchAll();

include '../header.php';
?>

<style>
@media print {
    body, html {
        height: auto !important;
        overflow: visible !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
}
</style>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-print text-dark me-2"></i> Printable Reports</h2>
            <p class="text-muted">Official summary records of lost and found items in the community</p>
        </div>
        <div class="d-print-none">
            <button onclick="window.print();" class="btn btn-dark fw-bold me-2"><i class="fa-solid fa-print me-1"></i> Print Report</button>
            <a href="dashboard.php" class="btn btn-outline-secondary fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
        </div>
    </div>

    <!-- Report Header for Print -->
    <div class="card shadow-sm p-4 mb-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Community Lost & Found System - Official Status Report</h3>
            <p class="text-muted mb-0">Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
        </div>

        <h4 class="fw-bold text-primary mb-3">1. Lost Items Summary Report</h4>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Reporter</th>
                        <th>Location Lost</th>
                        <th>Date Lost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lostItems as $item): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['location_lost']); ?></td>
                            <td><?php echo $item['date_lost']; ?></td>
                            <td><?php echo $item['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm p-4 mb-4" style="page-break-before: always;">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Community Lost & Found System - Official Status Report</h3>
            <p class="text-muted mb-0">Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
        </div>

        <h4 class="fw-bold text-success mb-3">2. Found Items Summary Report</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Finder</th>
                        <th>Location Found</th>
                        <th>Date Found</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foundItems as $item): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['location_found']); ?></td>
                            <td><?php echo $item['date_found']; ?></td>
                            <td><?php echo $item['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
