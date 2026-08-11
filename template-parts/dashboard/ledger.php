<?php
/**
 * Dashboard Financial Ledger Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$ledger = VICOBA_Ledger::get_box_balance($group_id);
$transactions = VICOBA_Ledger::get_ledger_transactions($group_id, 100);

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_manage = in_array($user_role, array('super_admin', 'group_admin', 'treasurer'));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Ledger ya Fedha & Sanduku</h1>
            <p class="text-xs text-slate-500 mt-1">Single Source of Truth kwa miamala yote ya fedha za kikundi</p>
        </div>

        <?php if ($can_manage) : ?>
            <button onclick="document.getElementById('addExpenseModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-receipt mr-2"></i> Ingiza Gharama za Uendeshaji
            </button>
        <?php endif; ?>
    </div>

    <!-- 4 Account Breakdown Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-slate-400">Salio la Jumla (Box)</span>
            <p class="text-xl font-extrabold text-slate-800 mt-1">TZS <?php echo number_format($ledger['box_balance']); ?></p>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-slate-400">Pesa Taslimu (Cash)</span>
            <p class="text-xl font-extrabold text-emerald-700 mt-1">TZS <?php echo number_format($ledger['cash_balance']); ?></p>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-slate-400">Mobile Money</span>
            <p class="text-xl font-extrabold text-sky-700 mt-1">TZS <?php echo number_format($ledger['mobile_balance']); ?></p>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase text-slate-400">Akaunti ya Benki</span>
            <p class="text-xl font-extrabold text-indigo-700 mt-1">TZS <?php echo number_format($ledger['bank_balance']); ?></p>
        </div>
    </div>

    <!-- Ledger Log Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-book-bookmark text-vicoba-600 mr-2"></i> Kumbukumbu Zote Za Miamala (General Ledger)
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Kodi ya Muamala</th>
                        <th class="py-3.5 px-4">Aina</th>
                        <th class="py-3.5 px-4">Maelezo</th>
                        <th class="py-3.5 px-4">Kiasi (TZS)</th>
                        <th class="py-3.5 px-4">Njia ya Malipo</th>
                        <th class="py-3.5 px-4 text-right">Tarehe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($transactions)) : ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Hakuna miamala iliyorekodiwa kwenye ledger bado.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($transactions as $t) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800"><?php echo esc_html($t->transaction_code); ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 border">
                                        <?php echo str_replace('_', ' ', esc_html($t->type)); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4"><?php echo esc_html($t->description); ?></td>
                                <td class="py-3.5 px-4 font-extrabold <?php echo in_array($t->type, array('share_purchase', 'loan_repayment', 'fine_payment', 'social_fund_in')) ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                    <?php echo in_array($t->type, array('share_purchase', 'loan_repayment', 'fine_payment', 'social_fund_in')) ? '+' : '-'; ?> TZS <?php echo number_format($t->amount); ?>
                                </td>
                                <td class="py-3.5 px-4 uppercase text-[10px] font-bold text-slate-500"><?php echo esc_html($t->payment_method); ?></td>
                                <td class="py-3.5 px-4 text-right text-slate-500"><?php echo date('d/m/Y H:i', strtotime($t->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Expense -->
<div id="addExpenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-receipt mr-2 text-slate-700"></i> Rekodi Gharama za Uendeshaji</h3>
            <button onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="addExpenseForm" class="mt-4 space-y-4">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kiasi cha Gharama (TZS) *</label>
                <input name="amount" type="number" step="500" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="mf. 15000">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Maelezo ya Matumizi / Gharama *</label>
                <input name="description" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="mf. Ununuzi wa daftari la mikutano na kalamu">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Njia ya Malipo *</label>
                <select name="payment_method" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="cash">Pesa Taslimu (Cash)</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank">Benki</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveExpenseBtn" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md">Hifadhi Gharama</button>
            </div>
        </form>
    </div>
</div>
