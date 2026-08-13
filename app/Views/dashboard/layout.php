<!DOCTYPE html>
<html lang="sw" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Dashboard — VICOBA Manager Pro') ?></title>
<meta name="description" content="VICOBA Manager Pro Dashboard — Mfumo wa Enterprise wa Vikundi">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php include VIEW_PATH . '/dashboard/tanzania_data.php'; ?>
<style>
  :root {
    --bg-base: #070e1c;
    --bg-surface: #0d1930;
    --bg-elevated: #101d34;
    --bg-card: rgba(255,255,255,0.04);
    --border: rgba(255,255,255,0.07);
    --border-strong: rgba(255,255,255,0.12);
    --accent: #2563eb;
    --accent-2: #7c3aed;
    --text-primary: #f1f5f9;
    --text-secondary: rgba(241,245,249,0.55);
    --text-muted: rgba(241,245,249,0.3);
    --sidebar-w: 260px;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }
  body {
    font-family: 'Outfit', sans-serif;
    background: var(--bg-base);
    color: var(--text-primary);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
  }
  [x-cloak] { display: none !important; }

  /* SCROLLBAR */
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 9999px; }
  ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

  /* ══ SIDEBAR ══ */
  #sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    height: 100vh;
    max-height: 100vh;
    width: var(--sidebar-w);
    z-index: 40;
    display: flex;
    flex-direction: column;
    background: linear-gradient(180deg, #0a1628 0%, #080f1e 100%);
    border-right: 1px solid var(--border);
    transition: transform .25s cubic-bezier(.4,0,.2,1);
  }
  #sidebar.closed { transform: translateX(-100%); }
  .sidebar-logo {
    display: flex;
    align-items: center;
    gap: .875rem;
    padding: 1.25rem 1.25rem 1rem;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
  }
  .sidebar-logo-icon {
    width: 42px; height: 42px;
    border-radius: 13px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    box-shadow: 0 0 20px rgba(37,99,235,.35);
    flex-shrink: 0;
  }
  .sidebar-brand { font-size: 1rem; font-weight: 900; color: #fff; letter-spacing: -.01em; line-height: 1.2; }
  .sidebar-group { font-size: .68rem; color: rgba(255,255,255,.4); margin-top: .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  .sidebar-section {
    font-size: .6rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: rgba(255,255,255,.3);
    padding: 1rem 1.25rem .35rem;
    flex-shrink: 0;
  }
  .sidebar-nav {
    flex: 1 1 0%;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding: .5rem .75rem 1.5rem;
    -webkit-overflow-scrolling: touch;
  }

  .nav-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .625rem .875rem;
    border-radius: .875rem;
    font-size: .83rem; font-weight: 500;
    color: rgba(255,255,255,.6);
    text-decoration: none;
    transition: all .18s;
    margin-bottom: .2rem;
    white-space: nowrap; overflow: hidden;
  }
  .nav-item:hover { color: #fff; background: rgba(255,255,255,.08); }
  .nav-item.active {
    background: linear-gradient(135deg, rgba(37,99,235,.35), rgba(124,58,237,.25));
    color: #fff;
    font-weight: 700;
    border: 1px solid rgba(37,99,235,.4);
    box-shadow: 0 4px 20px rgba(37,99,235,.2);
  }
  .nav-item .nav-icon { font-size: 1.05rem; flex-shrink: 0; }

  .sidebar-user {
    border-top: 1px solid var(--border);
    padding: 1rem .875rem;
    flex-shrink: 0;
    background: #080f1e;
  }
  .user-card {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem;
    border-radius: 1rem;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border);
    margin-bottom: .625rem;
  }
  .user-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: .875rem; font-weight: 800; color: #fff; flex-shrink: 0;
  }
  .user-name { font-size: .83rem; font-weight: 700; color: #fff; }
  .user-role { font-size: .65rem; color: rgba(255,255,255,.4); }
  .logout-btn {
    display: flex; align-items: center; gap: .5rem;
    width: 100%;
    padding: .5rem .75rem;
    border-radius: .75rem;
    background: none; border: none;
    font-size: .78rem; font-weight: 600;
    color: rgba(255,255,255,.35);
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
    text-decoration: none;
    transition: .2s;
  }
  .logout-btn:hover { color: #f87171; background: rgba(239,68,68,.08); }

  /* ══ TOP BAR ══ */
  #topbar {
    position: sticky; top: 0; z-index: 30;
    height: 60px;
    display: flex; align-items: center; gap: 1rem;
    padding: 0 1.5rem;
    background: rgba(7,14,28,.85);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
  }
  .topbar-page-title { font-size: 1rem; font-weight: 800; color: #fff; }
  .topbar-breadcrumb { font-size: .72rem; color: var(--text-muted); }
  .tb-btn {
    display: flex; align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: .75rem;
    background: var(--bg-card);
    border: 1px solid var(--border);
    cursor: pointer; transition: .2s;
    flex-shrink: 0;
    font-size: 1rem;
    color: var(--text-secondary);
    text-decoration: none;
  }
  .tb-btn:hover { background: rgba(255,255,255,.09); color: #fff; }
  .tb-badge {
    position: relative;
  }
  .tb-badge::after {
    content: '';
    position: absolute;
    top: 7px; right: 7px;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #ef4444;
    border: 1.5px solid var(--bg-base);
  }

  /* ══ MAIN ══ */
  #main-wrap {
    margin-left: var(--sidebar-w);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    transition: margin-left .25s cubic-bezier(.4,0,.2,1);
  }
  #main-wrap.full { margin-left: 0; }
  .main-content { flex: 1; padding: 1.5rem; }

  /* ══ CARDS / COMPONENTS ══ */
  .card {
    background: #0d1930;
    border: 1px solid var(--border);
    border-radius: 1.25rem;
  }
  .card-p { padding: 1.5rem; }
  .card-title { font-size: .9rem; font-weight: 800; color: #fff; }
  .card-sub { font-size: .72rem; color: var(--text-muted); margin-top: .15rem; }

  /* ══ STAT CARD ══ */
  .stat-card {
    background: #0d1930;
    border: 1px solid var(--border);
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
    position: relative;
    overflow: hidden;
    transition: all .25s;
  }
  .stat-card:hover { border-color: var(--border-strong); transform: translateY(-2px); box-shadow: 0 12px 40px rgba(0,0,0,.25); }
  .stat-card .stat-value { font-size: 1.6rem; font-weight: 900; color: #fff; letter-spacing: -.02em; margin-top: .25rem; }
  .stat-card .stat-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); }
  .stat-card .stat-change { font-size: .72rem; font-weight: 700; margin-top: .35rem; display: flex; align-items: center; gap: .25rem; }
  .stat-card .stat-icon-wrap { position: absolute; top: 1.25rem; right: 1.25rem; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; }

  /* ══ FORM ELEMENTS ══ */
  .form-label { display: block; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); margin-bottom: .5rem; }
  .form-input {
    width: 100%;
    padding: .75rem 1rem;
    background: rgba(255,255,255,.05);
    border: 1.5px solid rgba(255,255,255,.09);
    border-radius: .875rem;
    color: #fff;
    font-size: .875rem;
    font-family: 'Outfit', sans-serif;
    outline: none;
    transition: all .2s;
    appearance: none;
  }
  .form-input::placeholder { color: rgba(255,255,255,.2); }
  .form-input:focus { border-color: rgba(37,99,235,.5); background: rgba(37,99,235,.07); box-shadow: 0 0 0 4px rgba(37,99,235,.1); }
  select.form-input {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 2.75rem;
  }
  select.form-input option { background: #0f172a; color: #f1f5f9; }
  .form-input:disabled { opacity: .4; cursor: not-allowed; }

  /* ══ BUTTONS ══ */
  .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: .875rem; font-size: .83rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: all .2s; border: none; font-family: 'Outfit', sans-serif; }
  .btn:active { transform: scale(.97); }
  .btn-primary { background: linear-gradient(135deg, #2563eb, #7c3aed); color: #fff; box-shadow: 0 0 20px rgba(37,99,235,.25); }
  .btn-primary:hover { box-shadow: 0 8px 30px rgba(37,99,235,.4); transform: translateY(-1px); }
  .btn-secondary { background: rgba(255,255,255,.06); color: var(--text-primary); border: 1px solid var(--border); }
  .btn-secondary:hover { background: rgba(255,255,255,.1); border-color: var(--border-strong); }
  .btn-danger { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; box-shadow: 0 0 20px rgba(220,38,38,.2); }
  .btn-danger:hover { box-shadow: 0 8px 30px rgba(220,38,38,.35); }
  .btn-success { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; box-shadow: 0 0 20px rgba(22,163,74,.2); }
  .btn-success:hover { box-shadow: 0 8px 30px rgba(22,163,74,.35); }
  .btn-sm { padding: .4rem .875rem; font-size: .75rem; border-radius: .625rem; }

  /* ══ MODAL ══ */
  .modal-overlay { position: fixed; inset: 0; background: rgba(4,9,20,.75); backdrop-filter: blur(12px); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 1rem; }
  .modal-box { background: #0d1930; border: 1px solid rgba(255,255,255,.1); border-radius: 1.5rem; box-shadow: 0 30px 60px rgba(0,0,0,.6); width: 100%; max-width: 32rem; max-height: 90vh; overflow-y: auto; }
  .modal-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,.07); }
  .modal-body { padding: 1.5rem; }
  .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,.07); display: flex; justify-content: flex-end; gap: .75rem; }
  .modal-title { font-size: 1.1rem; font-weight: 800; color: #fff; }

  /* ══ TABLE ══ */
  .data-table { width: 100%; border-collapse: collapse; }
  .data-table th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); padding: .875rem 1rem; text-align: left; border-bottom: 1px solid var(--border); background: rgba(255,255,255,.02); }
  .data-table td { padding: .875rem 1rem; font-size: .83rem; border-bottom: 1px solid rgba(255,255,255,.04); color: var(--text-primary); }
  .data-table tr:last-child td { border-bottom: none; }
  .data-table tr:hover td { background: rgba(255,255,255,.025); }

  /* ══ BADGE ══ */
  .badge { display: inline-flex; align-items: center; padding: .25rem .625rem; border-radius: 9999px; font-size: .65rem; font-weight: 700; border-width: 1px; }
  .badge-success { background: rgba(34,197,94,.12); color: #4ade80; border-color: rgba(34,197,94,.25); }
  .badge-danger  { background: rgba(239,68,68,.12); color: #f87171; border-color: rgba(239,68,68,.25); }
  .badge-warning { background: rgba(251,191,36,.12); color: #fbbf24; border-color: rgba(251,191,36,.25); }
  .badge-info    { background: rgba(37,99,235,.12); color: #60a5fa; border-color: rgba(37,99,235,.25); }
  .badge-purple  { background: rgba(124,58,237,.12); color: #a78bfa; border-color: rgba(124,58,237,.25); }
  .badge-muted   { background: rgba(255,255,255,.06); color: rgba(255,255,255,.45); border-color: rgba(255,255,255,.1); }

  /* ══ MOBILE OVERLAY ══ */
  #sidebar-overlay { display: none !important; }
  @media (max-width: 768px) {
    #sidebar-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 35; backdrop-filter: blur(4px);
    }
    #sidebar-overlay.visible { display: block !important; }
  }

  /* ══ PRINT ══ */
  @media print { .no-print { display: none !important; } body { background: white; color: black; } }

  /* ══ RESPONSIVE ══ */
  @media (max-width: 768px) {
    #sidebar { transform: translateX(-100%); }
    #sidebar.open { transform: translateX(0); }
    #main-wrap { margin-left: 0 !important; }
  }
</style>
</head>
<body x-data="vicoba()" x-init="init()" class="h-full">

<!-- SIDEBAR OVERLAY (mobile) -->
<div id="sidebar-overlay" @click="closeSidebar()" :class="{'visible': sidebarOpen}"></div>

<!-- ══ SIDEBAR ══ -->
<aside id="sidebar" :class="{'open': sidebarOpen}">

  <!-- Logo -->
  <div class="sidebar-logo">
    <div class="sidebar-logo-icon">🏛️</div>
    <div style="min-width:0">
      <div class="sidebar-brand">VICOBA <span style="color:#60a5fa">PRO</span></div>
      <div class="sidebar-group"><?= e($group->name ?? 'Mfumo wa Kikundi') ?></div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="sidebar-nav">
    <div class="sidebar-section">Dashibodi</div>
    <?php
    $role  = $user->role;
    $links = [
      ['view'=>'overview',     'icon'=>'🏠', 'label'=>'Muhtasari',             'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'members',      'icon'=>'👥', 'label'=>'Wanachama',             'roles'=>['super_admin','group_admin','secretary','treasurer']],
      ['view'=>'shares',       'icon'=>'💰', 'label'=>'Hisa',                  'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'loans',        'icon'=>'🏦', 'label'=>'Mikopo',                'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'fines',        'icon'=>'⚠️',  'label'=>'Faini',                 'roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'meetings',     'icon'=>'📅', 'label'=>'Mikutano',              'roles'=>['super_admin','group_admin','secretary','treasurer']],
      ['view'=>'social-fund',  'icon'=>'❤️',  'label'=>'Mfuko wa Jamii',        'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'shareout',     'icon'=>'🎯', 'label'=>'Mgawanyo (Shareout)',   'roles'=>['super_admin','group_admin','treasurer']],
      ['view'=>'ledger',       'icon'=>'📒', 'label'=>'Daftari la Fedha',      'roles'=>['super_admin','group_admin','treasurer']],
      ['view'=>'accounting',   'icon'=>'📜', 'label'=>'Uhasibu & Statements',  'roles'=>['super_admin','group_admin','treasurer']],
      ['view'=>'collateral',   'icon'=>'📂', 'label'=>'Dhamana & Nyaraka',     'roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'reports',      'icon'=>'📊', 'label'=>'Ripoti PDF',            'roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'settings',     'icon'=>'⚙️',  'label'=>'Mipangilio',            'roles'=>['super_admin','group_admin']],
      ['view'=>'super-admin',  'icon'=>'🌐', 'label'=>'Super Admin',           'roles'=>['super_admin']],
    ];
    $prev_section = null;
    foreach ($links as $l):
      if (!in_array($role, $l['roles'])) continue;
      $isActive = $current_view === $l['view'];
      // Add section break before admin tools
      if (in_array($l['view'],['reports','settings','super-admin']) && $prev_section !== 'tools'):
        $prev_section = 'tools';
        echo '<div class="sidebar-section" style="margin-top:.75rem">Zana</div>';
      elseif($prev_section === null):
        $prev_section = 'main';
      endif;
    ?>
    <a href="/dashboard/<?= $l['view'] ?>"
       class="nav-item <?= $isActive ? 'active' : '' ?>">
      <span class="nav-icon"><?= $l['icon'] ?></span>
      <span><?= $l['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- User info -->
  <div class="sidebar-user no-print">
    <div class="user-card">
      <div class="user-avatar"><?= strtoupper(substr($user->display_name ?? 'U', 0, 2)) ?></div>
      <div style="min-width:0;flex:1">
        <div class="user-name"><?= e($user->display_name ?? '') ?></div>
        <div class="user-role"><?= role_label($user->role) ?></div>
      </div>
    </div>
    <a href="/logout" class="logout-btn">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Toka kwenye Akaunti
    </a>
  </div>
</aside>

<!-- ══ MAIN WRAPPER ══ -->
<div id="main-wrap" :class="{'full': !sidebarOpen && window.innerWidth < 768}">

  <!-- TOP BAR -->
  <header id="topbar" class="no-print">
    <!-- Mobile menu toggle -->
    <button @click="toggleSidebar()" class="tb-btn md:hidden" style="font-size:.875rem">☰</button>

    <!-- Page breadcrumb -->
    <div style="flex:1;min-width:0">
      <div class="topbar-page-title"><?= e($page_title ?? ucfirst(str_replace('-',' ',$current_view))) ?></div>
      <div class="topbar-breadcrumb">Dashboard › <?= e(ucfirst(str_replace('-',' ',$current_view))) ?></div>
    </div>

    <!-- Right actions -->
    <div style="display:flex;align-items:center;gap:.625rem">

      <?php if ($overdue_count > 0 && in_array($role,['super_admin','group_admin','treasurer'])): ?>
      <a href="/dashboard/loans" class="tb-btn tb-badge" title="<?= $overdue_count ?> Mikopo Imechelewa">⚠️</a>
      <?php endif; ?>

      <?php if ($unread_count > 0): ?>
      <a href="#" class="tb-btn tb-badge" title="Arifa Mpya">🔔</a>
      <?php else: ?>
      <a href="#" class="tb-btn" title="Arifa">🔔</a>
      <?php endif; ?>

      <a href="/dashboard/settings" class="tb-btn" title="Mipangilio">⚙️</a>

      <div style="width:1px;height:24px;background:var(--border);margin:0 .25rem"></div>

      <div style="display:flex;align-items:center;gap:.625rem">
        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;color:#fff">
          <?= strtoupper(substr($user->display_name ?? 'U', 0, 2)) ?>
        </div>
        <div class="hidden sm:block">
          <div style="font-size:.78rem;font-weight:700;color:#fff"><?= e($user->display_name ?? '') ?></div>
          <div style="font-size:.63rem;color:var(--text-muted)"><?= role_label($user->role) ?></div>
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <?php
    $view_file = VIEW_PATH . '/dashboard/' . str_replace('-', '_', $current_view) . '.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        $view_file2 = VIEW_PATH . '/dashboard/' . $current_view . '.php';
        if (file_exists($view_file2)) include $view_file2;
    }
    ?>
  </main>

  <!-- FOOTER -->
  <footer class="no-print" style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;font-size:.68rem;color:var(--text-muted)">
    <span>© <?= date('Y') ?> VICOBA Manager Pro v3.5 Enterprise</span>
    <span style="display:flex;align-items:center;gap:.375rem">
      <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px rgba(34,197,94,.5)"></span>
      Mfumo Unafanya Kazi Vizuri
    </span>
  </footer>
</div>

<script>
function vicoba() {
  return {
    sidebarOpen: window.innerWidth >= 768,
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      if (this.sidebarOpen) {
        sidebar.classList.add('open');
        if (window.innerWidth < 768) overlay.classList.add('visible');
      } else {
        sidebar.classList.remove('open');
        overlay.classList.remove('visible');
      }
    },
    closeSidebar() {
      if (window.innerWidth < 768) {
        this.sidebarOpen = false;
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('visible');
      }
    },
    init() {
      // Handle resize
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
          document.getElementById('sidebar').classList.remove('open');
          document.getElementById('sidebar-overlay').classList.remove('visible');
        }
      });
    }
  }
}

// Global chart defaults — dark theme
if (typeof Chart !== 'undefined') {
  Chart.defaults.color = 'rgba(241,245,249,0.45)';
  Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
  Chart.defaults.font.family = "'Outfit', sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.plugins.legend.labels.boxWidth = 10;
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
  Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(13,25,48,0.95)';
  Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.1)';
  Chart.defaults.plugins.tooltip.borderWidth = 1;
  Chart.defaults.plugins.tooltip.padding = 12;
  Chart.defaults.plugins.tooltip.cornerRadius = 10;
  Chart.defaults.plugins.tooltip.titleColor = '#fff';
  Chart.defaults.plugins.tooltip.bodyColor = 'rgba(241,245,249,0.7)';
  Chart.defaults.scale.grid.color = 'rgba(255,255,255,0.05)';
  Chart.defaults.scale.ticks.color = 'rgba(241,245,249,0.4)';
}
</script>
</body>
</html>
