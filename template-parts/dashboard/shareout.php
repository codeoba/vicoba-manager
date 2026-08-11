<?php
/**
 * Dashboard: Share-Out (Mgawanyo) - Complete
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id     = $member ? $member->group_id : 1;
$group        = VICOBA_Groups::get_group($group_id);
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_admin     = in_array($user_role, ['super_admin','group_admin','treasurer','administrator']);

global $wpdb;
// Calculate share-out preview
$members_active = VICOBA_Members::get_members_by_group($group_id, 'active');
$shares_table   = $wpdb->prefix . 'vicoba_shares';
$loans_table    = $wpdb->prefix . 'vicoba_loans';
$sf_table       = $wpdb->prefix . 'vicoba_social_fund';
$fines_table    = $wpdb->prefix . 'vicoba_fines';
$ledger_table   = $wpdb->prefix . 'vicoba_ledger';
$so_table       = $wpdb->prefix . 'vicoba_shareout';
$so_dist_table  = $wpdb->prefix . 'vicoba_shareout_distributions';

// Totals for preview
$total_shares_value = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(total_amount),0) FROM $shares_table WHERE group_id=%d", $group_id));
$total_interest     = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(interest_amount),0) FROM $loans_table WHERE group_id=%d AND status='closed'", $group_id));
$total_fines        = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $fines_table WHERE group_id=%d AND status='paid'", $group_id));
$total_sf_contrib   = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}vicoba_social_fund_contributions WHERE group_id=%d", $group_id));
$total_sf_out       = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $sf_table WHERE group_id=%d AND status='approved'", $group_id));
$total_expenses     = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $ledger_table WHERE group_id=%d AND type='expense'", $group_id));

$pool = $total_shares_value + $total_interest + $total_fines - $total_sf_out - $total_expenses;

// Past shareout distributions
$past_shareouts = $wpdb->get_results($wpdb->prepare(
    "SELECT so.*, COUNT(d.id) as dist_count FROM $so_table so LEFT JOIN $so_dist_table d ON so.id=d.shareout_id WHERE so.group_id=%d GROUP BY so.id ORDER BY so.created_at DESC LIMIT 10", $group_id
));
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Share-Out (Mgawanyo)</h1>
            <p class="text-xs text-slate-500 mt-1">Hesabu na fanya mgawanyo wa mapato ya mzunguko kwa wanachama</p>
        </div>
    </div>

    <!-- Pool Preview -->
    <div class="glass-card p-6 rounded-2xl border border-vicoba-200/60 bg-gradient-to-br from-vicoba-50 to-emerald-50 shadow-sm">
        <h3 class="text-sm font-extrabold text-vicoba-800 mb-4 flex items-center">
            <i class="fa-solid fa-calculator mr-2 text-vicoba-600"></i> Muhtasari wa Fedha za Mzunguko (Preview)
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-3 bg-white/70 rounded-xl border border-vicoba-200/60 text-center">
                <p class="text-[10px] font-bold uppercase text-vicoba-400">Jumla ya Hisa</p>
                <p class="text-sm font-extrabold text-vicoba-800 mt-1">TZS <?php echo number_format($total_shares_value); ?></p>
            </div>
            <div class="p-3 bg-white/70 rounded-xl border border-emerald-200/60 text-center">
                <p class="text-[10px] font-bold uppercase text-emerald-400">Riba ya Mikopo</p>
                <p class="text-sm font-extrabold text-emerald-800 mt-1">TZS <?php echo number_format($total_interest); ?></p>
            </div>
            <div class="p-3 bg-white/70 rounded-xl border border-amber-200/60 text-center">
                <p class="text-[10px] font-bold uppercase text-amber-400">Faini Zilizokusanywa</p>
                <p class="text-sm font-extrabold text-amber-800 mt-1">TZS <?php echo number_format($total_fines); ?></p>
            </div>
            <div class="p-3 bg-white/70 rounded-xl border border-rose-200/60 text-center">
                <p class="text-[10px] font-bold uppercase text-rose-400">Gharama za Uendeshaji</p>
                <p class="text-sm font-extrabold text-rose-800 mt-1">- TZS <?php echo number_format($total_expenses); ?></p>
            </div>
            <div class="p-3 bg-white/70 rounded-xl border border-rose-200/60 text-center">
                <p class="text-[10px] font-bold uppercase text-rose-400">Mfuko wa Jamii Uliotolewa</p>
                <p class="text-sm font-extrabold text-rose-800 mt-1">- TZS <?php echo number_format($total_sf_out); ?></p>
            </div>
            <div class="p-3 bg-gradient-to-br from-vicoba-600 to-emerald-600 rounded-xl text-center">
                <p class="text-[10px] font-bold uppercase text-white/80">POOL YA KUGAWANYWA</p>
                <p class="text-base font-extrabold text-white mt-1">TZS <?php echo number_format(max(0,$pool)); ?></p>
            </div>
        </div>

        <!-- Per-member preview -->
        <?php if (count($members_active) > 0 && $pool > 0): ?>
        <div class="border-t border-vicoba-200/60 pt-4">
            <h4 class="text-xs font-extrabold text-vicoba-700 mb-3 flex items-center"><i class="fa-solid fa-chart-pie mr-1.5"></i> Mgawanyo wa Kila Mwanachama (Mfano)</h4>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php
                $total_group_shares = (float)$wpdb->get_var($wpdb->prepare(
                    "SELECT COALESCE(SUM(share_count),0) FROM $shares_table WHERE group_id=%d", $group_id));
                foreach ($members_active as $ma):
                    $my_shares = (float)$wpdb->get_var($wpdb->prepare(
                        "SELECT COALESCE(SUM(share_count),0) FROM $shares_table WHERE group_id=%d AND member_id=%d", $group_id, $ma->id));
                    $pct = $total_group_shares > 0 ? ($my_shares / $total_group_shares) : (1 / count($members_active));
                    $payout = round($pool * $pct);
                ?>
                <div class="flex items-center justify-between py-1.5 px-3 bg-white/60 rounded-xl text-xs border border-vicoba-100">
                    <div class="flex items-center">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-vicoba-500 to-emerald-400 text-white font-bold flex items-center justify-center text-[10px] mr-2">
                            <?php echo strtoupper(substr($ma->full_name, 0, 1)); ?>
                        </div>
                        <span class="font-semibold text-slate-800"><?php echo esc_html($ma->full_name); ?></span>
                        <span class="ml-2 text-slate-400"><?php echo number_format($my_shares, 0); ?> hisa (<?php echo number_format($pct * 100, 1); ?>%)</span>
                    </div>
                    <span class="font-extrabold text-vicoba-700">TZS <?php echo number_format($payout); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($is_admin && $pool > 0): ?>
        <div class="mt-5 flex justify-end">
            <button id="finalizeShareoutBtn"
                class="inline-flex items-center px-6 py-3 rounded-2xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:opacity-90 text-white font-bold text-sm shadow-xl transition">
                <i class="fa-solid fa-flag-checkered mr-2"></i> Fanya Mgawanyo Sasa (Finalize)
            </button>
        </div>
        <?php endif; ?>
        <?php if ($pool <= 0): ?>
        <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-bold">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i> Bado kuna mikopo inayoendelea au hakuna fedha za kutosha kwa mgawanyo.
        </div>
        <?php endif; ?>
    </div>

    <!-- Past Shareouts -->
    <?php if (!empty($past_shareouts)): ?>
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800">
            <i class="fa-solid fa-history text-vicoba-600 mr-2"></i> Historia ya Mgawanyo Uliopita
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Tarehe</th>
                        <th class="py-3.5 px-4">Jumla ya Pool</th>
                        <th class="py-3.5 px-4">Wanachama Waliopewa</th>
                        <th class="py-3.5 px-4">Hali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php foreach ($past_shareouts as $so): ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-bold text-vicoba-800"><?php echo date('d M Y', strtotime($so->created_at)); ?></td>
                        <td class="py-3.5 px-4 font-extrabold text-vicoba-700">TZS <?php echo number_format($so->total_pool ?? 0); ?></td>
                        <td class="py-3.5 px-4"><?php echo (int)$so->dist_count; ?> wanachama</td>
                        <td class="py-3.5 px-4"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Imekamilika</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
