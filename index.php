<?php
require_once 'db.php';
// Fetch summary statistics for the homepage
$stats = [
    'lost' => $pdo->query("SELECT COUNT(*) FROM lost_items")->fetchColumn(),
    'found' => $pdo->query("SELECT COUNT(*) FROM found_items")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM lost_items WHERE status='Resolved'")->fetchColumn() + 
                  $pdo->query("SELECT COUNT(*) FROM found_items WHERE status='Claimed'")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()
];

// Fetch recent lost items
$recentLost = $pdo->query("SELECT l.*, u.full_name FROM lost_items l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 3")->fetchAll();

// Fetch recent found items
$recentFound = $pdo->query("SELECT f.*, u.full_name FROM found_items f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 3")->fetchAll();

include 'header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap');

:root {
    --blue-slate: #555b6eff;
    --muted-teal: #89b0aeff;
    --frozen-water: #bee3dbff;
    --bright-snow: #faf9f9ff;
    --peach-fuzz: #ffd6baff;
    --ink: #3b3f4d; /* darker shade of blue-slate for body text contrast */
}

body {
    background: var(--bright-snow);
    color: var(--blue-slate);
    font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
}

.lf-wrap { max-width: 1040px; margin: 0 auto; padding: 0 1.25rem; }

/* Hero */
.lf-hero { padding: 5rem 0 3.5rem; }
.lf-hero h1 {
    font-size: clamp(2.25rem, 5vw, 3.5rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.1;
    color: var(--ink);
    margin: 0 0 1rem;
    max-width: 16ch;
}
.lf-hero p {
    font-size: 1.125rem;
    line-height: 1.6;
    max-width: 52ch;
    margin: 0 0 2rem;
}
.lf-actions { display: flex; flex-wrap: wrap; gap: .75rem; }

/* Buttons */
.lf-btn {
    display: inline-block;
    padding: .7rem 1.4rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: .95rem;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: background-color .15s, color .15s, border-color .15s;
}
.lf-btn:focus-visible { outline: 3px solid var(--muted-teal); outline-offset: 2px; }
.lf-btn-primary { background: var(--blue-slate); color: var(--bright-snow); }
.lf-btn-primary:hover { background: var(--ink); color: var(--bright-snow); }
.lf-btn-accent { background: var(--peach-fuzz); color: var(--ink); }
.lf-btn-accent:hover { background: #ffc7a0; color: var(--ink); }
.lf-btn-ghost { border-color: var(--muted-teal); color: var(--blue-slate); background: transparent; }
.lf-btn-ghost:hover { background: var(--frozen-water); color: var(--ink); }

/* Stats */
.lf-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: var(--frozen-water);
    border-radius: 16px;
    padding: 1.5rem 0;
    margin-bottom: 4rem;
}
.lf-stat { padding: 0 1.5rem; border-left: 1px solid var(--muted-teal); }
.lf-stat:first-child { border-left: 0; }
.lf-stat strong { display: block; font-size: 1.9rem; font-weight: 700; color: var(--ink); line-height: 1.2; }
.lf-stat span { font-size: .9rem; }

/* Sections */
.lf-section { margin-bottom: 4rem; }
.lf-section-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.lf-section-head h2 { font-size: 1.4rem; font-weight: 700; color: var(--ink); margin: 0; }
.lf-link { color: var(--blue-slate); font-weight: 600; font-size: .95rem; text-decoration: underline; text-decoration-color: var(--muted-teal); text-underline-offset: 4px; }
.lf-link:hover { color: var(--ink); text-decoration-color: var(--peach-fuzz); }

.lf-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }

