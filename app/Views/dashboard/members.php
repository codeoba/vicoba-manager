<?php
$group_id = (int)($group->id ?? 0);
?>
<div x-data="membersPage()" x-init="load()">

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-extrabold text-slate-800">Wanachama</h1>
    <p class="text-sm text-slate-500 mt-0.5">Simamia wanachama wa kikundi</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','secretary'])): ?>
  <button @click="showModal('add-member-modal')" class="btn-primary">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Ongeza Mwanachama
  </button>
  <?php endif; ?>
</div>

<!-- Search -->
<div class="mb-4">
  <input type="search" x-model="search" placeholder="Tafuta kwa jina, namba ya simu..." class="form-input max-w-sm">
</div>

<!-- Stats row -->
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="stat-card text-center"><p class="text-2xl font-extrabold text-blue-600" x-text="members.filter(m=>m.status==='active').length"></p><p class="text-xs text-slate-500 mt-1">Wanaofanya Kazi</p></div>
  <div class="stat-card text-center"><p class="text-2xl font-extrabold text-amber-600" x-text="members.filter(m=>m.status==='suspended').length"></p><p class="text-xs text-slate-500 mt-1">Wamesimamishwa</p></div>
  <div class="stat-card text-center"><p class="text-2xl font-extrabold text-slate-600" x-text="members.filter(m=>m.status==='alumni').length"></p><p class="text-xs text-slate-500 mt-1">Waliomaliza</p></div>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="table-auto w-full">
      <thead><tr>
        <th>Namba</th><th>Jina Kamili</th><th>Simu</th><th>Jukumu</th><th>Hali</th><th>Tarehe ya Kujiunga</th>
        <?php if(in_array($user->role,['super_admin','group_admin','secretary','treasurer'])): ?><th>Vitendo</th><?php endif; ?>
      </tr></thead>
      <tbody>
        <template x-if="loading"><tr><td colspan="7" class="text-center py-10 text-slate-400">Inapakia...</td></tr></template>
        <template x-if="!loading && filtered.length===0"><tr><td colspan="7" class="text-center py-10 text-slate-400">Hakuna wanachama wanaofanana na utafutaji</td></tr></template>
        <template x-for="m in filtered" :key="m.id">
          <tr class="hover:bg-slate-50">
            <td class="font-mono text-xs text-slate-600" x-text="m.member_number"></td>
            <td>
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs flex-shrink-0" x-text="m.full_name.charAt(0)"></div>
                <div>
                  <p class="font-semibold text-slate-800 text-sm" x-text="m.full_name"></p>
                  <p class="text-xs text-slate-400" x-text="m.email||''"></p>
                </div>
              </div>
            </td>
            <td class="text-slate-600 text-sm" x-text="m.phone||'—'"></td>
            <td>
              <span class="badge text-xs" :class="roleBadge(m.role)" x-text="roleLabel(m.role)"></span>
            </td>
            <td>
              <span class="badge text-xs"
                :class="m.status==='active'?'bg-emerald-50 text-emerald-700 border-emerald-200':m.status==='suspended'?'bg-red-50 text-red-700 border-red-200':'bg-slate-100 text-slate-600 border-slate-200'"
                x-text="m.status"></span>
            </td>
            <td class="text-slate-500 text-xs" x-text="m.joined_date||'—'"></td>
            <?php if(in_array($user->role,['super_admin','group_admin','secretary','treasurer'])): ?>
            <td>
              <div class="flex items-center gap-1">
                <button @click="editMember(m)" class="btn-secondary text-xs py-1 px-2">✏️</button>
                <button @click="changeStatus(m)" class="btn-secondary text-xs py-1 px-2">🔄</button>
                <button @click="changeRole(m)" class="btn-secondary text-xs py-1 px-2">🎭</button>
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
<div id="add-member-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h3 class="font-bold text-slate-800">Ongeza Mwanachama Mpya</h3>
      <button @click="hideModal('add-member-modal')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
    </div>
    <form @submit.prevent="addMember" class="px-6 py-4 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2"><label class="form-label">Jina Kamili *</label><input x-model="form.full_name" type="text" required class="form-input" placeholder="Jina la Kwanza na Familia"></div>
        <div><label class="form-label">Namba ya Simu *</label><input x-model="form.phone" type="tel" required class="form-input"></div>
        <div><label class="form-label">Barua Pepe</label><input x-model="form.email" type="email" class="form-input"></div>
        <div><label class="form-label">Jinsia</label>
          <select x-model="form.gender" class="form-input"><option value="">-- Chagua --</option><option value="male">Mwanaume</option><option value="female">Mwanamke</option></select>
        </div>
        <div><label class="form-label">Jukumu</label>
          <select x-model="form.role" class="form-input">
            <option value="member">Mwanachama</option>
            <option value="secretary">Katibu</option>
            <option value="treasurer">Mweka Hazina</option>
            <option value="group_admin">Mwenyekiti</option>
          </select>
        </div>
        <div class="sm:col-span-2"><label class="form-label">Namba ya NIDA</label><input x-model="form.nida_number" type="text" class="form-input" placeholder="Itahifadhiwa kwa usalama"></div>
        <div><label class="form-label">Mawasiliano ya Dharura</label><input x-model="form.emergency_contact" type="text" class="form-input"></div>
        <div><label class="form-label">Simu ya Dharura</label><input x-model="form.emergency_phone" type="tel" class="form-input"></div>
        <div class="sm:col-span-2"><label class="form-label">Nywila ya Akaunti</label><input x-model="form.password" type="password" class="form-input" placeholder="Atatumiwa au atumiwe mwenyewe (min 8)"></div>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('add-member-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">
          <template x-if="saving">⏳ Inahifadhi...</template>
          <template x-if="!saving">💾 Hifadhi</template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function membersPage() {
  return {
    members:[],loading:true,search:'',form:{},saving:false,
    get filtered() {
      const q = this.search.toLowerCase();
      return this.members.filter(m => !q || m.full_name.toLowerCase().includes(q) || (m.phone||'').includes(q) || (m.member_number||'').toLowerCase().includes(q));
    },
    async load() {
      this.loading = true;
      const d = await api('/api/members');
      if(d) this.members = d.members;
      this.loading = false;
    },
    async addMember() {
      this.saving = true;
      const d = await api('/api/members/add','POST', this.form);
      this.saving = false;
      if(d) { hideModal('add-member-modal'); this.form={}; this.load(); Swal.fire({icon:'success',title:'Amefanikiwa!',text:'Mwanachama ameongezwa kikamilifu.',confirmButtonColor:'#2563eb',timer:2000}); }
    },
    editMember(m) { this.form = {...m}; showModal('add-member-modal'); },
    async changeStatus(m) {
      const statuses = ['active','suspended','alumni'].filter(s=>s!==m.status);
      const {value} = await Swal.fire({title:'Badilisha Hali',input:'select',inputOptions:{active:'Active',suspended:'Suspended',alumni:'Alumni'},inputValue:m.status,showCancelButton:true,confirmButtonColor:'#2563eb'});
      if(value) { await api('/api/members/update-status','POST',{member_id:m.id,status:value}); this.load(); }
    },
    async changeRole(m) {
      const {value} = await Swal.fire({title:'Badilisha Jukumu',input:'select',inputOptions:{member:'Mwanachama',secretary:'Katibu',treasurer:'Mweka Hazina',group_admin:'Mwenyekiti'},inputValue:m.role,showCancelButton:true,confirmButtonColor:'#2563eb'});
      if(value) { await api('/api/members/update-role','POST',{member_id:m.id,role:value}); this.load(); }
    },
    roleLabel(r) { return {super_admin:'Super Admin',group_admin:'Mwenyekiti',secretary:'Katibu',treasurer:'Mweka Hazina',member:'Mwanachama'}[r]||r; },
    roleBadge(r) { return {super_admin:'bg-purple-50 text-purple-700 border-purple-200',group_admin:'bg-blue-50 text-blue-700 border-blue-200',secretary:'bg-cyan-50 text-cyan-700 border-cyan-200',treasurer:'bg-amber-50 text-amber-700 border-amber-200',member:'bg-slate-100 text-slate-600 border-slate-200'}[r]||''; }
  }
}
</script>
