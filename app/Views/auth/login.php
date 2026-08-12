<!DOCTYPE html>
<html lang="sw" class="h-full bg-slate-50">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Ingia — VICOBA Manager') ?></title>
<meta name="description" content="VICOBA Manager — Mfumo wa kusimamia vikundi vya akiba na mikopo.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Inter', sans-serif; }
  .gradient-bg { background: linear-gradient(135deg, #1e3a5f 0%, #0f2440 40%, #1a4f6e 100%); }
  .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); }
  .input-field { @apply w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-slate-800 placeholder-slate-400; }
  .btn-primary { @apply inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-blue-500/30; }
</style>
</head>
<body class="h-full gradient-bg flex items-center justify-center p-4 min-h-screen">

<div class="w-full max-w-md">
  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl glass mb-4 shadow-xl">
      <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
      </svg>
    </div>
    <h1 class="text-2xl font-extrabold text-white tracking-tight">VICOBA Manager</h1>
    <p class="text-blue-200 text-sm mt-1">Mfumo wa Kusimamia Vikundi</p>
  </div>

  <!-- Card -->
  <div class="bg-white rounded-2xl shadow-2xl p-8">
    <h2 class="text-xl font-bold text-slate-800 mb-1">Karibu Tena!</h2>
    <p class="text-slate-500 text-sm mb-6">Ingiza taarifa zako za kuingia</p>

    <!-- Flash Messages -->
    <?php foreach ($flash as $f): ?>
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium
      <?= $f['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
      <?= e($f['message']) ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="/login" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jina la Mtumiaji au Barua Pepe</label>
        <input type="text" name="username" id="login-username" required autocomplete="username"
          class="input-field" placeholder="jina.mtumiaji">
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nywila</label>
        <div class="relative">
          <input type="password" name="password" id="login-password" required autocomplete="current-password"
            class="input-field pr-11" placeholder="••••••••">
          <button type="button" onclick="togglePwd('login-password',this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" id="login-submit" class="btn-primary w-full mt-2">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        Ingia
      </button>
    </form>

    <p class="text-center text-sm text-slate-500 mt-6">
      Kikundi kipya?
      <a href="/register" class="text-blue-600 font-semibold hover:underline">Sajili hapa</a>
    </p>
  </div>
</div>

<script>
function togglePwd(id, btn) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
