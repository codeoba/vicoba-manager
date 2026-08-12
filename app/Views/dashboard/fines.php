<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="finesPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Usimamizi wa Faini</h1><p class="text-sm text-slate-500 mt-0.5">Toa faini na urekodi malipo ya faini za nidhamu au mikutano</p></div>
  <?php if(in_array($user->role,['super_admin','group_admin','secretary','treasurer'])): ?>
  <button @click="showModal('issue-fine-modal')" class="btn btn-primary">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>Toa Faini Mpya</span>
  </button>
  <?php endif; ?>
</div>

<div class="grid grid-cols-2 gap-4 mb-6">
  <div class="stat-card"><p class="text-xs font-semibold text-slate-400 uppercase">Jumla ya Faini Hazijalipwa</p><p class="text-3xl font-extrabold text-red-600 mt-1" x-text="money(pendingTotal)"></p></div>
  <div class="stat-card"><p class="text-xs font-semibold text-slate-400 uppercase">Faini Zilizolipwa</p><p class="text-3xl font-extrabold text-emerald-600 mt-1" x-text="money(paidTotal)"></p></div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead class="bg-slate-50 border-b">
        <tr class="text-xs font-semibold text-slate-500 uppercase">
          <th class="py-3.5 px-4">Mwanachama</th><th class="py-3.5 px-4">Sababu / Aina</th><th class="py-3.5 px-4 text-right">Kiasi</th><th class="py-3.5 px-4 text-center">Hali</th><th class="py-3.5 px-4">Tarehe</th><th class="py-3.5 px-4 text-right">Vitendo</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm">
        <template x-if="fines.length === 0"><tr><td colspan="6" class="text-center py-12 text-slate-400">Hakuna faini zilizorekodiwa</td></tr></template>
        <template x-for="f in fines" :key="f.id">
          <tr class="hover:bg-slate-50">
            <td class="py-3.5 px-4 font-semibold text-slate-800" x-text="f.member_name"></td>
            <td class="py-3.5 px-4 text-slate-600" x-text="f.reason || f.fine_type_name"></td>
            <td class="py-3.5 px-4 text-right font-bold text-red-600" x-text="money(f.amount)"></td>
            <td class="py-3.5 px-4 text-center">
              <span class="px-2.5 py-1 rounded-full text-xs font-semibold border" :class="f.status==='paid'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'" x-text="f.status==='paid'?'Imelipwa':'Inasubiri'"></span>
            </td>
            <td class="py-3.5 px-4 text-xs text-slate-500" x-text="f.created_at"></td>
            <td class="py-3.5 px-4 text-right">
              <template x-if="f.status==='pending' && canManage">
                <button @click="payFine(f)" class="btn-success text-xs py-1 px-2.5">💵 Lipa Faini</button>
              </template>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<div id="issue-fine-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b"><h3 class="font-bold text-slate-800">Toa Faini Mpya</h3><button @click="hideModal('issue-fine-modal')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button></div>
    <form @submit.prevent="submitIssueFine" class="px-6 py-4 space-y-4">
      <div>
        <label class="form-label">Mwanachama *</label>
        <select x-model="form.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in members" :key="m.id"><option :value="m.id" x-text="m.full_name"></option></template>
        </select>
      </div>
      <div>
        <label class="form-label">Sababu *</label>
        <input x-model="form.reason" type="text" required class="form-input" placeholder="Mfano: Kuchelewa mkutano">
      </div>
      <div>
        <label class="form-label">Kiasi cha Faini (TZS) *</label>
        <input x-model.number="form.amount" type="number" min="500" required class="form-input" placeholder="1000">
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('issue-fine-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">💾 Toa Faini</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
function finesPage() {
  return {
    fines:[], members:[], form:{}, saving:false,
    get canManage() { return ['super_admin','group_admin','treasurer','secretary'].includes(APP.role); },
    get pendingTotal() { return this.fines.filter(f=>f.status==='pending').reduce((a,b)=>a+Number(b.amount),0); },
    get paidTotal() { return this.fines.filter(f=>f.status==='paid').reduce((a,b)=>a+Number(b.amount),0); },
    async load() {
      const d = await api('/api/fines'); if(d) this.fines = d.fines;
      const m = await api('/api/members'); if(m) this.members = m.members;
    },
    async submitIssueFine() {
      this.saving = true; const d = await api('/api/fines/issue','POST', this.form); this.saving = false;
      if(d) { hideModal('issue-fine-modal'); this.form={}; this.load(); Swal.fire({icon:'success',title:'Faini imetolewa!',confirmButtonColor:'#2563eb'}); }
    },
    async payFine(f) {
      if(await confirm_action('Thibitisha Malipo', `Lipa faini ya ${money(f.amount)} kwa ${f.member_name}?`, 'Ndio, Lipa', '#16a34a')) {
        const d = await api('/api/fines/pay','POST',{fine_id: f.id, payment_method:'cash'});
        if(d) { this.load(); Swal.fire({icon:'success',title:'Imelipwa!',confirmButtonColor:'#16a34a'}); }
      }
    }
  }
}
</script>
