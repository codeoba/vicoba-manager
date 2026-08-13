<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="ledgerPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">📖 Daftari kuu la Fedha (General Ledger)</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Orodha kamili ya miamala yote ya mapato na matumizi ya kikundi</p>
  </div>
  <div style="display:flex;gap:.5rem">
    <?php if(in_array($user->role,['super_admin','group_admin','treasurer'])): ?>
    <button @click="showModal('add-expense-modal')" class="btn btn-danger">➕ Rekodi Gharama / Matumizi</button>
    <?php endif; ?>
    <a href="/export/ledger-csv?group_id=<?= $group_id ?>" target="_blank" class="btn btn-secondary">📥 Pakua CSV</a>
  </div>
</div>

<!-- Table Card -->
<div class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Namba</th>
          <th>Tarehe</th>
          <th>Aina</th>
          <th>Mwanachama</th>
          <th style="text-align:right">Kiasi</th>
          <th>Njia</th>
          <th>Maelezo</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading"><tr><td colspan="7" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
        <template x-if="!loading && txns.length === 0"><tr><td colspan="7" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna miamala iliyorekodiwa</td></tr></template>
        <template x-for="t in txns" :key="t.id">
          <tr>
            <td style="font-family:monospace;font-size:.78rem;color:rgba(255,255,255,.5)" x-text="t.transaction_code"></td>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="t.created_at"></td>
            <td style="font-weight:700;color:#fff" x-text="t.type"></td>
            <td style="color:rgba(255,255,255,.8)" x-text="t.member_name||'—'"></td>
            <td style="text-align:right;font-weight:800" :style="['loan_disbursement','expense'].includes(t.type)?'color:#f87171':'color:#4ade80'" x-text="(['loan_disbursement','expense'].includes(t.type)?'-':'+') + money(t.amount)"></td>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="t.payment_method"></td>
            <td style="color:rgba(255,255,255,.6);font-size:.78rem" x-text="t.description"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD EXPENSE MODAL -->
<div id="add-expense-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Rekodi Matumizi / Gharama</div>
      <button @click="hideModal('add-expense-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="submitExpense" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Aina / Maelezo ya Matumizi *</label>
        <select x-model="expenseForm.description" required class="form-input">
          <option value="">-- Chagua Aina ya Matumizi --</option>
          <option value="Stationery na Nakala">Stationery na Nakala (Vitabu, Kalamu, Print)</option>
          <option value="Usafiri wa Mikutano">Usafiri wa Mikutano</option>
          <option value="Chai na Vinywaji Mkutanon">Chai na Vinywaji Mkutanon</option>
          <option value="Kodi ya Ukumbi / Pango">Kodi ya Ukumbi / Pango</option>
          <option value="Gharama za Simu na SMS">Gharama za Simu na SMS</option>
          <option value="Gharama za Benki">Gharama za Benki</option>
          <option value="Matumizi Mengineyo">Matumizi Mengineyo</option>
        </select>
      </div>

      <div>
        <label class="form-label">Kiasi cha Gharama (TZS) *</label>
        <input x-model.number="expenseForm.amount" type="number" min="100" required class="form-input">
      </div>

      <div>
        <label class="form-label">Njia ya Malipo</label>
        <select x-model="expenseForm.payment_method" class="form-input">
          <option value="cash">Pesa Taslimu (Cash)</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="bank">Benki</option>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" @click="hideModal('add-expense-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Matumizi</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function ledgerPage() {
  return {
    txns:[], expenseForm:{payment_method:'cash'}, saving:false, loading:true,
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/reports/summary').then(r=>r.json());
        if(d && d.recent_txns) this.txns = d.recent_txns;
      } catch(e) {}
      this.loading = false;
    },
    async submitExpense() {
      this.saving = true;
      try {
        const res = await fetch('/api/ledger/expense', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.expenseForm) });
        const d = await res.json();
        this.saving = false;
        if(d && d.success) {
          document.getElementById('add-expense-modal').style.display = 'none';
          this.expenseForm = {payment_method:'cash'}; this.load();
          Swal.fire({icon:'success',title:'Imerekodiwa!',text:'Gharama imerekodiwa kikamilifu.',confirmButtonColor:'#2563eb'});
        }
      } catch(e) { this.saving = false; }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; },
    money(v) { return 'TZS ' + Number(v||0).toLocaleString(); }
  }
}
</script>
