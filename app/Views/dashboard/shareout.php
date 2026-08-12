<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="shareoutPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Mgawanyo wa Mwaka (Share-Out)</h1><p class="text-sm text-slate-500 mt-0.5">Hesabu na ugawa hisa, faida ya riba, na faini kwa wanachama mwisho wa mzunguko</p></div>
  <?php if(in_array($user->role,['super_admin','group_admin','treasurer'])): ?>
  <button @click="showModal('finalize-shareout-modal')" class="btn-primary">🎯 Anzisha Mgawanyo Mpya</button>
  <?php endif; ?>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
  <h3 class="font-bold text-slate-800 text-lg mb-2">💡 Jinsi Mgawanyo Unavyofanya Kazi</h3>
  <p class="text-sm text-slate-600 leading-relaxed">
    Mfumo unajumlisha: <strong>(1) Pool ya Hisa Zote + (2) Faida ya Riba za Mikopo + (3) Faini Zilizolipwa</strong>, kisha unakata gharama za uendeshaji na kugawa kiasi kilichobaki kwa kila mwanachama kulingana na <em>idadi ya hisa zake</em>. Kama mwanachama ana mkopo hajamaliza, unakatwa kwenye mgawanyo wake.
  </p>
</div>

<!-- FINALIZE MODAL -->
<div id="finalize-shareout-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b"><h3 class="font-bold text-slate-800">Fanya Mgawanyo wa Kikundi</h3><button @click="hideModal('finalize-shareout-modal')" class="text-slate-400 text-xl">&times;</button></div>
    <form @submit.prevent="submitShareout" class="px-6 py-4 space-y-4">
      <div><label class="form-label">Tarehe ya Mgawanyo</label><input x-model="form.shareout_date" type="date" required class="form-input"></div>
      <div><label class="form-label">Gharama za Uendeshaji (Operating Costs - TZS)</label><input x-model.number="form.operating_costs" type="number" class="form-input" placeholder="0"></div>
      <div><label class="form-label">Akiba ya Madeni Mabaya (Bad Debt Provision - TZS)</label><input x-model.number="form.bad_debt_provision" type="number" class="form-input" placeholder="0"></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('finalize-shareout-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">🎯 Kaza & Hakiki Mgawanyo</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
function shareoutPage() {
  return {
    form:{operating_costs:0, bad_debt_provision:0}, saving:false,
    async load() {},
    async submitShareout() {
      if(await confirm_action('Thibitisha Mgawanyo', 'Je, una uhakika wa kufanya mgawanyo? Mchakato huu utahifadhi rekodi za mgawanyo wa wanachama wote.', 'Ndio, Tekeleza', '#2563eb')) {
        this.saving = true; const d = await api('/api/shareout/finalize','POST', this.form); this.saving = false;
        if(d) { hideModal('finalize-shareout-modal'); Swal.fire({icon:'success',title:'Mgawanyo umekamilika!',text:'Pakua CSV kupitia ukurasa wa ripoti.',confirmButtonColor:'#2563eb'}); }
      }
    }
  }
}
</script>
