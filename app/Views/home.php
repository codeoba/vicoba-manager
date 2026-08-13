<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VICOBA Manager Pro — Mfumo wa Kisasa wa Vikundi vya Akiba na Mikopo Tanzania</title>
<meta name="description" content="Simamia Hisa, Mikopo, Faini, Mikutano, na Uhasibu wa Pande Mbili. Mfumo Namba 1 wa VICOBA Tanzania.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Outfit',sans-serif;background:#050c1a;color:#f1f5f9;overflow-x:hidden}

/* ── NAVBAR ── */
.navbar{position:fixed;top:0;left:0;right:0;z-index:100;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;background:rgba(5,12,26,0.7);backdrop-filter:blur(24px);border-bottom:1px solid rgba(255,255,255,0.06);transition:all .3s}
.nav-logo{display:flex;align-items:center;gap:.75rem;text-decoration:none}
.nav-logo-icon{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:1.25rem;box-shadow:0 0 20px rgba(37,99,235,.4)}
.nav-brand{font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.02em}
.nav-brand span{color:#60a5fa}
.nav-links{display:flex;align-items:center;gap:.25rem}
.nav-link{padding:.5rem 1rem;border-radius:.625rem;font-size:.85rem;font-weight:600;color:rgba(255,255,255,.65);text-decoration:none;transition:all .2s}
.nav-link:hover{color:#fff;background:rgba(255,255,255,.08)}
.nav-cta{padding:.625rem 1.5rem;border-radius:.75rem;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-size:.85rem;font-weight:700;text-decoration:none;box-shadow:0 0 20px rgba(37,99,235,.35);transition:all .2s;border:1px solid rgba(255,255,255,.1)}
.nav-cta:hover{transform:translateY(-1px);box-shadow:0 8px 30px rgba(37,99,235,.45)}

/* ── HERO ── */
.hero{min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:8rem 2rem 5rem}
.hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% -5%,rgba(37,99,235,.25),transparent),radial-gradient(ellipse 60% 50% at 80% 80%,rgba(124,58,237,.15),transparent),#050c1a}
.hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(37,99,235,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(37,99,235,.04) 1px,transparent 1px);background-size:60px 60px}
.hero-glow-1{position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.12),transparent 70%);top:-150px;left:-100px;pointer-events:none;animation:glow-pulse 6s ease-in-out infinite}
.hero-glow-2{position:absolute;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,.1),transparent 70%);bottom:-100px;right:-100px;pointer-events:none;animation:glow-pulse 8s ease-in-out infinite reverse}
@keyframes glow-pulse{0%,100%{opacity:.6;transform:scale(1)}50%{opacity:1;transform:scale(1.1)}}

