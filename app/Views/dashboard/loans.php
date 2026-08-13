<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="loansPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">🏦 Usimamizi wa Mikopo</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Omba, idhinisha, toa na urekodi marejesho ya mikopo ya wanachama</p>
  </div>
  <button @click="showModal('apply-loan-modal')" class="btn btn-primary">
    <span>➕ Omba Mkopo Mpya</span>
  </button>
</div>

<!-- Filter Tabs -->
<div style="display:flex;gap:.5rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;overflow-x:auto">
  <button @click="statusFilter='all'" :class="statusFilter==='all'?'badge badge-info':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">Zote (<span x-text="loans.length"></span>)</button>
  <button @click="statusFilter='active'" :class="statusFilter==='active'?'badge badge-success':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">Inayoendelea (<span x-text="loans.filter(l=>l.status==='active').length"></span>)</button>
  <button @click="statusFilter='pending'" :class="statusFilter==='pending'?'badge badge-warning':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">Inayoidhinishwa (<span x-text="loans.filter(l=>l.status.startsWith('pending')).length"></span>)</button>
  <button @click="statusFilter='overdue'" :class="statusFilter==='overdue'?'badge badge-danger':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">Iliyochelewa ⚠️ (<span x-text="loans.filter(l=>l.status==='overdue').length"></span>)</button>
  <button @click="statusFilter='completed'" :class="statusFilter==='completed'?'badge badge-muted':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">Iliyoisha (<span x-text="loans.filter(l=>l.status==='completed').length"></span>)</button>
</div>

<!-- Table Card -->
<div class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Namba</th>
          <th>Mwanachama</th>
          <th style="text-align:right">Mtaji</th>
          <th style="text-align:right">Salio</th>
          <th style="text-align:center">Hali</th>
          <th>Mwisho WA Kulipa</th>
          <th style="text-align:right">Vitendo</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading">
          <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr>
        </template>
        <template x-if="!loading && filtered.length===0">
          <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna mikopo kwa hali hii</td></tr>
        </template>
        <template x-for="l in filtered" :key="l.id">
          <tr>
            <td style="font-family:monospace;font-size:.78rem;color:rgba(255,255,255,.5)" x-text="l.loan_code"></td>
            <td>
              <div style="font-weight:700;color:#fff;font-size:.85rem" x-text="l.member_name"></div>
              <div style="font-size:.68rem;color:rgba(255,255,255,.35)" x-text="l.interest_type + ' @ ' + l.interest_rate + '%'"></div>
            </td>
            <td style="text-align:right;font-weight:800;color:#fff" x-text="money(l.principal_amount)"></td>
            <td style="text-align:right;font-weight:800;color:#fbbf24" x-text="money(l.balance_remaining)"></td>
            <td style="text-align:center">
              <span class="badge" :class="statusBadgeClass(l.status)" x-text="l.status"></span>
            </td>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="l.due_date||'—'"></td>
            <td style="text-align:right">
              <div style="display:flex;gap:.375rem;justify-content:flex-end">
                <button @click="viewSchedule(l)" class="btn btn-secondary btn-sm" title="Ratiba">📅</button>
                <template x-if="l.status==='active'||l.status==='overdue'">
                  <button @click="repayModal(l)" class="btn btn-success btn-sm">💵 Rejesho</button>
                </template>
                <template x-if="l.status.startsWith('pending')">
                  <button @click="disburseModal(l)" class="btn btn-primary btn-sm">✅ Toa</button>
                </template>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- APPLY LOAN MODAL -->
