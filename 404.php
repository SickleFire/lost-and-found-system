<?php
http_response_code(404);
/**
 * Custom 404 Not Found Page
 */
require_once 'db.php';
include 'header.php';
?>

<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm border-0 p-5 rounded-4 bg-white">
                <div class="mb-4">
                    <span class="display-1 fw-bold text-muted" style="color: var(--blue-slate) !important; opacity: 0.8;">404</span>
                </div>
                <h1 class="h3 fw-bold mb-3" style="color: var(--blue-slate);">Page Not Found</h1>
                <p class="text-muted mb-4">
                    Oops! The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fa-solid fa-house me-2"></i> Go to Home
                    </a>
                    <a href="lost_items.php" class="btn btn-success">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Browse Lost Items
                    </a>
                    <a href="found_items.php" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-box-open me-2"></i> Browse Found Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'footer.php'; 
?>