<?php
require_once '../db.php';
requireAdminOrStaff();

$id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';

// Fetch the targeted user account record from the database based on your ERD chart
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Redirect back if the user record does not exist
if (!$user) {
    header("Location: manage_users.php");
    exit;
}

// Process user structural profile update data modifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role_id = intval($_POST['role_id'] ?? 0);

    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || $role_id <= 0) {
        $error = 'Please fill in all required fields completely.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address format.';
    } elseif (!empty($phone) && !preg_match('/^09\d{9}$/', $phone)) {
        $error = 'Phone number must start with 09 and be exactly 11 digits.';
    } else {
        // Double check uniqueness constraint boundaries for username/email changes
        $stmtCheck = $pdo->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmtCheck->execute([$username, $email, $id]);
        if ($stmtCheck->fetch()) {
            $error = 'The username or email address is already assigned to another user.';
        } else {
            // Apply modifications using your authentic ERD schema column names
            $stmtUpdate = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, username = ?, email = ?, phone = ?, role_id = ? WHERE user_id = ?");
            if ($stmtUpdate->execute([$firstname, $lastname, $username, $email, $phone, $role_id, $id])) {
                $success = 'The user account profile details have been updated successfully.';
                
                // Keep local values synchronized for display continuity inside the form fields
                $user['firstname'] = $firstname;
                $user['lastname'] = $lastname;
                $user['username'] = $username;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['role_id'] = $role_id;
            } else {
                $error = 'Failed to execute database updates. Please try again.';
            }
        }
    }
}

// Query system master roles for dropdown element selection lookups
$systemRoles = $pdo->query("SELECT * FROM roles ORDER BY role_id ASC")->fetchAll();

include '../header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm p-4 border border-light-subtle" style="border-radius: 16px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold text-dark m-0"><i class="fa-solid fa-user-pen text-secondary me-2"></i> Edit User Profile</h3>
                        <p class="text-muted small m-0">Modify registration parameters, contact coordinates, or manage security role tiers</p>
                    </div>
                    <a href="manage_users.php" class="btn btn-outline-secondary bg-white text-dark border-secondary-subtle px-3 py-1.5 fw-bold" style="border-radius: 20px; font-size: 0.85rem;">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Ledger
                    </a>
                </div>

                <hr class="text-light-subtle my-3">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-1"></i> <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="edit_user.php?id=<?php echo $id; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label fw-bold small text-muted">First Name *</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label fw-bold small text-muted">Last Name *</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold small text-muted">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small text-muted">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label for="phone" class="form-label fw-bold small text-muted">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g. 09123456789" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            <div class="form-text text-muted" style="font-size: 0.75rem;">Must be 11 digits starting with 09 (if provided).</div>
                        </div>
                        <div class="col-md-5 mb-4">
                            <label for="role_id" class="form-label fw-bold small text-muted">Assign System Access Role *</label>
                            <select class="form-select text-capitalize" id="role_id" name="role_id" required>
                                <option value="">Select Role</option>
                                <?php foreach ($systemRoles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>" <?php echo (intval($user['role_id']) === intval($role['role_id'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['role']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="manage_users.php" class="btn btn-outline-secondary px-4 fw-bold" style="border-radius: 8px;">Cancel</a>
                        <button type="submit" class="btn btn-dark px-4 fw-bold" style="border-radius: 8px;"><i class="fa-solid fa-save me-1"></i> Apply Modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
