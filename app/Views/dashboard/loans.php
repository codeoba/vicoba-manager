<?php
$group_id = (int)($group->id ?? 0);
?>
<div x-data="loansPage()" x-init="load()">

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-extrabold text-slate-800">Usimamizi wa Mikopo</h1>
    <p class="text-sm text-slate-500 mt-0.5">Omba, idhinisha, toa na urekodi marejesho ya mikopo</p>
  </div>
  <div class="flex items-center gap-2">
    <button @click="showModal('apply-loan-modal')" class="btn-primary">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Omba Mkopo Mpya
    </button>
  </div>
</div>

<!-- Tabs for status filtering -->
<div class="flex items-center gap-2 border-b border-slate-200 mb-6 overflow-x-auto">
  <button @click="statusFilter='all'" :class="statusFilter==='all'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">Zote (<span x-text="loans.length"></span>)</button>
  <button @click="statusFilter='active'" :class="statusFilter==='active'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">Inayoendelea (<span x-text="loans.filter(l=>l.status==='active').length"></span>)</button>
  <button @click="statusFilter='pending'" :class="statusFilter==='pending'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">Inayoidhinishwa (<span x-text="loans.filter(l=>l.status.startsWith('pending')).length"></span>)</button>
  <button @click="statusFilter='overdue'" :class="statusFilter==='overdue'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">Iliyochelewa ⚠️ (<span x-text="loans.filter(l=>l.status==='overdue').length"></span>)</button>
  <button @click="statusFilter='completed'" :class="statusFilter==='completed'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">Iliyoisha (<span x-text="loans.filter(l=>l.status==='completed').length"></span>)</button>
</div>

