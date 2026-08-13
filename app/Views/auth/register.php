<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sajili Kikundi Kipya — VICOBA Manager Pro</title>
<meta name="description" content="Sajili kikundi chako cha VICOBA kwenye mfumo wa kisasa. Bure kabisa.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php include VIEW_PATH . '/dashboard/tanzania_data.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Outfit',sans-serif;background:#050c1a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 50% at 10% 30%,rgba(37,99,235,.1),transparent),radial-gradient(ellipse 60% 60% at 90% 80%,rgba(124,58,237,.08),transparent);pointer-events:none}

.page-wrap{width:100%;max-width:860px;position:relative;z-index:1}

/* TOP NAV */
.top-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem}
.nav-logo{display:flex;align-items:center;gap:.625rem;text-decoration:none}
.nav-logo-icon{width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.nav-brand-text{font-size:1rem;font-weight:800;color:#fff}
.nav-brand-text span{color:#60a5fa}
.back-link{display:inline-flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:600;color:rgba(255,255,255,.5);text-decoration:none;padding:.5rem 1rem;border-radius:.75rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);transition:.2s}
.back-link:hover{color:#fff;background:rgba(255,255,255,.08)}

/* CARD */
.card{background:rgba(255,255,255,.04);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08);border-radius:2rem;overflow:hidden;box-shadow:0 40px 80px rgba(0,0,0,.4)}

.card-header{padding:2.5rem 2.5rem 1.75rem;border-bottom:1px solid rgba(255,255,255,.06);background:linear-gradient(135deg,rgba(37,99,235,.07),rgba(124,58,237,.04))}
.card-header h1{font-size:1.75rem;font-weight:900;color:#fff;letter-spacing:-.02em;margin-bottom:.375rem}
.card-header p{color:rgba(255,255,255,.45);font-size:.875rem}

/* STEPS INDICATOR */
.steps{display:flex;align-items:center;gap:0;margin-top:1.5rem}
.step-item{display:flex;align-items:center;gap:.625rem;flex:1}
.step-circle{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0;transition:.3s}
.step-active .step-circle{background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;box-shadow:0 0 16px rgba(37,99,235,.4)}
.step-inactive .step-circle{background:rgba(255,255,255,.08);color:rgba(255,255,255,.35);border:1.5px solid rgba(255,255,255,.1)}
.step-label{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.5)}
.step-active .step-label{color:#93c5fd}
.step-line{flex:1;height:1.5px;background:rgba(255,255,255,.08);margin:0 .5rem}

/* FORM BODY */
.card-body{padding:2.5rem}
.section-heading{display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem}
.section-num{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;flex-shrink:0}
.section-title-text{font-size:1rem;font-weight:700;color:#fff}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:2rem}
.form-grid-full{grid-column:1 / -1}
.field-wrap{position:relative}
.field-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.45);margin-bottom:.5rem}
.field-input{width:100%;padding:.875rem 1rem;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.08);border-radius:.875rem;color:#fff;font-size:.9rem;font-family:'Outfit',sans-serif;outline:none;transition:all .2s;appearance:none}
.field-input::placeholder{color:rgba(255,255,255,.2)}
.field-input:focus{border-color:rgba(37,99,235,.5);background:rgba(37,99,235,.07);box-shadow:0 0 0 4px rgba(37,99,235,.08)}
select.field-input{cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 1rem center;background-size:16px;padding-right:2.75rem}
select.field-input option{background:#0f172a;color:#f1f5f9}
.field-input:disabled{opacity:.4;cursor:not-allowed}

.divider{height:1px;background:rgba(255,255,255,.06);margin:2rem 0}

/* SUBMIT */
.card-footer{padding:0 2.5rem 2.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
.security-note{font-size:.72rem;color:rgba(255,255,255,.3);display:flex;align-items:center;gap:.4rem}
.submit-btn{display:inline-flex;align-items:center;gap:.75rem;padding:1rem 2.5rem;border-radius:1rem;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-size:1rem;font-weight:800;font-family:'Outfit',sans-serif;border:none;cursor:pointer;box-shadow:0 0 40px rgba(37,99,235,.3);transition:all .3s}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 20px 50px rgba(37,99,235,.4)}

.flash-box{margin-bottom:1.5rem;padding:1rem 1.25rem;border-radius:1rem;font-size:.83rem;font-weight:600;display:flex;align-items:center;gap:.625rem}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac}

@media(max-width:640px){.form-grid{grid-template-columns:1fr}.form-grid-full{grid-column:1 / -1}.card-body,.card-footer{padding:1.5rem}.card-header{padding:1.5rem}}
</style>
</head>
<body x-data="registerForm()">

<div class="page-wrap">

  <!-- TOP NAV -->
  <div class="top-nav">
    <a href="/" class="nav-logo">
      <div class="nav-logo-icon">🏛️</div>
      <div class="nav-brand-text">VICOBA <span>PRO</span></div>
    </a>
    <a href="/login" class="back-link">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Rudi Kuingia
    </a>
  </div>

  <!-- MAIN CARD -->
  <div class="card">

    <!-- CARD HEADER -->
    <div class="card-header">
      <h1>🏛️ Sajili Kikundi Kipya cha VICOBA</h1>
      <p>Jaza fomu hii kwa makini. Utakuwa tayari kutumia mfumo baada ya dakika 2.</p>

      <div class="steps" style="margin-top:1.5rem">
        <div class="step-item step-active">
          <div class="step-circle">1</div>
          <div class="step-label">Taarifa za Kikundi</div>
        </div>
        <div class="step-line"></div>
        <div class="step-item step-active">
          <div class="step-circle">2</div>
          <div class="step-label">Taarifa za Admin</div>
        </div>
        <div class="step-line"></div>
        <div class="step-item step-inactive">
          <div class="step-circle">✓</div>
          <div class="step-label">Imekamilika</div>
        </div>
      </div>
    </div>

    <div class="card-body">

      <?php foreach ($flash as $f): ?>
      <div class="flash-box <?= $f['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
        <span><?= $f['type'] === 'error' ? '⚠️' : '✅' ?></span>
        <span><?= e($f['message']) ?></span>
      </div>
      <?php endforeach; ?>

      <form method="POST" action="/register" id="reg-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <!-- SECTION 1 -->
        <div class="section-heading">
          <div class="section-num" style="background:rgba(37,99,235,.15);color:#60a5fa">1</div>
          <div class="section-title-text">Taarifa za Kikundi Chako</div>
        </div>

        <div class="form-grid">
          <div class="field-wrap form-grid-full">
            <label class="field-label">Jina la Kikundi *</label>
            <input type="text" name="group_name" required class="field-input" placeholder="Mfano: VICOBA Amani Mwanza 2024">
          </div>

          <div class="field-wrap">
            <label class="field-label">Mkoa *</label>
            <select name="region" x-model="selectedRegion" @change="selectedDistrict=''" required class="field-input">
              <option value="">-- Chagua Mkoa --</option>
              <template x-for="(districts, reg) in regionsData" :key="reg">
                <option :value="reg" x-text="reg"></option>
              </template>
            </select>
          </div>

          <div class="field-wrap">
            <label class="field-label">Wilaya *</label>
            <select name="district" x-model="selectedDistrict" required class="field-input" :disabled="!selectedRegion">
              <option value="">-- Chagua Wilaya --</option>
              <template x-for="d in availableDistricts" :key="d">
                <option :value="d" x-text="d"></option>
              </template>
            </select>
          </div>

          <div class="field-wrap">
            <label class="field-label">Tarehe ya Kuanzishwa</label>
            <input type="date" name="founding_date" class="field-input">
          </div>

          <div class="field-wrap">
            <label class="field-label">Bei kwa Hisa Moja (TZS) *</label>
            <input type="number" name="share_price" value="1000" min="500" step="500" required class="field-input">
          </div>
        </div>

        <div class="divider"></div>

        <!-- SECTION 2 -->
        <div class="section-heading">
          <div class="section-num" style="background:rgba(124,58,237,.15);color:#a78bfa">2</div>
          <div class="section-title-text">Taarifa za Mwenyekiti / Admin wa Kikundi</div>
        </div>

        <div class="form-grid">
          <div class="field-wrap form-grid-full">
            <label class="field-label">Jina Kamili *</label>
            <input type="text" name="admin_name" required class="field-input" placeholder="Jina la Kwanza na la Familia">
          </div>

          <div class="field-wrap">
            <label class="field-label">Jina la Kuingilia (Username) *</label>
            <input type="text" name="admin_username" required class="field-input" placeholder="admin_amani">
          </div>

          <div class="field-wrap">
            <label class="field-label">Namba ya Simu *</label>
            <input type="tel" name="admin_phone" required class="field-input" placeholder="0712 345 678">
          </div>

          <div class="field-wrap form-grid-full">
            <label class="field-label">Barua Pepe (Optional)</label>
            <input type="email" name="admin_email" class="field-input" placeholder="admin@kikundi.or.tz">
          </div>

          <div class="field-wrap">
            <label class="field-label">Nywila *</label>
            <input type="password" name="admin_password" required minlength="8" class="field-input" placeholder="Angalau herufi 8">
          </div>

          <div class="field-wrap">
            <label class="field-label">Thibitisha Nywila *</label>
            <input type="password" name="admin_password_confirm" required minlength="8" class="field-input" placeholder="Rudia nywila yako">
          </div>
        </div>

      </form>
    </div>

    <div class="card-footer">
      <div class="security-note">
        <span>🔒</span> Data zako zinalindwa kwa mujibu wa kanuni za usalama. Huhitaji kadi ya benki.
      </div>
      <button type="submit" form="reg-form" class="submit-btn">
        <span>🚀 Sajili Kikundi & Anza</span>
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </button>
    </div>

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
