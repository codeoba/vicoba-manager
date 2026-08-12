<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Sajili Kikundi — VICOBA Manager') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Inter', sans-serif; }
  .gradient-bg { background: linear-gradient(135deg, #1e3a5f 0%, #0f2440 40%, #1a4f6e 100%); }
  .input-field { @apply w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-800; }
  label { @apply block text-sm font-semibold text-slate-700 mb-1.5; }
</style>
</head>
<body class="min-h-screen gradient-bg py-10 px-4">
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
            <label>Jina la Kikundi *</label>
            <input type="text" name="group_name" required class="input-field" placeholder="Mfano: VICOBA Amani">
          </div>
          <div>
            <label>Mkoa</label>
            <input type="text" name="region" class="input-field" placeholder="Mfano: Dar es Salaam">
          </div>
          <div>
            <label>Wilaya</label>
            <input type="text" name="district" class="input-field" placeholder="Mfano: Ilala">
          </div>
          <div>
            <label>Tarehe ya Kuanzishwa</label>
            <input type="date" name="founding_date" class="input-field">
          </div>
          <div>
            <label>Sarafu</label>
            <select name="currency" class="input-field">
              <option value="TZS">TZS — Shilingi ya Tanzania</option>
              <option value="KES">KES — Shilingi ya Kenya</option>
              <option value="UGX">UGX — Shilingi ya Uganda</option>
              <option value="USD">USD — Dola ya Marekani</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section: Admin -->
      <div>
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">
          👤 Taarifa za Mwenyekiti (Admin)
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label>Jina Kamili *</label>
            <input type="text" name="full_name" required class="input-field" placeholder="Jina la Kwanza na la Familia">
          </div>
          <div>
            <label>Namba ya Simu *</label>
            <input type="tel" name="phone" required class="input-field" placeholder="+255 7XX XXX XXX">
          </div>
          <div>
            <label>Barua Pepe</label>
            <input type="email" name="email" class="input-field" placeholder="mfano@email.com">
          </div>
          <div class="sm:col-span-2">
            <label>Jina la Mtumiaji *</label>
            <input type="text" name="username" required class="input-field" placeholder="jina.mtumiaji (bila nafasi)">
          </div>
          <div>
            <label>Nywila *</label>
            <input type="password" name="password" required minlength="8" class="input-field" placeholder="Herufi 8 au zaidi">
          </div>
          <div>
            <label>Thibitisha Nywila *</label>
            <input type="password" name="confirm_password" required minlength="8" class="input-field" placeholder="Rudia nywila">
          </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
        🚀 Sajili Kikundi Sasa
      </button>
    </form>

    <p class="text-center text-sm text-slate-500 mt-4">
      Una akaunti tayari?
      <a href="/login" class="text-blue-600 font-semibold hover:underline">Ingia hapa</a>
    </p>
  </div>
</div>
</body>
</html>
