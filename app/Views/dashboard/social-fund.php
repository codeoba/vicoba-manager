<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="socialFundPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">❤️ Mfuko wa Jamii (Social Fund)</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Michango na usaidizi wa majanga, msiba au sherehe za wanachama</p>
  </div>
  <div style="display:flex;gap:.5rem">
    <button @click="showModal('contribute-modal')" class="btn btn-primary">❤️ Weka Mchango</button>
    <button @click="showModal('request-modal')" class="btn btn-secondary">🆘 Omba Usaidizi</button>
  </div>
</div>

<!-- Balance Banner -->
<div style="border-radius:1.5rem;padding:1.75rem 2rem;background:linear-gradient(135deg,rgba(225,29,72,.2) 0%,rgba(168,85,247,.15) 100%);border:1px solid rgba(225,29,72,.3)">
  <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#fecdd3">Salio la Mfuko wa Jamii</div>
  <div style="font-size:2.25rem;font-weight:900;color:#fff;margin-top:.25rem" x-text="money(balance)">TZS 0</div>
  <div style="font-size:.78rem;color:rgba(255,255,255,.5);margin-top:.35rem">Pesa hii inalinda wanachama wakati wa dharura na majanga</div>
</div>

<!-- Table Card -->
<div class="card" style="overflow:hidden">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
    <div class="card-title">📜 Historia ya Mfuko wa Jamii</div>
  </div>
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Tarehe</th>
          <th>Aina</th>
          <th>Mwanachama</th>
          <th>Sababu</th>
          <th style="text-align:right">Kiasi</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading"><tr><td colspan="5" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
        <template x-if="!loading && records.length === 0"><tr><td colspan="5" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna rekodi zilizopatikana</td></tr></template>
        <template x-for="r in records" :key="r.id">
          <tr>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="r.created_at"></td>
            <td>
              <span class="badge" :class="r.type==='contribution'?'badge-success':'badge-danger'" x-text="r.type==='contribution'?'Mchango':'Usaidizi'"></span>
            </td>
            <td style="font-weight:700;color:#fff;font-size:.83rem" x-text="r.member_name||'—'"></td>
            <td style="color:rgba(255,255,255,.7)" x-text="r.reason||'—'"></td>
            <td style="text-align:right;font-weight:800" :style="r.type==='contribution'?'color:#4ade80':'color:#f87171'" x-text="(r.type==='contribution'?'+':'-') + money(r.amount)"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- CONTRIBUTE MODAL -->
<div id="contribute-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Rekodi Mchango wa Jamii</div>
      <button @click="hideModal('contribute-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="submitContribute" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Mwanachama *</label>
        <select x-model="cForm.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in members" :key="m.id">
            <option :value="m.id" x-text="m.full_name"></option>
          </template>
        </select>
      </div>
      <div>
        <label class="form-label">Kiasi (TZS) *</label>
        <input x-model.number="cForm.amount" type="number" min="100" required class="form-input">
      </div>
      <div class="modal-footer">
        <button type="button" @click="hideModal('contribute-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Mchango</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- REQUEST MODAL -->
<div id="request-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Omba Usaidizi wa Mfuko wa Jamii</div>
      <button @click="hideModal('request-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="submitRequest" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Mwanachama *</label>
        <select x-model="rForm.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in members" :key="m.id">
            <option :value="m.id" x-text="m.full_name"></option>
          </template>
        </select>
      </div>
      <div>
        <label class="form-label">Kiasi cha Usaidizi (TZS) *</label>
        <input x-model.number="rForm.amount" type="number" min="100" required class="form-input">
      </div>
      <div>
        <label class="form-label">Sababu / Janga *</label>
        <input x-model="rForm.reason" type="text" required class="form-input" placeholder="Mfano: Msiba wa familia">
      </div>
      <div class="modal-footer">
        <button type="button" @click="hideModal('request-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>🚀 Tuma Ombi</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function socialFundPage() {
  return {
    balance:0, records:[], members:[], cForm:{}, rForm:{}, saving:false, loading:true,
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/social-fund').then(r=>r.json());
        if(d) { this.balance = d.balance||0; this.records = d.records||[]; }
        const m = await fetch('/api/members').then(r=>r.json());
        if(m && m.members) this.members = m.members;
      } catch(e) {}
      this.loading = false;
    },
    async submitContribute() {
      this.saving = true;
      try {
        const res = await fetch('/api/social-fund/contribute', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.cForm) });
        const d = await res.json();
        this.saving = false;
        if(d && d.success) { document.getElementById('contribute-modal').style.display='none'; this.cForm={}; this.load(); Swal.fire({icon:'success',title:'Imewasilishwa!',text:'Mchango umewekwa.',confirmButtonColor:'#2563eb'}); }
      } catch(e) { this.saving = false; }
    },
    async submitRequest() {
      this.saving = true;
      try {
        const res = await fetch('/api/social-fund/request', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.rForm) });
        const d = await res.json();
        this.saving = false;
        if(d && d.success) { document.getElementById('request-modal').style.display='none'; this.rForm={}; this.load(); Swal.fire({icon:'success',title:'Imewasilishwa!',text:'Ombi limewasilishwa.',confirmButtonColor:'#2563eb'}); }
      } catch(e) { this.saving = false; }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; },
    money(v) { return 'TZS ' + Number(v||0).toLocaleString(); }
  }
}
</script>
