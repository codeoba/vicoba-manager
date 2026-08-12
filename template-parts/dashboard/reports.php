<?php
/**
 * Dashboard: Reports & Export - Complete
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id     = $member ? $member->group_id : 1;
$group        = VICOBA_Groups::get_group($group_id);
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';

global $wpdb;
$filter_member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;
$filter_from      = sanitize_text_field($_GET['from'] ?? date('Y-01-01'));
$filter_to        = sanitize_text_field($_GET['to'] ?? date('Y-m-d'));
$members_all      = VICOBA_Members::get_members_by_group($group_id);

// Ledger summary
$shares_table  = $wpdb->prefix . 'vicoba_shares';
$loans_table   = $wpdb->prefix . 'vicoba_loans';
$repay_table   = $wpdb->prefix . 'vicoba_loan_repayments';
$fines_table   = $wpdb->prefix . 'vicoba_fines';
$sf_contrib    = $wpdb->prefix . 'vicoba_social_fund_contributions';
$sf_payouts    = $wpdb->prefix . 'vicoba_social_fund';
$ledger_table  = $wpdb->prefix . 'vicoba_ledger';
$mt            = $wpdb->prefix . 'vicoba_members';

$where_group  = $wpdb->prepare("WHERE group_id=%d", $group_id);
$where_date   = "AND DATE(created_at) BETWEEN '$filter_from' AND '$filter_to'";
$where_member = $filter_member_id ? " AND member_id=$filter_member_id" : '';

$sum_shares      = (float)$wpdb->get_var("SELECT COALESCE(SUM(total_amount),0) FROM $shares_table $where_group $where_date $where_member");
$sum_repayments  = (float)$wpdb->get_var("SELECT COALESCE(SUM(amount_paid),0) FROM $repay_table WHERE loan_id IN (SELECT id FROM $loans_table WHERE group_id=$group_id) " . str_replace('created_at', 'payment_date', $where_date));
$sum_fines_paid  = (float)$wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM $fines_table $where_group AND status='paid' $where_date");
$sum_sf_contrib  = (float)$wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM $sf_contrib $where_group $where_date");
$sum_sf_out      = (float)$wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM $sf_payouts $where_group AND status='approved' $where_date");
$sum_expenses    = (float)$wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM $ledger_table $where_group AND type='expense' $where_date");
$sum_loans_given = (float)$wpdb->get_var("SELECT COALESCE(SUM(principal_amount),0) FROM $loans_table $where_group AND status!='pending_guarantors' $where_date");
$pending_loans   = (float)$wpdb->get_var("SELECT COALESCE(SUM(balance_remaining),0) FROM $loans_table $where_group AND status='active'");

// Per-member statement
$member_statement = [];
if ($filter_member_id) {
    $member_statement = $wpdb->get_results($wpdb->prepare(
        "SELECT 'hisa' as type, payment_date as date, total_amount as amount, CONCAT(share_count,' hisa') as description FROM $shares_table WHERE group_id=%d AND member_id=%d
         UNION ALL
         SELECT 'rejesho' as type, payment_date as date, amount_paid as amount, 'Rejesho la Mkopo' as description FROM $repay_table WHERE loan_id IN (SELECT id FROM $loans_table WHERE group_id=%d AND member_id=%d)
         UNION ALL
         SELECT 'faini' as type, paid_at as date, amount, reason as description FROM $fines_table WHERE group_id=%d AND member_id=%d AND status='paid'
         ORDER BY date DESC LIMIT 100",
        $group_id, $filter_member_id,
        $group_id, $filter_member_id,
        $group_id, $filter_member_id
    ));
}
?>
<div class="space-y-6" id="printableReport">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Ripoti & Export</h1>
            <p class="text-xs text-slate-500 mt-1">Angalia muhtasari wa fedha, taarifa za wanachama, na chapisha / pakua ripoti</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <?php
            $csv_base = VICOBA_Router::get_url('dashboard', 'reports') . '&vicoba_export=ledger_csv&from=' . urlencode($filter_from) . '&to=' . urlencode($filter_to) . '&group_id=' . $group_id;
            $stmt_csv = VICOBA_Router::get_url('dashboard', 'reports') . '&vicoba_export=member_statement_csv&member_id=' . ($filter_member_id ?: ($member ? $member->id : 0)) . '&group_id=' . $group_id;
            ?>
            <a href="<?php echo esc_url($csv_base); ?>" target="_blank" class="inline-flex items-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-file-csv mr-2"></i> Pakua Ledger (CSV)
            </a>
            <a href="<?php echo esc_url($stmt_csv); ?>" target="_blank" class="inline-flex items-center px-3 py-2 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-file-arrow-down mr-2"></i> Pakua Statement (CSV)
            </a>

            <button id="printReportBtn" onclick="window.print()" class="inline-flex items-center px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-print mr-2"></i> Chapisha
            </button>
        </div>
    </div>


    <!-- Filters -->
    <form method="GET" class="glass-card p-5 rounded-2xl border border-slate-200/80 bg-white no-print">
        <input type="hidden" name="vicoba_route" value="dashboard">
        <input type="hidden" name="vicoba_subroute" value="reports">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mwanachama (Hiari)</label>
                <select name="member_id" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <option value="">Kikundi Kizima</option>
                    <?php foreach ($members_all as $m): ?>
                    <option value="<?php echo $m->id; ?>" <?php selected($filter_member_id, $m->id); ?>><?php echo esc_html($m->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tarehe Kuanzia</label>
                <input name="from" type="date" value="<?php echo esc_attr($filter_from); ?>" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tarehe Mpaka</label>
                <input name="to" type="date" value="<?php echo esc_attr($filter_to); ?>" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-md">
                    <i class="fa-solid fa-filter mr-2"></i> Chuja Ripoti
                </button>
            </div>
        </div>
    </form>

    <!-- Print Header (only visible when printing) -->
    <div class="hidden print-only text-center py-4 border-b-2 border-slate-800 mb-4">
        <h2 class="text-xl font-extrabold"><?php echo esc_html($group->name ?? 'VICOBA Group'); ?></h2>
        <p class="text-sm">Ripoti ya Fedha | Kipindi: <?php echo $filter_from; ?> hadi <?php echo $filter_to; ?></p>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php
        $cards = [
            ['label'=>'Jumla ya Hisa', 'value'=>$sum_shares, 'icon'=>'fa-coins', 'color'=>'amber'],
            ['label'=>'Rejesho za Mikopo', 'value'=>$sum_repayments, 'icon'=>'fa-money-check-dollar', 'color'=>'emerald'],
            ['label'=>'Mikopo Iliyotolewa', 'value'=>$sum_loans_given, 'icon'=>'fa-hand-holding-dollar', 'color'=>'rose'],
            ['label'=>'Mikopo Inayoendelea', 'value'=>$pending_loans, 'icon'=>'fa-clock-rotate-left', 'color'=>'vicoba'],
            ['label'=>'Faini Zilizolipwa', 'value'=>$sum_fines_paid, 'icon'=>'fa-gavel', 'color'=>'slate'],
            ['label'=>'Mfuko wa Jamii Imeingia', 'value'=>$sum_sf_contrib, 'icon'=>'fa-heart-pulse', 'color'=>'emerald'],
            ['label'=>'Mfuko wa Jamii Uliotoka', 'value'=>$sum_sf_out, 'icon'=>'fa-heart-crack', 'color'=>'rose'],
            ['label'=>'Gharama za Uendeshaji', 'value'=>$sum_expenses, 'icon'=>'fa-receipt', 'color'=>'slate'],
        ];
        foreach ($cards as $card): ?>
        <div class="glass-card p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase text-slate-400"><?php echo $card['label']; ?></span>
                <div class="w-8 h-8 rounded-xl bg-<?php echo $card['color']; ?>-100 text-<?php echo $card['color']; ?>-600 flex items-center justify-center text-sm">
                    <i class="fa-solid <?php echo $card['icon']; ?>"></i>
                </div>
            </div>
            <p class="text-sm font-extrabold text-slate-800">TZS <?php echo number_format($card['value']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Member Statement (if filtered) -->
    <?php if ($filter_member_id && !empty($member_statement)):
        $filter_member_obj = array_filter($members_all, fn($m) => $m->id == $filter_member_id);
        $filter_member_obj = reset($filter_member_obj);
    ?>
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center justify-between">
            <span><i class="fa-solid fa-file-lines text-vicoba-600 mr-2"></i> Taarifa ya Mwanachama: <?php echo esc_html($filter_member_obj->full_name ?? ''); ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Tarehe</th>
                        <th class="py-3.5 px-4">Aina</th>
                        <th class="py-3.5 px-4">Maelezo</th>
                        <th class="py-3.5 px-4 text-right">Kiasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php foreach ($member_statement as $row): ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4"><?php echo date('d/m/Y', strtotime($row->date)); ?></td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php
                                echo $row->type === 'hisa' ? 'bg-amber-50 text-amber-700' : ($row->type === 'rejesho' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700');
                            ?>"><?php echo esc_html($row->type); ?></span>
                        </td>
                        <td class="py-3.5 px-4"><?php echo esc_html($row->description); ?></td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-800">TZS <?php echo number_format($row->amount); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    body { background: white; }
    .glass-card { box-shadow: none; border: 1px solid #e2e8f0; }
}
.print-only { display: none; }
</style>