.hero-content{position:relative;z-index:10;text-align:center;max-width:900px}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1rem;border-radius:2rem;background:rgba(37,99,235,.1);border:1px solid rgba(37,99,235,.3);color:#60a5fa;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2rem}
.hero-badge-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 8px rgba(34,197,94,.6);animation:dot-blink 2s ease-in-out infinite}
@keyframes dot-blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero-title{font-size:clamp(2.5rem,7vw,5.5rem);font-weight:900;line-height:1.05;letter-spacing:-.03em;color:#fff;margin-bottom:1.5rem}
.hero-title .gradient-text{background:linear-gradient(135deg,#60a5fa 0%,#a78bfa 50%,#34d399 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-subtitle{font-size:1.1rem;color:rgba(255,255,255,.55);max-width:600px;margin:0 auto 3rem;line-height:1.7;font-weight:400}
.hero-actions{display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;margin-bottom:4rem}
.btn-hero-primary{display:inline-flex;align-items:center;gap:.75rem;padding:1rem 2.5rem;border-radius:1rem;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-size:1rem;font-weight:700;text-decoration:none;box-shadow:0 0 40px rgba(37,99,235,.4);transition:all .3s;border:1px solid rgba(255,255,255,.15)}
.btn-hero-primary:hover{transform:translateY(-3px);box-shadow:0 20px 60px rgba(37,99,235,.5)}
.btn-hero-secondary{display:inline-flex;align-items:center;gap:.75rem;padding:1rem 2.5rem;border-radius:1rem;background:rgba(255,255,255,.06);color:#fff;font-size:1rem;font-weight:600;text-decoration:none;border:1px solid rgba(255,255,255,.12);transition:all .3s;backdrop-filter:blur(10px)}
.btn-hero-secondary:hover{background:rgba(255,255,255,.1);transform:translateY(-2px)}

/* ── STATS BAR ── */
.stats-bar{display:flex;align-items:center;justify-content:center;gap:0;border-radius:1.5rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);backdrop-filter:blur(12px);padding:1.5rem;flex-wrap:wrap;max-width:720px;margin:0 auto}
.stat-item{flex:1;min-width:120px;text-align:center;padding:0 2rem;border-right:1px solid rgba(255,255,255,.08)}
.stat-item:last-child{border-right:none}
.stat-value{font-size:1.75rem;font-weight:900;color:#fff;letter-spacing:-.02em}
.stat-value span{font-size:1.1rem;color:#60a5fa}
.stat-label{font-size:.7rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;font-weight:600}

/* ── FEATURES ── */
.section{padding:6rem 2rem;max-width:1200px;margin:0 auto}
.section-label{display:inline-flex;padding:.35rem .875rem;border-radius:2rem;background:rgba(37,99,235,.12);border:1px solid rgba(37,99,235,.25);color:#60a5fa;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:1rem}
.section-title{font-size:clamp(1.8rem,4vw,3rem);font-weight:900;color:#fff;line-height:1.1;margin-bottom:1.25rem;letter-spacing:-.02em}
.section-sub{font-size:1rem;color:rgba(255,255,255,.5);max-width:550px;line-height:1.7}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;margin-top:4rem}
.feature-card{padding:1.75rem;border-radius:1.5rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);transition:all .35s;position:relative;overflow:hidden}
.feature-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(37,99,235,.08),transparent);opacity:0;transition:.35s}
.feature-card:hover{border-color:rgba(37,99,235,.35);transform:translateY(-4px);box-shadow:0 20px 50px rgba(0,0,0,.3)}
.feature-card:hover::before{opacity:1}
.feature-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1.25rem}
.feature-title{font-size:1rem;font-weight:700;color:#fff;margin-bottom:.5rem}
.feature-desc{font-size:.85rem;color:rgba(255,255,255,.5);line-height:1.7}

/* ── DASHBOARD MOCKUP ── */
.mockup-section{padding:6rem 2rem;text-align:center;position:relative;overflow:hidden}
.mockup-section::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 50% at 50% 100%,rgba(37,99,235,.07),transparent)}
.mockup-frame{max-width:900px;margin:3rem auto 0;border-radius:20px;overflow:hidden;border:1px solid rgba(255,255,255,.1);box-shadow:0 40px 100px rgba(0,0,0,.5);background:rgba(15,23,42,0.9)}
.mockup-titlebar{padding:.75rem 1.25rem;background:rgba(255,255,255,.04);display:flex;align-items:center;gap:.5rem;border-bottom:1px solid rgba(255,255,255,.08)}
.mockup-dot{width:12px;height:12px;border-radius:50%}
.mockup-inner{padding:1.5rem}
.mockup-stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
.mockup-stat{border-radius:12px;padding:1.25rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);text-align:left}
.mockup-stat-num{font-size:1.5rem;font-weight:800;color:#fff}
.mockup-stat-lbl{font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;margin-top:.25rem}
.chart-bar-row{display:flex;align-items:flex-end;gap:.5rem;height:80px;border-radius:12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05);padding:1rem}
.chart-bar{flex:1;border-radius:4px 4px 0 0;opacity:.85}

