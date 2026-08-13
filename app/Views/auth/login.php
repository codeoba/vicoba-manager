<!DOCTYPE html>
<html lang="sw" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Ingia — VICOBA Manager Enterprise') ?></title>
<meta name="description" content="VICOBA Manager — Mfumo wa Kusimamia Vikundi vya Akiba na Mikopo.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  body { font-family: 'Outfit', 'Inter', sans-serif; }
  .bg-mesh {
    background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                #0f172a;
  }
  .glass-card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(20px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }
  .form-input-elite {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 2.75rem;
    border-radius: 1rem;
    border: 1.5px solid #e2e8f0;
    outline: none;
    font-size: 0.95rem;
    background-color: #f8fafc;
    color: #0f172a;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .form-input-elite:focus {
    border-color: #2563eb;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
  }
</style>
</head>
<body class="min-h-screen bg-mesh text-slate-100 flex items-center justify-center p-4 sm:p-6 md:p-10" x-data="{showPass: false}">

<div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 rounded-3xl overflow-hidden shadow-2xl border border-white/10">

  <!-- LEFT HERO PANEL (Desktop Branding & Live Metrics) -->
  <div class="lg:col-span-6 bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-950 p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden hidden lg:flex">
    <div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Logo -->
    <div class="relative z-10">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-500/40">
          🏛️
        </div>
        <div>
          <h2 class="text-2xl font-black tracking-wide text-white">VICOBA <span class="text-blue-400">PRO</span></h2>
          <p class="text-xs text-blue-200 uppercase font-bold tracking-widest">Enterprise Microfinance Engine</p>
        </div>
      </div>
    </div>

    <!-- Content Feature Showcase -->
    <div class="relative z-10 my-10 space-y-6">
      <h1 class="text-3xl font-extrabold text-white leading-tight">
        Mfumo Namba #1 wa Kidishtali wa <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Vikundi vya VICOBA</span> Tanzania
      </h1>
      <p class="text-slate-300 text-sm leading-relaxed">
        Simamia Hisa, Mikopo, Faini, Mikutano, na Uhasibu wa Pande Mbili (Double-Entry Accounting) kwa usalama na uwazi wa 100%.
      </p>

      <!-- Metrics Cards -->
      <div class="grid grid-cols-2 gap-4 pt-2">
        <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10">
          <p class="text-2xl font-black text-blue-400">500+</p>
          <p class="text-xs text-slate-300 font-medium">Vikundi Vilivyosajiliwa</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10">
          <p class="text-2xl font-black text-emerald-400">99.9%</p>
          <p class="text-xs text-slate-300 font-medium">Uhakika wa Data & Backup</p>
        </div>
      </div>
    </div>

    <!-- Security Footer -->
    <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-6 text-xs text-slate-400">
      <span class="flex items-center gap-1.5"><span class="text-emerald-400">🔒</span> 256-Bit SSL Encrypted</span>
      <span>Toleo la 3.5 Standalone</span>
    </div>
  </div>

  <!-- RIGHT LOGIN FORM PANEL -->
  <div class="lg:col-span-6 glass-card p-8 sm:p-12 flex flex-col justify-center relative">
    
    <!-- Mobile Brand Header -->
    <div class="lg:hidden text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white text-2xl mb-2 shadow-lg">🏛️</div>
      <h2 class="text-2xl font-black text-slate-900">VICOBA PRO</h2>
      <p class="text-xs text-slate-500">Enterprise Microfinance Engine</p>
    </div>

    <div class="mb-8">
      <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Karibu Tena! 👋</h2>
      <p class="text-slate-500 text-sm mt-1">Ingiza jina lako la mtumiaji na nywila kuingia kwenye akaunti yako.</p>
    </div>

    <!-- Flash Messages -->
    <?php foreach ($flash as $f): ?>
    <div class="mb-6 px-4 py-3.5 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-sm
      <?= $f['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
      <span class="text-lg"><?= $f['type'] === 'error' ? '⚠️' : '✅' ?></span>
      <span><?= e($f['message']) ?></span>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="/login" class="space-y-5">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <!-- Username Field -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Jina la Mtumiaji au Barua Pepe</label>
        <div class="relative">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">👤</span>
          <input type="text" name="username" id="login-username" required autocomplete="username" value="admin"
            class="form-input-elite" placeholder="Ingiza username yako">
        </div>
      </div>

      <!-- Password Field -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Nywila (Password)</label>
        </div>
        <div class="relative">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">🔑</span>
          <input :type="showPass ? 'text' : 'password'" name="password" id="login-password" required autocomplete="current-password" value="Admin@1234"
            class="form-input-elite pr-12" placeholder="••••••••">
          <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm font-bold">
            <span x-text="showPass ? 'Ficha' : 'Onyesha'"></span>
          </button>
        </div>
      </div>

      <!-- Login Submit Button -->
      <button type="submit" id="login-submit" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white font-extrabold text-base shadow-xl shadow-blue-500/25 transition-all duration-200 flex items-center justify-center gap-2 group">
        <span>INGIA KWENYE MFUMO</span>
        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </button>
    </form>

    <!-- Quick Demo Accounts Helper -->
    <div class="mt-8 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
      <div class="flex items-center justify-between text-xs font-bold text-slate-700">
        <span>🔑 Demo Admin Credentials:</span>
        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px]">Pre-filled</span>
      </div>
      <p class="text-xs text-slate-500 font-mono">Username: <strong class="text-slate-800">admin</strong> | Password: <strong class="text-slate-800">Admin@1234</strong></p>
    </div>

    <!-- Registration Link -->
    <div class="mt-8 text-center border-t border-slate-100 pt-6">
      <p class="text-sm text-slate-600">
        Bado hujasajili kikundi chako?
        <a href="/register" class="text-blue-600 font-extrabold hover:text-blue-800 hover:underline">Sajili Kikundi Kipya Hapa →</a>
      </p>
    </div>

  </div>

</div>

</body>
</html>
