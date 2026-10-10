<?php
// Determine root prefix if included from a subfolder like /admin/
$is_admin_subfolder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$root_prefix = $is_admin_subfolder ? '../' : '';
?>
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
            --teal: #0F766E;
            --ink: #1B2430;
            --muted: #4A5663;
            --white: #FFFFFF;
            --border: #E3E8EC;
            --found-border: #CBD3DA;
            --stat-green: #E3F4F1;
            --stat-green-border: #C5E6E0;
            --stat-green-text: #0B5C55;
            --item-text: #3A4652;
            --date-text: #5B6772;
            --badge-bg: #EEF1F4;
            --closed-bg: #E6EAEE;
            --open-bg: #DDF3E7;
        }

        body {
            background-color: #FAFDF9;
            background-image:
                radial-gradient(ellipse at 0% 10%, rgba(214, 246, 198, 0.55), transparent 42%),
                radial-gradient(ellipse at 100% 12%, rgba(196, 236, 210, 0.55), transparent 42%),
                radial-gradient(ellipse at 50% 32%, rgba(233, 251, 223, 0.42), transparent 48%);
            color: var(--muted);
            font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main { flex: 1; }
        .text-dark { color: var(--ink) !important; }
        .text-muted { color: var(--muted) !important; }

        /* Navigation Bar */
        .navbar {
            background-color: var(--white) !important;
            border-bottom: 1px solid var(--border);
        }
        .navbar-brand {
            color: var(--teal) !important;
            font-weight: 700;
        }
        .navbar-brand i {
            color: var(--teal) !important;
        }
        .navbar-nav .nav-link {
            color: var(--muted) !important;
            font-weight: 500;
            transition: color .15s ease;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
            color: var(--teal) !important;
        }
        .navbar-nav .dropdown-menu {
            background-color: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(27, 36, 48, 0.1);
        }
        .navbar-nav .dropdown-item {
            color: var(--muted);
            font-weight: 500;
        }
        .navbar-nav .dropdown-item:hover {
            background-color: var(--badge-bg);
            color: var(--ink);
        }

        /* Buttons matching index.php */
        .btn-primary {
            background-color: var(--teal);
            border-color: var(--teal);
            color: var(--white);
            font-weight: 600;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            transition: background-color .15s, border-color .15s;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--ink);
            border-color: var(--ink);
            color: var(--white);
        }

        .btn-success {
            background-color: var(--teal);
            border-color: var(--teal);
            color: var(--white);
            font-weight: 600;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            transition: background-color .15s, border-color .15s;
        }
        .btn-success:hover, .btn-success:focus {
            background-color: #0B5C55;
            border-color: #0B5C55;
            color: var(--white);
        }

        .btn-warning {
            background-color: var(--teal);
            border-color: var(--teal);
            color: var(--white);
            font-weight: 600;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            transition: background-color .15s, border-color .15s;
        }
        .btn-warning:hover, .btn-warning:focus {
            background-color: #0B5C55;
            border-color: #0B5C55;
            color: var(--white);
        }

        .btn-outline-primary {
            border-color: var(--teal);
            color: var(--teal);
            font-weight: 600;
            border-radius: 999px;
        }
        .btn-outline-primary:hover {
            background-color: var(--stat-green);
            border-color: var(--teal);
            color: var(--ink);
        }

        .btn-outline-secondary, .btn-outline-success {
            border-color: var(--found-border);
            color: var(--muted);
            font-weight: 600;
            border-radius: 999px;
        }
        .btn-outline-secondary:hover, .btn-outline-success:hover {
            background-color: var(--badge-bg);
            color: var(--ink);
        }

        .btn-dark {
            background-color: var(--ink);
            border-color: var(--ink);
            color: var(--white);
            border-radius: 999px;
            font-weight: 600;
        }
        .btn-dark:hover {
            background-color: var(--ink);
        }

        /* Cards & Containers */
        .card {
            border: 1px solid var(--border);
            border-radius: 16px;
            background-color: var(--white);
            box-shadow: 0 4px 20px rgba(27, 36, 48, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(27, 36, 48, 0.08);
        }
        .bg-light {
            background-color: var(--badge-bg) !important;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: .35em .75em;
            border-radius: 999px;
        }
        .bg-danger { background-color: var(--teal) !important; color: var(--white); }
        .bg-success { background-color: var(--teal) !important; color: var(--white); }
        .bg-primary { background-color: var(--teal) !important; color: var(--white); }
        .bg-warning { background-color: var(--open-bg) !important; color: #14683A !important; }
        .bg-secondary { background-color: var(--closed-bg) !important; color: var(--muted) !important; }

        /* Form controls */
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid var(--found-border);
            padding: .65rem 1rem;
            background-color: var(--white);
            color: var(--ink);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
        }
        .input-group-text {
            background-color: var(--badge-bg);
            border: 1px solid var(--found-border);
            border-radius: 12px 0 0 12px;
            color: var(--muted);
        }
        .form-control { border-radius: 12px; }
        .input-group .form-control:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Nav Tabs */
        .nav-tabs {
            border-bottom: 2px solid var(--border);
        }
        .nav-tabs .nav-link {
            color: var(--muted);
            border: none;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
            padding: .75rem 1.25rem;
        }
        .nav-tabs .nav-link.active {
            background-color: var(--stat-green);
            color: var(--ink);
            border-bottom: 3px solid var(--teal);
        }

        /* Tables */
        .table {
            color: var(--muted);
        }
        .table-light {
            background-color: var(--badge-bg) !important;
            color: var(--ink);
        }
        .table>:not(caption)>*>* {
            border-bottom-color: var(--border);
        }

        /* Footer */
        footer {
            background-color: var(--white);
            color: var(--muted);
            border-top: 1px solid var(--border);
        }
        footer .text-muted {
            color: var(--muted) !important;
        }

        .home-eyebrow,
        .home-view-all {
            color: var(--teal);
        }
        .home-stat-card {
            background-color: var(--white);
            border: 1px solid var(--border);
        }
        .home-stat-card-returned {
            background-color: var(--stat-green);
            border-color: var(--stat-green-border);
            color: var(--stat-green-text);
        }
        .home-stat-card-returned .home-stat-value,
        .home-stat-card-returned .home-stat-label {
            color: var(--stat-green-text) !important;
        }
        .home-stat-icon {
            width: 1.75rem;
            height: 1.75rem;
            flex: 0 0 1.75rem;
            background-color: var(--stat-green);
            border: 1px solid var(--stat-green-border);
            color: var(--stat-green-text);
            font-size: .75rem;
        }
        .home-report-found {
            background-color: var(--white);
            border: 1px solid var(--found-border);
            color: var(--ink);
        }
        .home-report-found:hover,
        .home-report-found:focus {
            background-color: var(--badge-bg);
            border-color: var(--found-border);
            color: var(--ink);
        }
        .home-item-card {
            border-color: var(--border) !important;
            background-color: var(--white);
        }
        .home-item-category {
            background-color: var(--badge-bg);
            color: var(--item-text);
        }
        .item-category {
            background-color: var(--badge-bg);
            color: var(--item-text);
        }
        .home-item-status-open {
            background-color: var(--open-bg);
            color: #14683A;
        }
        .item-status-open {
            background-color: var(--open-bg);
            color: #14683A;
        }
        .home-item-status-closed {
            background-color: var(--closed-bg);
            color: var(--muted);
        }
        .item-status-closed {
            background-color: var(--closed-bg);
            color: var(--muted);
        }
        .home-item-status-pending {
            background-color: var(--badge-bg);
            color: var(--muted);
        }
        .item-status-pending {
            background-color: var(--badge-bg);
            color: var(--muted);
        }
        .home-item-location {
            color: var(--item-text);
        }
        .item-location {
            color: var(--item-text);
        }
        .home-item-date {
            color: var(--date-text);
        }
        .item-date {
            color: var(--date-text);
        }
        .home-item-description {
            color: var(--muted);
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $root_prefix; ?>index.php">
            <i class="fa-solid fa-box-open me-2"></i>Community Lost &amp; Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo $root_prefix; ?>index.php"><i class="fa-solid fa-home me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $root_prefix; ?>lost_items.php"><i class="fa-solid fa-search me-1"></i> Lost Items</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $root_prefix; ?>found_items.php"><i class="fa-solid fa-bullhorn me-1"></i> Found Items</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root_prefix; ?>my_items.php"><i class="fa-solid fa-folder-open me-1"></i> My Posts &amp; Claims</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdminOrStaff()): ?>
                        <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="<?php echo $root_prefix; ?>admin/dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $root_prefix; ?>logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $root_prefix; ?>login.php"><i class="fa-solid fa-sign-in-alt me-1"></i> Login</a></li>
                    <li class="nav-item"><a class="btn btn-warning ms-2" href="<?php echo $root_prefix; ?>register.php"><i class="fa-solid fa-user-plus me-1"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
