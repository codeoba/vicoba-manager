<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="reportsPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">📄 Ripoti & Export</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Pakua ripoti za kifedha na taarifa za wanachama kwa Excel/CSV na PDF</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.25rem">

  <!-- Ledger CSV Export Card -->
  <div class="card card-p" style="display:flex;flex-direction:column;gap:1rem">
    <div style="display:flex;align-items:center;gap:.875rem">
      <div style="width:42px;height:42px;border-radius:12px;background:rgba(37,99,235,.15);color:#60a5fa;display:flex;align-items:center;justify-content:center;font-size:1.3rem">📊</div>
      <div>
        <div class="card-title">Ripoti ya Daftari la Fedha (Ledger CSV)</div>
        <div class="card-sub">Miamala yote ya mapato, hisa, mikopo na faini</div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
      <div><label class="form-label">Kuanzia Tarehe</label><input x-model="from" type="date" class="form-input"></div>
      <div><label class="form-label">Mpaka Tarehe</label><input x-model="to" type="date" class="form-input"></div>
    </div>
    <a :href="'/export/ledger-csv?group_id=<?= $group_id ?>&from=' + (from||'') + '&to=' + (to||'')" target="_blank" class="btn btn-primary" style="justify-center">
      📥 Pakua Daftari Kuu (CSV)
    </a>
  </div>

  <!-- Member Statement CSV Export Card -->
  <div class="card card-p" style="display:flex;flex-direction:column;gap:1rem">
    <div style="display:flex;align-items:center;gap:.875rem">
      <div style="width:42px;height:42px;border-radius:12px;background:rgba(34,197,94,.15);color:#4ade80;display:flex;align-items:center;justify-content:center;font-size:1.3rem">👤</div>
      <div>
        <div class="card-title">Taarifa ya Mwanachama (Member Statement)</div>
        <div class="card-sub">Ripoti binafsi ya hisa na mikopo ya mwanachama mmoja</div>
      </div>
    </div>
    <div>
      <label class="form-label">Chagua Mwanachama</label>
      <select x-model="selectedMember" class="form-input">
        <option value="">-- Chagua Mwanachama --</option>
        <template x-for="m in members" :key="m.id">
          <option :value="m.id" x-text="m.full_name + ' (' + m.member_number + ')'"></option>
        </template>
      </select>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
      <a :href="'/export/statement-csv?group_id=<?= $group_id ?>&member_id=' + selectedMember" target="_blank" class="btn btn-secondary btn-sm" :style="!selectedMember?'opacity:.4;pointer-events:none':''">
        📥 CSV Statement
      </a>
      <a :href="'/export/statement-pdf?group_id=<?= $group_id ?>&member_id=' + selectedMember" target="_blank" class="btn btn-primary btn-sm" :style="!selectedMember?'opacity:.4;pointer-events:none':''">
        📑 PDF Statement
      </a>
    </div>
  </div>

  <!-- Annual Report Card -->
  <div class="card card-p" style="display:flex;flex-direction:column;gap:1rem">
    <div style="display:flex;align-items:center;gap:.875rem">
      <div style="width:42px;height:42px;border-radius:12px;background:rgba(168,85,247,.15);color:#c084fc;display:flex;align-items:center;justify-content:center;font-size:1.3rem">🏛️</div>
      <div>
        <div class="card-title">Ripoti ya Mwaka ya Kikundi (Annual PDF)</div>
        <div class="card-sub">Taarifa kamili ya uendeshaji na mahesabu ya mwaka</div>
      </div>
    </div>
    <a href="/export/annual-report-pdf?group_id=<?= $group_id ?>" target="_blank" class="btn btn-secondary" style="justify-center">
      📄 Pakua Ripoti ya Mwaka (PDF)
    </a>
  </div>

  <!-- Shareout CSV Export Card -->
  <div class="card card-p" style="display:flex;flex-direction:column;gap:1rem">
    <div style="display:flex;align-items:center;gap:.875rem">
      <div style="width:42px;height:42px;border-radius:12px;background:rgba(251,191,36,.15);color:#fbbf24;display:flex;align-items:center;justify-content:center;font-size:1.3rem">🎯</div>
      <div>
        <div class="card-title">Ripoti ya Mgawanyo (Share-Out CSV)</div>
        <div class="card-sub">Orodha ya migawanyo iliyofanyika mwisho wa mwaka</div>
      </div>
    </div>
    <a href="/export/shareout-csv?group_id=<?= $group_id ?>" target="_blank" class="btn btn-secondary" style="justify-center">
      📥 Pakua Mgawanyo (CSV)
    </a>
  </div>

</div>

</div>

<script>
function reportsPage() {
  return {
    from: '', to: '', selectedMember: '', members: [],
    async load() {
      try {
        const d = await fetch('/api/members').then(r=>r.json());
        if (d && d.members) this.members = d.members;
      } catch(e) {}
    }
  }
}
</script>
