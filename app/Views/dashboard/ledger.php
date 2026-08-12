<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="ledgerPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Daftari kuu la Fedha (General Ledger)</h1><p class="text-sm text-slate-500 mt-0.5">Orodha kamili ya miamala yote ya mapato na matumizi ya kikundi</p></div>
  <div class="flex gap-2">
    <?php if(in_array($user->role,['super_admin','group_admin','treasurer'])): ?>
    <button @click="showModal('add-expense-modal')" class="btn-danger">➕ Rekodi Gharama / Matumizi</button>
    <?php endif; ?>
    <a href="/export/ledger-csv?group_id=<?= $group_id ?>" target="_blank" class="btn-secondary">📥 Pakua CSV</a>
  </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead class="bg-slate-50 border-b"><tr class="text-xs font-semibold text-slate-500 uppercase"><th class="py-3 px-4">Namba</th><th class="py-3 px-4">Tarehe</th><th class="py-3 px-4">Aina</th><th class="py-3 px-4">Mwanachama</th><th class="py-3 px-4 text-right">Kiasi</th><th class="py-3 px-4">Njia</th><th class="py-3 px-4">Maelezo</th></tr></thead>
      <tbody class="divide-y divide-slate-100 text-sm">
        <template x-if="txns.length === 0"><tr><td colspan="7" class="text-center py-12 text-slate-400">Hakuna miamala iliyorekodiwa</td></tr></template>
        <template x-for="t in txns" :key="t.id">
          <tr class="hover:bg-slate-50">
            <td class="py-3 px-4 font-mono text-xs text-slate-600" x-text="t.transaction_code"></td>
            <td class="py-3 px-4 text-slate-500 text-xs" x-text="t.created_at"></td>
            <td class="py-3 px-4 font-semibold text-slate-800" x-text="t.type"></td>
            <td class="py-3 px-4 text-slate-700" x-text="t.member_name||'—'"></td>
            <td class="py-3 px-4 text-right font-bold" :class="['loan_disbursement','expense'].includes(t.type)?'text-red-600':'text-emerald-600'" x-text="(['loan_disbursement','expense'].includes(t.type)?'-':'+') + money(t.amount)"></td>
            <td class="py-3 px-4 text-xs text-slate-600" x-text="t.payment_method"></td>
            <td class="py-3 px-4 text-xs text-slate-500" x-text="t.description"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<div id="add-expense-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b"><h3 class="font-bold text-slate-800">Rekodi Matumizi / Gharama</h3><button @click="hideModal('add-expense-modal')" class="text-slate-400 text-xl">&times;</button></div>
    <form @submit.prevent="submitExpense" class="px-6 py-4 space-y-4">
      <div><label class="form-label">Maelezo ya Matumizi *</label><input x-model="expenseForm.description" type="text" required class="form-input" placeholder="Mfano: Nauli ya katibu, daftari..."></div>
      <div><label class="form-label">Kiasi (TZS) *</label><input x-model.number="expenseForm.amount" type="number" min="500" required class="form-input" placeholder="5000"></div>
      <div><label class="form-label">Njia ya Malipo</label><select x-model="expenseForm.payment_method" class="form-input"><option value="cash">Pesa Taslimu</option><option value="mobile_money">Mobile Money</option><option value="bank">Bank</option></select></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('add-expense-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">💾 Hifadhi Matumizi</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
function ledgerPage() {
  return {
    txns:[], expenseForm:{payment_method:'cash'}, saving:false,
    async load() {
      const d = await api('/api/reports/summary'); if(d) this.txns = d.recent_txns||[];
    },
    async submitExpense() {
      this.saving = true; const d = await api('/api/ledger/expense','POST', this.expenseForm); this.saving = false;
      if(d) { hideModal('add-expense-modal'); this.expenseForm={payment_method:'cash'}; this.load(); Swal.fire({icon:'success',title:'Gharama Imerekodiwa!',confirmButtonColor:'#2563eb'}); }
    }
  }
}
</script>
