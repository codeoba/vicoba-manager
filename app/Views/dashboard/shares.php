<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="sharesPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">💰 Daftari la Hisa</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Simamia ununuzi na michango ya hisa za wanachama</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','treasurer','secretary'])): ?>
  <button @click="showModal('record-share-modal')" class="btn btn-primary">
    <span>➕ Rekodi Hisa Mpya</span>
  </button>
  <?php endif; ?>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
  <div class="stat-card">
    <div class="stat-label">Jumla ya Hisa (Pool)</div>
    <div class="stat-value" style="color:#60a5fa" x-text="money(total_pool)">TZS 0</div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Mtaji wote wa kikundi</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Hisa Mwezi Huu</div>
    <div class="stat-value" style="color:#4ade80" x-text="money(this_month)">TZS 0</div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Michango ya mwezi wa sasa</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Bei ya Hisa Moja</div>
    <div class="stat-value"><?= money($group->share_price ?? 1000) ?></div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Kiwango kilichopangwa</div>
  </div>
</div>

<!-- Tables: Member Shares Summary + Recent Shares -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

  <!-- Member Shares Summary -->
  <div class="card" style="overflow:hidden">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
      <div class="card-title">📊 Muhtasari wa Hisa kwa Mwanachama</div>
    </div>
    <div style="overflow-x:auto;max-height:360px">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mwanachama</th>
            <th style="text-align:center">Hisa</th>
            <th style="text-align:right">Jumla (TZS)</th>
          </tr>
        </thead>
        <tbody>
          <template x-if="loading"><tr><td colspan="3" style="text-align:center;padding:2rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
          <template x-for="m in member_summary" :key="m.id">
            <tr>
              <td>
                <div style="font-weight:700;color:#fff;font-size:.83rem" x-text="m.full_name"></div>
                <div style="font-size:.68rem;color:rgba(255,255,255,.35)" x-text="m.member_number"></div>
              </td>
              <td style="text-align:center">
                <span class="badge badge-info" style="font-size:.78rem;font-weight:800" x-text="m.total_shares"></span>
              </td>
              <td style="text-align:right;font-weight:800;color:#4ade80" x-text="money(m.total_amount)"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Shares -->
  <div class="card" style="overflow:hidden">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
      <div class="card-title">🧾 Michango ya Hivi Karibuni</div>
    </div>
    <div style="overflow-x:auto;max-height:360px">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mwanachama</th>
            <th>Hisa</th>
            <th>Kiasi</th>
            <th>Tarehe</th>
          </tr>
        </thead>
        <tbody>
          <template x-if="loading"><tr><td colspan="4" style="text-align:center;padding:2rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
          <template x-for="s in recent" :key="s.id">
            <tr>
              <td style="font-weight:700;color:#fff;font-size:.83rem" x-text="s.member_name"></td>
              <td><span class="badge badge-purple" x-text="s.share_count + ' hisa'"></span></td>
              <td style="font-weight:800;color:#4ade80" x-text="money(s.total_amount)"></td>
              <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="s.payment_date"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- RECORD SHARE MODAL -->
<div id="record-share-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Rekodi Ununuzi wa Hisa</div>
      <button @click="hideModal('record-share-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="recordShare" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Mwanachama *</label>
        <select x-model="form.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in membersList" :key="m.id">
            <option :value="m.id" x-text="m.full_name + ' (' + m.member_number + ')'"></option>
          </template>
        </select>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
          <label class="form-label">Idadi ya Hisa *</label>
          <input x-model.number="form.share_count" type="number" min="1" required class="form-input">
        </div>

        <div>
          <label class="form-label">Njia ya Malipo</label>
          <select x-model="form.payment_method" class="form-input">
            <option value="cash">Pesa Taslimu (Cash)</option>
            <option value="mobile_money">Simu (M-Pesa/Tigo/Airtel)</option>
            <option value="bank">Benki</option>
          </select>
        </div>
      </div>

      <div>
        <label class="form-label">Tarehe ya Malipo *</label>
        <input x-model="form.payment_date" type="date" required class="form-input">
      </div>

      <div style="border-radius:1rem;padding:1rem;background:rgba(37,99,235,.1);border:1px solid rgba(37,99,235,.2)">
        <div style="font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase">Jumla ya Kiasi Kinacholipwa</div>
        <div style="font-size:1.5rem;font-weight:900;color:#60a5fa" x-text="money((form.share_count||0) * share_price)">TZS 0</div>
      </div>

      <div class="modal-footer">
        <button type="button" @click="hideModal('record-share-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Hisa</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function sharesPage() {
  return {
    total_pool: 0, this_month: 0, member_summary: [], recent: [], membersList: [],
    loading: true, saving: false, share_price: <?= (float)($group->share_price ?? 1000) ?>,
    form: { member_id: '', share_count: 1, payment_method: 'cash', payment_date: '<?= today() ?>' },
    async load() {
      this.loading = true;
      try {
        const res = await fetch('/api/shares');
        const d = await res.json();
        if (d) {
          this.total_pool = d.total_pool || 0;
          this.this_month = d.this_month || 0;
          this.member_summary = d.member_summary || [];
          this.recent = d.recent || [];
        }
        const mRes = await fetch('/api/members');
        const mData = await mRes.json();
        if (mData && mData.members) this.membersList = mData.members;
      } catch(e) {}
      this.loading = false;
    },
    async recordShare() {
      this.saving = true;
      try {
        const res = await fetch('/api/shares/record', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.form) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('record-share-modal').style.display = 'none';
          this.form = { member_id: '', share_count: 1, payment_method: 'cash', payment_date: '<?= today() ?>' };
          this.load();
          Swal.fire({ icon:'success', title:'Imekamilika!', text:'Ununuzi wa hisa umerekodiwa.', confirmButtonColor:'#2563eb', timer:2000 });
        }
      } catch(e) { this.saving = false; }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; },
    money(val) { return 'TZS ' + Number(val||0).toLocaleString(); }
  }
}
</script>
