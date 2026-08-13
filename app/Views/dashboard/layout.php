<!DOCTYPE html>
<html lang="sw" class="h-full bg-slate-50">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Dashboard — VICOBA Manager') ?></title>
<meta name="description" content="VICOBA Manager Dashboard">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php include VIEW_PATH . '/dashboard/tanzania_data.php'; ?>
<style>
  body { font-family:'Inter',sans-serif; }
  [x-cloak] { display:none !important; }
  .scrollbar-thin::-webkit-scrollbar { width:5px; height:5px; }
  .scrollbar-thin::-webkit-scrollbar-track { background:transparent; }
  .scrollbar-thin::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:9999px; }
  @media print { .no-print { display:none!important; } body { background:white; } }

  /* Premium Global Components */
  .modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .modal-box {
    background-color: #ffffff;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 32rem;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid #f1f5f9;
  }
  .form-label {
    display: block;
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.375rem;
  }
  .form-input {
    width: 100%;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #cbd5e1;
    outline: none;
    font-size: 0.875rem;
    background-color: #ffffff;
    color: #1e293b;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease-in-out;
  }
  .form-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
  }
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
  }
  .btn:active {
    transform: scale(0.98);
  }
  .btn-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  }
  .btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
  }
  .btn-secondary {
    background-color: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
  }
  .btn-secondary:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
  }
  .btn-danger {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
  }
  .btn-danger:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
  }
  .btn-success {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
  }
  .btn-success:hover {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.4);
  }
  .stat-card {
    background-color: #ffffff;
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
  }
  .badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    border-width: 1px;
  }
  .table-auto th {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.875rem 1rem;
    text-align: left;
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
  }
  .table-auto td {
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    border-bottom: 1px solid #f1f5f9;
  }
</style>
</head>
<body class="h-full bg-slate-50" x-data="vicoba()" x-init="init()">

