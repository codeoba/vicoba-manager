<?php
// ══ Fetch all data ══
$group_id = (int)($group->id ?? 0);
if (!$group_id) {
    echo '<div style="text-align:center;padding:5rem 2rem;color:rgba(255,255,255,.4)">
            <div style="font-size:3rem;margin-bottom:1rem">🏛️</div>
            <div style="font-size:1rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.5rem">Bado hujajiunga na kikundi</div>
            <div style="font-size:.85rem">Wasiliana na admin wa kikundi chako akuongeze kwenye mfumo.</div>
          </div>';
    return;
}

try {
    $stats = Models\Reports::getSummary($group_id);
} catch (\Throwable $e) {
    error_log('Error fetching stats summary: ' . $e->getMessage());
    $stats = [
        'total_shares' => 0, 'active_loans' => 0, 'fines_pending' => 0, 'fines_paid' => 0,
        'member_count' => 0, 'overdue_loans' => 0, 'balance' => ['income' => 0, 'expenses' => 0],
        'health_score' => 100, 'risk_level' => 'LOW', 'npl_rate' => 0, 'repayment_rate' => 100,
        'social_fund_balance' => 0
    ];
}

try {
    $recent_txns = Database::all(
        "SELECT t.*, m.full_name as member_name FROM " . Database::t('transactions') . " t
         LEFT JOIN " . Database::t('members') . " m ON m.id=t.member_id
         WHERE t.group_id=? ORDER BY t.created_at DESC LIMIT 8",
        [$group_id]
    );
} catch (\Throwable $e) {
    $recent_txns = [];
}

try {
    $recent_loans = Database::all(
        "SELECT l.*, m.full_name as member_name FROM " . Database::t('loans') . " l
         JOIN " . Database::t('members') . " m ON m.id=l.member_id
         WHERE l.group_id=? ORDER BY l.created_at DESC LIMIT 6",
        [$group_id]
    );
} catch (\Throwable $e) {
    $recent_loans = [];
}

try {
    $share_monthly = Database::all(
        "SELECT DATE_FORMAT(payment_date,'%b %Y') as label, SUM(total_amount) as amount
         FROM " . Database::t('shares') . " WHERE group_id=?
         GROUP BY DATE_FORMAT(payment_date,'%Y-%m'), DATE_FORMAT(payment_date,'%b %Y')
         ORDER BY MIN(payment_date) DESC LIMIT 7",
        [$group_id]
    );
    $share_monthly = array_reverse($share_monthly);
} catch (\Throwable $e) {
    $share_monthly = [];
}

try {
    $loan_monthly = Database::all(
        "SELECT DATE_FORMAT(COALESCE(disbursed_at, created_at),'%b %Y') as label, COUNT(*) as count, SUM(principal_amount) as amount
         FROM " . Database::t('loans') . " WHERE group_id=?
         GROUP BY DATE_FORMAT(COALESCE(disbursed_at, created_at),'%Y-%m'), DATE_FORMAT(COALESCE(disbursed_at, created_at),'%b %Y')
         ORDER BY MIN(COALESCE(disbursed_at, created_at)) DESC LIMIT 7",
        [$group_id]
    );
    $loan_monthly = array_reverse($loan_monthly);
} catch (\Throwable $e) {
    $loan_monthly = [];
}

try {
    $loan_status = Database::all(
        "SELECT status, COUNT(*) as cnt FROM " . Database::t('loans') . " WHERE group_id=? GROUP BY status",
        [$group_id]
    );
} catch (\Throwable $e) {
    $loan_status = [];
}
?>

<div style="display:flex;flex-direction:column;gap:1.5rem">

