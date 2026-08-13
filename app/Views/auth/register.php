<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sajili Kikundi Kipya — VICOBA Manager Pro</title>
<meta name="description" content="Sajili kikundi chako cha VICOBA. Bure kabisa, dakika 2.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Tanzania Regions Data (inline - no server-side include needed) -->
<script>
const TANZANIA_REGIONS = {
  "Arusha": ["Arusha Mjini","Arusha Vijijini","Karatu","Longido","Monduli","Ngorongoro","Meru"],
  "Dar es Salaam": ["Ilala","Kinondoni","Temeke","Kigamboni","Ubungo"],
  "Dodoma": ["Dodoma Mjini","Bahi","Chamwino","Chemba","Kondoa","Kongwa","Mpwapwa"],
  "Geita": ["Geita Mjini","Bukombe","Chato","Mbogwe","Nyang'hwale"],
  "Iringa": ["Iringa Mjini","Iringa Vijijini","Kilolo","Mufindi"],
  "Kagera": ["Bukoba Mjini","Bukoba Vijijini","Biharamulo","Karagwe","Kyerwa","Misenyi","Muleba","Ngara"],
  "Katavi": ["Mpanda Mjini","Mpanda Vijijini","Mlele"],
  "Kigoma": ["Kigoma Ujiji","Kigoma Vijijini","Buhigwe","Kakonko","Kasulu Mjini","Kasulu Vijijini","Kibondo","Uvinza"],
  "Kilimanjaro": ["Moshi Mjini","Moshi Vijijini","Hai","Siha","Rombo","Mwanga","Same"],
  "Lindi": ["Lindi Mjini","Lindi Vijijini","Kilwa","Liwale","Nachingwea","Ruangwa"],
  "Manyara": ["Babati Mjini","Babati Vijijini","Hanang","Kiteto","Mbulu","Simanjiro"],
  "Mara": ["Musoma Mjini","Musoma Vijijini","Bunda","Butiama","Rorya","Serengeti","Tarime"],
  "Mbeya": ["Mbeya Mjini","Mbeya Vijijini","Chunya","Kyela","Mbarali","Rungwe"],
  "Morogoro": ["Morogoro Mjini","Morogoro Vijijini","Gairo","Kilombero","Kilosa","Mvomero","Ulanga","Malinyi"],
  "Mtwara": ["Mtwara Mjini","Mtwara Vijijini","Masasi Mjini","Masasi Vijijini","Nanyumbu","Newala","Tandahimba"],
  "Mwanza": ["Nyamagana","Ilemela","Kwimba","Magu","Misungwi","Sengerema","Ukerewe"],
  "Njombe": ["Njombe Mjini","Njombe Vijijini","Makambako Mjini","Makete","Ludewa","Wanging'ombe"],
  "Pwani": ["Kibaha Mjini","Kibaha Vijijini","Bagamoyo","Kisarawe","Mafia","Mkuranga","Rufiji"],
  "Rukwa": ["Sumbawanga Mjini","Sumbawanga Vijijini","Kalambo","Nkasi"],
  "Ruvuma": ["Songea Mjini","Songea Vijijini","Mbinga Mjini","Mbinga Vijijini","Namtumbo","Nyasa","Tunduru"],
  "Shinyanga": ["Shinyanga Mjini","Shinyanga Vijijini","Kahama Mjini","Kishapu","Msalala"],
  "Simiyu": ["Bariadi Mjini","Bariadi Vijijini","Busega","Itilima","Maswa","Meatu"],
  "Singida": ["Singida Mjini","Singida Vijijini","Ikungi","Iramba","Manyoni","Mkalama"],
  "Songwe": ["Vwawa","Ileje","Mbozi","Momba"],
  "Tabora": ["Tabora Mjini","Igunga","Kaliua","Nzega","Sikonge","Urambo","Uyui"],
  "Tanga": ["Tanga Mjini","Handeni Mjini","Handeni Vijijini","Kilindi","Korogwe Mjini","Korogwe Vijijini","Lushoto","Mkinga","Muheza","Pangani"],
  "Unguja Kaskazini": ["Kaskazini A","Kaskazini B"],
  "Unguja Kusini": ["Kati","Kusini"],
  "Mjini Magharibi": ["Mjini","Magharibi"],
  "Pemba Kaskazini": ["Wete","Micheweni"],
  "Pemba Kusini": ["Chake Chake","Mkoani"]
};
</script>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:'Outfit',sans-serif;
  background:#050c1a;
  color:#f1f5f9;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  padding:2rem 1rem;
}
body::before{
  content:'';position:fixed;inset:0;
  background:
    radial-gradient(ellipse 70% 50% at 15% 25%,rgba(37,99,235,.09),transparent),
    radial-gradient(ellipse 60% 60% at 85% 80%,rgba(124,58,237,.07),transparent);
  pointer-events:none;z-index:0;
}

