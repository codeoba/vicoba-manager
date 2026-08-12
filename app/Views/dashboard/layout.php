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
<style>
  body { font-family:'Inter',sans-serif; }
  [x-cloak] { display:none !important; }
  .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-150; }
  .sidebar-link.active { @apply bg-blue-600 text-white shadow-lg shadow-blue-500/30; }
  .stat-card { @apply bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition; }
  .btn { @apply inline-flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200; }
  .btn-primary { @apply btn bg-blue-600 hover:bg-blue-700 text-white shadow; }
  .btn-secondary { @apply btn bg-slate-100 hover:bg-slate-200 text-slate-700; }
  .btn-danger { @apply btn bg-red-600 hover:bg-red-700 text-white; }
  .btn-success { @apply btn bg-emerald-600 hover:bg-emerald-700 text-white; }
  .modal-overlay { @apply fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4; }
  .modal-box { @apply bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto; }
  .form-label { @apply block text-sm font-semibold text-slate-700 mb-1; }
  .form-input { @apply w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition; }
  .badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border; }
  .table-auto th { @apply text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 text-left bg-slate-50 border-b; }
  .table-auto td { @apply py-3 px-4 text-sm border-b border-slate-50; }
  .scrollbar-thin::-webkit-scrollbar { width:5px; height:5px; }
  .scrollbar-thin::-webkit-scrollbar-track { background:transparent; }
  .scrollbar-thin::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:9999px; }
  @media print { .no-print { display:none!important; } body { background:white; } }
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
    <div class="w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
      </svg>
    </div>
    <div class="min-w-0">
      <p class="text-white font-bold text-sm leading-tight truncate">VICOBA Manager</p>
      <p class="text-blue-300 text-xs truncate"><?= e($group->name ?? 'Mfumo wa Kikundi') ?></p>
    </div>
  </div>

  <!-- Nav Links -->
  <nav class="flex-1 overflow-y-auto scrollbar-thin px-3 py-4 space-y-0.5">
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
      ['view'=>'reports',      'icon'=>'📊', 'label'=>'Ripoti',           'roles'=>['super_admin','group_admin','treasurer','secretary']],
      ['view'=>'settings',     'icon'=>'⚙️', 'label'=>'Mipangilio',       'roles'=>['super_admin','group_admin']],
      ['view'=>'super-admin',  'icon'=>'🌐', 'label'=>'Super Admin',      'roles'=>['super_admin']],
    ];
    foreach ($links as $l):
      if (!in_array($role, $l['roles'])) continue;
      $isActive = $current_view === $l['view'];
    ?>
    <a href="/dashboard/<?= $l['view'] ?>"
       class="sidebar-link <?= $isActive ? 'active' : '' ?>">
      <span><?= $l['icon'] ?></span>
      <span><?= $l['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- User Info -->
  <div class="border-t border-white/10 px-4 py-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
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
  <header class="sticky top-0 z-20 bg-white border-b border-slate-200 px-4 sm:px-6 py-3 no-print">
    <div class="flex items-center justify-between gap-4">
      <!-- Mobile menu button -->
      <button @click="sidebarOpen=!sidebarOpen" class="md:hidden p-2 rounded-lg hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <!-- Breadcrumb -->
      <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
        <span>Dashboard</span>
        <span>›</span>
        <span class="font-semibold text-slate-800"><?= ucfirst(str_replace('-', ' ', $current_view)) ?></span>
      </div>

      <!-- Right side -->
      <div class="flex items-center gap-3 ml-auto">
        <?php if ($overdue_count > 0 && in_array($role, ['super_admin','group_admin','treasurer'])): ?>
        <a href="/dashboard/loans" class="hidden sm:flex items-center gap-1.5 text-xs bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg font-semibold">
          ⚠️ <?= $overdue_count ?> mkopo umechelewesha
        </a>
        <?php endif; ?>

        <!-- Notifications Bell -->
        <div class="relative" x-data="{open:false}">
          <button @click="open=!open;loadNotifications()" class="relative p-2 rounded-xl hover:bg-slate-100 transition" title="Arifa">
            <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <?php if ($unread_count > 0): ?>
            <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold"><?= min($unread_count, 9) ?></span>
            <?php endif; ?>
          </button>
          <!-- Dropdown -->
          <div x-show="open" @click.outside="open=false" x-transition
               class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden" x-cloak>
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
              <span class="font-bold text-sm text-slate-800">Arifa</span>
              <button @click="markAllRead()" class="text-xs text-blue-600 hover:underline">Soma Zote</button>
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
        <span class="hidden sm:flex items-center gap-1.5 text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1.5 rounded-lg">
          🏛️ <?= e($group->name) ?>
        </span>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Flash Messages -->
  <?php foreach ($flash as $f): ?>
  <div class="mx-4 sm:mx-6 mt-4 px-4 py-3 rounded-xl text-sm font-medium no-print
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
