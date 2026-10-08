<?php
require_once '../db.php';
requireAdminOrStaff();

$action = $_GET['action'] ?? '';
$target_user_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';

// Handle structural cascading delete using the authentic user_id PK field structural layout
if ($action === 'delete' && $target_user_id > 0) {
    // Basic protection rule: Prevent admins from accidentally deleting their own logged-in session account
    if ($target_user_id === intval($_SESSION['user_id'])) {
        $error = "Security Violation: You cannot delete your own active administrator account session.";
    } else {
        $stmtDelete = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmtDelete->execute([$target_user_id])) {
            $success = "The user account record has been successfully purged from the portal database registry.";
        } else {
            $error = "Database constraint conflict: Unable to drop user account record profile.";
        }
    }
}

// Search and filter parameter variables tracking
$search = trim($_GET['search'] ?? '');
$role_id = intval($_GET['role'] ?? 0);

// Fetch all structural lookup roles from the database definitions
$systemRoles = $pdo->query("SELECT * FROM roles ORDER BY role_id ASC")->fetchAll();

// Construct base select query explicitly combining split user elements and joined role lookup strings
$userSql = "SELECT u.*, r.role 
            FROM users u 
            JOIN roles r ON u.role_id = r.role_id 
            WHERE 1=1";
$userParams = [];

if ($search !== '') {
    $userSql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ? OR u.phone LIKE ?)";
    $searchWild = "%$search%";
    $userParams = array_merge($userParams, [$searchWild, $searchWild, $searchWild, $searchWild, $searchWild]);
}

if ($role_id > 0) {
    $userSql .= " AND u.role_id = ?";
    $userParams[] = $role_id;
}

$userSql .= " ORDER BY u.created_at DESC";
$stmtUsers = $pdo->prepare($userSql);
$stmtUsers->execute($userParams);
$usersList = $stmtUsers->fetchAll();

include '../header.php';
?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-users text-dark me-2"></i> Manage Community Users</h2>
            <p class="text-muted">View registered system users, update profile details, credentials, and roles</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary bg-white text-dark border-secondary-subtle px-3 py-1.5 fw-bold" style="border-radius: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle me-1"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Filter Control Card Form -->
    <div class="card shadow-sm mb-4 bg-light border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form method="GET" action="manage_users.php" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Search Name / Username / Contact</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">System Role</label>
                    <select class="form-select" name="role">
                        <option value="">All Roles</option>
                        <?php foreach ($systemRoles as $r): ?>
                            <option value="<?php echo $r['role_id']; ?>" <?php echo $role_id === intval($r['role_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold px-3">Filter</button>
                    <a href="manage_users.php" class="btn btn-outline-secondary bg-white"><i class="fa-solid fa-rotate-right"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Ledger Grid Table -->
    <div class="card shadow-sm p-4 border border-light-subtle mb-5" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-dark small border-bottom" style="font-weight: 600;">
                        <th class="py-3">ID</th>
                        <th class="py-3">Full Name</th>
                        <th class="py-3">Username</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Phone</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Registered Date</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usersList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No registered users found matching your filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usersList as $user): ?>
                            <tr class="border-bottom border-light-subtle">
                                <td class="py-3 text-muted"><?php echo $user['user_id']; ?></td>
                                <td class="py-3 fw-bold text-dark">
                                    <?php 
                                    $fullName = htmlspecialchars($user['firstname'] . ' ' . $user['lastname']);
                                    if ($user['role'] === 'staff') echo $fullName . ' (Staff)';
                                    elseif ($user['role'] === 'admin') echo 'System Administrator';
                                    else echo $fullName;
                                    ?>
                                </td>
                                <td class="py-3"><span style="color: #d63384; font-size: 0.9rem;"><?php echo htmlspecialchars($user['username']); ?></span></td>
                                <td class="py-3 text-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="py-3 text-muted"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
                                <td class="py-3">
                                    <?php
                                    $badgeStyle = 'background-color: #6c757d; color: #fff;';
                                    if ($user['role'] === 'admin') $badgeStyle = 'background-color: #495057; color: #fff;';
                                    if ($user['role'] === 'staff') $badgeStyle = 'background-color: #f8d7da; color: #842029;';
                                    if ($user['role'] === 'member') $badgeStyle = 'background-color: #e2e3e5; color: #41464b;';
                                    ?>
                                    <span class="badge rounded-pill px-3 py-1.5 text-uppercase text-xs" style="<?php echo $badgeStyle; ?> font-size: 0.75rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td class="py-3 text-muted small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="py-3">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <!-- Edit button matches layout mockup functionality -->
                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-secondary bg-white text-muted border-light-subtle p-2 d-inline-flex align-items-center justify-content-center" style="border-radius: 6px; width: 32px; height: 32px;" title="Edit user profile">
                                            <i class="fa-regular fa-pen-to-square font-size: 0.85rem;"></i>
                                        </a>
                                        
                                        <?php if (intval($user['user_id']) !== intval($_SESSION['user_id'])): ?>
                                            <!-- Delete icon matches layout mockup functionality -->
                                            <a href="manage_users.php?action=delete&id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger bg-white p-2 d-inline-flex align-items-center justify-content-center border-light-subtle" 
                                               style="border-radius: 6px; width: 32px; height: 32px; color: #ea868f;"
                                               onclick="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars($user['username']); ?>? This will drop their associated profile summary data links.');" 
                                               title="Delete User record profile">
                                                <i class="fa-regular fa-trash-can font-size: 0.85rem;"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>