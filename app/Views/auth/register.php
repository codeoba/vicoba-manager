<!DOCTYPE html>
<html lang="sw" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Sajili Kikundi — VICOBA Manager Enterprise') ?></title>
<meta name="description" content="Sajili Kikundi chako cha VICOBA bure kwenye mfumo wa kisasa wa VICOBA Manager.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php include VIEW_PATH . '/dashboard/tanzania_data.php'; ?>
<style>
  body { font-family: 'Outfit', 'Inter', sans-serif; }
  .bg-mesh {
    background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                #0f172a;
  }
  .glass-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
  }
  .form-label-elite { display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; tracking: 0.05em; color: #475569; margin-bottom: 0.5rem; }
  .form-input-elite {
    width: 100%;
    padding: 0.875rem 1rem;
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
<body class="min-h-screen bg-mesh text-slate-100 flex items-center justify-center p-4 sm:p-6 md:p-10" x-data="registerForm()">

<div class="w-full max-w-4xl glass-card rounded-3xl p-6 sm:p-10 lg:p-12 text-slate-900 relative shadow-2xl border border-white/20">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-6 mb-8 gap-4">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-3xl shadow-xl shadow-blue-500/30 flex-shrink-0">
        🏛️
      </div>
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sajili Kikundi Kipya cha VICOBA</h1>
        <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest mt-0.5">Anza Kutumia Mfumo Kiotomatiki • Bure Kabisa</p>
      </div>
    </div>
    <a href="/login" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
      <span>🔑 Umeshasajiliwa? Ingia Hapa</span>
    </a>
  </div>

  <!-- Flash Messages -->
  <?php foreach ($flash as $f): ?>
  <div class="mb-6 px-4 py-3.5 rounded-2xl text-sm font-semibold flex items-center gap-3
    <?= $f['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
    <span class="text-lg"><?= $f['type'] === 'error' ? '⚠️' : '✅' ?></span>
    <span><?= e($f['message']) ?></span>
  </div>
  <?php endforeach; ?>

  <form method="POST" action="/register" class="space-y-8">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <!-- Section 1: Taarifa za Kikundi -->
    <div class="space-y-4">
      <div class="flex items-center gap-2 text-slate-800 font-extrabold text-base">
        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">1</span>
        <span>Taarifa za Kikundi Chako</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
          <label class="form-label-elite">Jina la Kikundi *</label>
          <input type="text" name="group_name" required class="form-input-elite" placeholder="Mfano: VICOBA Amani Mwanza">
        </div>

        <div>
          <label class="form-label-elite">Mkoa *</label>
          <select name="region" x-model="selectedRegion" @change="selectedDistrict=''" required class="form-input-elite">
            <option value="">-- Chagua Mkoa --</option>
            <template x-for="(districts, reg) in regionsData" :key="reg">
              <option :value="reg" x-text="reg"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="form-label-elite">Wilaya *</label>
          <select name="district" x-model="selectedDistrict" required class="form-input-elite" :disabled="!selectedRegion">
            <option value="">-- Chagua Wilaya --</option>
            <template x-for="d in availableDistricts" :key="d">
              <option :value="d" x-text="d"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="form-label-elite">Tarehe ya Kuanzishwa</label>
          <input type="date" name="founding_date" class="form-input-elite">
        </div>

        <div>
          <label class="form-label-elite">Bei kwa Hisa Moja (TZS) *</label>
          <input type="number" name="share_price" value="1000" min="500" step="500" required class="form-input-elite">
        </div>
      </div>
    </div>

    <!-- Section 2: Taarifa za Mwenyekiti / Admin -->
    <div class="space-y-4 pt-4 border-t border-slate-100">
      <div class="flex items-center gap-2 text-slate-800 font-extrabold text-base">
        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">2</span>
        <span>Taarifa za Mwenyekiti / Admin wa Kikundi</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
          <label class="form-label-elite">Jina Kamili *</label>
          <input type="text" name="admin_name" required class="form-input-elite" placeholder="Jina la Kwanza na la Familia">
        </div>

        <div>
          <label class="form-label-elite">Jina la Kuingilia (Username) *</label>
          <input type="text" name="admin_username" required class="form-input-elite" placeholder="admin_kikundi">
        </div>

        <div>
          <label class="form-label-elite">Namba ya Simu *</label>
          <input type="tel" name="admin_phone" required class="form-input-elite" placeholder="0712345678">
        </div>

        <div class="sm:col-span-2">
          <label class="form-label-elite">Barua Pepe (Email)</label>
          <input type="email" name="admin_email" class="form-input-elite" placeholder="admin@kikundi.or.tz">
        </div>

        <div>
          <label class="form-label-elite">Nywila (Password) *</label>
          <input type="password" name="admin_password" required minlength="8" class="form-input-elite" placeholder="Angalau herufi 8">
        </div>

        <div>
          <label class="form-label-elite">Thibitisha Nywila *</label>
          <input type="password" name="admin_password_confirm" required minlength="8" class="form-input-elite" placeholder="Rudia nywila yako">
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="text-xs text-slate-500">
        🔒 Data zako zinalindwa kwa mujibu wa sheria za usalama wa data.
      </div>
      <button type="submit" class="w-full sm:w-auto py-4 px-8 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white font-extrabold text-base shadow-xl shadow-blue-500/25 transition-all duration-200 flex items-center justify-center gap-2">
        <span>🚀 SAJILI KIKUNDI & ANZA KUTUMIA</span>
      </button>
    </div>
  </form>

</div>

<script>
function registerForm() {
  return {
    selectedRegion: '',
    selectedDistrict: '',
    regionsData: typeof TANZANIA_REGIONS !== 'undefined' ? TANZANIA_REGIONS : {},
    get availableDistricts() {
      return this.selectedRegion ? (this.regionsData[this.selectedRegion] || []) : [];
    }
  }
}
</script>
</body>
</html>
