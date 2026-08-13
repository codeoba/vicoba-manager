<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="collateralPage()" x-init="load()" class="space-y-6">

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-slate-800">Dhamana & Nyaraka za Mikopo (Collateral Vault)</h1>
    <p class="text-sm text-slate-500 mt-0.5">Usimamizi na uhakiki wa dhamana zilizowekwa kwa mikopo ya wanachama</p>
  </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="table-auto w-full">
      <thead>
        <tr><th>Namba ya Mkopo</th><th>Mwanachama</th><th>Kiasi cha Mkopo</th><th>Dhumuni / Dhamana</th><th class="text-right">Hali ya Uthibitisho</th></tr>
      </thead>
      <tbody>
        <template x-if="items.length===0"><tr><td colspan="5" class="text-center py-10 text-slate-400">Hakuna dhamana zilizorekodiwa bado</td></tr></template>
        <template x-for="item in items" :key="item.loan_id">
          <tr class="hover:bg-slate-50">
            <td class="font-mono text-xs font-bold text-slate-700" x-text="item.loan_code"></td>
            <td class="font-semibold text-slate-800" x-text="item.member_name"></td>
            <td class="font-bold text-blue-600" x-text="money(item.principal_amount)"></td>
            <td class="text-slate-600 text-sm" x-text="item.purpose"></td>
            <td class="text-right"><span class="badge bg-emerald-50 text-emerald-700 border-emerald-200 text-xs">✅ IMETHIBITISHWA</span></td>
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
    items:[],
    async load() {
      const d = await api('/api/loans'); if(d) this.items = d.loans || [];
    }
  }
}
</script>
