<?php
require_once '../db.php';
requireAdminOrStaff();

$success = '';
$error = '';

// Handle Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $userId = intval($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $newPassword = $_POST['new_password'] ?? '';

    if ($userId <= 0 || $username === '' || $fullName === '' || $email === '' || $phone === '' || $role === '') {
        $error = 'All fields (Username, Full Name, Email, Phone, and Role) are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^09\d{9}$/', $phone)) {
        $error = 'Phone number must start with 09 and contain exactly 11 digits (e.g. 09123456789).';
    } else {
        // Check if username or email already taken by another user
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmtCheck->execute([$username, $email, $userId]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $error = 'Username or email address is already in use by another account.';
        } else {
            if ($newPassword !== '') {
                // Update with password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?");
                $stmtUpdate->execute([$username, $fullName, $email, $phone, $role, $hashedPassword, $userId]);
            } else {
                // Update without changing password
                $stmtUpdate = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
                $stmtUpdate->execute([$username, $fullName, $email, $phone, $role, $userId]);
            }
            $success = 'User account updated successfully.';
        }
    }
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = intval($_GET['id'] ?? 0);
    // Prevent self-deletion if current user is admin
    if ($delId === intval($_SESSION['user_id'] ?? 0)) {
        $error = 'You cannot delete your own active admin account.';
    } elseif ($delId > 0) {
        $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->execute([$delId]);
        header("Location: manage_users.php?msg=" . urlencode("User deleted successfully."));
        exit;
    }
}

if (isset($_GET['msg'])) {
    $success = $_GET['msg'];
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include '../header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-users-gear text-dark me-2"></i> Manage Community Users</h2>
            <p class="text-muted">View registered system users, update profile details, credentials, and roles</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><code><?php echo htmlspecialchars($u['username']); ?></code></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'staff' ? 'warning text-dark' : 'primary'); ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $u['id']; ?>" title="Edit User">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <?php if ($u['id'] !== intval($_SESSION['user_id'] ?? 0)): ?>
                                    <a href="manage_users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars($u['username']); ?>? This will also affect their posted items and claims.');" title="Delete User">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit User Modals (Placed outside table container to prevent DOM/Bootstrap glitching) -->
<?php foreach ($users as $u): ?>
<div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="manage_users.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i> Edit User: <?php echo htmlspecialchars($u['username']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($u['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>" placeholder="09XXXXXXXXX" pattern="09[0-9]{9}" maxlength="11" title="Phone number must start with 09 and be 11 digits" required>
                        <div class="form-text">Must start with 09 and be exactly 11 digits (e.g. 09123456789).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">System Role</label>
                        <select class="form-select" name="role">
                            <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="staff" <?php echo $u['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password <span class="text-muted fw-normal small">(Leave blank to keep current password)</span></label>
                        <input type="password" class="form-control" name="new_password" placeholder="Enter new password if changing">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
