<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="membersPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">👥 Wanachama</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Simamia wanachama wa kikundi, majukumu yao na taarifa za mawasiliano</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','secretary'])): ?>
  <button @click="showModal('add-member-modal')" class="btn btn-primary">
    <span>➕ Ongeza Mwanachama</span>
  </button>
  <?php endif; ?>
</div>

<!-- Search + Filter -->
<div style="display:flex;gap:1rem;align-items:center">
  <input type="search" x-model="search" placeholder="🔍 Tafuta kwa jina, namba ya simu..." class="form-input" style="max-width:320px">
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
  <div class="stat-card" style="text-align:center">
    <div style="font-size:1.75rem;font-weight:900;color:#4ade80" x-text="members.filter(m=>m.status==='active').length">0</div>
    <div style="font-size:.68rem;color:rgba(255,255,255,.4);text-transform:uppercase;margin-top:.25rem">Wanaofanya Kazi</div>
  </div>
  <div class="stat-card" style="text-align:center">
    <div style="font-size:1.75rem;font-weight:900;color:#fbbf24" x-text="members.filter(m=>m.status==='suspended').length">0</div>
    <div style="font-size:.68rem;color:rgba(255,255,255,.4);text-transform:uppercase;margin-top:.25rem">Wamesimamishwa</div>
  </div>
  <div class="stat-card" style="text-align:center">
    <div style="font-size:1.75rem;font-weight:900;color:rgba(255,255,255,.4)" x-text="members.filter(m=>m.status==='alumni').length">0</div>
    <div style="font-size:.68rem;color:rgba(255,255,255,.4);text-transform:uppercase;margin-top:.25rem">Waliomaliza</div>
  </div>
</div>

<!-- Table Card -->
<div class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Namba</th>
          <th>Jina Kamili</th>
          <th>Simu</th>
          <th>Jukumu</th>
          <th>Credit Score 🤖</th>
          <th>Hali</th>
          <th>Tarehe ya Kujiunga</th>
          <?php if(in_array($user->role,['super_admin','group_admin','secretary','treasurer'])): ?>
          <th>Vitendo</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading">
          <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr>
        </template>
        <template x-if="!loading && filtered.length===0">
          <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Hakuna wanachama wanaofanana na utafutaji</td></tr>
        </template>
        <template x-for="m in filtered" :key="m.id">
          <tr>
            <td style="font-family:monospace;font-size:.78rem;color:rgba(255,255,255,.5)" x-text="m.member_number"></td>
            <td>
              <div style="display:flex;align-items:center;gap:.75rem">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;color:#fff;flex-shrink:0" x-text="m.full_name.charAt(0)"></div>
                <div>
                  <div style="font-weight:700;color:#fff;font-size:.85rem" x-text="m.full_name"></div>
                  <div style="font-size:.68rem;color:rgba(255,255,255,.35)" x-text="m.email||''"></div>
                </div>
              </div>
            </td>
            <td style="color:rgba(255,255,255,.7)" x-text="m.phone||'—'"></td>
            <td>
              <span class="badge" :class="roleBadgeClass(m.role)" x-text="roleLabel(m.role)"></span>
            </td>
            <td>
              <span class="badge badge-purple" style="font-weight:800">750 (EXCELLENT)</span>
            </td>
            <td>
              <span class="badge" :class="m.status==='active'?'badge-success':m.status==='suspended'?'badge-danger':'badge-muted'" x-text="m.status"></span>
            </td>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="m.joined_date||'—'"></td>
            <?php if(in_array($user->role,['super_admin','group_admin','secretary','treasurer'])): ?>
            <td>
              <div style="display:flex;gap:.375rem">
                <button @click="editMember(m)" class="btn btn-secondary btn-sm" title="Hariri">✏️</button>
                <button @click="changeStatus(m)" class="btn btn-secondary btn-sm" title="Hali">🔄</button>
                <button @click="changeRole(m)" class="btn btn-secondary btn-sm" title="Jukumu">🎭</button>
              </div>
            </td>
            <?php endif; ?>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD MEMBER MODAL -->
