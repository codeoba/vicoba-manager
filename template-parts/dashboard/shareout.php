<?php
/**
 * Dashboard Share-Out Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$sim = VICOBA_Shareout::calculate_shareout_distribution($group_id);
$is_error = is_wp_error($sim);

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_finalize = in_array($user_role, array('super_admin', 'group_admin'));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mgawanyo wa Mwaka (Share-Out)</h1>
            <p class="text-xs text-slate-500 mt-1">Uhesabuji na mgawanyo wa faida ya mzunguko kwa uwiano wa hisa za kila mwanachama</p>
        </div>

        <?php if ($can_finalize && !$is_error) : ?>
            <button onclick="finalizeShareout()" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-lock mr-2"></i> Kufunga Mzunguko & Kufanya Mgawanyo
            </button>
        <?php endif; ?>
    </div>

    <?php if ($is_error) : ?>
        <div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-center font-bold text-sm">
            <i class="fa-solid fa-circle-exclamation text-2xl mb-2 text-rose-600"></i>
            <p><?php echo $sim->get_error_message(); ?></p>
        </div>
    <?php else : ?>
        <!-- Shareout Math Breakdown Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold uppercase text-slate-400">Jumla ya Mtaji wa Hisa</span>
                <p class="text-xl font-extrabold text-slate-800 mt-1">TZS <?php echo number_format($sim['total_shares_pool']); ?></p>
            </div>

            <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold uppercase text-slate-400">Faida ya Riba & Faini</span>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">+ TZS <?php echo number_format($sim['total_interest_profit'] + $sim['total_fines_collected']); ?></p>
            </div>

            <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold uppercase text-slate-400">Gharama & Mikopo Sugu</span>
                <p class="text-xl font-extrabold text-rose-600 mt-1">- TZS <?php echo number_format($sim['total_expenses'] + $sim['total_bad_debt']); ?></p>
            </div>

            <div class="glass-card p-5 rounded-2xl border border-emerald-300 bg-emerald-50/50 shadow-xs">
                <span class="text-xs font-bold uppercase text-emerald-800">Faida ya Hisa 1 (Dividend/Share)</span>
                <p class="text-xl font-extrabold text-emerald-900 mt-1">TZS <?php echo number_format($sim['dividend_per_share'], 2); ?></p>
            </div>
        </div>

        <!-- Distribution Table per Member -->
        <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
                <i class="fa-solid fa-calculator text-vicoba-600 mr-2"></i> Ratiba ya Mgawanyo kwa Wanachama
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                            <th class="py-3.5 px-4">Mwanachama</th>
                            <th class="py-3.5 px-4">Hisa Zake</th>
                            <th class="py-3.5 px-4">Uwiano (%)</th>
                            <th class="py-3.5 px-4">Mgawanyo Ghafi (Gross)</th>
                            <th class="py-3.5 px-4">Makato ya Deni</th>
                            <th class="py-3.5 px-4 text-right">Kiasi Anachochukua (Net)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php foreach ($sim['member_payouts'] as $p) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <?php echo esc_html($p['full_name']); ?> <span class="text-[10px] text-slate-400 font-normal">(<?php echo esc_html($p['member_number']); ?>)</span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-amber-600"><?php echo number_format($p['total_member_shares']); ?> Hisa</td>
                                <td class="py-3.5 px-4 font-mono text-slate-600"><?php echo number_format($p['share_ratio'] * 100, 2); ?>%</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">TZS <?php echo number_format($p['gross_payout']); ?></td>
                                <td class="py-3.5 px-4 text-rose-600 font-semibold">- TZS <?php echo number_format($p['active_loan_deduction']); ?></td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-emerald-700 text-sm">TZS <?php echo number_format($p['net_payout']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
async function finalizeShareout() {
    const confirm = await Swal.fire({
        title: 'Unathibitisha Kufunga Mzunguko?',
        text: 'Hii itafanya mgawanyo rasmi wa fedha na kuandika miamala yote kwenye ledger. Kitendo hiki hakiwezi kufutwa!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Ndiyo, Tekeleza Mgawanyo!'
    });

    if (confirm.isConfirmed) {
        try {
            const res = await fetch(vicobaData.root + 'vicoba/v1/shareout/finalize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': vicobaData.nonce,
                },
                body: JSON.stringify({ group_id: <?php echo $group_id; ?> })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Umekamilika!', data.message, 'success').then(() => location.reload());
            }
        } catch(err) {
            Swal.fire('Hitilafu', 'Imeshindikana kutekeleza Share-Out.', 'error');
        }
    }
}
</script>
