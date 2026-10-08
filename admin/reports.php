<?php
// Force XAMPP to sync with your actual local timezone to eliminate the 1-day lag notice
date_default_timezone_set('Asia/Manila');

require_once '../db.php';
requireAdminOrStaff();

// 1. Fetch Lost Items dataset directly from your centralized table structure
$lostItems = $pdo->query("SELECT i.*, c.category, s.status, CONCAT(u.firstname, ' ', u.lastname) AS full_name 
                          FROM items i 
                          JOIN users u ON i.user_id = u.user_id 
                          JOIN categories c ON i.category_id = c.category_id 
                          JOIN i_status s ON i.status_id = s.status_id 
                          WHERE i.item_type = 'Lost' 
                          ORDER BY i.created_at DESC")->fetchAll();

// 2. Fetch Found Items dataset directly from your centralized table structure
$foundItems = $pdo->query("SELECT i.*, c.category, s.status, CONCAT(u.firstname, ' ', u.lastname) AS full_name 
                           FROM items i 
                           JOIN users u ON i.user_id = u.user_id 
                           JOIN categories c ON i.category_id = c.category_id 
                           JOIN i_status s ON i.status_id = s.status_id 
                           WHERE i.item_type = 'Found' 
                           ORDER BY i.created_at DESC")->fetchAll();

include '../header.php';
?>

<style>
/* Clean Web Feed Layout */
.report-section {
    background: #fff;
    padding: 20px 0;
    margin-bottom: 40px;
}
.report-table {
    width: 100% !important;
    border-collapse: collapse;
}
.report-table th, .report-table td {
    padding: 10px 12px !important;
    text-align: left;
    vertical-align: middle !important;
    white-space: nowrap; 
}
.report-table th:nth-child(1), .report-table td:nth-child(1),
.report-table th:nth-child(4), .report-table td:nth-child(4) {
    white-space: normal !important; 
    word-break: break-word !important;
}
.report-table th {
    font-weight: 600 !important;
    background-color: #f8f9fa !important;
}
.report-table td.item-title {
    font-weight: 700 !important;
    color: #000;
}

/* Conditional Print Scope Filtering Classes via Custom Attributes */
body[data-print-scope="lost"] .section-found { display: none !important; }
body[data-print-scope="found"] .section-lost { display: none !important; }

/* Force standard page split parameters on combined reports prints */
body[data-print-scope="all"] .section-found {
    page-break-before: always !important;
}

/* Core Print Layout Overrides - Suppressing Document Background Spacers */
@media print {
    @page {
        size: auto;
        margin: 15mm 20mm 20mm 20mm; /* Clean document canvas safety margins */
    }
    
    header, nav, .navbar, .d-print-none, footer, .footer, #footer, [class*="footer"] {
        display: none !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        opacity: 0 !important;
        visibility: hidden !important;
    }
    
    html, body {
        background: #fff !important;
        color: #000 !important;
        font-size: 13px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 auto !important;
    }
    
    .report-section {
        padding: 0 !important;
        margin-top: 5px !important;
        margin-bottom: 20px;
        page-break-inside: avoid;
    }
    
    .table-responsive {
        overflow: visible !important;
    }
}
</style>

<script>
// Dynamic runtime interceptor method to clear out native web browser layout top header fields
function runCleanPrint() {
    var originalTitle = document.title;
    
    // FIX: Overwriting the page element title property forces browser prints to display blank headers
    document.title = ""; 
    
    // Fire printing panel prompt action
    window.print();
    
    // Restore system parameters cleanly for workspace operations loop continuity
    setTimeout(function() {
        document.title = originalTitle;
    }, 50);
}

function updatePrintScope(scopeValue) {
    document.body.setAttribute('data-print-scope', scopeValue);
    if(scopeValue === 'lost') {
        document.getElementById('lostCard').style.display = 'block';
        document.getElementById('foundCard').style.display = 'none';
    } else if(scopeValue === 'found') {
        document.getElementById('lostCard').style.display = 'none';
        document.getElementById('foundCard').style.display = 'block';
    } else {
        document.getElementById('lostCard').style.display = 'block';
        document.getElementById('foundCard').style.display = 'block';
    }
}
</script>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-print text-dark me-2"></i> Printable Reports</h2>
            <p class="text-muted mb-0">Official summary records of lost and found items in the community</p>
        </div>
        <div>
            <!-- Button maps execution directly via the new dynamic title sanitizer handler -->
            <button onclick="runCleanPrint();" class="btn btn-dark fw-bold me-2" style="border-radius: 8px;"><i class="fa-solid fa-print me-1"></i> Print Report</button>
            <a href="dashboard.php" class="btn btn-outline-secondary fw-bold" style="border-radius: 8px;"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4 bg-light border-0 d-print-none" style="border-radius: 12px;">
        <div class="card-body p-3 d-flex align-items-center gap-3">
            <span class="fw-bold small text-muted text-uppercase tracking-wider"><i class="fa-solid fa-filter me-1"></i> Print Scope:</span>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="printScope" id="scopeAll" value="all" checked onclick="updatePrintScope('all');">
                <label class="form-check-label fw-bold small text-dark" for="scopeAll">Print All Summary Reports</label>
            </div>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="printScope" id="scopeLost" value="lost" onclick="updatePrintScope('lost');">
                <label class="form-check-label fw-bold small text-primary" for="scopeLost">Lost Items Only</label>
            </div>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="printScope" id="scopeFound" value="found" onclick="updatePrintScope('found');">
                <label class="form-check-label fw-bold small text-success" for="scopeFound">Found Items Only</label>
            </div>
        </div>
    </div>

    <script>document.body.setAttribute('data-print-scope', 'all');</script>

    <!-- 1. Lost Items Summary Section -->
    <div class="report-section section-lost" id="lostCard">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Community Lost & Found System - Official Status Report</h3>
            <p class="text-muted mb-0">Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
        </div>

        <h4 class="fw-bold text-primary mb-3" style="font-size: 1.4rem;">1. Lost Items Summary Report</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered report-table align-middle">
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
                    <?php if (empty($lostItems)): ?>
                        <tr><td colspan="6" class="text-center py-3 text-muted">No lost items recorded in the system.</td></tr>
                    <?php else: ?>
                        <?php foreach ($lostItems as $item): ?>
                            <tr>
                                <td class="item-title"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td class="font-monospace text-muted"><?php echo htmlspecialchars($item['event_date']); ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($item['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 2. Found Items Summary Section -->
    <div class="report-section section-found" id="foundCard">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Community Lost & Found System - Official Status Report</h3>
            <p class="text-muted mb-0">Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
        </div>

        <h4 class="fw-bold text-success mb-3" style="font-size: 1.4rem;">2. Found Items Summary Report</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered report-table align-middle">
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
                    <?php if (empty($foundItems)): ?>
                        <tr><td colspan="6" class="text-center py-3 text-muted">No found items recorded in the system.</td></tr>
                    <?php else: ?>
                        <?php foreach ($foundItems as $item): ?>
                            <tr>
                                <td class="item-title"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td class="font-monospace text-muted"><?php echo htmlspecialchars($item['event_date']); ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($item['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
