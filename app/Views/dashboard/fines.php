<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="finesPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">⚠️ Usimamizi wa Faini</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Toa faini na urekodi malipo ya faini za nidhamu au mikutano</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','secretary','treasurer'])): ?>
  <button @click="showModal('issue-fine-modal')" class="btn btn-primary">
    <span>➕ Toa Faini Mpya</span>
  </button>
  <?php endif; ?>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
  <div class="stat-card">
    <div class="stat-label">Jumla ya Faini Hazijalipwa</div>
    <div class="stat-value" style="color:#f87171" x-text="money(pendingTotal)">TZS 0</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Faini Zilizolipwa</div>
    <div class="stat-value" style="color:#4ade80" x-text="money(paidTotal)">TZS 0</div>
  </div>
</div>

<!-- Table Card -->
<div class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Mwanachama</th>
          <th>Sababu / Aina</th>
          <th style="text-align:right">Kiasi</th>
          <th style="text-align:center">Hali</th>
          <th>Tarehe</th>
          <th style="text-align:right">Vitendo</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading"><tr><td colspan="6" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
        <template x-if="!loading && fines.length === 0">
          <tr><td colspan="6" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna faini zilizorekodiwa</td></tr>
        </template>
        <template x-for="f in fines" :key="f.id">
          <tr>
            <td style="font-weight:700;color:#fff;font-size:.85rem" x-text="f.member_name"></td>
            <td style="color:rgba(255,255,255,.7)" x-text="f.reason || f.fine_type_name"></td>
            <td style="text-align:right;font-weight:800;color:#f87171" x-text="money(f.amount)"></td>
            <td style="text-align:center">
              <span class="badge" :class="f.status==='paid'?'badge-success':'badge-danger'" x-text="f.status==='paid'?'Imelipwa':'Inasubiri'"></span>
            </td>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="f.created_at"></td>
            <td style="text-align:right">
              <template x-if="f.status==='pending' && canManage">
                <button @click="payFine(f)" class="btn btn-success btn-sm">💵 Lipa Faini</button>
              </template>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- ISSUE FINE MODAL -->
<div id="issue-fine-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Toa Faini Mpya</div>
      <button @click="hideModal('issue-fine-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="issueFine" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
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
        <label class="form-label">Aina ya Faini</label>
        <select x-model="form.fine_type_id" @change="onTypeChange" class="form-input">
          <option value="">-- Faini ya Kawaida / Nyingine --</option>
          <template x-for="ft in fineTypes" :key="ft.id">
            <option :value="ft.id" x-text="ft.name + ' (TZS ' + Number(ft.amount).toLocaleString() + ')'"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="form-label">Kiasi cha Faini (TZS) *</label>
        <input x-model.number="form.amount" type="number" min="100" required class="form-input">
      </div>

      <div>
        <label class="form-label">Sababu ya Faini *</label>
        <input x-model="form.reason" type="text" required class="form-input" placeholder="Mfano: Kuchelewa mkutano">
      </div>

      <div class="modal-footer">
        <button type="button" @click="hideModal('issue-fine-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Faini</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function finesPage() {
  return {
    fines: [], members: [], fineTypes: [], form: {}, saving: false, loading: true,
    get canManage() { return ['super_admin','group_admin','secretary','treasurer'].includes(APP.role); },
    get pendingTotal() { return this.fines.filter(f=>f.status==='pending').reduce((a,b)=>a+Number(b.amount||0),0); },
    get paidTotal() { return this.fines.filter(f=>f.status==='paid').reduce((a,b)=>a+Number(b.amount||0),0); },
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/fines').then(r=>r.json());
        if (d) { this.fines = d.fines || []; this.fineTypes = d.types || []; }
        const m = await fetch('/api/members').then(r=>r.json());
        if (m && m.members) this.members = m.members;
      } catch(e) {}
      this.loading = false;
    },
    onTypeChange() {
      const ft = this.fineTypes.find(t => t.id == this.form.fine_type_id);
      if (ft) { this.form.amount = ft.amount; this.form.reason = ft.name; }
    },
    async issueFine() {
      this.saving = true;
      try {
        const res = await fetch('/api/fines/issue', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.form) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('issue-fine-modal').style.display = 'none';
          this.form = {}; this.load();
          Swal.fire({ icon:'success', title:'Faini Imetolewa!', text:'Faini imewasilishwa.', confirmButtonColor:'#2563eb', timer:2000 });
        }
      } catch(e) { this.saving = false; }
    },
    async payFine(f) {
      if (confirm('Thibitisha malipo ya faini ya TZS ' + Number(f.amount).toLocaleString() + ' kwa ' + f.member_name + '?')) {
        const res = await fetch('/api/fines/pay', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({fine_id: f.id}) });
        const d = await res.json();
        if (d && d.success) { this.load(); Swal.fire({ icon:'success', title:'Imelipwa!', text:'Faini imelipwa.', confirmButtonColor:'#16a34a' }); }
      }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; },
    money(v) { return 'TZS ' + Number(v||0).toLocaleString(); }
  }
}
</script>
