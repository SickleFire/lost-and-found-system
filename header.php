<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Lost &amp; Found System</title>
    <!-- Google Fonts: Figtree -->
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --blue-slate: #555b6eff;
            --muted-teal: #89b0aeff;
            --frozen-water: #bee3dbff;
            --bright-snow: #faf9f9ff;
            --peach-fuzz: #ffd6baff;
            --ink: #3b3f4d;
        }

        body {
            background-color: var(--bright-snow);
            color: var(--blue-slate);
            font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main { flex: 1; }

        /* Navigation Bar */
        .navbar {
            background-color: var(--blue-slate) !important;
            border-bottom: 1px solid rgba(137, 176, 174, 0.3);
        }
        .navbar-brand {
            color: var(--bright-snow) !important;
            font-weight: 700;
        }
        .navbar-brand i {
            color: var(--peach-fuzz) !important;
        }
        .navbar-nav .nav-link {
            color: var(--frozen-water) !important;
            font-weight: 500;
            transition: color .15s ease;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
            color: var(--bright-snow) !important;
        }
        .navbar-nav .dropdown-menu {
            background-color: var(--bright-snow);
            border: 1px solid var(--muted-teal);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(85, 91, 110, 0.1);
        }
        .navbar-nav .dropdown-item {
            color: var(--blue-slate);
            font-weight: 500;
        }
        .navbar-nav .dropdown-item:hover {
            background-color: var(--frozen-water);
            color: var(--ink);
        }

        /* Buttons matching index.php */
        .btn-primary {
            background-color: var(--blue-slate);
            border-color: var(--blue-slate);
            color: var(--bright-snow);
            font-weight: 600;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            transition: background-color .15s, border-color .15s;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--ink);
            border-color: var(--ink);
            color: var(--bright-snow);
        }

        .btn-success {
            background-color: var(--muted-teal);
            border-color: var(--muted-teal);
            color: var(--ink);
            font-weight: 600;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            transition: background-color .15s, border-color .15s;
        }
        .btn-success:hover, .btn-success:focus {
            background-color: #729b99;
            border-color: #729b99;
            color: var(--ink);
        }

        .btn-warning {
            background-color: var(--peach-fuzz);
            border-color: var(--peach-fuzz);
            color: var(--ink);
            font-weight: 600;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            transition: background-color .15s, border-color .15s;
        }
        .btn-warning:hover, .btn-warning:focus {
            background-color: #ffc7a0;
            border-color: #ffc7a0;
            color: var(--ink);
        }

        .btn-outline-primary {
            border-color: var(--muted-teal);
            color: var(--blue-slate);
            font-weight: 600;
            border-radius: 999px;
        }
        .btn-outline-primary:hover {
            background-color: var(--frozen-water);
            border-color: var(--muted-teal);
            color: var(--ink);
        }

        .btn-outline-secondary, .btn-outline-success {
            border-color: var(--muted-teal);
            color: var(--blue-slate);
            font-weight: 600;
            border-radius: 999px;
        }
        .btn-outline-secondary:hover, .btn-outline-success:hover {
            background-color: var(--frozen-water);
            color: var(--ink);
        }

        .btn-dark {
            background-color: var(--blue-slate);
            border-color: var(--blue-slate);
            color: var(--bright-snow);
            border-radius: 999px;
            font-weight: 600;
        }
        .btn-dark:hover {
            background-color: var(--ink);
        }

        /* Cards & Containers */
        .card {
            border: 1px solid var(--frozen-water);
            border-radius: 16px;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(85, 91, 110, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(85, 91, 110, 0.08);
        }
        .bg-light {
            background-color: var(--frozen-water) !important;
            border-radius: 16px;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: .35em .75em;
            border-radius: 999px;
        }
        .bg-danger { background-color: var(--blue-slate) !important; color: var(--bright-snow); }
        .bg-success { background-color: var(--muted-teal) !important; color: var(--ink); }
        .bg-primary { background-color: var(--blue-slate) !important; color: var(--bright-snow); }
        .bg-warning { background-color: var(--peach-fuzz) !important; color: var(--ink) !important; }
        .bg-secondary { background-color: var(--frozen-water) !important; color: var(--ink) !important; }

        /* Form controls */
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid var(--muted-teal);
            padding: .65rem 1rem;
            background-color: #fff;
            color: var(--ink);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--blue-slate);
            box-shadow: 0 0 0 3px rgba(137, 176, 174, 0.25);
        }
        .input-group-text {
            background-color: var(--frozen-water);
            border: 1px solid var(--muted-teal);
            border-radius: 12px 0 0 12px;
            color: var(--blue-slate);
        }
        .form-control { border-radius: 12px; }
        .input-group .form-control:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Nav Tabs */
        .nav-tabs {
            border-bottom: 2px solid var(--frozen-water);
        }
        .nav-tabs .nav-link {
            color: var(--blue-slate);
            border: none;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
            padding: .75rem 1.25rem;
        }
        .nav-tabs .nav-link.active {
            background-color: var(--frozen-water);
            color: var(--ink);
            border-bottom: 3px solid var(--muted-teal);
        }

        /* Tables */
        .table {
            color: var(--blue-slate);
        }
        .table-light {
            background-color: var(--frozen-water) !important;
            color: var(--ink);
        }
        .table>:not(caption)>*>* {
            border-bottom-color: var(--frozen-water);
        }

        /* Footer */
        footer {
            background-color: var(--blue-slate);
            color: var(--frozen-water);
            border-top: 1px solid rgba(137, 176, 174, 0.3);
        }
        footer .text-muted {
            color: var(--frozen-water) !important;
            opacity: 0.8;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fa-solid fa-box-open me-2"></i>Community Lost &amp; Found
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
                    <li class="nav-item"><a class="nav-link" href="my_items.php"><i class="fa-solid fa-folder-open me-1"></i> My Posts &amp; Claims</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdminOrStaff()): ?>
                        <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="admin/dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-sign-in-alt me-1"></i> Login</a></li>
                    <li class="nav-item"><a class="btn btn-warning ms-2" href="register.php"><i class="fa-solid fa-user-plus me-1"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
