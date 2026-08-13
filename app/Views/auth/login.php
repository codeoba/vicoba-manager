<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingia — VICOBA Manager Pro</title>
<meta name="description" content="Ingia kwenye akaunti yako ya VICOBA Manager — mfumo wa kisasa wa vikundi vya akiba na mikopo Tanzania.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Outfit',sans-serif;background:#050c1a;color:#f1f5f9;min-height:100vh;display:flex;align-items:stretch}

.left-panel{width:48%;background:linear-gradient(135deg,#0d1b3e 0%,#0f2240 40%,#0d1b3e 100%);display:flex;flex-direction:column;justify-content:space-between;padding:3rem;position:relative;overflow:hidden}
.left-panel::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 40%,rgba(37,99,235,.18),transparent 60%),radial-gradient(circle at 80% 80%,rgba(124,58,237,.12),transparent 50%)}
.lp-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(37,99,235,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(37,99,235,.04) 1px,transparent 1px);background-size:40px 40px}

.lp-logo{display:flex;align-items:center;gap:.75rem;position:relative;z-index:1}
.lp-logo-icon{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:1.35rem;box-shadow:0 0 24px rgba(37,99,235,.4)}
.lp-brand{font-size:1.15rem;font-weight:800;color:#fff}
.lp-brand span{color:#60a5fa}
.lp-badge{font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin-top:.15rem}

.lp-hero{position:relative;z-index:1;margin:auto 0}
.lp-tag{display:inline-flex;align-items:center;gap:.5rem;padding:.35rem .875rem;border-radius:2rem;background:rgba(37,99,235,.12);border:1px solid rgba(37,99,235,.25);color:#93c5fd;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2rem}
.lp-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px rgba(34,197,94,.6);animation:dot-blink 2s ease-in-out infinite}
@keyframes dot-blink{0%,100%{opacity:1}50%{opacity:.3}}
.lp-title{font-size:2.5rem;font-weight:900;line-height:1.1;color:#fff;margin-bottom:1rem;letter-spacing:-.02em}
.lp-title .hl{background:linear-gradient(135deg,#60a5fa,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.lp-sub{color:rgba(255,255,255,.5);font-size:.9rem;line-height:1.7;max-width:340px;margin-bottom:2.5rem}

.lp-metrics{display:grid;grid-template-columns:1fr 1fr;gap:.875rem}
.lp-metric{padding:1.1rem 1.25rem;border-radius:1rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);backdrop-filter:blur(8px)}
.lp-metric-num{font-size:1.4rem;font-weight:900;color:#fff;letter-spacing:-.02em}
.lp-metric-lbl{font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.07em;margin-top:.2rem}

.lp-footer{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;font-size:.7rem;color:rgba(255,255,255,.3);border-top:1px solid rgba(255,255,255,.07);padding-top:1.5rem}

/* RIGHT PANEL */
.right-panel{flex:1;display:flex;align-items:center;justify-content:center;padding:2.5rem;background:#050c1a;position:relative}
.right-panel::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 70% 30%,rgba(37,99,235,.06),transparent)}

.login-card{width:100%;max-width:420px;position:relative}
.login-card-head{margin-bottom:2.5rem}
.login-card-head h2{font-size:1.85rem;font-weight:900;color:#fff;letter-spacing:-.02em;margin-bottom:.4rem}
.login-card-head p{color:rgba(255,255,255,.45);font-size:.875rem}

.field-wrap{margin-bottom:1.25rem}
.field-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.5);margin-bottom:.625rem}
.field-inner{position:relative}
.field-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-size:1rem;pointer-events:none}
.field-input{width:100%;padding:.875rem 1rem .875rem 2.75rem;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:1rem;color:#fff;font-size:.95rem;font-family:'Outfit',sans-serif;outline:none;transition:all .2s}
.field-input::placeholder{color:rgba(255,255,255,.25)}
.field-input:focus{border-color:rgba(37,99,235,.6);background:rgba(37,99,235,.08);box-shadow:0 0 0 4px rgba(37,99,235,.1)}
.toggle-btn{position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:.75rem;font-family:'Outfit',sans-serif;font-weight:600;transition:.2s}
.toggle-btn:hover{color:rgba(255,255,255,.8)}

.flash-box{margin-bottom:1.25rem;padding:.875rem 1rem;border-radius:.875rem;font-size:.83rem;font-weight:600;display:flex;align-items:center;gap:.625rem}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac}

.submit-btn{width:100%;padding:1rem;border-radius:1rem;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-size:1rem;font-weight:800;font-family:'Outfit',sans-serif;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.625rem;box-shadow:0 0 40px rgba(37,99,235,.3);transition:all .3s;letter-spacing:.01em;margin-top:1.75rem}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 20px 50px rgba(37,99,235,.4)}
.submit-btn:active{transform:translateY(0)}

.demo-box{margin-top:1.5rem;padding:1rem 1.25rem;border-radius:1rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)}
.demo-box-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem}
.demo-box-label{font-size:.7rem;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.07em}
.demo-badge{font-size:.6rem;font-weight:700;padding:.15rem .5rem;border-radius:2rem;background:rgba(37,99,235,.2);color:#60a5fa;border:1px solid rgba(37,99,235,.3)}
.demo-creds{font-family:monospace;font-size:.78rem;color:rgba(255,255,255,.5)}
.demo-creds strong{color:rgba(255,255,255,.85)}

.register-link{text-align:center;margin-top:2rem;font-size:.83rem;color:rgba(255,255,255,.4)}
.register-link a{color:#60a5fa;font-weight:700;text-decoration:none;transition:.2s}
.register-link a:hover{color:#93c5fd;text-decoration:underline}

@media(max-width:900px){
  .left-panel{display:none}
  body{align-items:center;justify-content:center;padding:1.5rem}
  .right-panel{padding:0}
}
</style>
</head>
<body x-data="{showPass: false}">

<!-- LEFT BRANDING PANEL -->
<div class="left-panel">
  <div class="lp-grid"></div>

  <div class="lp-logo">
    <div class="lp-logo-icon">🏛️</div>
    <div>
      <div class="lp-brand">VICOBA <span>PRO</span></div>
      <div class="lp-badge">Enterprise Microfinance Engine</div>
    </div>
  </div>

  <div class="lp-hero">
    <div class="lp-tag"><div class="lp-dot"></div> Mfumo Unaoendesha Moja kwa Moja</div>
    <h2 class="lp-title">Simamia Fedha za<br>Kikundi Chako kwa<br><span class="hl">Akili ya Kibenki</span></h2>
    <p class="lp-sub">Uhasibu wa kina, Credit Scoring, SMS za kiotomatiki, na Ripoti rasmi za PDF — vyote mahali pamoja.</p>

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
    <span>🔒 Data Yako Inalindwa</span>
    <span>© <?= date('Y') ?> VICOBA Manager Pro</span>
  </div>
</div>

<!-- RIGHT LOGIN FORM PANEL -->
<div class="right-panel">
  <div class="login-card">

    <div class="login-card-head">
      <h2>Karibu Tena! 👋</h2>
      <p>Ingiza taarifa zako za kuingia kwenye akaunti yako</p>
    </div>

    <?php foreach ($flash as $f): ?>
    <div class="flash-box <?= $f['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
      <span><?= $f['type'] === 'error' ? '⚠️' : '✅' ?></span>
      <span><?= e($f['message']) ?></span>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="/login">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div class="field-wrap">
        <label class="field-label">Jina la Mtumiaji</label>
        <div class="field-inner">
          <span class="field-icon">👤</span>
          <input type="text" name="username" id="login-username" required autocomplete="username"
            value="admin" class="field-input" placeholder="jina.mtumiaji">
        </div>
      </div>

      <div class="field-wrap">
        <label class="field-label">Nywila (Password)</label>
        <div class="field-inner">
          <span class="field-icon">🔑</span>
          <input :type="showPass ? 'text' : 'password'" name="password" required autocomplete="current-password"
            value="Admin@1234" class="field-input" placeholder="••••••••••••" style="padding-right:4.5rem">
          <button type="button" @click="showPass = !showPass" class="toggle-btn">
            <span x-text="showPass ? '🙈 Ficha' : '👁 Onyesha'"></span>
          </button>
        </div>
      </div>

      <button type="submit" id="login-submit" class="submit-btn">
        Ingia Kwenye Mfumo
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </button>
    </form>

    <div class="demo-box">
      <div class="demo-box-head">
        <span class="demo-box-label">🔑 Demo Credentials</span>
        <span class="demo-badge">Pre-Filled</span>
      </div>
      <div class="demo-creds">Username: <strong>admin</strong> &nbsp;|&nbsp; Password: <strong>Admin@1234</strong></div>
    </div>

    <div class="register-link">
      Bado hujasajili kikundi chako?
      <a href="/register">Sajili Kikundi Kipya →</a>
    </div>

  </div>
</div>

</body>
</html>
