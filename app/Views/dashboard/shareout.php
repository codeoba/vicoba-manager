<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="shareoutPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">🎯 Mgawanyo wa Mwaka (Share-Out)</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Hesabu na ugawa hisa, faida ya riba, na faini kwa wanachama mwisho wa mzunguko</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','treasurer'])): ?>
  <button @click="showModal('finalize-shareout-modal')" class="btn btn-primary">🎯 Anzisha Mgawanyo Mpya</button>
  <?php endif; ?>
</div>

<!-- Info Card -->
<div class="card card-p">
  <div style="font-size:1.05rem;font-weight:800;color:#fff;margin-bottom:.5rem">💡 Jinsi Mgawanyo Unavyofanya Kazi</div>
  <p style="font-size:.85rem;color:rgba(255,255,255,.7);line-height:1.7">
    Mfumo unajumlisha: <strong style="color:#60a5fa">(1) Pool ya Hisa Zote + (2) Faida ya Riba za Mikopo + (3) Faini Zilizolipwa</strong>, kisha unakata gharama za uendeshaji na kugawa kiasi kilichobaki kwa kila mwanachama kulingana na <em style="color:#a78bfa">idadi ya hisa zake</em>. Kama mwanachama ana mkopo hajamaliza, unakatwa kwenye mgawanyo wake.
  </p>
</div>

<!-- FINALIZE MODAL -->
<div id="finalize-shareout-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="modal-title">Fanya Mgawanyo wa Kikundi</div>
      <button @click="hideModal('finalize-shareout-modal')" style="background:none;border:none;color:rgba(255,255,255,.4);font-size:1.5rem;cursor:pointer">&times;</button>
    </div>
    <form @submit.prevent="submitShareout" class="modal-body" style="display:flex;flex-direction:column;gap:1rem">
      <div>
        <label class="form-label">Tarehe ya Mgawanyo</label>
        <input x-model="form.shareout_date" type="date" required class="form-input">
      </div>
      <div>
        <label class="form-label">Gharama za Uendeshaji (Operating Costs - TZS)</label>
        <input x-model.number="form.operating_costs" type="number" class="form-input" placeholder="0">
      </div>
      <div>
        <label class="form-label">Akiba ya Madeni Mabaya (Bad Debt Provision - TZS)</label>
        <input x-model.number="form.bad_debt_provision" type="number" class="form-input" placeholder="0">
      </div>
      <div class="modal-footer">
        <button type="button" @click="hideModal('finalize-shareout-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">🎯 Kaza & Hakiki Mgawanyo</button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function shareoutPage() {
  return {
    form:{ operating_costs:0, bad_debt_provision:0 }, saving:false,
    async load() {},
    async submitShareout() {
      if(confirm('Je, una uhakika wa kufanya mgawanyo? Mchakato huu utahifadhi rekodi za mgawanyo wa wanachama wote.')) {
        this.saving = true;
        try {
          const res = await fetch('/api/shareout/finalize', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.form) });
          const d = await res.json();
          this.saving = false;
          if (d && d.success) {
            document.getElementById('finalize-shareout-modal').style.display = 'none';
            Swal.fire({ icon:'success', title:'Mgawanyo umekamilika!', text:'Pakua CSV kupitia ukurasa wa ripoti.', confirmButtonColor:'#2563eb' });
          }
        } catch(e) { this.saving = false; }
      }
    },
    showModal(id) { document.getElementById(id).style.display = 'flex'; },
    hideModal(id) { document.getElementById(id).style.display = 'none'; }
  }
}
</script>
