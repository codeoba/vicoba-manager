<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="meetingsPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">📅 Mikutano na Mahudhurio</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Ratibu mikutano, rekodi mahudhurio, na hifadhi muhtasari (minutes)</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','secretary'])): ?>
  <button @click="showModal('create-meeting-modal')" class="btn btn-primary">
    <span>➕ Ratibu Mkutano Mpya</span>
  </button>
  <?php endif; ?>
</div>

<!-- Meetings List -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.25rem">
  <template x-if="loading">
    <div class="card card-p" style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,.3)">Inapakia mikutano...</div>
  </template>
  <template x-if="!loading && meetings.length === 0">
    <div class="card card-p" style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,.3)">Hakuna mikutano iliyoratibiwa bado</div>
  </template>
  <template x-for="m in meetings" :key="m.id">
    <div class="card card-p" style="display:flex;flex-direction:column;gap:1rem">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span class="badge badge-info" style="font-family:monospace;font-weight:800" x-text="m.meeting_code"></span>
        <span class="badge" :class="m.status==='completed'?'badge-success':'badge-warning'" x-text="m.status==='completed'?'Umekamilika':'Umeratibiwa'"></span>
      </div>
      <div>
        <div style="font-size:1.05rem;font-weight:800;color:#fff" x-text="'📅 Tarehe: ' + m.meeting_date"></div>
        <div style="font-size:.78rem;color:rgba(255,255,255,.45);margin-top:.25rem" x-text="'📍 Mahali: ' + (m.location||'Haikutajwa')"></div>
        <div style="font-size:.83rem;color:rgba(255,255,255,.7);margin-top:.5rem" x-text="'📝 Agenda: ' + (m.agenda||'Bila agenda')"></div>
      </div>
      <div style="padding-top:1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:.5rem">
        <button @click="openMinutesModal(m)" class="btn btn-secondary btn-sm">📖 Muhtasari</button>
        <button @click="openAttendanceModal(m)" class="btn btn-primary btn-sm">👥 Mahudhurio</button>
      </div>
    </div>
  </template>
</div>

<!-- CREATE MEETING MODAL -->
<div id="create-meeting-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Ratibu Mkutano Mpya</div>
      <button @click="hideModal('create-meeting-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="createMeeting" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Tarehe ya Mkutano *</label>
        <input x-model="form.meeting_date" type="date" required class="form-input">
      </div>
      <div>
        <label class="form-label">Mahali pa Mkutano</label>
        <input x-model="form.location" type="text" class="form-input" placeholder="Mfano: Ukumbi wa Ofisi">
      </div>
      <div>
        <label class="form-label">Agenda kuu</label>
        <textarea x-model="form.agenda" rows="3" class="form-input" placeholder="Orodhesha mada kuu..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" @click="hideModal('create-meeting-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Hifadhi Mkutano</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function meetingsPage() {
  return {
    meetings: [], form: {}, saving: false, loading: true,
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/meetings').then(r=>r.json());
        if (d && d.meetings) this.meetings = d.meetings;
      } catch(e) {}
      this.loading = false;
    },
    async createMeeting() {
      this.saving = true;
      try {
        const res = await fetch('/api/meetings/create', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.form) });
        const d = await res.json();
        this.saving = false;
        if (d && d.success) {
          document.getElementById('create-meeting-modal').style.display = 'none';
          this.form = {}; this.load();
          Swal.fire({ icon:'success', title:'Imeratibiwa!', text:'Mkutano umeongezwa kikamilifu.', confirmButtonColor:'#2563eb' });
        }
      } catch(e) { this.saving = false; }
    },
    openMinutesModal(m) { Swal.fire({ title: 'Muhtasari: ' + m.meeting_code, text: m.agenda || 'Hakuna muhtasari uliohifadhiwa.', confirmButtonColor:'#2563eb' }); },
    openAttendanceModal(m) { Swal.fire({ title: 'Mahudhurio: ' + m.meeting_code, text: 'Mahudhurio yamerekodiwa kwa mwanachama.', confirmButtonColor:'#2563eb' }); },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; }
  }
}
</script>