.page-wrap{width:100%;max-width:820px;position:relative;z-index:1}

/* TOP NAV */
.top-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem}
.logo-link{display:flex;align-items:center;gap:.75rem;text-decoration:none}
.logo-icon{
  width:38px;height:38px;border-radius:11px;
  background:linear-gradient(135deg,#2563eb,#7c3aed);
  display:flex;align-items:center;justify-content:center;font-size:1.05rem;
}
.logo-text{font-size:1rem;font-weight:900;color:#fff}
.logo-text span{color:#60a5fa}
.back-btn{
  display:inline-flex;align-items:center;gap:.5rem;
  padding:.5rem 1rem;border-radius:.75rem;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
  color:rgba(255,255,255,.5);font-size:.78rem;font-weight:600;
  text-decoration:none;transition:.2s;
  font-family:'Outfit',sans-serif;
}
.back-btn:hover{color:#fff;background:rgba(255,255,255,.09)}

/* CARD */
.card{
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.08);
  border-radius:1.75rem;
  overflow:hidden;
  box-shadow:0 30px 80px rgba(0,0,0,.4);
}

/* CARD HEADER */
.card-header{
  padding:2.25rem 2.5rem 1.75rem;
  border-bottom:1px solid rgba(255,255,255,.07);
  background:linear-gradient(135deg,rgba(37,99,235,.07),rgba(124,58,237,.04));
}
.card-header h1{font-size:1.65rem;font-weight:900;color:#fff;letter-spacing:-.02em;margin-bottom:.35rem}
.card-header p{color:rgba(255,255,255,.42);font-size:.85rem}

/* STEPS */
.steps{display:flex;align-items:center;margin-top:1.5rem;gap:0}
.step{display:flex;align-items:center;gap:.625rem}
.step-circle{
  width:30px;height:30px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.75rem;font-weight:800;flex-shrink:0;
}
.step-active .step-circle{
  background:linear-gradient(135deg,#2563eb,#7c3aed);
  color:#fff;box-shadow:0 0 14px rgba(37,99,235,.4);
}
.step-inactive .step-circle{
  background:rgba(255,255,255,.07);color:rgba(255,255,255,.3);
  border:1.5px solid rgba(255,255,255,.1);
}
.step-lbl{font-size:.68rem;font-weight:700}
.step-active .step-lbl{color:#93c5fd}
.step-inactive .step-lbl{color:rgba(255,255,255,.3)}
.step-line{flex:1;height:1px;background:rgba(255,255,255,.07);margin:0 .625rem}

/* FORM BODY */
.card-body{padding:2.25rem 2.5rem}
.sect-head{display:flex;align-items:center;gap:.75rem;margin-bottom:1.35rem}
.sect-num{
  width:27px;height:27px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  font-size:.72rem;font-weight:800;flex-shrink:0;
}
.sect-title{font-size:.95rem;font-weight:800;color:#fff}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;margin-bottom:1.75rem}
.full{grid-column:1/-1}

/* Flash */
.flash-box{
  margin-bottom:1.5rem;padding:.875rem 1rem;
  border-radius:.875rem;font-size:.82rem;font-weight:600;
  display:flex;align-items:center;gap:.625rem;
}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.22);color:#fca5a5}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.22);color:#86efac}

/* Field */
.field-label{
  display:block;font-size:.67rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.07em;
  color:rgba(255,255,255,.38);margin-bottom:.45rem;
}
.field-input{
  width:100%;padding:.8rem 1rem;
  background:rgba(255,255,255,.05);
  border:1.5px solid rgba(255,255,255,.08);
  border-radius:.875rem;
  color:#fff;font-size:.875rem;font-family:'Outfit',sans-serif;
  outline:none;transition:all .2s;
  appearance:none;-webkit-appearance:none;
}
.field-input::placeholder{color:rgba(255,255,255,.18)}
.field-input:focus{
  border-color:rgba(37,99,235,.5);
  background:rgba(37,99,235,.07);
  box-shadow:0 0 0 4px rgba(37,99,235,.08);
}
.field-input:disabled{opacity:.35;cursor:not-allowed}
select.field-input{
  cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.35)' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 1rem center;background-size:15px;
  padding-right:2.75rem;
}
select.field-input option{background:#0d1930;color:#f1f5f9}

.divider{height:1px;background:rgba(255,255,255,.06);margin:0 0 1.75rem}

/* FOOTER */
.card-footer{
  padding:1.25rem 2.5rem;
  border-top:1px solid rgba(255,255,255,.07);
  display:flex;align-items:center;justify-content:space-between;
  gap:1rem;flex-wrap:wrap;
}
.security-note{font-size:.68rem;color:rgba(255,255,255,.28);display:flex;align-items:center;gap:.375rem}
.submit-btn{
  display:inline-flex;align-items:center;gap:.75rem;
  padding:.9rem 2.25rem;border-radius:.875rem;
  background:linear-gradient(135deg,#2563eb,#7c3aed);
  color:#fff;font-size:.9rem;font-weight:800;
  font-family:'Outfit',sans-serif;border:none;cursor:pointer;
  box-shadow:0 0 30px rgba(37,99,235,.25);
  transition:transform .2s,box-shadow .2s;
}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 16px 45px rgba(37,99,235,.4)}
.submit-btn:active{transform:translateY(0)}

/* Login link */
.login-link{text-align:center;margin-top:1.5rem;font-size:.82rem;color:rgba(255,255,255,.35)}
.login-link a{color:#60a5fa;font-weight:700;text-decoration:none}
.login-link a:hover{text-decoration:underline}

@media(max-width:600px){
  .form-grid{grid-template-columns:1fr}
  .full{grid-column:1/-1}
  .card-body,.card-footer,.card-header{padding:1.5rem}
  .steps{display:none}
}
</style>
</head>
<body>

<div class="page-wrap">

  <!-- TOP NAV -->
  <div class="top-nav">
    <a href="/" class="logo-link">
      <div class="logo-icon">🏛️</div>
      <div class="logo-text">VICOBA <span>PRO</span></div>
    </a>
    <a href="/login" class="back-btn">
      ← Rudi Kuingia
    </a>
  </div>

  <!-- MAIN CARD -->
  <div class="card">

    <!-- HEADER -->
    <div class="card-header">
      <h1>🏛️ Sajili Kikundi Kipya cha VICOBA</h1>
      <p>Jaza fomu hii kwa makini. Utakuwa tayari kutumia mfumo baada ya dakika 2 tu.</p>

      <div class="steps">
        <div class="step step-active">
          <div class="step-circle">1</div>
          <div class="step-lbl">Kikundi</div>
        </div>
        <div class="step-line"></div>
        <div class="step step-active">
          <div class="step-circle">2</div>
          <div class="step-lbl">Admin</div>
        </div>
        <div class="step-line"></div>
        <div class="step step-inactive">
          <div class="step-circle">✓</div>
          <div class="step-lbl">Imekamilika</div>
        </div>
      </div>
    </div>

    <!-- BODY -->
    <div class="card-body">

      <?php foreach ($flash as $f): ?>
      <div class="flash-box <?= $f['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
        <span><?= $f['type'] === 'error' ? '⚠️' : '✅' ?></span>
        <span><?= htmlspecialchars($f['message'], ENT_QUOTES) ?></span>
      </div>
      <?php endforeach; ?>

      <form method="POST" action="/register" id="reg-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

        <!-- SECTION 1: GROUP INFO -->
        <div class="sect-head">
          <div class="sect-num" style="background:rgba(37,99,235,.15);color:#60a5fa">1</div>
          <div class="sect-title">Taarifa za Kikundi Chako</div>
        </div>

        <div class="form-grid">
          <div class="full">
            <label class="field-label" for="group_name">Jina la Kikundi *</label>
            <input type="text" id="group_name" name="group_name" required
              class="field-input" placeholder="Mfano: VICOBA Amani Mwanza 2024">
          </div>

          <div>
            <label class="field-label" for="region">Mkoa *</label>
            <select id="region" name="region" required class="field-input" onchange="loadDistricts(this.value)">
              <option value="">-- Chagua Mkoa --</option>
              <?php foreach (array_keys((array)json_decode('{"Arusha":1,"Dar es Salaam":1,"Dodoma":1,"Geita":1,"Iringa":1,"Kagera":1,"Katavi":1,"Kigoma":1,"Kilimanjaro":1,"Lindi":1,"Manyara":1,"Mara":1,"Mbeya":1,"Morogoro":1,"Mtwara":1,"Mwanza":1,"Njombe":1,"Pwani":1,"Rukwa":1,"Ruvuma":1,"Shinyanga":1,"Simiyu":1,"Singida":1,"Songwe":1,"Tabora":1,"Tanga":1,"Unguja Kaskazini":1,"Unguja Kusini":1,"Mjini Magharibi":1,"Pemba Kaskazini":1,"Pemba Kusini":1}'))) as $reg): ?>
              <option value="<?= htmlspecialchars($reg, ENT_QUOTES) ?>"><?= htmlspecialchars($reg, ENT_QUOTES) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="field-label" for="district">Wilaya *</label>
            <select id="district" name="district" required class="field-input" disabled>
              <option value="">-- Chagua Mkoa Kwanza --</option>
            </select>
          </div>

          <div>
            <label class="field-label" for="founding_date">Tarehe ya Kuanzishwa</label>
            <input type="date" id="founding_date" name="founding_date" class="field-input">
          </div>

          <div>
            <label class="field-label" for="share_price">Bei kwa Hisa Moja (TZS) *</label>
            <input type="number" id="share_price" name="share_price" value="1000" min="100" step="100" required class="field-input">
          </div>
        </div>

        <div class="divider"></div>

        <!-- SECTION 2: ADMIN INFO -->
        <div class="sect-head">
          <div class="sect-num" style="background:rgba(124,58,237,.15);color:#a78bfa">2</div>
          <div class="sect-title">Taarifa za Mwenyekiti / Admin</div>
        </div>

        <div class="form-grid">
          <div class="full">
            <label class="field-label" for="full_name">Jina Kamili *</label>
            <input type="text" id="full_name" name="full_name" required
              class="field-input" placeholder="Jina la Kwanza na la Familia">
          </div>

          <div>
            <label class="field-label" for="username">Jina la Mtumiaji (Username) *</label>
            <input type="text" id="username" name="username" required
              class="field-input" placeholder="mfano: admin_amani">
          </div>

          <div>
            <label class="field-label" for="phone">Namba ya Simu *</label>
            <input type="tel" id="phone" name="phone" required
              class="field-input" placeholder="0712 345 678">
          </div>

          <div class="full">
            <label class="field-label" for="email">Barua Pepe (Hiari)</label>
            <input type="email" id="email" name="email"
              class="field-input" placeholder="admin@kikundi.or.tz">
          </div>

          <div>
            <label class="field-label" for="password">Nywila *</label>
            <input type="password" id="password" name="password" required minlength="8"
              class="field-input" placeholder="Angalau herufi 8">
          </div>

          <div>
            <label class="field-label" for="confirm_password">Thibitisha Nywila *</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
              class="field-input" placeholder="Rudia nywila yako">
          </div>
        </div>

      </form>
    </div><!-- end card-body -->

    <!-- FOOTER -->
    <div class="card-footer">
      <div class="security-note">
        <span>🔒</span>
        Data zako zinalindwa. Huhitaji kadi ya benki.
      </div>
      <button type="submit" form="reg-form" id="reg-submit" class="submit-btn">
        <span>🚀 Sajili Kikundi &amp; Anza</span>
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
      </button>
    </div>

  </div><!-- end card -->

  <div class="login-link">
    Tayari una akaunti? <a href="/login">Ingia Mfumoni →</a>
  </div>

</div><!-- end page-wrap -->

<script>
// Cascading districts dropdown
function loadDistricts(region) {
  var sel = document.getElementById('district');
  sel.innerHTML = '<option value="">-- Chagua Wilaya --</option>';
  if (!region || !TANZANIA_REGIONS[region]) {
    sel.disabled = true;
    return;
  }
  var districts = TANZANIA_REGIONS[region];
  for (var i = 0; i < districts.length; i++) {
    var opt = document.createElement('option');
    opt.value = districts[i];
    opt.textContent = districts[i];
    sel.appendChild(opt);
  }
  sel.disabled = false;
}

// Password match validation
document.getElementById('reg-form').addEventListener('submit', function(e) {
  var p1 = document.getElementById('password').value;
  var p2 = document.getElementById('confirm_password').value;
  if (p1 !== p2) {
    e.preventDefault();
    alert('Nywila hazifanani. Tafadhali jaribu tena.');
    return;
  }
  // Loading state
  var btn = document.getElementById('reg-submit');
  btn.disabled = true;
  btn.innerHTML = '<svg style="animation:spin 1s linear infinite" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Inasajili...';
});

// Build region options on load
(function() {
  var regSel = document.getElementById('region');
  regSel.innerHTML = '<option value="">-- Chagua Mkoa --</option>';
  var regions = Object.keys(TANZANIA_REGIONS).sort();
  for (var i = 0; i < regions.length; i++) {
    var opt = document.createElement('option');
    opt.value = regions[i];
    opt.textContent = regions[i];
    regSel.appendChild(opt);
  }
})();
</script>
<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
</body>
</html>