<!-- ═══ SIDEBAR ════════════════════════════════════════════════════════════ -->
<div class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col"
     style="background:linear-gradient(180deg,#1e3a5f 0%,#0f2440 100%)"
     :class="{'translate-x-0':sidebarOpen, '-translate-x-full':!sidebarOpen, 'md:translate-x-0':true}"
     x-cloak>

  <!-- Logo -->
  <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
    <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/30">
      <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
      </svg>
    </div>
    <div class="min-w-0">
      <p class="text-white font-extrabold text-base tracking-tight truncate">VICOBA Manager</p>
      <p class="text-blue-300 text-xs truncate"><?= e($group->name ?? 'Mfumo wa Kikundi') ?></p>
    </div>
  </div>

  <!-- Nav Links -->
  <nav class="flex-1 overflow-y-auto scrollbar-thin px-3 py-4 space-y-1.5 flex flex-col">
    <?php
    $role = $user->role;
    $links = [
      ['view'=>'overview',     'icon'=>'🏠', 'label'=>'Muhtasari',       'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'members',      'icon'=>'👥', 'label'=>'Wanachama',        'roles'=>['super_admin','group_admin','secretary','treasurer']],
      ['view'=>'shares',       'icon'=>'💰', 'label'=>'Hisa',             'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'loans',        'icon'=>'🏦', 'label'=>'Mikopo',           'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'fines',        'icon'=>'⚠️', 'label'=>'Faini',            'roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'meetings',     'icon'=>'📅', 'label'=>'Mikutano',         'roles'=>['super_admin','group_admin','secretary','treasurer']],
      ['view'=>'social-fund',  'icon'=>'❤️', 'label'=>'Mfuko wa Jamii',   'roles'=>['super_admin','group_admin','treasurer','secretary','member']],
      ['view'=>'shareout',     'icon'=>'🎯', 'label'=>'Mgawanyo',         'roles'=>['super_admin','group_admin','treasurer']],
      ['view'=>'ledger',       'icon'=>'📒', 'label'=>'Daftari la Fedha', 'roles'=>['super_admin','group_admin','treasurer']],
      ['view'=>'accounting',   'icon'=>'📜', 'label'=>'Uhasibu & Statements', 'roles'=>['super_admin','group_admin','treasurer']],
      ['view'=>'collateral',   'icon'=>'📂', 'label'=>'Dhamana & Nyaraka','roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'reports',      'icon'=>'📊', 'label'=>'Ripoti',           'roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'settings',     'icon'=>'⚙️', 'label'=>'Mipangilio',       'roles'=>['super_admin','group_admin']],
      ['view'=>'super-admin',  'icon'=>'🌐', 'label'=>'Super Admin',      'roles'=>['super_admin']],
    ];
    foreach ($links as $l):
      if (!in_array($role, $l['roles'])) continue;
      $isActive = $current_view === $l['view'];
    ?>
    <a href="/dashboard/<?= $l['view'] ?>"
       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= $isActive ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
      <span class="text-lg flex-shrink-0"><?= $l['icon'] ?></span>
      <span class="truncate"><?= $l['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- User Info -->
  <div class="border-t border-white/10 px-4 py-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-md">
        <?= strtoupper(substr($user->display_name ?? 'U', 0, 1)) ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-white text-sm font-semibold truncate"><?= e($user->display_name ?? '') ?></p>
        <p class="text-blue-300 text-xs truncate"><?= role_label($user->role) ?></p>
      </div>
    </div>
    <a href="/logout" class="mt-3 w-full flex items-center gap-2 text-slate-400 hover:text-white text-xs font-medium transition">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Toka
    </a>
  </div>
</div>

<!-- ═══ MAIN CONTENT ══════════════════════════════════════════════════════ -->
<div class="md:ml-64 min-h-screen flex flex-col">

  <!-- Top Bar -->
  <header class="sticky top-0 z-20 bg-white border-b border-slate-200 px-4 sm:px-6 py-3.5 no-print shadow-sm">
    <div class="flex items-center justify-between gap-4">
      <!-- Mobile menu button -->
      <button @click="sidebarOpen=!sidebarOpen" class="md:hidden p-2 rounded-xl hover:bg-slate-100 transition">
        <svg class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <!-- Breadcrumb -->
      <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
        <span>Dashboard</span>
        <span>›</span>
        <span class="font-bold text-slate-800"><?= ucfirst(str_replace('-', ' ', $current_view)) ?></span>
      </div>

      <!-- Right side -->
      <div class="flex items-center gap-3 ml-auto">
        <?php if ($overdue_count > 0 && in_array($role, ['super_admin','group_admin','treasurer'])): ?>
        <a href="/dashboard/loans" class="hidden sm:flex items-center gap-1.5 text-xs bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded-xl font-semibold hover:bg-red-100 transition">
          ⚠️ <?= $overdue_count ?> mkopo umechelewesha
        </a>
        <?php endif; ?>

        <!-- Notifications Bell -->
        <div class="relative" x-data="{open:false}">
          <button @click="open=!open;loadNotifications()" class="relative p-2 rounded-xl hover:bg-slate-100 transition" title="Arifa">
            <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <?php if ($unread_count > 0): ?>
            <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold shadow-sm"><?= min($unread_count, 9) ?></span>
            <?php endif; ?>
          </button>
          <!-- Dropdown -->
          <div x-show="open" @click.outside="open=false" x-transition
               class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden" x-cloak>
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <span class="font-bold text-sm text-slate-800">Arifa</span>
              <button @click="markAllRead()" class="text-xs text-blue-600 hover:underline font-medium">Soma Zote</button>
            </div>
            <div class="max-h-72 overflow-y-auto scrollbar-thin" id="notifications-list">
              <template x-if="notifications.length === 0">
                <p class="text-center text-sm text-slate-400 py-8">Hakuna arifa mpya</p>
              </template>
              <template x-for="n in notifications" :key="n.id">
                <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 transition"
                     :class="{'bg-blue-50/50': !n.is_read}"
                     @click="markRead(n.id)">
                  <p class="text-sm font-semibold text-slate-800" x-text="n.title"></p>
                  <p class="text-xs text-slate-500 mt-0.5" x-text="n.message"></p>
                  <p class="text-[11px] text-slate-400 mt-1" x-text="n.created_at"></p>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Group Badge -->
        <?php if ($group): ?>
        <span class="hidden sm:flex items-center gap-1.5 text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1.5 rounded-xl shadow-xs">
          🏛️ <?= e($group->name) ?>
        </span>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Flash Messages -->
  <?php foreach ($flash as $f): ?>
  <div class="mx-4 sm:mx-6 mt-4 px-4 py-3 rounded-xl text-sm font-medium no-print shadow-sm
    <?= $f['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
    <?= e($f['message']) ?>
  </div>
  <?php endforeach; ?>

  <!-- Page Content -->
  <main class="flex-1 p-4 sm:p-6">
    <?php
    $view_file = VIEW_PATH . '/dashboard/' . $current_view . '.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else { ?>
    <div class="text-center py-20 text-slate-400">
      <p class="text-4xl mb-2">🚧</p>
      <p class="font-semibold">Kipengele hiki kinaandaliwa...</p>
    </div>
    <?php } ?>
  </main>
</div>

<!-- ═══ GLOBAL SCRIPTS ════════════════════════════════════════════════════ -->
<script>
const APP = {
  csrf:    '<?= Auth::csrf() ?>',
  role:    '<?= e($user->role) ?>',
  groupId: <?= (int)($group->id ?? 0) ?>,
  userId:  <?= (int)$user->id ?>,
  currency:'<?= e($group->currency ?? 'TZS') ?>',
};

async function api(endpoint, method='GET', body=null) {
  const opts = {
    method, headers: {'Content-Type':'application/json','X-CSRF-Token':APP.csrf,'X-Requested-With':'XMLHttpRequest'}
  };
  if (body) opts.body = JSON.stringify({...body, _csrf: APP.csrf});
  const res = await fetch(endpoint, opts);
  const json = await res.json();
  if (!json.success && json.message) {
    Swal.fire({icon:'error', title:'Hitilafu', text:json.message, confirmButtonColor:'#2563eb'});
    return null;
  }
  return json;
}

async function confirm_action(title, text, btnLabel='Ndio', btnColor='#dc2626') {
  const res = await Swal.fire({ title, text, icon:'warning', showCancelButton:true, confirmButtonColor:btnColor, cancelButtonColor:'#94a3b8', confirmButtonText:btnLabel, cancelButtonText:'Hapana' });
  return res.isConfirmed;
}

function money(n) { return APP.currency + ' ' + Number(n||0).toLocaleString(); }

function vicoba() {
  return {
    sidebarOpen: false,
    notifications: [],
    async init() {
      this.sidebarOpen = window.innerWidth >= 768;
    },
    async loadNotifications() {
      const d = await api('/api/notifications');
      if (d) this.notifications = d.notifications;
    },
    async markRead(id) {
      await api('/api/notifications/mark-read','POST',{notification_id:id});
      this.notifications = this.notifications.map(n => n.id===id?{...n,is_read:1}:n);
    },
    async markAllRead() {
      for (const n of this.notifications.filter(n=>!n.is_read)) await this.markRead(n.id);
    }
  }
}

function showModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function hideModal(id) { document.getElementById(id)?.classList.add('hidden'); }
</script>

</body>
</html>
