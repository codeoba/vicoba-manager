<div x-data="superAdminPage()" x-init="load()" class="space-y-6">

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-slate-800">Super Admin Panel</h1>
    <p class="text-sm text-slate-500 mt-0.5">Usimamizi mkuu wa mfumo wa SaaS, vikundi, watumiaji, na mipangilio ya SMS</p>
  </div>
  <div class="flex gap-2">
    <button @click="showModal('create-group-modal')" class="btn btn-primary">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      <span>Unda Kikundi Kipya</span>
    </button>
  </div>
</div>

<!-- SaaS Overview Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jumla ya Vikundi</p>
    <p class="text-3xl font-extrabold text-blue-600 mt-1" x-text="groups.length"></p>
    <p class="text-xs text-emerald-600 font-medium mt-1" x-text="groups.filter(g=>g.status==='active').length + ' Wanaofanya kazi'"></p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Wanachama Wote</p>
    <p class="text-3xl font-extrabold text-slate-800 mt-1" x-text="total_members"></p>
    <p class="text-xs text-slate-400 mt-1">Kwenye vikundi vyote</p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Akaunti za Mfumo</p>
    <p class="text-3xl font-extrabold text-indigo-600 mt-1" x-text="users.length"></p>
    <p class="text-xs text-slate-400 mt-1">Super Admins & Admins</p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hali ya Mfumo</p>
    <p class="text-3xl font-extrabold text-emerald-600 mt-1">OK 🟢</p>
    <p class="text-xs text-slate-400 mt-1">Database & PHP Online</p>
  </div>
</div>

<!-- Tabs -->
<div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto">
  <button @click="tab='groups'" :class="tab==='groups'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">🏛️ Vikundi Vyote (<span x-text="groups.length"></span>)</button>
  <button @click="tab='users'" :class="tab==='users'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">👥 Watumiaji wa Mfumo (<span x-text="users.length"></span>)</button>
  <button @click="tab='audit'" :class="tab==='audit'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">📋 System Audit Log</button>
</div>

<!-- TAB 1: GROUPS MANAGEMENT -->
<div x-show="tab==='groups'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="table-auto w-full">
      <thead>
        <tr>
          <th>ID</th><th>Jina la Kikundi</th><th>Mkoa / Wilaya</th><th class="text-center">Wanachama</th><th>Bei ya Hisa</th><th>Hali</th><th class="text-right">Vitendo</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="groups.length===0"><tr><td colspan="7" class="text-center py-10 text-slate-400">Hakuna vikundi bado</td></tr></template>
        <template x-for="g in groups" :key="g.id">
          <tr class="hover:bg-slate-50">
            <td class="font-mono text-xs text-slate-500" x-text="g.id"></td>
            <td class="font-bold text-slate-800" x-text="g.name"></td>
            <td class="text-slate-600 text-sm" x-text="(g.region||'') + ' / ' + (g.district||'')"></td>
            <td class="text-center font-bold text-blue-600" x-text="g.member_count"></td>
            <td class="text-slate-700 text-sm" x-text="money(g.share_price)"></td>
            <td>
              <span class="badge text-xs" :class="g.status==='active'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'" x-text="g.status"></span>
            </td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-1.5">
                <button @click="toggleGroupStatus(g)" class="btn btn-secondary text-xs px-2.5 py-1">🔄 Badilisha Hali</button>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- TAB 2: SYSTEM USERS MANAGEMENT -->
<div x-show="tab==='users'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" x-cloak>
  <div class="overflow-x-auto">
    <table class="table-auto w-full">
      <thead>
        <tr>
          <th>Username</th><th>Jina</th><th>Email</th><th>Jukumu (Role)</th><th>Kikundi</th><th class="text-right">Vitendo</th>
        </tr>
      </thead>
      <tbody>
        <template x-for="u in users" :key="u.id">
          <tr class="hover:bg-slate-50">
            <td class="font-mono text-xs font-bold text-slate-700" x-text="u.username"></td>
            <td class="font-semibold text-slate-800" x-text="u.display_name||'—'"></td>
            <td class="text-slate-500 text-xs" x-text="u.email||'—'"></td>
            <td><span class="badge text-xs" :class="u.role==='super_admin'?'bg-purple-50 text-purple-700 border-purple-200':'bg-blue-50 text-blue-700 border-blue-200'" x-text="u.role"></span></td>
            <td class="text-xs text-slate-600" x-text="u.group_name||'Super Admin (All)'"></td>
            <td class="text-right">
              <button @click="resetPassword(u)" class="btn btn-secondary text-xs px-2.5 py-1">🔑 Reset Password</button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- TAB 3: AUDIT LOG -->
