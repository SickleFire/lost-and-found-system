<?php
require_once '../db.php';
requireAdminOrStaff();

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include '../header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-users-gear text-dark me-2"></i> Manage Community Users</h2>
            <p class="text-muted">View registered system users and staff accounts</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <div class="card shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'staff' ? 'warning text-dark' : 'primary'); ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
