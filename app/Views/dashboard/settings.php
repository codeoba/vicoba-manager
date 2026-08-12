<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="settingsPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Mipangilio ya Kikundi</h1><p class="text-sm text-slate-500 mt-0.5">Badilisha bei ya hisa, kiwango cha riba ya mkopo na kanuni za kikundi</p></div>
</div>

<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 max-w-3xl">
  <form @submit.prevent="saveSettings" class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="form-label">Jina la Kikundi *</label>
        <input x-model="form.name" type="text" required class="form-input">
      </div>
      <div>
        <label class="form-label">Bei ya Hisa Moja (TZS) *</label>
        <input x-model.number="form.share_price" type="number" step="500" min="500" required class="form-input">
      </div>
      <div>
        <label class="form-label">Riba ya Mkopo (%) *</label>
        <input x-model.number="form.loan_interest_rate" type="number" step="0.5" min="0" required class="form-input">
      </div>
      <div>
        <label class="form-label">Aina ya Riba</label>
        <select x-model="form.loan_interest_type" class="form-input">
          <option value="flat">Flat Interest (Riba ya Mwanzo)</option>
          <option value="reducing_balance">Reducing Balance (Riba Inayopungua)</option>
        </select>
      </div>
      <div>
        <label class="form-label">Kiwango cha Juu cha Mkopo (Multiplier)</label>
        <input x-model.number="form.max_loan_multiplier" type="number" min="1" max="10" class="form-input">
        <p class="text-xs text-slate-400 mt-1">Mfano: 3 maana yake mkopo max ni mara 3 ya hisa za mwanachama.</p>
      </div>
      <div>
        <label class="form-label">Muda wa Juu wa Mkopo (Miezi)</label>
        <input x-model.number="form.max_loan_period" type="number" min="1" max="36" class="form-input">
      </div>
      <div>
        <label class="form-label">Mchango wa Mfuko wa Jamii (kwa mkutano)</label>
        <input x-model.number="form.social_fund_per_meeting" type="number" min="0" class="form-input">
      </div>
    </div>

    <div class="pt-4 border-t flex justify-end">
      <button type="submit" class="btn-primary" :disabled="saving">
        <template x-if="saving">⏳ Inahifadhi...</template>
        <template x-if="!saving">💾 Hifadhi Mipangilio</template>
      </button>
    </div>
  </form>
</div>
</div>
<script>
function settingsPage() {
  return {
    form:{
      name: '<?= e($group->name ?? '') ?>',
      share_price: <?= (float)($group->share_price ?? 1000) ?>,
      loan_interest_rate: <?= (float)($group->loan_interest_rate ?? 10) ?>,
      loan_interest_type: '<?= e($group->loan_interest_type ?? 'flat') ?>',
      max_loan_multiplier: <?= (int)($group->max_loan_multiplier ?? 3) ?>,
      max_loan_period: <?= (int)($group->max_loan_period ?? 12) ?>,
      social_fund_per_meeting: <?= (float)($group->social_fund_per_meeting ?? 1000) ?>,
    },
    saving:false,
    async load() {},
    async saveSettings() {
      this.saving = true; const d = await api('/api/settings/update','POST', this.form); this.saving = false;
      if(d) { Swal.fire({icon:'success',title:'Imesahirishwa!',text:'Mipangilio imehifadhiwa.',confirmButtonColor:'#2563eb'}); }
    }
  }
}
</script>
