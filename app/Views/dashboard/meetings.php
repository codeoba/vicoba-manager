<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="meetingsPage()" x-init="load()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div><h1 class="text-2xl font-extrabold text-slate-800">Mikutano na Mahudhurio</h1><p class="text-sm text-slate-500 mt-0.5">Ratibu mikutano, rekodi mahudhurio, na hifadhi muhtasari (minutes)</p></div>
  <?php if(in_array($user->role,['super_admin','group_admin','secretary'])): ?>
  <button @click="showModal('create-meeting-modal')" class="btn-primary">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Ratibu Mkutano Mpya
  </button>
  <?php endif; ?>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <template x-for="m in meetings" :key="m.id">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-3">
      <div class="flex items-center justify-between">
        <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg" x-text="m.meeting_code"></span>
        <span class="badge text-xs" :class="m.status==='completed'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-amber-50 text-amber-700 border-amber-200'" x-text="m.status==='completed'?'Umekamilika':'Umeratibiwa'"></span>
      </div>
      <div>
        <p class="text-base font-bold text-slate-800" x-text="'📅 Tarehe: ' + m.meeting_date"></p>
        <p class="text-xs text-slate-500 mt-1" x-text="'📍 Mahali: ' + (m.location||'Haikutajwa')"></p>
        <p class="text-xs text-slate-600 mt-2" x-text="'📝 Agenda: ' + (m.agenda||'Bila agenda')"></p>
      </div>
      <div class="pt-3 border-t flex items-center justify-between gap-2">
        <button @click="openMinutesModal(m)" class="btn-secondary text-xs">📖 Muhtasari (Minutes)</button>
        <button @click="openAttendanceModal(m)" class="btn-primary text-xs">👥 Mahudhurio</button>
      </div>
    </div>
  </template>
</div>

<!-- CREATE MEETING MODAL -->
<div id="create-meeting-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b"><h3 class="font-bold text-slate-800">Ratibu Mkutano Mpya</h3><button @click="hideModal('create-meeting-modal')" class="text-slate-400 text-xl">&times;</button></div>
    <form @submit.prevent="createMeeting" class="px-6 py-4 space-y-4">
      <div><label class="form-label">Tarehe ya Mkutano *</label><input x-model="form.meeting_date" type="date" required class="form-input"></div>
      <div><label class="form-label">Mahali pa Mkutano</label><input x-model="form.location" type="text" class="form-input" placeholder="Mfano: Ukumbi wa Ofisi"></div>
      <div><label class="form-label">Agenda kuu</label><textarea x-model="form.agenda" rows="3" class="form-input" placeholder="Orodhesha mada kuu..."></textarea></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="hideModal('create-meeting-modal')" class="btn-secondary">Ghairi</button>
        <button type="submit" class="btn-primary" :disabled="saving">💾 Hifadhi Mkutano</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
function meetingsPage() {
  return {
    meetings:[], members:[], form:{}, selectedMeeting:null, saving:false,
    async load() {
      const d = await api('/api/meetings'); if(d) this.meetings = d.meetings;
      const m = await api('/api/members'); if(m) this.members = m.members;
    },
    async createMeeting() {
      this.saving = true; const d = await api('/api/meetings/create','POST', this.form); this.saving = false;
      if(d) { hideModal('create-meeting-modal'); this.form={}; this.load(); Swal.fire({icon:'success',title:'Mkutano Umeratibiwa!',confirmButtonColor:'#2563eb'}); }
    },
    async openMinutesModal(m) {
      const {value: text} = await Swal.fire({
        title: `Muhtasari wa Mkutano: ${m.meeting_code}`,
        input: 'textarea',
        inputValue: m.minutes||'',
        inputPlaceholder: 'Andika muhtasari wa mkutano hapa...',
        showCancelButton: true,
        confirmButtonText: '💾 Hifadhi Muhtasari',
        confirmButtonColor: '#2563eb'
      });
      if(text !== undefined) {
        await api('/api/meetings/update-minutes','POST',{meeting_id: m.id, minutes: text});
        this.load();
      }
    },
    async openAttendanceModal(m) {
      Swal.fire({
        title: `Mahudhurio: ${m.meeting_code}`,
        text: 'Nenda kwenye ukurasa wa mahudhurio au rekodi mambo kwa haraka.',
        icon: 'info',
        confirmButtonColor: '#2563eb'
      });
    }
  }
}
</script>
