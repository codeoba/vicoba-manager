<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingia — VICOBA Manager Pro</title>
<meta name="description" content="Ingia kwenye akaunti yako ya VICOBA Manager Pro.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{height:100%}
body{
  font-family:'Outfit',sans-serif;
  background:#050c1a;
  color:#f1f5f9;
  min-height:100vh;
  display:flex;
  align-items:stretch;
}

/* ── LEFT BRANDING PANEL ── */
.left-panel{
  width:48%;
  background:linear-gradient(160deg,#0a1628 0%,#0d1f3f 50%,#0a1628 100%);
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  padding:2.5rem 3rem;
  position:relative;
  overflow:hidden;
  flex-shrink:0;
}
.lp-bg{
  position:absolute;inset:0;
  background:
    radial-gradient(circle at 25% 35%,rgba(37,99,235,.18),transparent 55%),
    radial-gradient(circle at 80% 75%,rgba(124,58,237,.12),transparent 50%);
  pointer-events:none;
}
.lp-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(37,99,235,.04) 1px,transparent 1px),
    linear-gradient(90deg,rgba(37,99,235,.04) 1px,transparent 1px);
  background-size:44px 44px;
  pointer-events:none;
}
.lp-content{position:relative;z-index:1;display:flex;flex-direction:column;height:100%}