<div id="apply-loan-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Omba Mkopo Mpya</div>
      <button @click="hideModal('apply-loan-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="applyLoan" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Mwanachama Anayeomba *</label>
        <select x-model="form.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in members" :key="m.id">
            <option :value="m.id" x-text="m.full_name + ' (' + m.member_number + ')'"></option>
          </template>
        </select>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
          <label class="form-label">Kiasi cha Mkopo (TZS) *</label>
          <input x-model.number="form.principal_amount" type="number" step="1000" min="5000" required class="form-input">
        </div>
        <div>
          <label class="form-label">Muda wa Marejesho (Miezi) *</label>
          <input x-model.number="form.repayment_months" type="number" min="1" max="36" required class="form-input">
        </div>
      </div>

      <div>
        <label class="form-label">Dhumuni la Mkopo</label>
        <input x-model="form.purpose" type="text" class="form-input" placeholder="Mfano: Biashara ya duka">
      </div>

      <div class="modal-footer">
        <button type="button" @click="hideModal('apply-loan-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inatuma...</span></template>
          <template x-if="!saving"><span>🚀 Wasilisha Ombi</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- REPAY LOAN MODAL -->
<div id="repay-loan-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Rekodi Rejesho la Mkopo</div>
      <button @click="hideModal('repay-loan-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="submitRepayment" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Kiasi Kinacholipwa (TZS) *</label>
        <input x-model.number="repayForm.amount_paid" type="number" step="500" min="1" required class="form-input">
      </div>
      <div>
        <label class="form-label">Njia ya Malipo</label>
        <select x-model="repayForm.payment_method" class="form-input">
          <option value="cash">Pesa Taslimu (Cash)</option>
          <option value="mobile_money">Mobile Money (Simu)</option>
          <option value="bank">Benki</option>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" @click="hideModal('repay-loan-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Rejesho</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function loansPage() {
  return {
    loans:[], members:[], statusFilter:'all', form:{repayment_months:6}, repayForm:{payment_method:'cash'}, selectedLoan:null, saving:false, loading:true,
    get filtered() {
      if (this.statusFilter === 'all') return this.loans;
      if (this.statusFilter === 'pending') return this.loans.filter(l => l.status.startsWith('pending'));
      return this.loans.filter(l => l.status === this.statusFilter);
    },
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/loans').then(r=>r.json());
        if (d && d.loans) this.loans = d.loans;
        const m = await fetch('/api/members').then(r=>r.json());
        if (m && m.members) this.members = m.members;
      } catch(e) {}
      this.loading = false;
    },
    async applyLoan() {
      this.saving = true;
      try {
        const res = await fetch('/api/loans/apply', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.form) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('apply-loan-modal').style.display = 'none';
          this.form = {repayment_months:6}; this.load();
          Swal.fire({icon:'success', title:'Ombi limepokelewa!', text:'Ombi la mkopo limewasilishwa.', confirmButtonColor:'#2563eb'});
        }
      } catch(e) { this.saving = false; }
    },
    repayModal(l) { this.selectedLoan = l; this.repayForm.amount_paid = l.monthly_installment||l.balance_remaining; document.getElementById('repay-loan-modal').style.display = 'flex'; },
    async submitRepayment() {
      this.saving = true;
      try {
        const res = await fetch('/api/loans/repay', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...this.repayForm, loan_id: this.selectedLoan.id}) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('repay-loan-modal').style.display = 'none';
          this.load();
          Swal.fire({icon:'success', title:'Imelipwa!', text:'Rejesho limerekodiwa.', confirmButtonColor:'#2563eb'});
        }
      } catch(e) { this.saving = false; }
    },
    async disburseModal(l) {
      if (confirm('Je, una uhakika wa kutoa mkopo kwa ' + l.member_name + '?')) {
        const res = await fetch('/api/loans/disburse', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({loan_id: l.id}) });
        const d = await res.json();
        if (d && d.success) { this.load(); Swal.fire({icon:'success', title:'Umetolewa!', text:'Mkopo umetolewa kikamilifu.', confirmButtonColor:'#16a34a'}); }
      }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; },
    money(v) { return 'TZS ' + Number(v||0).toLocaleString(); },
    statusBadgeClass(s) {
      return { active:'badge-success', overdue:'badge-danger', completed:'badge-muted', rejected:'badge-muted' }[s] || 'badge-warning';
    }
  }
}
</script>
