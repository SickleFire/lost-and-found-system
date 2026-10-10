<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Query user and join roles table to get string representation of user's role access level
        $stmt = $pdo->prepare("SELECT u.*, r.role 
                               FROM users u 
                               JOIN roles r ON u.role_id = r.role_id 
                               WHERE u.username = ? OR u.email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        // Target authentic column passwordhash to match input checks
        if ($user && password_verify($password, $user['passwordhash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = trim($user['firstname'] . ' ' . $user['lastname']);
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-lock text-primary fa-3x mb-2"></i>
                    <h3 class="fw-bold">Member & Staff Login</h3>
                    <p class="text-muted">Sign in to report or claim items</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-1"></i> Registration successful! Please log in below.</div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="fa-solid fa-sign-in-alt me-2"></i> Login</button>
                </form>

                <hr class="my-4">
                <div class="text-center">
                    <p class="text-muted mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>