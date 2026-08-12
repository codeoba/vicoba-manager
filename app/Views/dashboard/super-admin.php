<div x-data="superAdminPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Super Admin Panel (SaaS Owner)</h1><p class="text-sm text-slate-500 mt-0.5">Usimamizi wa vikundi vyote, usajili na hali zao (Active / Suspended)</p></div>
  <button @click="showModal('create-group-modal')" class="btn-primary">➕ Unda Kikundi Kipya</button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead class="bg-slate-50 border-b"><tr class="text-xs font-semibold text-slate-500 uppercase"><th class="py-3 px-4">ID</th><th class="py-3 px-4">Jina la Kikundi</th><th class="py-3 px-4">Mkoa / Wilaya</th><th class="py-3 px-4 text-center">Wanachama</th><th class="py-3 px-4">Bei ya Hisa</th><th class="py-3 px-4">Hali</th><th class="py-3 px-4 text-right">Vitendo</th></tr></thead>
      <tbody class="divide-y divide-slate-100 text-sm">
        <template x-for="g in groups" :key="g.id">
          <tr class="hover:bg-slate-50">
            <td class="py-3 px-4 font-mono text-xs text-slate-500" x-text="g.id"></td>
            <td class="py-3 px-4 font-bold text-slate-800" x-text="g.name"></td>
            <td class="py-3 px-4 text-slate-600" x-text="(g.region||'') + ' / ' + (g.district||'')"></td>
            <td class="py-3 px-4 text-center font-bold text-blue-600" x-text="g.member_count"></td>
            <td class="py-3 px-4 text-slate-700" x-text="money(g.share_price)"></td>
            <td class="py-3 px-4">
              <span class="badge text-xs" :class="g.status==='active'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'" x-text="g.status"></span>
            </td>
            <td class="py-3 px-4 text-right">
              <button @click="toggleStatus(g)" class="btn-secondary text-xs py-1 px-2.5">🔄 Badilisha Hali</button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<div id="create-group-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b"><h3 class="font-bold text-slate-800">Unda Kikundi Kipya</h3><button @click="hideModal('create-group-modal')" class="text-slate-400 text-xl">&times;</button></div>
    <form @submit.prevent="createGroup" class="px-6 py-4 space-y-4">
      <div><label class="form-label">Jina la Kikundi *</label><input x-model="form.name" type="text" required class="form-input" placeholder="VICOBA Amani"></div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="form-label">Mkoa</label><input x-model="form.region" type="text" class="form-input"></div>
        <div><label class="form-label">Wilaya</label><input x-model="form.district" type="text" class="form-input"></div>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('create-group-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">💾 Unda Kikundi</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
function superAdminPage() {
  return {
    groups:[], form:{}, saving:false,
    async load() {
      const d = await api('/api/superadmin/groups'); if(d) this.groups = d.groups;
    },
    async createGroup() {
      this.saving = true; const d = await api('/api/superadmin/create-group','POST', this.form); this.saving = false;
      if(d) { hideModal('create-group-modal'); this.form={}; this.load(); Swal.fire({icon:'success',title:'Kikundi kimeundwa!',confirmButtonColor:'#2563eb'}); }
    },
    async toggleStatus(g) {
      const newStatus = g.status === 'active' ? 'suspended' : 'active';
      const d = await api('/api/superadmin/group-status','POST',{group_id: g.id, status: newStatus});
      if(d) { this.load(); Swal.fire({icon:'success',title:'Hali imebadilishwa!',confirmButtonColor:'#2563eb'}); }
    }
  }
}
</script>
