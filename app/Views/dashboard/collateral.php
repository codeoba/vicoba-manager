<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="collateralPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">📁 Dhamana & Nyaraka za Mikopo (Collateral Vault)</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Usimamizi na uhakiki wa dhamana zilizowekwa kwa mikopo ya wanachama</p>
  </div>
</div>

<!-- Table Card -->
<div class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Namba ya Mkopo</th>
          <th>Mwanachama</th>
          <th>Kiasi cha Mkopo</th>
          <th>Dhumuni / Dhamana</th>
          <th style="text-align:right">Hali ya Uthibitisho</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading"><tr><td colspan="5" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
        <template x-if="!loading && items.length===0"><tr><td colspan="5" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna dhamana zilizorekodiwa bado</td></tr></template>
        <template x-for="item in items" :key="item.id">
          <tr>
            <td style="font-family:monospace;font-size:.78rem;color:rgba(255,255,255,.5)" x-text="item.loan_code"></td>
            <td style="font-weight:700;color:#fff" x-text="item.member_name"></td>
            <td style="font-weight:800;color:#60a5fa" x-text="money(item.principal_amount)"></td>
            <td style="color:rgba(255,255,255,.7);font-size:.83rem" x-text="item.purpose||'Dhamana ya Akiba'"></td>
            <td style="text-align:right"><span class="badge badge-success">✅ IMETHIBITISHWA</span></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

</div>

<script>
function collateralPage() {
  return {
    items: [], loading: true,
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/loans').then(r=>r.json());
        if (d && d.loans) this.items = d.loans;
      } catch(e) {}
      this.loading = false;
    },
    money(v) { return 'TZS ' + Number(v||0).toLocaleString(); }
  }
}
</script>
