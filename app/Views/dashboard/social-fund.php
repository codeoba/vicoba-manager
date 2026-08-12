<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="socialFundPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Mfuko wa Jamii (Social Fund)</h1><p class="text-sm text-slate-500 mt-0.5">Michango na usaidizi wa majanga, msiba au sherehe za wanachama</p></div>
  <div class="flex gap-2">
    <button @click="showModal('contribute-modal')" class="btn-primary">❤️ Weka Mchango</button>
    <button @click="showModal('request-modal')" class="btn-secondary">🆘 Omba Usaidizi</button>
  </div>
</div>

<div class="bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-2xl p-6 shadow-lg mb-6">
  <p class="text-xs font-semibold uppercase tracking-wider text-rose-100">Salio la Mfuko wa Jamii</p>
  <p class="text-4xl font-extrabold mt-1" x-text="money(balance)"></p>
  <p class="text-xs text-rose-100 mt-2">Pesa hii inalinda wanachama wakati wa dharura</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="px-5 py-4 border-b"><h3 class="font-bold text-slate-800">Historia ya Mfuko wa Jamii</h3></div>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead class="bg-slate-50 border-b"><tr class="text-xs font-semibold text-slate-500 uppercase"><th class="py-3 px-4">Tarehe</th><th class="py-3 px-4">Aina</th><th class="py-3 px-4">Mwanachama</th><th class="py-3 px-4">Sababu</th><th class="py-3 px-4 text-right">Kiasi</th></tr></thead>
      <tbody class="divide-y divide-slate-100 text-sm">
        <template x-for="r in records" :key="r.id">
          <tr class="hover:bg-slate-50">
            <td class="py-3 px-4 text-slate-500 text-xs" x-text="r.created_at"></td>
            <td class="py-3 px-4 font-semibold" :class="r.type==='contribution'?'text-emerald-600':'text-rose-600'" x-text="r.type==='contribution'?'Mchango':'Usaidizi / Disburse'"></td>
            <td class="py-3 px-4 font-medium" x-text="r.member_name||'—'"></td>
            <td class="py-3 px-4 text-slate-600" x-text="r.reason||'—'"></td>
            <td class="py-3 px-4 text-right font-bold" :class="r.type==='contribution'?'text-emerald-600':'text-rose-600'" x-text="(r.type==='contribution'?'+':'-') + money(r.amount)"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- CONTRIBUTE MODAL -->
<div id="contribute-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b"><h3 class="font-bold text-slate-800">Rekodi Mchango wa Jamii</h3><button @click="hideModal('contribute-modal')" class="text-slate-400 text-xl">&times;</button></div>
    <form @submit.prevent="submitContribute" class="px-6 py-4 space-y-4">
      <div>
        <label class="form-label">Mwanachama</label>
        <select x-model="contribForm.member_id" class="form-input">
          <option value="">-- Mchango wa Kikundi / Mwanachama --</option>
          <template x-for="m in members" :key="m.id"><option :value="m.id" x-text="m.full_name"></option></template>
        </select>
      </div>
      <div><label class="form-label">Kiasi (TZS) *</label><input x-model.number="contribForm.amount" type="number" min="500" required class="form-input" placeholder="1000"></div>
      <div><label class="form-label">Sababu</label><input x-model="contribForm.reason" type="text" class="form-input" placeholder="Mchango wa mwezi"></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('contribute-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">💾 Hifadhi Mchango</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
function socialFundPage() {
  return {
    balance:0, records:[], members:[], contribForm:{}, saving:false,
    async load() {
      const d = await api('/api/social-fund'); if(d) { this.balance = d.balance; this.records = d.records; }
      const m = await api('/api/members'); if(m) this.members = m.members;
    },
    async submitContribute() {
      this.saving = true; const d = await api('/api/social-fund/contribute','POST', this.contribForm); this.saving = false;
      if(d) { hideModal('contribute-modal'); this.contribForm={}; this.load(); Swal.fire({icon:'success',title:'Mchango umehifadhiwa!',confirmButtonColor:'#2563eb'}); }
    }
  }
}
</script>
