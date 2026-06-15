<?php
// =============================================================
// iSkout Admin — reports.php
// Admin panel queue for reviewing student reports.
// =============================================================

require_once '../includes/db.php';
require_once '../includes/admin_guard.php';

$sql = "SELECT r.id as report_id, r.reason, r.details, r.created_at,
               l.id as listing_id, l.name as listing_name,
               u.webmail as reporter_email
        FROM reports r
        JOIN listings l ON r.listing_id = l.id
        JOIN users u ON r.reported_by_user_id = u.id
        WHERE r.status = 'pending'
        ORDER BY r.created_at ASC";

$res = $conn->query($sql);
$reports = [];
while ($row = $res->fetch_assoc()) {
    $reports[] = $row;
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports Queue - iSkout Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .flash-alert { padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px; }
    .flash-success { background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; }
    .flash-error   { background:#fff0f0; border:1px solid #f5caca; color:#d32f2f; }
    
    .report-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02); margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-start; border-left:4px solid var(--maroon-main); }
    .report-main { flex-grow:1; }
    .report-meta { font-size:12px; color:var(--text-muted); margin-bottom:8px; }
    .report-reason { font-size:16px; font-weight:700; color:var(--text-dark); margin:0 0 8px 0; }
    .report-details { font-size:14px; color:#444; line-height:1.5; margin:0; background:#f4f5f7; padding:12px; border-radius:8px; }
    .report-actions { display:flex; gap:12px; margin-left:24px; }
    .btn-outline { border:1px solid #e2e2e7; background:transparent; padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:600; color:var(--text-dark); font-size:13px; }
    .btn-outline:hover { background:#f4f5f7; }
    .btn-red { background:var(--maroon-main); color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; }
  </style>
</head>
<body>

  <div class="desktop-layout active">
    
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <h1 class="brand-logo">iSkout</h1>
      </div>
      
      <div class="menu-section">
        <h3 class="menu-title">MANAGEMENT</h3>
        <nav class="sidebar-menu">
          <a href="dashboard.php" class="menu-item">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
          </a>
          <a href="add_listing.php" class="menu-item">
            <i class="fa-solid fa-plus"></i> Add Listing
          </a>
          <a href="reports.php" class="menu-item active content-queue">
            <i class="fa-solid fa-flag"></i> Reports Queue
          </a>
        </nav>
      </div>
      
      <div class="sidebar-footer">
        <button class="btn-logout" onclick="window.location.href='../actions/logout.php'">
          <span>Logout</span> <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </button>
      </div>
    </aside>

    <main class="admin-main-content">
      <header class="admin-header">
        <h2 class="page-title">Reports Queue</h2>
        <p class="page-subtitle">Review user-submitted issues for active listings</p>
      </header>

      <?php if ($flash): ?>
        <div class="flash-alert flash-<?= $flash['type'] ?>">
          <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <?php if (empty($reports)): ?>
        <div style="padding:40px; text-align:center; background:#fff; border-radius:16px; color:var(--text-muted); font-size:16px; font-weight:600">
          <i class="fas fa-check-circle fa-3x" style="color:#2e7d32; margin-bottom:16px"></i><br>
          Queue is clear. No pending reports.
        </div>
      <?php else: ?>
        <?php foreach ($reports as $r): ?>
          <div class="report-card">
            <div class="report-main">
              <div class="report-meta">
                Reported on <?= date('M j, Y g:i A', strtotime($r['created_at'])) ?> by <?= htmlspecialchars($r['reporter_email']) ?>
              </div>
              <h4 class="report-reason">Target: <?= htmlspecialchars($r['listing_name']) ?> — <?= htmlspecialchars($r['reason']) ?></h4>
              <?php if ($r['details']): ?>
                <p class="report-details">"<?= nl2br(htmlspecialchars($r['details'])) ?>"</p>
              <?php else: ?>
                <p class="report-details" style="color:#aaa; font-style:italic">No additional details provided.</p>
              <?php endif; ?>
            </div>
            <div class="report-actions">
              <button class="btn-outline" onclick="window.open('../listing.php?id=<?= $r['listing_id'] ?>', '_blank')">View</button>
              <button class="btn-outline" onclick="window.location.href='edit_listing.php?id=<?= $r['listing_id'] ?>'">Edit Listing</button>
              <form method="POST" action="../actions/admin/dismiss_report.php" style="margin:0">
                <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                <button type="submit" class="btn-red" onclick="return confirm('Dismiss this report?');">Dismiss</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </main>
  </div>
</body>
</html>