/* ── FOOTER CTA ── */
.footer-cta{padding:6rem 2rem;text-align:center;position:relative;overflow:hidden}
.footer-cta-box{max-width:720px;margin:0 auto;padding:4rem;border-radius:2.5rem;background:linear-gradient(135deg,rgba(37,99,235,.15),rgba(124,58,237,.1));border:1px solid rgba(37,99,235,.25);position:relative;overflow:hidden}
.footer-cta-box::before{content:'';position:absolute;inset:-50%;background:radial-gradient(circle at 50% 50%,rgba(37,99,235,.1),transparent 60%)}
.footer-cta-title{font-size:clamp(1.75rem,4vw,2.75rem);font-weight:900;color:#fff;margin-bottom:1rem;line-height:1.1}
.footer-cta-sub{color:rgba(255,255,255,.55);margin-bottom:2.5rem;font-size:.95rem;line-height:1.7}
footer-bottom{padding:2rem;text-align:center;border-top:1px solid rgba(255,255,255,.06);color:rgba(255,255,255,.3);font-size:.8rem}

/* ── FLOAT ANIMATION ── */
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
.float-anim{animation:float 6s ease-in-out infinite}

@media(max-width:768px){.nav-links{display:none}.stats-bar{gap:1rem}.stat-item{border-right:none;border-bottom:1px solid rgba(255,255,255,.08);padding:1rem}.stat-item:last-child{border-bottom:none}.mockup-stat-row{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="/" class="nav-logo">
    <div class="nav-logo-icon">🏛️</div>
    <span class="nav-brand">VICOBA <span>PRO</span></span>
  </a>
  <div class="nav-links">
    <a href="#features" class="nav-link">Vipengele</a>
    <a href="#about" class="nav-link">Kuhusu</a>
    <a href="/register" class="nav-link">Sajili</a>
  </div>
  <a href="/login" class="nav-cta">🔑 Ingia Kwenye Mfumo</a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-glow-1"></div>
  <div class="hero-glow-2"></div>

  <div class="hero-content">
    <div class="hero-badge">
      <div class="hero-badge-dot"></div>
      Mfumo Mkubwa Wa Hali Ya Juu — Toleo 3.5 Enterprise
    </div>

    <h1 class="hero-title">
      Simamia Kikundi Chako cha VICOBA<br>
      kwa <span class="gradient-text">Ufanisi wa Kibenki</span>
    </h1>

    <p class="hero-subtitle">
      Mfumo wa kina wa kusimamia Hisa, Mikopo, Faini, Mikutano, Uhasibu wa Pande Mbili, na Uchambuzi wa Credit Score — wote mahali pamoja, kwa usalama wa 100%.
    </p>

    <div class="hero-actions">
      <a href="/register" class="btn-hero-primary">
        🚀 Anza Bure Sasa
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </a>
      <a href="/login" class="btn-hero-secondary">
        🔑 Ingia Akaunti Iliyopo
      </a>
    </div>

    <div class="stats-bar float-anim">
      <div class="stat-item">
        <div class="stat-value">500<span>+</span></div>
        <div class="stat-label">Vikundi Vilivyosajiliwa</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">50k<span>+</span></div>
        <div class="stat-label">Wanachama Wanaotumia</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">99.9<span>%</span></div>
        <div class="stat-label">Uhakika wa Uptime</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">TZS<span> Bilioni</span></div>
        <div class="stat-label">Zilizosimamiwa</div>
      </div>
    </div>
  </div>
</section>

<!-- DASHBOARD MOCKUP PREVIEW -->
<section class="mockup-section">
  <span class="section-label">📊 Dashboard ya Kisasa</span>
  <h2 class="section-title" style="margin-top:.75rem">Uone Kila Kitu kwa Jicho Moja</h2>
  <div class="mockup-frame">
    <div class="mockup-titlebar">
      <div class="mockup-dot" style="background:#ff5f56"></div>
      <div class="mockup-dot" style="background:#ffbd2e"></div>
      <div class="mockup-dot" style="background:#27c93f"></div>
      <div style="flex:1;text-align:center;font-size:.7rem;color:rgba(255,255,255,.3)">vikoba.mdandu.com/dashboard/overview</div>
    </div>
    <div class="mockup-inner">
      <div class="mockup-stat-row">
        <div class="mockup-stat" style="background:linear-gradient(135deg,rgba(37,99,235,.15),rgba(37,99,235,.05));border-color:rgba(37,99,235,.2)">
          <div class="mockup-stat-num" style="color:#60a5fa">Tsh 12.5M</div>
          <div class="mockup-stat-lbl">💰 Hisa (Pool)</div>
        </div>
        <div class="mockup-stat" style="background:linear-gradient(135deg,rgba(34,197,94,.1),transparent);border-color:rgba(34,197,94,.2)">
          <div class="mockup-stat-num" style="color:#4ade80">48</div>
          <div class="mockup-stat-lbl">👥 Wanachama</div>
        </div>
        <div class="mockup-stat" style="background:linear-gradient(135deg,rgba(251,191,36,.1),transparent);border-color:rgba(251,191,36,.2)">
          <div class="mockup-stat-num" style="color:#fbbf24">Tsh 3.2M</div>
          <div class="mockup-stat-lbl">🏦 Mikopo Active</div>
        </div>
        <div class="mockup-stat" style="background:linear-gradient(135deg,rgba(124,58,237,.12),transparent);border-color:rgba(124,58,237,.2)">
          <div class="mockup-stat-num" style="color:#a78bfa">87</div>
          <div class="mockup-stat-lbl">🤖 Health Score</div>
        </div>
      </div>
      <div class="chart-bar-row">
        <div class="chart-bar" style="height:40%;background:rgba(37,99,235,.5)"></div>
        <div class="chart-bar" style="height:65%;background:rgba(37,99,235,.6)"></div>
        <div class="chart-bar" style="height:50%;background:rgba(37,99,235,.5)"></div>
        <div class="chart-bar" style="height:80%;background:linear-gradient(to top,#2563eb,#7c3aed)"></div>
        <div class="chart-bar" style="height:70%;background:rgba(37,99,235,.6)"></div>
        <div class="chart-bar" style="height:90%;background:linear-gradient(to top,#2563eb,#7c3aed);box-shadow:0 0 16px rgba(37,99,235,.4)"></div>
        <div class="chart-bar" style="height:75%;background:rgba(37,99,235,.5)"></div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section" id="features">
  <span class="section-label">✨ Vipengele Vyote</span>
  <h2 class="section-title" style="margin-top:.75rem">Mfumo Kamili wa Enterprise<br>kwa Vikundi Vyako</h2>
  <p class="section-sub">Kila kitu unachohitaji kusimamia kikundi cha VICOBA kwa ufanisi wa kibenki wa kweli.</p>

  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(37,99,235,.15)">📊</div>
      <div class="feature-title">Double-Entry Accounting</div>
      <div class="feature-desc">Uhasibu wa kisheria wa pande mbili (Debits = Credits) ukijumuisha Trial Balance, P&L, na Balance Sheet.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(124,58,237,.15)">🤖</div>
      <div class="feature-title">AI Credit Scoring (300-850)</div>
      <div class="feature-desc">Mfumo wa kiakili wa kupima uwezo wa mwanachama kupewa mkopo kwa kutumia historia yake ya nidhamu.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(34,197,94,.12)">💵</div>
      <div class="feature-title">Loan Amortization Schedule</div>
      <div class="feature-desc">Ratiba kamili ya marejesho ya mkopo (Flat Rate & Reducing Balance EMI) kwa kila mwezi.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(251,191,36,.12)">📱</div>
      <div class="feature-title">SMS Notifications</div>
      <div class="feature-desc">SMS za kiotomatiki kwa malipo, mikopo, faini, na vikumbusha vya mikutano kupitia BeemSMS / Africa's Talking.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(239,68,68,.12)">🛡️</div>
      <div class="feature-title">Dual-Authorization (Maker-Checker)</div>
      <div class="feature-desc">Ulinzi wa pande mbili wa miamala mikubwa. Hakuna mtu mmoja anayeweza kutoa pesa kubwa peke yake.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(20,184,166,.12)">📄</div>
      <div class="feature-title">PDF Statements & Annual Reports</div>
      <div class="feature-desc">Taarifa rasmi za PDF kwa wanachama na ripoti za mwaka zinazokubalika na Serikali na Halmashauri.</div>
    </div>
  </div>
</section>

<!-- FOOTER CTA -->
<section class="footer-cta">
  <div class="footer-cta-box">
    <div style="font-size:3rem;margin-bottom:1.5rem">🚀</div>
    <h2 class="footer-cta-title">Anza Leo — Bure Kabisa!</h2>
    <p class="footer-cta-sub">Sajili kikundi chako cha VICOBA sasa hivi na uanze kusimamia fedha zenu kwa ufanisi na usalama wa 100%. Hakuna kadi ya benki inayohitajika.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="/register" class="btn-hero-primary">✨ Sajili Kikundi Kipya — Bure</a>
      <a href="/login" class="btn-hero-secondary">🔑 Ingia Akaunti Iliyopo</a>
    </div>
  </div>
</section>

<div style="padding:1.5rem;text-align:center;border-top:1px solid rgba(255,255,255,.05);color:rgba(255,255,255,.25);font-size:.75rem">
  © <?= date('Y') ?> VICOBA Manager Pro — Toleo la 3.5 Enterprise | Imetengenezwa kwa 💙 Tanzania
</div>

</body>
</html>
