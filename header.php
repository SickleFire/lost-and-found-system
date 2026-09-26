<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Lost & Found System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; }
        .hero-section { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 60px 0; border-radius: 0 0 2rem 2rem; margin-bottom: 40px; }
        .card { border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
        footer { background-color: #212529; color: #adb5bd; }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fa-solid fa-box-open text-warning me-2"></i>Community Lost & Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa-solid fa-home me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="lost_items.php"><i class="fa-solid fa-search me-1"></i> Lost Items</a></li>
                <li class="nav-item"><a class="nav-link" href="found_items.php"><i class="fa-solid fa-bullhorn me-1"></i> Found Items</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="my_items.php"><i class="fa-solid fa-folder-open me-1"></i> My Posts & Claims</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdminOrStaff()): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="admin/dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-sign-in-alt me-1"></i> Login</a></li>
                    <li class="nav-item"><a class="btn btn-warning text-dark ms-2" href="register.php"><i class="fa-solid fa-user-plus me-1"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
