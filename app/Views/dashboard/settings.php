<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="settingsPage()" x-init="load()" style="max-width:800px;margin:0 auto">

  <!-- Header -->
  <div style="margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">⚙️ Mipangilio ya Kikundi</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Badilisha bei ya hisa, kiwango cha riba ya mkopo na kanuni za kikundi chako</p>
  </div>

  <!-- Form Card -->
  <div class="card card-p">
    <form @submit.prevent="saveSettings" style="display:flex;flex-direction:column;gap:1.25rem">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

        <div style="grid-column:1/-1">
          <label class="form-label">Jina la Kikundi *</label>
          <input x-model="form.name" type="text" required class="form-input" placeholder="Jina la kikundi">
        </div>

        <div>
          <label class="form-label">Bei ya Hisa Moja (TZS) *</label>
          <input x-model.number="form.share_price" type="number" step="100" min="100" required class="form-input">
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
          <p style="font-size:.7rem;color:rgba(255,255,255,.35);margin-top:.35rem">Mfano: 3 maana yake mkopo max ni mara 3 ya hisa za mwanachama.</p>
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

      <div style="padding-top:1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Mipangilio</span></template>
        </button>
      </div>

    </form>
  </div>

</div>

<script>
function settingsPage() {
  return {
    form: {
      name: '<?= e($group->name ?? '') ?>',
      share_price: <?= (float)($group->share_price ?? 1000) ?>,
      loan_interest_rate: <?= (float)($group->loan_interest_rate ?? 10) ?>,
      loan_interest_type: '<?= e($group->loan_interest_type ?? 'flat') ?>',
      max_loan_multiplier: <?= (int)($group->max_loan_multiplier ?? 3) ?>,
      max_loan_period: <?= (int)($group->max_loan_period ?? 12) ?>,
      social_fund_per_meeting: <?= (float)($group->social_fund_per_meeting ?? 1000) ?>,
    },
    saving: false,
    async load() {},
    async saveSettings() {
      this.saving = true;
      try {
        const res = await fetch('/api/settings/update', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          Swal.fire({ icon: 'success', title: 'Imesajiliwa!', text: 'Mipangilio imehifadhiwa vizuri.', confirmButtonColor: '#2563eb' });
        } else {
          Swal.fire({ icon: 'error', title: 'Hitilafu', text: d.message || 'Haikufanikiwa kuhifadhi.' });
        }
      } catch (err) {
        this.saving = false;
        Swal.fire({ icon: 'error', title: 'Hitilafu', text: 'Imeshindwa kuunganishwa na seva.' });
      }
    }
  }
}
</script>
