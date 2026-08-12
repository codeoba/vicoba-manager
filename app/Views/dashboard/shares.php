<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="sharesPage()" x-init="load()">

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-extrabold text-slate-800">Daftari la Hisa</h1>
    <p class="text-sm text-slate-500 mt-0.5">Simamia ununuzi na michango ya hisa za wanachama</p>
  </div>
  <?php if(in_array($user->role,['super_admin','group_admin','treasurer','secretary'])): ?>
  <button @click="showModal('record-share-modal')" class="btn btn-primary">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>Rekodi Hisa Mpya</span>
  </button>
  <?php endif; ?>
</div>

<!-- Stats row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase">Jumla ya Hisa (Pool)</p>
    <p class="text-3xl font-extrabold text-blue-600 mt-1" x-text="money(total_pool)"></p>
    <p class="text-xs text-slate-400 mt-1">Mtaji wote wa kikundi</p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase">Hisa Mwezi Huu</p>
    <p class="text-3xl font-extrabold text-emerald-600 mt-1" x-text="money(this_month)"></p>
    <p class="text-xs text-slate-400 mt-1">Michango ya mwezi wa sasa</p>
  </div>
  <div class="stat-card">
    <p class="text-xs font-semibold text-slate-400 uppercase">Bei ya Hisa Moja</p>
    <p class="text-3xl font-extrabold text-slate-800 mt-1"><?= money($group->share_price ?? 1000) ?></p>
    <p class="text-xs text-slate-400 mt-1">Kiwango kilichopangwa</p>
  </div>
</div>

<!-- Tables: Member Shares Summary + Recent Shares -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- Member Shares Summary -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">📊 Muhtasari wa Hisa kwa Mwanachama</h3>
    </div>
    <div class="overflow-x-auto max-h-96 scrollbar-thin">
      <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 sticky top-0">
          <tr class="text-xs font-semibold text-slate-500 uppercase border-b">
            <th class="py-3 px-4">Mwanachama</th>
            <th class="py-3 px-4 text-center">Idadi ya Hisa</th>
            <th class="py-3 px-4 text-right">Thamani (TZS)</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          <template x-for="m in member_summary" :key="m.id">
            <tr class="hover:bg-slate-50">
              <td class="py-3 px-4 font-medium text-slate-800" x-text="m.full_name"></td>
              <td class="py-3 px-4 text-center font-bold text-blue-600" x-text="m.total_shares"></td>
              <td class="py-3 px-4 text-right font-bold text-slate-700" x-text="money(m.total_amount)"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">🧾 Miamala ya Hisa ya Hivi Karibuni</h3>
    </div>
    <div class="overflow-x-auto max-h-96 scrollbar-thin">
      <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 sticky top-0">
          <tr class="text-xs font-semibold text-slate-500 uppercase border-b">
            <th class="py-3 px-4">Tarehe</th>
            <th class="py-3 px-4">Mwanachama</th>
            <th class="py-3 px-4 text-center">Hisa</th>
            <th class="py-3 px-4 text-right">Kiasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          <template x-for="s in recent" :key="s.id">
            <tr class="hover:bg-slate-50">
              <td class="py-3 px-4 text-slate-500 text-xs" x-text="s.payment_date"></td>
              <td class="py-3 px-4 font-medium text-slate-800" x-text="s.member_name"></td>
              <td class="py-3 px-4 text-center font-semibold text-blue-600" x-text="'+' + s.share_count"></td>
              <td class="py-3 px-4 text-right font-bold text-emerald-600" x-text="money(s.total_amount)"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- RECORD SHARE MODAL -->
<div id="record-share-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h3 class="font-bold text-slate-800 text-base">Rekodi Ununuzi wa Hisa</h3>
      <button @click="hideModal('record-share-modal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
    </div>
    <form @submit.prevent="recordShare" class="px-6 py-4 space-y-4">
      <div>
        <label class="form-label">Chagua Mwanachama *</label>
        <select x-model="form.member_id" required class="form-input">
          <option value="">-- Chagua Mwanachama --</option>
          <template x-for="m in members" :key="m.id">
            <option :value="m.id" x-text="m.full_name + ' (' + m.member_number + ')'"></option>
          </template>
        </select>
      </div>
      <div>
        <label class="form-label">Idadi ya Hisa *</label>
        <input x-model.number="form.share_count" type="number" min="1" required class="form-input" placeholder="Mfano: 5">
        <p class="text-xs text-slate-400 mt-1">Bei kwa hisa: <?= money($group->share_price ?? 1000) ?>. Jumla: <span class="font-bold text-blue-600" x-text="money((form.share_count||0) * <?= (float)($group->share_price ?? 1000) ?>)"></span></p>
      </div>
      <div>
        <label class="form-label">Njia ya Malipo</label>
        <select x-model="form.payment_method" class="form-input">
          <option value="cash">Pesa Taslimu (Cash)</option>
          <option value="mobile_money">Mobile Money (M-Pesa, TigoPesa, Airtel)</option>
          <option value="bank">Bank Transfer</option>
        </select>
      </div>
      <div>
        <label class="form-label">Kumbukumbu ya Malipo (Kama ipo)</label>
        <input x-model="form.payment_reference" type="text" class="form-input" placeholder="Mfano: QGH88921">
      </div>
      <div>
        <label class="form-label">Tarehe ya Malipo</label>
        <input x-model="form.payment_date" type="date" class="form-input">
      </div>

      <div class="flex justify-end gap-3 pt-3 border-t">
        <button type="button" @click="hideModal('record-share-modal')" class="btn btn-secondary">Ghairi</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <template x-if="saving"><span>⏳ Inahifadhi...</span></template>
          <template x-if="!saving"><span>💾 Rekodi Hisa</span></template>
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
function sharesPage() {
  return {
    total_pool:0, this_month:0, member_summary:[], recent:[], members:[], form:{share_count:1, payment_method:'cash'}, saving:false,
    async load() {
      const d = await api('/api/shares');
      if (d) {
        this.total_pool = d.total_pool;
        this.this_month = d.this_month;
        this.member_summary = d.member_summary;
        this.recent = d.recent;
      }
      const m = await api('/api/members');
      if (m) this.members = m.members;
    },
    async recordShare() {
      this.saving = true;
      const d = await api('/api/shares/record','POST', this.form);
      this.saving = false;
      if (d) {
        hideModal('record-share-modal');
        this.form = {share_count:1, payment_method:'cash'};
        this.load();
        Swal.fire({icon:'success', title:'Imerekodiwa!', text:'Hisa zimerekodiwa kikamilifu.', confirmButtonColor:'#2563eb', timer:2000});
      }
    }
  }
}
</script>
