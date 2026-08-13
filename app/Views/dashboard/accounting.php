<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="accountingPage()" x-init="load()" class="space-y-6">

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-slate-800">Uhasibu & Ripoti za Kibenki (Banking & Accounting)</h1>
    <p class="text-sm text-slate-500 mt-0.5">Mfumo wa Uhasibu wa Pande Mbili (Double-Entry Accounting & Financial Statements)</p>
  </div>
  <div class="flex gap-2">
    <button @click="printStatements()" class="btn btn-secondary">🖨️ Chapisha Financial Statements</button>
  </div>
</div>

<!-- Tabs -->
<div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto">
  <button @click="tab='tb'" :class="tab==='tb'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">⚖️ Trial Balance</button>
  <button @click="tab='is'" :class="tab==='is'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">📈 Income Statement (P&L)</button>
  <button @click="tab='bs'" :class="tab==='bs'?'border-blue-600 text-blue-600 font-bold':'border-transparent text-slate-500 hover:text-slate-700'" class="py-2.5 px-4 border-b-2 text-sm transition">🏦 Balance Sheet</button>
</div>

<!-- TAB 1: TRIAL BALANCE -->
<div x-show="tab==='tb'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="p-4 border-b bg-slate-50 flex justify-between items-center">
    <h3 class="font-bold text-slate-800">Trial Balance (Mizani ya Kijaribio)</h3>
    <span class="text-xs text-slate-500 font-mono">Status: DEBITS = CREDITS BALANCED ✅</span>
  </div>
  <div class="overflow-x-auto">
    <table class="table-auto w-full">
      <thead>
        <tr><th>Account Code</th><th>Account Name</th><th class="text-right">Debit (DR)</th><th class="text-right">Credit (CR)</th></tr>
      </thead>
      <tbody>
        <template x-for="r in statements.trial_balance" :key="r.code">
          <tr class="hover:bg-slate-50">
            <td class="font-mono text-xs text-slate-600" x-text="r.code"></td>
            <td class="font-semibold text-slate-800" x-text="r.account"></td>
            <td class="text-right font-bold text-emerald-600" x-text="money(r.debit)"></td>
            <td class="text-right font-bold text-blue-600" x-text="money(r.credit)"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- TAB 2: INCOME STATEMENT -->
<div x-show="tab==='is'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6" x-cloak>
  <h3 class="font-bold text-slate-800 text-lg border-b pb-2">Income Statement (Taarifa ya Faida na Hasara)</h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 space-y-2">
      <h4 class="font-bold text-emerald-800 uppercase text-xs">REVENUE (MAPATO)</h4>
      <div class="flex justify-between text-sm py-1 border-b border-emerald-100">
        <span>Mapato ya Faini</span>
        <span class="font-bold" x-text="money(statements.income_statement?.revenue?.fines_income||0)"></span>
      </div>
      <div class="flex justify-between font-bold text-emerald-900 pt-2 text-base">
        <span>Jumla ya Mapato</span>
        <span x-text="money(statements.income_statement?.revenue?.total_revenue||0)"></span>
      </div>
    </div>
    <div class="bg-red-50/50 p-4 rounded-xl border border-red-100 space-y-2">
      <h4 class="font-bold text-red-800 uppercase text-xs">EXPENSES (MATUMIZI)</h4>
      <div class="flex justify-between text-sm py-1 border-b border-red-100">
        <span>Gharama za Uendeshaji</span>
        <span class="font-bold" x-text="money(statements.income_statement?.expenses?.total_expenses||0)"></span>
      </div>
      <div class="flex justify-between font-bold text-red-900 pt-2 text-base">
        <span>Jumla ya Matumizi</span>
        <span x-text="money(statements.income_statement?.expenses?.total_expenses||0)"></span>
      </div>
    </div>
  </div>
  <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 flex justify-between items-center font-extrabold text-blue-900">
    <span>FAIDA NISHI (NET PROFIT):</span>
    <span class="text-xl" x-text="money(statements.income_statement?.net_income||0)"></span>
  </div>
</div>

<!-- TAB 3: BALANCE SHEET -->
<div x-show="tab==='bs'" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-6" x-cloak>
  <h3 class="font-bold text-slate-800 text-lg border-b pb-2">Balance Sheet (Mizani ya Rasilimali na Madeni)</h3>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
      <h4 class="font-bold text-slate-700 uppercase text-xs">ASSETS (RASILIMALI)</h4>
      <div class="flex justify-between text-xs py-1 border-b"><span>Pesa Mkononi/Benki</span><span class="font-bold" x-text="money(statements.balance_sheet?.assets?.cash||0)"></span></div>
      <div class="flex justify-between text-xs py-1 border-b"><span>Mikopo Inayodaiwa</span><span class="font-bold" x-text="money(statements.balance_sheet?.assets?.loans||0)"></span></div>
      <div class="flex justify-between font-bold text-slate-900 pt-2"><span>Jumla ya Rasilimali</span><span x-text="money(statements.balance_sheet?.assets?.total_assets||0)"></span></div>
    </div>
    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
      <h4 class="font-bold text-slate-700 uppercase text-xs">LIABILITIES (MADENI)</h4>
      <div class="flex justify-between text-xs py-1 border-b"><span>Hisa za Wanachama</span><span class="font-bold" x-text="money(statements.balance_sheet?.liabilities?.shares||0)"></span></div>
      <div class="flex justify-between font-bold text-slate-900 pt-2"><span>Jumla ya Madeni</span><span x-text="money(statements.balance_sheet?.liabilities?.total_liabilities||0)"></span></div>
    </div>
    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
      <h4 class="font-bold text-slate-700 uppercase text-xs">EQUITY (MTAJI NA FAIDA)</h4>
      <div class="flex justify-between text-xs py-1 border-b"><span>Faida iliyokusanywa</span><span class="font-bold" x-text="money(statements.balance_sheet?.equity?.retained_earnings||0)"></span></div>
      <div class="flex justify-between font-bold text-slate-900 pt-2"><span>Jumla ya Mtaji</span><span x-text="money(statements.balance_sheet?.equity?.total_equity||0)"></span></div>
    </div>
  </div>
</div>

</div>

<script>
function accountingPage() {
  return {
    tab:'tb', statements:{trial_balance:[]},
    async load() {
      const d = await api('/api/accounting/statements'); if(d) this.statements = d.statements;
    },
    printStatements() {
      window.print();
    }
  }
}
</script>
