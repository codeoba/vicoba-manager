<?php $group_id = (int)($group->id ?? 0); ?>
<div x-data="accountingPage()" x-init="load()" style="display:flex;flex-direction:column;gap:1.5rem">

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.25rem">📊 Uhasibu & Ripoti za Kibenki (Banking & Accounting)</h1>
    <p style="font-size:.83rem;color:rgba(255,255,255,.45)">Mfumo wa Uhasibu wa Pande Mbili (Double-Entry Accounting & Financial Statements)</p>
  </div>
  <button @click="printStatements()" class="btn btn-secondary">🖨️ Chapisha Financial Statements</button>
</div>

<!-- Tabs -->
<div style="display:flex;gap:.5rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;overflow-x:auto">
  <button @click="tab='tb'" :class="tab==='tb'?'badge badge-info':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">⚖️ Trial Balance</button>
  <button @click="tab='is'" :class="tab==='is'?'badge badge-success':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">📈 Income Statement (P&L)</button>
  <button @click="tab='bs'" :class="tab==='bs'?'badge badge-purple':'badge badge-muted'" style="cursor:pointer;padding:.5rem 1rem">🏦 Balance Sheet</button>
</div>

<!-- TAB 1: TRIAL BALANCE -->
<div x-show="tab==='tb'" class="card" style="overflow:hidden">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div class="card-title">Trial Balance (Mizani ya Kijaribio)</div>
    <span class="badge badge-success">DEBITS = CREDITS BALANCED ✅</span>
  </div>
  <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr><th>Account Code</th><th>Account Name</th><th style="text-align:right">Debit (DR)</th><th style="text-align:right">Credit (CR)</th></tr>
      </thead>
      <tbody>
        <template x-for="r in statements.trial_balance" :key="r.code">
          <tr>
            <td style="font-family:monospace;font-size:.78rem;color:rgba(255,255,255,.5)" x-text="r.code"></td>
            <td style="font-weight:700;color:#fff" x-text="r.account"></td>
            <td style="text-align:right;font-weight:800;color:#4ade80" x-text="money(r.debit)"></td>
            <td style="text-align:right;font-weight:800;color:#60a5fa" x-text="money(r.credit)"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

<!-- TAB 2: INCOME STATEMENT -->
<div x-show="tab==='is'" class="card" style="overflow:hidden">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
    <div class="card-title">Income Statement (Taarifa ya Faida na Hasara)</div>
  </div>
  <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem">
    <div style="font-weight:800;color:#4ade80;font-size:.9rem">MAPATO (INCOME)</div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Riba za Mikopo Zilizopatikana:</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.income_statement.interest_income)"></span>
    </div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Faini na Adhabu Zilizokusanywa:</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.income_statement.fine_income)"></span>
    </div>
    <div style="border-top:1px dashed var(--border);padding-top:.5rem;display:flex;justify-content:space-between;font-weight:800;color:#fff">
      <span>Jumla ya Mapato:</span>
      <span style="color:#4ade80" x-text="money(statements.income_statement.total_income)"></span>
    </div>

    <div style="font-weight:800;color:#f87171;font-size:.9rem;margin-top:1rem">MATUMIZI (EXPENSES)</div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Gharama za Uendeshaji:</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.income_statement.operating_expenses)"></span>
    </div>
    <div style="border-top:1px dashed var(--border);padding-top:.5rem;display:flex;justify-content:space-between;font-weight:800;color:#fff">
      <span>Jumla ya Matumizi:</span>
      <span style="color:#f87171" x-text="money(statements.income_statement.total_expenses)"></span>
    </div>

    <div style="border-top:2px solid var(--border);padding-top:1rem;margin-top:1rem;display:flex;justify-content:space-between;font-size:1.1rem;font-weight:900">
      <span>NET PROFIT (FAIDA SAFI):</span>
      <span style="color:#4ade80" x-text="money(statements.income_statement.net_profit)"></span>
    </div>
  </div>
</div>

<!-- TAB 3: BALANCE SHEET -->
<div x-show="tab==='bs'" class="card" style="overflow:hidden">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
    <div class="card-title">Balance Sheet (Mizani ya Mali na Madeni)</div>
  </div>
  <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem">
    <div style="font-weight:800;color:#60a5fa;font-size:.9rem">RILIMALI / MALI (ASSETS)</div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Fedha Taslimu (Cash & Bank):</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.balance_sheet.cash)"></span>
    </div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Mikopo Inayodaiwa (Loans Receivable):</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.balance_sheet.loans_receivable)"></span>
    </div>
    <div style="border-top:1px dashed var(--border);padding-top:.5rem;display:flex;justify-content:space-between;font-weight:800;color:#fff">
      <span>Jumla ya Mali (Total Assets):</span>
      <span style="color:#60a5fa" x-text="money(statements.balance_sheet.total_assets)"></span>
    </div>

    <div style="font-weight:800;color:#a78bfa;font-size:.9rem;margin-top:1rem">EQUITY & LIABILITIES (MTAJI NA MADENI)</div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Pool ya Hisa (Share Capital):</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.balance_sheet.share_capital)"></span>
    </div>
    <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,.8);font-size:.85rem;padding-left:1rem">
      <span>Akiba Zilizokusanywa (Retained Earnings):</span>
      <span style="font-weight:800;color:#fff" x-text="money(statements.balance_sheet.retained_earnings)"></span>
    </div>
    <div style="border-top:1px dashed var(--border);padding-top:.5rem;display:flex;justify-content:space-between;font-weight:800;color:#fff">
      <span>Jumla ya Mtaji & Madeni:</span>
      <span style="color:#a78bfa" x-text="money(statements.balance_sheet.total_equity_liabilities)"></span>
    </div>
  </div>
</div>

</div>

<script>
function accountingPage() {
  return {
    tab: 'tb', statements: { trial_balance: [], income_statement: {}, balance_sheet: {} }, loading: true,
    async load() {
      this.loading = true;
      try {
        const d = await fetch('/api/accounting/statements').then(r=>r.json());
        if (d && d.statements) this.statements = d.statements;
      } catch(e) {}
      this.loading = false;
    },
    printStatements() { window.print(); },
    money(v) { return 'TZS ' + Number(v||0).toLocaleString(); }
  }
}
</script>
