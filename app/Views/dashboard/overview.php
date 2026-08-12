<?php
// Fetch overview data
$group_id = (int)($group->id ?? 0);
if (!$group_id) { echo '<div class="text-center py-20 text-slate-400"><p class="text-4xl mb-2">🏛️</p><p class="font-bold text-slate-600">Bado haujajiunga na kikundi</p><p class="text-sm mt-2">Wasiliana na admin wa kikundi chako akuongeze.</p></div>'; return; }
$stats = Models\Reports::getSummary($group_id);
$recent_txns = Database::all("SELECT t.*, m.full_name as member_name FROM " . Database::t('transactions') . " t LEFT JOIN " . Database::t('members') . " m ON m.id=t.member_id WHERE t.group_id=? ORDER BY t.created_at DESC LIMIT 8", [$group_id]);
$recent_loans = Database::all("SELECT l.*, m.full_name as member_name FROM " . Database::t('loans') . " l JOIN " . Database::t('members') . " m ON m.id=l.member_id WHERE l.group_id=? ORDER BY l.created_at DESC LIMIT 5", [$group_id]);
$share_monthly = Database::all("SELECT DATE_FORMAT(payment_date,'%b %Y') as label, SUM(total_amount) as amount FROM " . Database::t('shares') . " WHERE group_id=? GROUP BY DATE_FORMAT(payment_date,'%Y-%m') ORDER BY MIN(payment_date) DESC LIMIT 6", [$group_id]);
$share_monthly = array_reverse($share_monthly);
?>
<div class="space-y-6">

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Wanachama</p>
    <p class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($stats['member_count']) ?></p>
    <p class="text-xs text-emerald-600 font-medium mt-1">Wanaofanya kazi</p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hisa (Pool)</p>
    <p class="text-2xl font-extrabold text-blue-600 mt-1"><?= money($stats['total_shares']) ?></p>
    <p class="text-xs text-slate-400 mt-1">Jumla ya hisa</p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mikopo (Balance)</p>
    <p class="text-2xl font-extrabold text-amber-600 mt-1"><?= money($stats['active_loans']) ?></p>
    <?php if($stats['overdue_loans'] > 0): ?>
    <p class="text-xs text-red-600 font-semibold mt-1">⚠️ <?= $stats['overdue_loans'] ?> imechelewa</p>
    <?php else: ?>
    <p class="text-xs text-emerald-600 mt-1">✅ Ziko sawa</p>
    <?php endif; ?>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Faini (Hazilipwa)</p>
    <p class="text-2xl font-extrabold text-red-600 mt-1"><?= money($stats['fines_pending']) ?></p>
    <p class="text-xs text-slate-400 mt-1">Faini zilipwa: <?= money($stats['fines_paid']) ?></p>
  </div>
</div>

<!-- Chart + Recent Loans -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
  <!-- Chart -->
  <div class="lg:col-span-3 bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-4">📈 Hisa kwa Mwezi</h3>
    <canvas id="shares-chart" height="120"></canvas>
  </div>

  <!-- Recent Loans -->
  <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-3">🏦 Mikopo ya Hivi Karibuni</h3>
    <?php if (empty($recent_loans)): ?>
    <p class="text-sm text-slate-400 text-center py-6">Hakuna mikopo bado</p>
    <?php else: foreach($recent_loans as $loan): ?>
    <div class="flex items-start justify-between py-2.5 border-b border-slate-50 last:border-0 gap-2">
      <div class="min-w-0">
        <p class="text-sm font-semibold text-slate-800 truncate"><?= e($loan->member_name) ?></p>
        <p class="text-xs text-slate-500"><?= $loan->loan_code ?></p>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="text-sm font-bold text-slate-800"><?= money($loan->principal_amount) ?></p>
        <span class="badge text-[10px] <?= match($loan->status) { 'active'=>'bg-emerald-50 text-emerald-700 border-emerald-200', 'overdue'=>'bg-red-50 text-red-700 border-red-200', 'completed'=>'bg-slate-100 text-slate-600 border-slate-200', default=>'bg-amber-50 text-amber-700 border-amber-200' } ?>">
          <?= $loan->status ?>
        </span>
      </div>
    </div>
    <?php endforeach; endif; ?>
    <a href="/dashboard/loans" class="block text-center text-xs font-semibold text-blue-600 hover:underline mt-3">Tazama Zote →</a>
  </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
    <h3 class="font-bold text-slate-800">🧾 Miamala ya Hivi Karibuni</h3>
    <a href="/dashboard/ledger" class="text-xs text-blue-600 hover:underline font-semibold">Tazama Zote →</a>
  </div>
  <div class="overflow-x-auto">
    <table class="table-auto w-full">
      <thead><tr><th>Tarehe</th><th>Aina</th><th>Mwanachama</th><th>Kiasi</th><th>Njia</th></tr></thead>
      <tbody>
        <?php if(empty($recent_txns)): ?>
        <tr><td colspan="5" class="text-center py-10 text-slate-400">Hakuna miamala bado</td></tr>
        <?php else: foreach($recent_txns as $t): ?>
        <tr class="hover:bg-slate-50">
          <td class="text-slate-500"><?= format_datetime($t->created_at) ?></td>
          <td><span class="font-semibold text-slate-700"><?= e(str_replace('_',' ', $t->type)) ?></span></td>
          <td><?= e($t->member_name ?? 'N/A') ?></td>
          <td class="font-semibold <?= str_contains($t->type,'disbursement')||str_contains($t->type,'expense') ? 'text-red-600' : 'text-emerald-600' ?>">
            <?= str_contains($t->type,'disbursement')||str_contains($t->type,'expense') ? '-' : '+' ?><?= money($t->amount) ?>
          </td>
          <td><?= e($t->payment_method) ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<script>
(function(){
  const ctx = document.getElementById('shares-chart');
  if (!ctx) return;
  const labels = <?= json_encode(array_column($share_monthly,'label')) ?>;
  const data   = <?= json_encode(array_map(fn($r)=>(float)$r->amount, $share_monthly)) ?>;
  new Chart(ctx, {
    type:'bar',
    data: { labels, datasets:[{ label:'Hisa (TZS)', data, backgroundColor:'rgba(37,99,235,0.15)', borderColor:'rgba(37,99,235,0.8)', borderWidth:2, borderRadius:8 }] },
    options:{ responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{ ticks:{ callback:v=>v.toLocaleString() } } } }
  });
})();
</script>
