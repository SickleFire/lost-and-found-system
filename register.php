<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email is already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, 'member')");
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-user-plus text-success fa-3x mb-2"></i>
                    <h3 class="fw-bold">Community Member Registration</h3>
                    <p class="text-muted">Create an account to post lost items or claim found items</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g. 555-0199" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password * (Min 6 chars)</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold"><i class="fa-solid fa-user-check me-2"></i> Register Account</button>
                </form>

                <hr class="my-4">
                <div class="text-center">
                    <p class="text-muted mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