<!-- ══ HEALTH BANNER ══ -->
<div style="border-radius:1.5rem;padding:1.75rem 2rem;background:linear-gradient(135deg,rgba(37,99,235,.15) 0%,rgba(124,58,237,.1) 100%);border:1px solid rgba(37,99,235,.2);position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 90% 50%,rgba(124,58,237,.12),transparent)"></div>
  <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;border:1px solid rgba(37,99,235,.1)"></div>
  <div style="position:absolute;top:-20px;right:-20px;width:140px;height:140px;border-radius:50%;border:1px solid rgba(37,99,235,.08)"></div>

  <div style="position:relative;z-index:1;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.5rem">
    <div>
      <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.875rem">
        <span style="padding:.3rem .75rem;border-radius:2rem;font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;<?= $stats['risk_level']==='LOW' ? 'background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.25)' : ($stats['risk_level']==='MEDIUM' ? 'background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25)' : 'background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.25)') ?>">
          <?= $stats['risk_level'] ?> RISK
        </span>
        <span style="font-size:.75rem;color:rgba(255,255,255,.5)">Tathmini ya Afya ya Kifedha — <?= date('F Y') ?></span>
      </div>
      <h2 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.375rem;letter-spacing:-.01em">Index ya Afya ya Kikundi</h2>
      <p style="font-size:.8rem;color:rgba(255,255,255,.5);max-width:480px;line-height:1.6">
        Ina hesabiwa kutoka kwa Kiwango cha Ulipaji (<?= $stats['repayment_rate'] ?>%), NPL Ratio (<?= $stats['npl_rate'] ?>%), na Ukuaji wa Hisa kwa mwezi.
      </p>
    </div>
    <div style="display:flex;gap:1.25rem;flex-wrap:wrap;align-items:center">
      <!-- Health Score Circle -->
      <div style="text-align:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:1.25rem;padding:1.25rem 1.75rem;backdrop-filter:blur(8px)">
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.4);margin-bottom:.375rem">Health Score</div>
        <div style="font-size:3rem;font-weight:900;line-height:1;<?= $stats['health_score']>=80 ? 'color:#4ade80' : ($stats['health_score']>=50 ? 'color:#fbbf24' : 'color:#f87171') ?>">
          <?= $stats['health_score'] ?><span style="font-size:1.1rem;font-weight:400;color:rgba(255,255,255,.3)">/100</span>
        </div>
      </div>
      <!-- Rate badges -->
      <div style="display:flex;flex-direction:column;gap:.625rem">
        <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.15);border-radius:.875rem;padding:.625rem .875rem">
          <div style="font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em">Ulipaji Rate</div>
          <div style="font-size:1.1rem;font-weight:800;color:#4ade80"><?= $stats['repayment_rate'] ?>%</div>
        </div>
        <div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.15);border-radius:.875rem;padding:.625rem .875rem">
          <div style="font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em">NPL Ratio</div>
          <div style="font-size:1.1rem;font-weight:800;color:#fbbf24"><?= $stats['npl_rate'] ?>%</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ KPI STATS ══ -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">

  <div class="stat-card">
    <div class="stat-icon-wrap" style="background:rgba(37,99,235,.12)">👥</div>
    <div class="stat-label">Wanachama Hai</div>
    <div class="stat-value"><?= number_format($stats['member_count']) ?></div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">📋 Wanaofanya kazi kikamilifu</div>
  </div>

  <div class="stat-card">
    <div class="stat-icon-wrap" style="background:rgba(34,197,94,.1)">💰</div>
    <div class="stat-label">Hisa (Pool)</div>
    <div class="stat-value" style="font-size:1.2rem;color:#4ade80"><?= money($stats['total_shares']) ?></div>
    <div class="stat-change" style="color:#4ade80">📈 Jumla ya hisa zote</div>
  </div>

  <div class="stat-card">
    <div class="stat-icon-wrap" style="background:rgba(251,191,36,.1)">🏦</div>
    <div class="stat-label">Mikopo (Active)</div>
    <div class="stat-value" style="font-size:1.2rem;color:#fbbf24"><?= money($stats['active_loans']) ?></div>
    <div class="stat-change" style="color:<?= $stats['overdue_loans'] > 0 ? '#f87171' : '#4ade80' ?>">
      <?= $stats['overdue_loans'] > 0 ? "⚠️ {$stats['overdue_loans']} imechelewa" : '✅ Hakuna iliyochelewa' ?>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon-wrap" style="background:rgba(239,68,68,.1)">⚠️</div>
    <div class="stat-label">Faini Hazilipwa</div>
    <div class="stat-value" style="font-size:1.2rem;color:#f87171"><?= money($stats['fines_pending']) ?></div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">✅ Zilipwa: <?= money($stats['fines_paid']) ?></div>
  </div>

  <?php if (!empty($stats['social_fund_balance'])): ?>
  <div class="stat-card">
    <div class="stat-icon-wrap" style="background:rgba(20,184,166,.1)">❤️</div>
    <div class="stat-label">Mfuko wa Jamii</div>
    <div class="stat-value" style="font-size:1.2rem;color:#2dd4bf"><?= money($stats['social_fund_balance']) ?></div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Bakaa iliyopo</div>
  </div>
  <?php endif; ?>