/* Item cards */
.lf-item {
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid var(--frozen-water);
    border-radius: 14px;
    padding: 1.4rem;
}
.lf-tags { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .9rem; }
.lf-tag {
    font-size: .8rem;
    font-weight: 600;
    padding: .2rem .65rem;
    border-radius: 999px;
    background: var(--frozen-water);
    color: var(--ink);
}
.lf-tag-status { background: var(--peach-fuzz); }
.lf-item h3 { font-size: 1.1rem; font-weight: 700; color: var(--ink); margin: 0 0 .6rem; }
.lf-meta { font-size: .875rem; margin: 0 0 .25rem; }
.lf-meta i { color: var(--muted-teal); width: 1.1rem; }
.lf-desc {
    margin: .9rem 0 1.25rem;
    font-size: .95rem;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.lf-item .lf-btn { margin-top: auto; text-align: center; }

.lf-empty { padding: 1.5rem; border: 1px dashed var(--muted-teal); border-radius: 14px; }

@media (max-width: 860px) {
    .lf-grid { grid-template-columns: 1fr; }
    .lf-stats { grid-template-columns: repeat(2, 1fr); row-gap: 1.25rem; }
    .lf-stat:nth-child(3) { border-left: 0; }
    .lf-hero { padding: 3rem 0 2.5rem; }
}
</style>

<!-- Hero -->
<header class="lf-hero">
    <div class="lf-wrap">
        <h1>Community Lost &amp; Found</h1>
        <p>Lost something in the community, or found something that isn't yours? Post it here and help it get back to its owner.</p>
        <div class="lf-actions">
            <?php if (isLoggedIn()): ?>
                <a href="post_lost.php" class="lf-btn lf-btn-primary">Post lost item</a>
                <a href="post_found.php" class="lf-btn lf-btn-accent">Post found item</a>
            <?php else: ?>
                <a href="login.php" class="lf-btn lf-btn-primary">Log in to post</a>
                <a href="register.php" class="lf-btn lf-btn-accent">Create account</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="lf-wrap">
    <!-- Statistics -->
    <section class="lf-stats" aria-label="Community statistics">
        <div class="lf-stat"><strong><?php echo $stats['lost']; ?></strong><span>Lost items</span></div>
        <div class="lf-stat"><strong><?php echo $stats['found']; ?></strong><span>Found items</span></div>
        <div class="lf-stat"><strong><?php echo $stats['resolved']; ?></strong><span>Returned to owners</span></div>
        <div class="lf-stat"><strong><?php echo $stats['users']; ?></strong><span>Members</span></div>
    </section>

    <!-- Recent Lost Items -->
    <section class="lf-section">
        <div class="lf-section-head">
            <h2>Recently lost</h2>
            <a href="lost_items.php" class="lf-link">View all lost items</a>
        </div>
        <?php if (empty($recentLost)): ?>
            <p class="lf-empty">No lost items reported yet. If you've lost something, post it so others can help.</p>
        <?php else: ?>
            <div class="lf-grid">
                <?php foreach ($recentLost as $item): ?>
                    <article class="lf-item">
                        <div class="lf-tags">
                            <span class="lf-tag"><?php echo htmlspecialchars($item['category']); ?></span>
                            <span class="lf-tag lf-tag-status"><?php echo htmlspecialchars($item['status']); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="lf-meta"><i class="fa-solid fa-map-marker-alt"></i><?php echo htmlspecialchars($item['location_lost']); ?></p>
                        <p class="lf-meta"><i class="fa-solid fa-calendar"></i>Lost on <?php echo htmlspecialchars($item['date_lost']); ?></p>
                        <p class="lf-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                        <a href="lost_detail.php?id=<?php echo (int)$item['id']; ?>" class="lf-btn lf-btn-ghost">View details</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Recent Found Items -->
    <section class="lf-section">
        <div class="lf-section-head">
            <h2>Recently found</h2>
            <a href="found_items.php" class="lf-link">View all found items</a>
        </div>
        <?php if (empty($recentFound)): ?>
            <p class="lf-empty">No found items reported yet. If you've found something, post it so the owner can claim it.</p>
        <?php else: ?>
            <div class="lf-grid">
                <?php foreach ($recentFound as $item): ?>
                    <article class="lf-item">
                        <div class="lf-tags">
                            <span class="lf-tag"><?php echo htmlspecialchars($item['category']); ?></span>
                            <span class="lf-tag lf-tag-status"><?php echo htmlspecialchars($item['status']); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="lf-meta"><i class="fa-solid fa-map-marker-alt"></i><?php echo htmlspecialchars($item['location_found']); ?></p>
                        <p class="lf-meta"><i class="fa-solid fa-calendar"></i>Found on <?php echo htmlspecialchars($item['date_found']); ?></p>
                        <p class="lf-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                        <a href="found_detail.php?id=<?php echo (int)$item['id']; ?>" class="lf-btn lf-btn-ghost">View details &amp; claim</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>