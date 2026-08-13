<div x-data="superAdminPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">🛡️ Super Admin Panel</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Usimamizi mkuu wa mfumo wa SaaS, vikundi, watumiaji, na mipangilio ya mfumo</p>
  </div>
  <button @click="showModal('create-group-modal')" class="btn btn-primary">
    <span>➕ Unda Kikundi Kipya</span>
  </button>
</div>

<!-- SaaS Overview Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
  <div class="stat-card">
    <div class="stat-label">Jumla ya Vikundi</div>
    <div class="stat-value" style="color:#60a5fa" x-text="groups.length">0</div>
    <div class="stat-change" style="color:#4ade80" x-text="groups.filter(g=>g.status==='active').length + ' Wanaofanya kazi'"></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Wanachama Wote</div>
    <div class="stat-value" x-text="total_members">0</div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Kwenye vikundi vyote</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Akaunti za Mfumo</div>
    <div class="stat-value" style="color:#a78bfa" x-text="users.length">0</div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Super Admins & Admins</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Hali ya Mfumo</div>
    <div class="stat-value" style="color:#4ade80">OK 🟢</div>
    <div class="stat-change" style="color:rgba(255,255,255,.4)">Database & PHP Online</div>
  </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:.5rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;overflow-x:auto">
  <button @click="tab='groups'" :class="tab==='groups'?'badge badge-info':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">🏛️ Vikundi Vyote (<span x-text="groups.length"></span>)</button>
  <button @click="tab='users'" :class="tab==='users'?'badge badge-purple':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">👥 Watumiaji wa Mfumo (<span x-text="users.length"></span>)</button>
</div>

<!-- TAB 1: GROUPS -->
<div x-show="tab==='groups'" class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th>Kikundi</th>
          <th>Mkoa / Wilaya</th>
          <th>Wanachama</th>
          <th>Hali</th>
          <th>Tarehe ya Usajili</th>
          <th style="text-align:right">Vitendo</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="loading"><tr><td colspan="6" style="text-align:center;padding:2.5rem;color:rgba(255,255,255,.3)">Inapakia...</td></tr></template>
        <template x-for="g in groups" :key="g.id">
          <tr>
            <td>
              <div style="font-weight:800;color:#fff" x-text="g.name"></div>
              <div style="font-size:.68rem;color:rgba(255,255,255,.35)" x-text="'Bei ya Hisa: TZS ' + Number(g.share_price||1000).toLocaleString()"></div>
            </td>
            <td style="color:rgba(255,255,255,.7)" x-text="(g.region||'') + ' / ' + (g.district||'')"></td>
            <td style="font-weight:700;color:#60a5fa" x-text="g.member_count||0"></td>
            <td>
              <span class="badge" :class="g.status==='active'?'badge-success':'badge-danger'" x-text="g.status"></span>
            </td>
            <td style="color:rgba(255,255,255,.4);font-size:.75rem" x-text="g.created_at"></td>
            <td style="text-align:right">
              <button @click="toggleGroupStatus(g)" class="btn btn-secondary btn-sm" x-text="g.status==='active'?'🔒 Sitisha':'✅ Activate'"></button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- CREATE GROUP MODAL -->
<div id="create-group-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Unda Kikundi Kipya cha VICOBA</div>
      <button @click="hideModal('create-group-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="createGroup" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Jina la Kikundi *</label>
        <input x-model="gForm.group_name" type="text" required class="form-input" placeholder="Mfano: VICOBA Amani 2024">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
          <label class="form-label">Mkoa</label>
          <input x-model="gForm.region" type="text" class="form-input">
        </div>
        <div>
          <label class="form-label">Wilaya</label>
          <input x-model="gForm.district" type="text" class="form-input">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
          <label class="form-label">Admin Full Name *</label>
          <input x-model="gForm.full_name" type="text" required class="form-input">
        </div>
        <div>
          <label class="form-label">Admin Username *</label>
          <input x-model="gForm.username" type="text" required class="form-input">
        </div>
      </div>
      <div>
        <label class="form-label">Admin Password *</label>
        <input x-model="gForm.password" type="password" required minlength="8" class="form-input">
      </div>

      <div class="modal-footer">
        <button type="button" @click="hideModal('create-group-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Unda Kikundi</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function superAdminPage() {
  return {
    tab: 'groups', groups: [], users: [], total_members: 0, loading: true, saving: false, gForm: {},
    async load() {
      this.loading = true;
      try {
        const res = await fetch('/api/superadmin/groups');
        const d = await res.json();
        if (d && d.groups) {
          this.groups = d.groups;
          this.total_members = d.groups.reduce((a,b) => a + Number(b.member_count||0), 0);
        }
      } catch(e) {}
      this.loading = false;
    },
    async toggleGroupStatus(g) {
      const newStatus = g.status === 'active' ? 'suspended' : 'active';
      if (confirm('Badilisha hali ya kikundi ' + g.name + ' kuwa ' + newStatus + '?')) {
        const res = await fetch('/api/superadmin/group-status', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({group_id:g.id, status:newStatus}) });
        const d = await res.json();
        if (d && d.success) this.load();
      }
    },
    async createGroup() {
      this.saving = true;
      try {
        const res = await fetch('/api/superadmin/create-group', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.gForm) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('create-group-modal').style.display = 'none';
          this.gForm = {}; this.load();
          Swal.fire({ icon:'success', title:'Kikundi Kimeundwa!', text:'Kikundi kipya kimesajiliwa.', confirmButtonColor:'#2563eb' });
        }
      } catch(e) { this.saving = false; }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; }
  }
}
</script>
