<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="reportsPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Ripoti & Export</h1><p class="text-sm text-slate-500 mt-0.5">Pakua ripoti za kifedha na taarifa za wanachama kwa Excel/CSV</p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  <!-- Ledger CSV Export Card -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">📊</div>
      <div>
        <h3 class="font-bold text-slate-800">Ripoti ya Daftari la Fedha (Ledger CSV)</h3>
        <p class="text-xs text-slate-500">Miamala yote ya mapato, hisa, mikopo na faini</p>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div><label class="form-label">Kuanzia Tarehe</label><input x-model="from" type="date" class="form-input"></div>
      <div><label class="form-label">Mpaka Tarehe</label><input x-model="to" type="date" class="form-input"></div>
    </div>
    <a :href="'/export/ledger-csv?group_id=<?= $group_id ?>&from=' + (from||'') + '&to=' + (to||'')" target="_blank" class="w-full btn btn-primary justify-center">
      📥 Pakua Daftari Kuu (CSV)
    </a>
  </div>

  <!-- Member Statement CSV Export Card -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">👤</div>
      <div>
        <h3 class="font-bold text-slate-800">Taarifa ya Mwanachama (Member Statement)</h3>
        <p class="text-xs text-slate-500">Ripoti binafsi ya hisa na mikopo ya mwanachama mmoja</p>
      </div>
    </div>
    <div>
      <label class="form-label">Chagua Mwanachama</label>
      <select x-model="selectedMember" class="form-input">
        <option value="">-- Chagua Mwanachama --</option>
        <template x-for="m in members" :key="m.id"><option :value="m.id" x-text="m.full_name + ' (' + m.member_number + ')'"></option></template>
      </select>
    </div>
    <a :href="'/export/statement-csv?group_id=<?= $group_id ?>&member_id=' + selectedMember" target="_blank" class="w-full btn btn-success justify-center" :class="{'opacity-50 pointer-events-none': !selectedMember}">
      📥 Pakua Statement (CSV)
    </a>
  </div>

</div>
</div>
<script>
function reportsPage() {
  return {
    members:[], from:'', to:'', selectedMember:'',
    async load() {
      const m = await api('/api/members'); if(m) this.members = m.members;
    }
  }
}
</script>