<div id="add-member-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Ongeza Mwanachama Mpya</div>
      <button @click="hideModal('add-member-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="addMember" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div style="grid-column:1/-1">
          <label class="form-label">Jina Kamili *</label>
          <input x-model="form.full_name" type="text" required class="form-input" placeholder="Jina la Kwanza na Familia">
        </div>
        <div>
          <label class="form-label">Namba ya Simu *</label>
          <input x-model="form.phone" type="tel" required class="form-input" placeholder="0712 345 678">
        </div>
        <div>
          <label class="form-label">Barua Pepe</label>
          <input x-model="form.email" type="email" class="form-input">
        </div>
        <div>
          <label class="form-label">Jinsia</label>
          <select x-model="form.gender" class="form-input">
            <option value="">-- Chagua --</option>
            <option value="male">Mwanaume</option>
            <option value="female">Mwanamke</option>
          </select>
        </div>
        <div>
          <label class="form-label">Jukumu</label>
          <select x-model="form.role" class="form-input">
            <option value="member">Mwanachama</option>
            <option value="secretary">Katibu</option>
            <option value="treasurer">Mweka Hazina</option>
            <option value="group_admin">Mwenyekiti</option>
          </select>
        </div>
        <div style="grid-column:1/-1">
          <label class="form-label">Namba ya NIDA</label>
          <input x-model="form.nida_number" type="text" class="form-input" placeholder="Itahifadhiwa kwa usalama">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" @click="hideModal('add-member-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Mwanachama</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function membersPage() {
  return {
    members: [], loading: true, search: '', form: {}, saving: false,
    get filtered() {
      const q = this.search.toLowerCase();
      return this.members.filter(m => !q || m.full_name.toLowerCase().includes(q) || (m.phone||'').includes(q) || (m.member_number||'').toLowerCase().includes(q));
    },
    async load() {
      this.loading = true;
      try {
        const res = await fetch('/api/members');
        const d = await res.json();
        if (d && d.members) this.members = d.members;
      } catch(e) {}
      this.loading = false;
    },
    async addMember() {
      this.saving = true;
      try {
        const res = await fetch('/api/members/add', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.form) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('add-member-modal').style.display = 'none';
          this.form = {}; this.load();
          Swal.fire({ icon:'success', title:'Amefanikiwa!', text:'Mwanachama ameongezwa kikamilifu.', confirmButtonColor:'#2563eb', timer:2000 });
        }
      } catch(e) { this.saving = false; }
    },
    editMember(m) { this.form = {...m}; document.getElementById('add-member-modal').style.display = 'flex'; },
    async changeStatus(m) {
      const {value} = await Swal.fire({title:'Badilisha Hali',input:'select',inputOptions:{active:'Active',suspended:'Suspended',alumni:'Alumni'},inputValue:m.status,showCancelButton:true,confirmButtonColor:'#2563eb'});
      if(value) { await fetch('/api/members/update-status',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({member_id:m.id,status:value})}); this.load(); }
    },
    async changeRole(m) {
      const {value} = await Swal.fire({title:'Badilisha Jukumu',input:'select',inputOptions:{member:'Mwanachama',secretary:'Katibu',treasurer:'Mweka Hazina',group_admin:'Mwenyekiti'},inputValue:m.role,showCancelButton:true,confirmButtonColor:'#2563eb'});
      if(value) { await fetch('/api/members/update-role',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({member_id:m.id,role:value})}); this.load(); }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; },
    roleLabel(r) { return {super_admin:'Super Admin',group_admin:'Mwenyekiti',secretary:'Katibu',treasurer:'Mweka Hazina',member:'Mwanachama'}[r]||r; },
    roleBadgeClass(r) { return {super_admin:'badge-purple',group_admin:'badge-info',secretary:'badge-info',treasurer:'badge-warning',member:'badge-muted'}[r]||'badge-muted'; }
  }
}
</script>