.lp-logo{display:flex;align-items:center;gap:.875rem;margin-bottom:auto}
.lp-logo-icon{
  width:46px;height:46px;border-radius:14px;
  background:linear-gradient(135deg,#2563eb,#7c3aed);
  display:flex;align-items:center;justify-content:center;
  font-size:1.3rem;
  box-shadow:0 0 24px rgba(37,99,235,.4);
  flex-shrink:0;
}
.lp-brand-name{font-size:1.15rem;font-weight:900;color:#fff;line-height:1.2}
.lp-brand-name span{color:#60a5fa}
.lp-brand-sub{font-size:.6rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.12em;margin-top:.15rem}

.lp-hero{padding:3rem 0 2.5rem}
.lp-tag{
  display:inline-flex;align-items:center;gap:.5rem;
  padding:.35rem .875rem;border-radius:2rem;
  background:rgba(37,99,235,.12);border:1px solid rgba(37,99,235,.28);
  color:#93c5fd;font-size:.7rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.08em;margin-bottom:1.75rem;
}
.lp-dot{
  width:6px;height:6px;border-radius:50%;
  background:#22c55e;box-shadow:0 0 6px rgba(34,197,94,.6);
  animation:blink 2s ease-in-out infinite;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.lp-title{
  font-size:2.4rem;font-weight:900;line-height:1.1;
  color:#fff;margin-bottom:1rem;letter-spacing:-.025em;
}
.lp-title .hl{
  background:linear-gradient(135deg,#60a5fa,#a78bfa);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.lp-sub{font-size:.85rem;color:rgba(255,255,255,.45);line-height:1.7;max-width:320px;margin-bottom:2rem}

.lp-metrics{display:grid;grid-template-columns:1fr 1fr;gap:.875rem}
.lp-metric{
  padding:1rem 1.25rem;border-radius:1rem;
  background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
}
.lp-metric-num{font-size:1.4rem;font-weight:900;color:#fff;letter-spacing:-.02em}
.lp-metric-lbl{font-size:.62rem;color:rgba(255,255,255,.38);text-transform:uppercase;letter-spacing:.07em;margin-top:.15rem}

.lp-footer{
  position:relative;z-index:1;
  display:flex;align-items:center;justify-content:space-between;
  font-size:.68rem;color:rgba(255,255,255,.28);
  border-top:1px solid rgba(255,255,255,.07);padding-top:1.25rem;
}

/* ── RIGHT FORM PANEL ── */
.right-panel{
  flex:1;
  display:flex;align-items:center;justify-content:center;
  padding:2rem;
  background:#050c1a;
  position:relative;
}
.right-panel::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 60% 50% at 70% 35%,rgba(37,99,235,.06),transparent);
  pointer-events:none;
}

.login-card{width:100%;max-width:400px;position:relative;z-index:1}

.card-head{margin-bottom:2rem}
.card-head h1{font-size:1.75rem;font-weight:900;color:#fff;letter-spacing:-.02em;margin-bottom:.35rem}
.card-head p{font-size:.85rem;color:rgba(255,255,255,.42)}

/* Flash messages */
.flash-box{
  margin-bottom:1rem;padding:.875rem 1rem;
  border-radius:.875rem;font-size:.82rem;font-weight:600;
  display:flex;align-items:center;gap:.625rem;
}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.22);color:#fca5a5}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.22);color:#86efac}

/* Field */
.field{margin-bottom:1.1rem}
.field-label{
  display:block;font-size:.68rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.07em;
  color:rgba(255,255,255,.42);margin-bottom:.5rem;
}
.field-inner{position:relative}
.field-icon{
  position:absolute;left:1rem;top:50%;transform:translateY(-50%);
  font-size:.95rem;pointer-events:none;
}
.field-input{
  width:100%;
  padding:.875rem 1rem .875rem 2.75rem;
  background:rgba(255,255,255,.05);
  border:1.5px solid rgba(255,255,255,.09);
  border-radius:1rem;
  color:#fff;font-size:.9rem;font-family:'Outfit',sans-serif;
  outline:none;transition:border-color .2s,background .2s,box-shadow .2s;
}
.field-input::placeholder{color:rgba(255,255,255,.22)}
.field-input:focus{
  border-color:rgba(37,99,235,.55);
  background:rgba(37,99,235,.08);
  box-shadow:0 0 0 4px rgba(37,99,235,.1);
}
.eye-btn{
  position:absolute;right:.875rem;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;
  color:rgba(255,255,255,.35);font-size:.72rem;font-weight:700;
  font-family:'Outfit',sans-serif;padding:.25rem .375rem;
  border-radius:.375rem;transition:.2s;
}
.eye-btn:hover{color:rgba(255,255,255,.75);background:rgba(255,255,255,.06)}

/* Submit button */
.submit-btn{
  width:100%;margin-top:1.5rem;
  padding:1rem 1.5rem;border-radius:1rem;
  background:linear-gradient(135deg,#2563eb,#7c3aed);
  color:#fff;font-size:.95rem;font-weight:800;
  font-family:'Outfit',sans-serif;border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:.625rem;
  box-shadow:0 0 40px rgba(37,99,235,.28);
  transition:transform .2s,box-shadow .2s;
  letter-spacing:.01em;
}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 16px 50px rgba(37,99,235,.4)}
.submit-btn:active{transform:translateY(0);box-shadow:none}

/* Demo box */
.demo-box{
  margin-top:1.5rem;padding:.875rem 1.125rem;
  border-radius:1rem;
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
}
.demo-box-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem}
.demo-label{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.38);text-transform:uppercase;letter-spacing:.07em}
.demo-pill{font-size:.58rem;font-weight:800;padding:.2rem .5rem;border-radius:2rem;background:rgba(37,99,235,.18);color:#60a5fa;border:1px solid rgba(37,99,235,.28)}
.demo-creds{font-family:monospace;font-size:.76rem;color:rgba(255,255,255,.45)}
.demo-creds strong{color:rgba(255,255,255,.82)}

/* Register link */
.reg-link{text-align:center;margin-top:1.75rem;font-size:.82rem;color:rgba(255,255,255,.38)}
.reg-link a{color:#60a5fa;font-weight:700;text-decoration:none;transition:.2s}
.reg-link a:hover{color:#93c5fd;text-decoration:underline}

@media(max-width:800px){
  .left-panel{display:none}
  body{display:block}
  .right-panel{min-height:100vh;padding:2rem 1.5rem}
}
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="lp-bg"></div>
  <div class="lp-grid"></div>
  <div class="lp-content">

    <div class="lp-logo">
      <div class="lp-logo-icon">🏛️</div>
      <div>
        <div class="lp-brand-name">VICOBA <span>PRO</span></div>
        <div class="lp-brand-sub">Enterprise Microfinance Engine</div>
      </div>
    </div>

    <div class="lp-hero">
      <div class="lp-tag">
        <div class="lp-dot"></div>
        Mfumo Unaoendesha Moja kwa Moja
      </div>
      <h2 class="lp-title">
        Simamia Fedha za<br>
        Kikundi Chako kwa<br>
        <span class="hl">Akili ya Kibenki</span>
      </h2>
      <p class="lp-sub">
        Uhasibu wa kina, Credit Scoring, SMS za kiotomatiki, na Ripoti rasmi za PDF — vyote mahali pamoja.
      </p>
      <div class="lp-metrics">
        <div class="lp-metric">
          <div class="lp-metric-num">500<span style="font-size:1rem;color:#60a5fa">+</span></div>
          <div class="lp-metric-lbl">Vikundi</div>
        </div>
        <div class="lp-metric">
          <div class="lp-metric-num">50k<span style="font-size:1rem;color:#4ade80">+</span></div>
          <div class="lp-metric-lbl">Wanachama</div>
        </div>
        <div class="lp-metric">
          <div class="lp-metric-num">99.9<span style="font-size:1rem;color:#a78bfa">%</span></div>
          <div class="lp-metric-lbl">Uptime SLA</div>
        </div>
        <div class="lp-metric">
          <div class="lp-metric-num">256<span style="font-size:1rem;color:#fbbf24">bit</span></div>
          <div class="lp-metric-lbl">SSL Encryption</div>
        </div>
      </div>
    </div>

    <div class="lp-footer">
      <span>🔒 Data Yako Inalindwa Kwa Usalama</span>
      <span>© <?= date('Y') ?> VICOBA Manager Pro</span>
    </div>

  </div>
</div>

<!-- RIGHT FORM PANEL -->
<div class="right-panel">
  <div class="login-card">

    <div class="card-head">
      <h1>Karibu Tena! 👋</h1>
      <p>Ingiza taarifa zako ili uingie kwenye mfumo</p>
    </div>

    <?php foreach ($flash as $f): ?>
    <div class="flash-box <?= $f['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
      <span><?= $f['type'] === 'error' ? '⚠️' : '✅' ?></span>
      <span><?= htmlspecialchars($f['message'], ENT_QUOTES) ?></span>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="/login" id="login-form">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

      <!-- Username -->
      <div class="field">
        <label class="field-label" for="login-username">Jina la Mtumiaji</label>
        <div class="field-inner">
          <span class="field-icon">👤</span>
          <input
            type="text"
            id="login-username"
            name="username"
            required
            autocomplete="username"
            class="field-input"
            placeholder="Andika jina lako la mtumiaji"
          >
        </div>
      </div>

      <!-- Password -->
      <div class="field">
        <label class="field-label" for="login-password">Nywila (Password)</label>
        <div class="field-inner">
          <span class="field-icon">🔑</span>
          <input
            type="password"
            id="login-password"
            name="password"
            required
            autocomplete="current-password"
            class="field-input"
            placeholder="••••••••••••"
            style="padding-right:4rem"
          >
          <button type="button" id="eye-toggle" class="eye-btn" onclick="togglePassword()">
            👁 Onyesha
          </button>
        </div>
      </div>

      <button type="submit" id="login-submit" class="submit-btn">
        Ingia Kwenye Mfumo
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
      </button>
    </form>

    <div class="demo-box">
      <div class="demo-box-row">
        <span class="demo-label">🔑 Demo Credentials</span>
        <span class="demo-pill">Bonyeza Kujaza</span>
      </div>
      <div class="demo-creds" style="cursor:pointer" onclick="fillDemo()" title="Bonyeza kujaza kiotomatiki">
        Username: <strong>admin</strong> &nbsp;|&nbsp; Password: <strong>Admin@1234</strong>
        <span style="color:rgba(255,255,255,.3);font-size:.65rem;margin-left:.5rem">(bonyeza hapa)</span>
      </div>
    </div>

    <div class="reg-link">
      Bado hujasajili kikundi chako?
      <a href="/register">Sajili Kikundi Kipya →</a>
    </div>

  </div>
</div>

<script>
function togglePassword() {
  var inp = document.getElementById('login-password');
  var btn = document.getElementById('eye-toggle');
  if (inp.type === 'password') {
    inp.type = 'text';
    btn.textContent = '🙈 Ficha';
  } else {
    inp.type = 'password';
    btn.textContent = '👁 Onyesha';
  }
}

function fillDemo() {
  document.getElementById('login-username').value = 'admin';
  document.getElementById('login-password').value = 'Admin@1234';
  document.getElementById('login-password').type = 'password';
  document.getElementById('eye-toggle').textContent = '👁 Onyesha';
}

// Loading state on submit
document.getElementById('login-form').addEventListener('submit', function() {
  var btn = document.getElementById('login-submit');
  btn.disabled = true;
  btn.innerHTML = '<svg style="animation:spin 1s linear infinite" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Inakagua...';
});
</script>
<style>
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
</style>
</body>
</html>