</div>

<!-- ══ CHARTS ROW ══ -->
<div style="display:grid;grid-template-columns:3fr 2fr;gap:1.25rem">

  <!-- Shares + Loans Line Chart -->
  <div class="card card-p">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
      <div>
        <div class="card-title">📈 Hisa na Mikopo kwa Mwezi</div>
        <div class="card-sub">Mwenendo wa miezi 7 iliyopita</div>
      </div>
      <div style="display:flex;gap:.5rem">
        <span class="badge badge-info">Hisa</span>
        <span class="badge badge-warning">Mikopo</span>
      </div>
    </div>
    <canvas id="chart-monthly" height="90"></canvas>
  </div>

  <!-- Loan Status Doughnut -->
  <div class="card card-p">
    <div style="margin-bottom:1.25rem">
      <div class="card-title">🏦 Hali ya Mikopo</div>
      <div class="card-sub">Mgawanyo wa hali za sasa</div>
    </div>
    <canvas id="chart-loan-status" height="150"></canvas>
    <div id="loan-status-legend" style="display:flex;flex-wrap:wrap;gap:.625rem;margin-top:1rem;justify-content:center"></div>
  </div>
</div>

<!-- ══ BOTTOM ROW: Recent Loans + Transactions ══ -->
<div style="display:grid;grid-template-columns:2fr 3fr;gap:1.25rem">

  <!-- Recent Loans -->
  <div class="card">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div class="card-title">🏦 Mikopo ya Hivi Karibuni</div>
      <a href="/dashboard/loans" style="font-size:.72rem;font-weight:700;color:#60a5fa;text-decoration:none;opacity:.8">Zote →</a>
    </div>
    <div style="padding:.5rem 0">
      <?php if (empty($recent_loans)): ?>
      <div style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3);font-size:.83rem">Hakuna mikopo bado</div>
      <?php else: foreach($recent_loans as $loan): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.04)">
        <div style="min-width:0">
          <div style="font-size:.83rem;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($loan->member_name) ?></div>
          <div style="font-size:.68rem;color:rgba(255,255,255,.35);margin-top:.1rem"><?= e($loan->loan_code) ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0;margin-left:.75rem">
          <div style="font-size:.83rem;font-weight:800;color:#fff"><?= money($loan->principal_amount) ?></div>
          <span class="badge <?= match($loan->status){ 'active'=>'badge-success','overdue'=>'badge-danger','completed'=>'badge-muted',default=>'badge-warning' } ?>" style="margin-top:.2rem">
            <?= $loan->status ?>
          </span>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="card" style="overflow:hidden">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div class="card-title">🧾 Miamala ya Hivi Karibuni</div>
      <a href="/dashboard/ledger" style="font-size:.72rem;font-weight:700;color:#60a5fa;text-decoration:none;opacity:.8">Zote →</a>
    </div>
    <div style="overflow-x:auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Tarehe</th>
            <th>Aina</th>
            <th>Mwanachama</th>
            <th>Kiasi</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($recent_txns)): ?>
          <tr><td colspan="4" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna miamala bado</td></tr>
          <?php else: foreach($recent_txns as $t): ?>
          <?php $isDebit = str_contains($t->type,'disbursement') || str_contains($t->type,'expense'); ?>
          <tr>
            <td style="color:rgba(255,255,255,.45);font-size:.75rem;white-space:nowrap"><?= format_datetime($t->created_at) ?></td>
            <td>
              <span style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.75)"><?= e(str_replace('_',' ',ucwords($t->type,'_'))) ?></span>
            </td>
            <td style="font-size:.8rem"><?= e($t->member_name ?? 'N/A') ?></td>
            <td style="font-weight:800;color:<?= $isDebit ? '#f87171' : '#4ade80' ?>;font-size:.83rem;white-space:nowrap">
              <?= $isDebit ? '−' : '+' ?><?= money($t->amount) ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div><!-- end flex column -->