<!-- Loans Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead class="bg-slate-50 border-b">
        <tr class="text-xs font-semibold text-slate-500 uppercase">
          <th class="py-3.5 px-4">Namba</th>
          <th class="py-3.5 px-4">Mwanachama</th>
          <th class="py-3.5 px-4 text-right">Kiasi</th>
          <th class="py-3.5 px-4 text-right">Salio</th>
          <th class="py-3.5 px-4 text-center">Hali</th>
          <th class="py-3.5 px-4">Mwisho wa Kulipa</th>
          <th class="py-3.5 px-4 text-right">Vitendo</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm">
        <template x-if="filtered.length === 0">
          <tr><td colspan="7" class="text-center py-12 text-slate-400">Hakuna mikopo kwa hali hii</td></tr>
        </template>
        <template x-for="l in filtered" :key="l.id">
          <tr class="hover:bg-slate-50">
            <td class="py-3.5 px-4 font-mono text-xs text-slate-600" x-text="l.loan_code"></td>
            <td class="py-3.5 px-4 font-semibold text-slate-800">
              <p x-text="l.member_name"></p>
              <p class="text-xs text-slate-400 font-normal" x-text="l.member_phone||''"></p>
            </td>
            <td class="py-3.5 px-4 text-right font-bold text-slate-700" x-text="money(l.principal_amount)"></td>
            <td class="py-3.5 px-4 text-right font-bold text-amber-600" x-text="money(l.balance_remaining)"></td>
            <td class="py-3.5 px-4 text-center">
              <span class="px-2.5 py-1 rounded-full text-xs font-semibold border"
                :class="statusClass(l.status)" x-text="l.status"></span>
            </td>
            <td class="py-3.5 px-4 text-xs text-slate-500" x-text="l.due_date||'—'"></td>
            <td class="py-3.5 px-4 text-right">
              <div class="flex items-center justify-end gap-1.5">
                <button @click="viewSchedule(l)" class="btn-secondary text-xs py-1 px-2.5" title="Ratiba">📅 Schedule</button>

                <!-- Repay button for active/overdue -->
                <template x-if="['active','overdue'].includes(l.status) && canManage">
                  <button @click="repayModal(l)" class="btn-primary text-xs py-1 px-2.5">💵 Lipa</button>
                </template>

                <!-- Disburse button for pending -->
                <template x-if="['pending_treasurer','pending_chairman'].includes(l.status) && canManage">
                  <button @click="disburseModal(l)" class="btn-success text-xs py-1 px-2.5">✅ Toa Mkopo</button>
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
<div id="apply-loan-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h3 class="font-bold text-slate-800">Wasilisha Ombi la Mkopo</h3>
      <button @click="hideModal('apply-loan-modal')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
    </div>
    <form @submit.prevent="applyLoan" class="px-6 py-4 space-y-4">
      <div>
        <label class="form-label">Mwanachama *</label>
        <select x-model="form.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in members" :key="m.id">
            <option :value="m.id" x-text="m.full_name + ' (' + m.member_number + ')'"></option>
          </template>
        </select>
      </div>
      <div>
        <label class="form-label">Kiasi cha Mkopo (TZS) *</label>
        <input x-model.number="form.principal_amount" type="number" step="1000" min="1000" required class="form-input" placeholder="Mfano: 500000">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="form-label">Muda (Miezi)</label>
          <input x-model.number="form.repayment_months" type="number" min="1" max="24" class="form-input" value="6">
        </div>
        <div>
          <label class="form-label">Riba (%)</label>
          <input x-model.number="form.interest_rate" type="number" step="0.5" class="form-input" value="<?= (float)($group->loan_interest_rate ?? 10) ?>">
        </div>
      </div>
      <div>
        <label class="form-label">Dhumuni la Mkopo</label>
        <textarea x-model="form.purpose" rows="2" class="form-input" placeholder="Mfano: Biashara ya duka, kilimo..."></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('apply-loan-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">
          <template x-if="saving">⏳ Inawasilisha...</template>
          <template x-if="!saving">🚀 Wasilisha Ombi</template>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- REPAYMENT MODAL -->
<div id="repay-loan-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h3 class="font-bold text-slate-800">Rekodi Rejesho la Mkopo</h3>
      <button @click="hideModal('repay-loan-modal')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
    </div>
    <form @submit.prevent="submitRepayment" class="px-6 py-4 space-y-4">
      <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-xs text-amber-800 space-y-1">
        <p><strong>Mkopo:</strong> <span x-text="selectedLoan?.loan_code"></span> (<span x-text="selectedLoan?.member_name"></span>)</p>
        <p><strong>Salio la Sasa:</strong> <span class="font-bold text-red-600" x-text="money(selectedLoan?.balance_remaining)"></span></p>
      </div>
      <div>
        <label class="form-label">Kiasi Kinacholipwa (TZS) *</label>
        <input x-model.number="repayForm.amount_paid" type="number" step="500" min="1" required class="form-input">
      </div>
      <div>
        <label class="form-label">Njia ya Malipo</label>
        <select x-model="repayForm.payment_method" class="form-input">
          <option value="cash">Pesa Taslimu (Cash)</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="bank">Bank Transfer</option>
        </select>
      </div>
      <div>
        <label class="form-label">Tarehe ya Malipo</label>
        <input x-model="repayForm.payment_date" type="date" class="form-input">
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('repay-loan-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">💾 Rekodi Rejesho</button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function loansPage() {
  return {
    loans:[], members:[], statusFilter:'all', form:{repayment_months:6, interest_rate:<?= (float)($group->loan_interest_rate ?? 10) ?>}, repayForm:{payment_method:'cash'}, selectedLoan:null, saving:false,
    get canManage() { return ['super_admin','group_admin','treasurer'].includes(APP.role); },
    get filtered() {
      if (this.statusFilter === 'all') return this.loans;
      if (this.statusFilter === 'pending') return this.loans.filter(l => l.status.startsWith('pending'));
      return this.loans.filter(l => l.status === this.statusFilter);
    },
    async load() {
      const d = await api('/api/loans');
      if (d) this.loans = d.loans;
      const m = await api('/api/members');
      if (m) this.members = m.members;
    },
    async applyLoan() {
      this.saving = true;
      const d = await api('/api/loans/apply','POST', this.form);
      this.saving = false;
      if (d) { hideModal('apply-loan-modal'); this.form={repayment_months:6}; this.load(); Swal.fire({icon:'success', title:'Wasilisho limepokelewa!', text:'Ombi la mkopo limewasilishwa.', confirmButtonColor:'#2563eb'}); }
    },
    repayModal(l) { this.selectedLoan = l; this.repayForm.amount_paid = l.monthly_installment||l.balance_remaining; showModal('repay-loan-modal'); },
    async submitRepayment() {
      this.saving = true;
      const d = await api('/api/loans/repay','POST', {...this.repayForm, loan_id: this.selectedLoan.id});
      this.saving = false;
      if (d) { hideModal('repay-loan-modal'); this.load(); Swal.fire({icon:'success', title:'Imelipwa!', text:'Rejesho limerekodiwa.', confirmButtonColor:'#2563eb'}); }
    },
    async disburseModal(l) {
      if (await confirm_action('Thibitisha Kutoa Mkopo', `Je, una uhakika wa kutoa mkopo wa ${money(l.principal_amount)} kwa ${l.member_name}?`, 'Ndio, Toa Mkopo', '#16a34a')) {
        const d = await api('/api/loans/disburse','POST',{loan_id: l.id});
        if (d) { this.load(); Swal.fire({icon:'success', title:'Umetolewa!', text:'Mkopo umetolewa kikamilifu.', confirmButtonColor:'#16a34a'}); }
      }
    },
    async viewSchedule(l) {
      const d = await api('/api/loans/schedule?loan_id=' + l.id);
      if (!d) return;
      let rows = d.schedule.map(s => `<tr><td class="py-1 px-2 border-b">${s.period}</td><td class="py-1 px-2 border-b text-right">${money(s.principal)}</td><td class="py-1 px-2 border-b text-right">${money(s.interest)}</td><td class="py-1 px-2 border-b text-right font-bold">${money(s.total)}</td></tr>`).join('');
      Swal.fire({
        title: `Ratiba ya Mkopo: ${d.loan_code}`,
        html: `<div class="text-xs text-left max-h-60 overflow-y-auto"><table class="w-full"><thead><tr><th class="py-1 px-2">Mwezi</th><th class="py-1 px-2 text-right">Mtaji</th><th class="py-1 px-2 text-right">Riba</th><th class="py-1 px-2 text-right">Jumla</th></tr></thead><tbody>${rows}</tbody></table></div>`,
        confirmButtonColor: '#2563eb'
      });
    },
    statusClass(s) {
      return {
        active:'bg-emerald-50 text-emerald-700 border-emerald-200',
        overdue:'bg-red-50 text-red-700 border-red-200 font-bold',
        completed:'bg-slate-100 text-slate-600 border-slate-200',
        rejected:'bg-gray-100 text-gray-500 border-gray-200',
      }[s] || 'bg-amber-50 text-amber-700 border-amber-200';
    }
  }
}
</script>
