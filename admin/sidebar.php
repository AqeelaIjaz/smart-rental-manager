<?php
/*
============================================
SIDEBAR - Included in every admin page
Usage: set $active = "dashboard"; then include 'sidebar.php';
============================================
*/
?>
<style>
  :root{ --primary:#1F5A52; --primary-dark:#17423C; --gold:#C99A3A; --warn:#C96A56; --success:#5AA89A; --bg:#F5EFE6; --line:#E7E0D3; --grey:#8A8A8A; }
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{ font-family:'Poppins', sans-serif; background:var(--bg); display:flex; min-height:100vh; }
  .sidebar{ width:220px; background:var(--primary-dark); color:#fff; flex-shrink:0; padding:24px 0; }
  .sidebar .brand{ padding:0 24px 24px; font-weight:700; font-size:16px; border-bottom:1px solid rgba(255,255,255,.1); margin-bottom:16px;}
  .sidebar .brand span{ display:block; font-size:10px; opacity:.6; font-weight:400; margin-top:2px;}
  .nav-item{ padding:12px 24px; font-size:13px; font-weight:600; display:block; color:rgba(255,255,255,.65); text-decoration:none; }
  .nav-item.active{ background:rgba(255,255,255,.08); color:#fff; border-right:3px solid var(--gold); }
  .main{ flex:1; padding:28px 32px; }
  .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;}
  .topbar h1{ font-size:20px; color:var(--primary-dark); }
  .admin-chip{ display:flex; align-items:center; gap:8px; background:#fff; padding:6px 14px 6px 6px; border-radius:20px; border:1px solid var(--line); font-size:12px; font-weight:600; }
  .admin-chip .av{ width:26px; height:26px; border-radius:50%; background:var(--gold); color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700;}
  table{ width:100%; border-collapse:collapse; background:#fff; border-radius:14px; overflow:hidden; border:1px solid var(--line); }
  thead tr{ background:var(--primary); }
  th{ text-align:left; padding:12px 16px; color:#fff; font-size:11.5px; }
  td{ padding:12px 16px; font-size:12.5px; border-top:1px solid var(--line); }
  .badge{ font-size:10px; font-weight:700; padding:4px 10px; border-radius:14px; }
  .badge.pending{ background:#FBF0DA; color:#8a6a1f;}
  .badge.verified{ background:#E5F1EE; color:#2f6b62;}
  .badge.high{ background:#FBE4E0; color:var(--warn);}
  .badge.medium{ background:#FBF0DA; color:#8a6a1f;}
  .badge.low{ background:#E5F1EE; color:#2f6b62;}
  .btn-sm{ font-size:11px; font-weight:700; padding:6px 14px; border-radius:8px; border:none; margin-right:6px; cursor:pointer; }
  .btn-sm.approve{ background:var(--primary); color:#fff; }
  .btn-sm.reject{ background:#fff; border:1.5px solid var(--warn); color:var(--warn); }
  .stat-row{ display:flex; gap:16px; margin-bottom:24px; }
  .stat{ flex:1; background:#fff; border-radius:14px; padding:18px 20px; border:1px solid var(--line); }
  .stat .num{ font-size:24px; font-weight:700; color:var(--primary-dark); }
  .stat .lbl{ font-size:11.5px; color:var(--grey); margin-top:4px; }
</style>

<div class="sidebar">
  <div class="brand">Smart Rental Manager<span>Admin Panel</span></div>
  <a href="dashboard.php" class="nav-item <?php echo ($active=='dashboard')?'active':''; ?>">Dashboard</a>
  <a href="verify_users.php" class="nav-item <?php echo ($active=='verify')?'active':''; ?>">Verify Users</a>
  <a href="disputes.php" class="nav-item <?php echo ($active=='disputes')?'active':''; ?>">Disputes</a>
  <a href="reports.php" class="nav-item <?php echo ($active=='reports')?'active':''; ?>">Reports</a>
  <a href="logout_confirm.php" class="nav-item">Logout</a>
</div>