<div x-show="tab==='audit'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" x-cloak>
  <div class="overflow-x-auto max-h-96 scrollbar-thin">
    <table class="table-auto w-full">
      <thead>
        <tr><th>Tarehe</th><th>Mtumiaji</th><th>Action</th><th>Target</th><th>Maelezo</th></tr>
      </thead>
      <tbody>
        <template x-if="logs.length===0"><tr><td colspan="5" class="text-center py-10 text-slate-400">Hakuna kumbukumbu za audit bado</td></tr></template>
        <template x-for="l in logs" :key="l.id">
          <tr class="hover:bg-slate-50 text-xs">
            <td class="text-slate-400 font-mono" x-text="l.created_at"></td>
            <td class="font-semibold text-slate-700" x-text="l.user_name||'System'"></td>
            <td class="font-mono text-blue-600" x-text="l.action"></td>
            <td class="text-slate-600" x-text="l.entity_type + '#' + (l.entity_id||'')"></td>
            <td class="text-slate-500" x-text="l.details||'—'"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- CREATE GROUP MODAL -->
<div id="create-group-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h3 class="font-bold text-slate-800 text-base">Unda Kikundi Kipya cha VICOBA</h3>
      <button @click="hideModal('create-group-modal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
    </div>
    <form @submit.prevent="createGroup" class="px-6 py-4 space-y-4">
      <div><label class="form-label">Jina la Kikundi *</label><input x-model="form.name" type="text" required class="form-input" placeholder="Mfano: VICOBA Amani Mwanza"></div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="form-label">Mkoa *</label>
          <select x-model="form.region" @change="onRegionChange()" required class="form-input">
            <option value="">-- Chagua Mkoa --</option>
            <template x-for="(districts, reg) in regionsData" :key="reg">
              <option :value="reg" x-text="reg"></option>
            </template>
          </select>
        </div>
        <div>
          <label class="form-label">Wilaya *</label>
          <select x-model="form.district" required class="form-input" :disabled="!form.region">
            <option value="">-- Chagua Wilaya --</option>
            <template x-for="d in availableDistricts" :key="d">
              <option :value="d" x-text="d"></option>
            </template>
          </select>
        </div>
      </div>
      <div><label class="form-label">Bei kwa Hisa Moja (TZS)</label><input x-model.number="form.share_price" type="number" class="form-input" value="1000"></div>
      <div class="flex justify-end gap-3 pt-3 border-t">
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
    tab:'groups', groups:[], users:[], logs:[], total_members:0, form:{share_price:1000}, saving:false,
    regionsData: typeof TANZANIA_REGIONS !== 'undefined' ? TANZANIA_REGIONS : {},
    get availableDistricts() {
      return this.form.region ? (this.regionsData[this.form.region] || []) : [];
    },
    onRegionChange() {
      this.form.district = '';
    },
    async load() {
      const d = await api('/api/superadmin/groups');
      if (d) {
        this.groups = d.groups || [];
        this.users = d.users || [];
        this.logs = d.logs || [];
        this.total_members = this.groups.reduce((a,b) => a + Number(b.member_count||0), 0);
      }
    },
    async createGroup() {
      this.saving = true;
      const d = await api('/api/superadmin/create-group','POST', this.form);
      this.saving = false;
      if (d) { hideModal('create-group-modal'); this.form={share_price:1000}; this.load(); Swal.fire({icon:'success',title:'Kikundi kimeundwa!',confirmButtonColor:'#2563eb'}); }
    },
    async toggleGroupStatus(g) {
      const newStatus = g.status === 'active' ? 'suspended' : 'active';
      const d = await api('/api/superadmin/group-status','POST',{group_id: g.id, status: newStatus});
      if (d) { this.load(); Swal.fire({icon:'success',title:'Hali imebadilishwa!',confirmButtonColor:'#2563eb'}); }
    },
    async resetPassword(u) {
      const {value: newPass} = await Swal.fire({
        title: `Reset Password: ${u.username}`,
        input: 'password',
        inputPlaceholder: 'Ingiza nywila mpya (min 6 chars)...',
        showCancelButton: true,
        confirmButtonText: '🔑 Hifadhi Nywila Mpya',
        confirmButtonColor: '#2563eb'
      });
      if (newPass) {
        const d = await api('/api/superadmin/reset-password','POST',{user_id: u.id, password: newPass});
        if (d) Swal.fire({icon:'success',title:'Nywila imebadilishwa!',confirmButtonColor:'#2563eb'});
      }
    }
  }
}
</script>
