<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Sajili Kikundi — VICOBA Manager') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php include VIEW_PATH . '/dashboard/tanzania_data.php'; ?>
<style>
  body { font-family: 'Inter', sans-serif; }
  .gradient-bg { background: linear-gradient(135deg, #1e3a5f 0%, #0f2440 40%, #1a4f6e 100%); }
  .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.375rem; }
  .form-input { width: 100%; padding: 0.625rem 0.875rem; border-radius: 0.75rem; border: 1px solid #cbd5e1; outline: none; font-size: 0.875rem; background-color: #ffffff; color: #1e293b; }
  .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
</style>
</head>
<body class="min-h-screen gradient-bg py-10 px-4" x-data="registerForm()">
<div class="max-w-2xl mx-auto">
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur mb-3">
      <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
    </div>
    <h1 class="text-2xl font-extrabold text-white">Sajili Kikundi Kipya</h1>
    <p class="text-blue-200 text-sm mt-1">VICOBA Manager — Anza leo, bila malipo</p>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl p-8">
    <?php foreach ($flash as $f): ?>
    <div class="mb-4 px-4 py-3 rounded-xl text-sm <?= $f['type'] === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' ?>">
      <?= e($f['message']) ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="/register" class="space-y-6">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <!-- Section: Kikundi -->
      <div>
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">
          🏛️ Taarifa za Kikundi
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="form-label">Jina la Kikundi *</label>
            <input type="text" name="group_name" required class="form-input" placeholder="Mfano: VICOBA Amani">
          </div>
          <div>
            <label class="form-label">Mkoa *</label>
            <select name="region" x-model="selectedRegion" @change="selectedDistrict=''" required class="form-input">
              <option value="">-- Chagua Mkoa --</option>
              <template x-for="(districts, reg) in regionsData" :key="reg">
                <option :value="reg" x-text="reg"></option>
              </template>
            </select>
          </div>
          <div>
            <label class="form-label">Wilaya *</label>
            <select name="district" x-model="selectedDistrict" required class="form-input" :disabled="!selectedRegion">
              <option value="">-- Chagua Wilaya --</option>
              <template x-for="d in availableDistricts" :key="d">
                <option :value="d" x-text="d"></option>
              </template>
            </select>
          </div>
          <div>
            <label class="form-label">Tarehe ya Kuanzishwa</label>
            <input type="date" name="founding_date" class="form-input">
          </div>
          <div>
            <label class="form-label">Bei kwa Hisa Moja (TZS)</label>
            <input type="number" name="share_price" value="1000" min="500" class="form-input">
          </div>
        </div>
      </div>

      <!-- Section: Admin -->
      <div>
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">
          👤 Taarifa za Mwenyekiti / Admin wa Kikundi
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="form-label">Jina Kamili *</label>
            <input type="text" name="admin_name" required class="form-input" placeholder="Jina la Kwanza na la Familia">
          </div>
          <div>
            <label class="form-label">Jina la Kuingilia (Username) *</label>
            <input type="text" name="admin_username" required class="form-input" placeholder="admin_kikundi">
          </div>
          <div>
            <label class="form-label">Namba ya Simu *</label>
            <input type="tel" name="admin_phone" required class="form-input" placeholder="0712345678">
          </div>
          <div class="sm:col-span-2">
            <label class="form-label">Barua Pepe</label>
            <input type="email" name="admin_email" class="form-input" placeholder="admin@kikundi.or.tz">
          </div>
          <div>
            <label class="form-label">Nywila *</label>
            <input type="password" name="admin_password" required minlength="8" class="form-input" placeholder="Min 8 characters">
          </div>
          <div>
            <label class="form-label">Thibitisha Nywila *</label>
            <input type="password" name="admin_password_confirm" required minlength="8" class="form-input">
          </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition">
        🚀 Sajili Kikundi & Anza Kutumia
      </button>

      <p class="text-center text-sm text-slate-500">
        Umeshasajiliwa? <a href="/login" class="text-blue-600 font-semibold hover:underline">Ingia Hapa</a>
      </p>
    </form>
  </div>
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
