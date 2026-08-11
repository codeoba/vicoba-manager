<?php
/**
 * Dashboard Shares Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;
$group = VICOBA_Groups::get_group($group_id);

$members = VICOBA_Members::get_members_by_group($group_id, 'active');
$shares_history = VICOBA_Shares::get_shares_by_group($group_id, 100);
$group_total = VICOBA_Shares::get_group_total_shares($group_id);
$member_total = $member ? VICOBA_Shares::get_member_total_shares($group_id, $member->id) : array('count' => 0, 'value' => 0);

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_record = in_array($user_role, array('super_admin', 'group_admin', 'treasurer'));
?>

<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Hisa na Michango</h1>
            <p class="text-xs text-slate-500 mt-1">
                Bei ya Hisa 1: <strong class="text-vicoba-700">TZS <?php echo number_format($group ? $group->share_price : 10000); ?></strong> | 
                Kikomo kwa mzunguko: <strong class="text-vicoba-700"><?php echo $group ? $group->min_shares_per_cycle : 1; ?> – <?php echo $group ? $group->max_shares_per_cycle : 5; ?> Hisa</strong>
            </p>
        </div>

        <?php if ($can_record) : ?>
            <button onclick="document.getElementById('buySharesModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:from-vicoba-700 hover:to-emerald-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-plus-circle mr-2"></i> Kuingiza Malipo ya Hisa
            </button>
        <?php endif; ?>
    </div>

    <!-- 2 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="glass-card p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Hisa Zangu (Personal Shares)</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">TZS <?php echo number_format($member_total['value']); ?></p>
                <p class="text-xs font-semibold text-amber-600 mt-1"><?php echo number_format($member_total['count']); ?> Hisa Zilizochangwa</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-user-gear"></i>
            </div>
        </div>

        <div class="glass-card p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Jumla ya Hisa za Kikundi</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">TZS <?php echo number_format($group_total['value']); ?></p>
                <p class="text-xs font-semibold text-emerald-600 mt-1"><?php echo number_format($group_total['count']); ?> Hisa Zote</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>
    </div>

    <!-- Shares Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-clock-rotate-left text-vicoba-600 mr-2"></i> Historia ya Ununuzi wa Hisa
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Mwanachama</th>
                        <th class="py-3.5 px-4">Idadi ya Hisa</th>
                        <th class="py-3.5 px-4">Bei ya Hisa 1</th>
                        <th class="py-3.5 px-4">Jumla ya Kiasi</th>
                        <th class="py-3.5 px-4">Njia ya Malipo</th>
                        <th class="py-3.5 px-4 text-right">Tarehe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($shares_history)) : ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Hakuna kumbukumbu za hisa zilizohifadhiwa bado.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($shares_history as $s) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <?php echo esc_html($s->full_name); ?> <span class="text-[10px] text-slate-400 font-normal">(<?php echo esc_html($s->member_number); ?>)</span>
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-amber-600">+<?php echo number_format($s->share_count); ?> Hisa</td>
                                <td class="py-3.5 px-4">TZS <?php echo number_format($s->unit_price); ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">TZS <?php echo number_format($s->total_amount); ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 border">
                                        <?php echo str_replace('_', ' ', esc_html($s->payment_method ?? 'cash')); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right text-slate-500"><?php echo date('d/m/Y', strtotime($s->payment_date)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Buy Shares -->
<div id="buySharesModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-coins mr-2 text-amber-500"></i> Kuingiza Malipo ya Hisa</h3>
            <button onclick="document.getElementById('buySharesModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="buySharesForm" class="mt-4 space-y-4">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Chagua Mwanachama *</label>
                <select name="member_id" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="">-- Chagua Mwanachama --</option>
                    <?php foreach ($members as $m) : ?>
                        <option value="<?php echo $m->id; ?>"><?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Idadi ya Hisa (<?php echo $group ? $group->min_shares_per_cycle : 1; ?> - <?php echo $group ? $group->max_shares_per_cycle : 5; ?>) *</label>
                <input name="share_count" type="number" min="<?php echo $group ? $group->min_shares_per_cycle : 1; ?>" max="<?php echo $group ? $group->max_shares_per_cycle : 5; ?>" value="1" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" oninput="updateTotalShareValue(this.value)">
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 text-xs flex justify-between items-center font-bold">
                <span class="text-amber-800">Jumla ya Kiasi cha Malipo:</span>
                <span class="text-amber-900 text-sm" id="calcTotalShareValue">TZS <?php echo number_format($group ? $group->share_price : 10000); ?></span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Njia ya Malipo *</label>
                <select name="payment_method" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="cash">Pesa Taslimu (Cash)</option>
                    <option value="mobile_money">Mobile Money (M-Pesa / Tigo / Airtel)</option>
                    <option value="bank">Akaunti ya Benki</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('buySharesModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveShareBtn" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">Hifadhi Malipo</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateTotalShareValue(count) {
    const unit = <?php echo $group ? $group->share_price : 10000; ?>;
    const total = (parseInt(count) || 0) * unit;
    document.getElementById('calcTotalShareValue').innerText = 'TZS ' + total.toLocaleString();
}
</script>