<script>
(function(){
  // ── Monthly Chart (Line) ──
  const mCtx = document.getElementById('chart-monthly');
  if (mCtx) {
    const labels  = <?= json_encode(array_column($share_monthly,'label')) ?>;
    const shares  = <?= json_encode(array_map(fn($r)=>(float)$r->amount, $share_monthly)) ?>;
    const lLabels = <?= json_encode(array_column($loan_monthly,'label')) ?>;
    const loans   = <?= json_encode(array_map(fn($r)=>(float)$r->amount, $loan_monthly)) ?>;

    // Align loan data to the same labels
    const mergedLoans = labels.map(l => {
      const idx = lLabels.indexOf(l);
      return idx >= 0 ? loans[idx] : 0;
    });

    const createGradient = (ctx, c1, c2) => {
      const g = ctx.createLinearGradient(0, 0, 0, 200);
      g.addColorStop(0, c1);
      g.addColorStop(1, c2);
      return g;
    };

    new Chart(mCtx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Hisa (TZS)',
            data: shares,
            backgroundColor: (ctx) => {
              const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
              g.addColorStop(0, 'rgba(37,99,235,0.7)');
              g.addColorStop(1, 'rgba(37,99,235,0.1)');
              return g;
            },
            borderColor: 'rgba(37,99,235,0.9)',
            borderWidth: 0,
            borderRadius: 6,
            borderSkipped: false,
          },
          {
            label: 'Mikopo (TZS)',
            data: mergedLoans,
            backgroundColor: (ctx) => {
              const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
              g.addColorStop(0, 'rgba(251,191,36,0.6)');
              g.addColorStop(1, 'rgba(251,191,36,0.05)');
              return g;
            },
            borderColor: 'rgba(251,191,36,0.9)',
            borderWidth: 0,
            borderRadius: 6,
            borderSkipped: false,
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', align: 'end' },
          tooltip: {
            callbacks: {
              label: (c) => ` ${c.dataset.label}: ${Number(c.raw).toLocaleString()} TZS`
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            ticks: { callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v }
          }
        }
      }
    });
  }

  // ── Loan Status Doughnut ──
  const dCtx = document.getElementById('chart-loan-status');
  if (dCtx) {
    const statusData = <?= json_encode(array_values($loan_status)) ?>;
    const sLabels = statusData.map(s => s.status ?? s['status']);
    const sCounts = statusData.map(s => s.cnt ?? s['cnt']);
    const colors = { active:'#4ade80', overdue:'#f87171', completed:'#60a5fa', pending:'#fbbf24', written_off:'#a78bfa' };
    const bgColors = sLabels.map(s => colors[s] || '#94a3b8');

    const chart = new Chart(dCtx, {
      type: 'doughnut',
      data: {
        labels: sLabels,
        datasets: [{
          data: sCounts,
          backgroundColor: bgColors.map(c => c + '33'),
          borderColor: bgColors,
          borderWidth: 2,
          hoverBorderWidth: 3,
          hoverOffset: 6,
        }]
      },
      options: {
        cutout: '68%',
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: c => ` ${c.label}: ${c.raw} mikopo` } }
        }
      }
    });

    // Custom legend
    const legend = document.getElementById('loan-status-legend');
    if (legend) {
      sLabels.forEach((l, i) => {
        const item = document.createElement('div');
        item.style.cssText = 'display:flex;align-items:center;gap:.375rem;font-size:.7rem;font-weight:600;color:rgba(255,255,255,.65)';
        item.innerHTML = `<span style="width:8px;height:8px;border-radius:50%;background:${bgColors[i]}"></span>${l} (${sCounts[i]})`;
        legend.appendChild(item);
      });
    }
  }
})();
</script>
