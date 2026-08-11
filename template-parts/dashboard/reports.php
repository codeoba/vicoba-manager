<?php
/**
 * Dashboard Reports Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;
$group = VICOBA_Groups::get_group($group_id);

$members = VICOBA_Members::get_members_by_group($group_id);
$selected_member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : ($member ? $member->id : 0);

$statement = null;
if ($selected_member_id > 0) {
    $statement = VICOBA_Reports::get_member_statement($group_id, $selected_member_id);
}

// Check CSV export request
if (isset($_GET['export_csv']) && $_GET['export_csv'] === 'group_summary') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="vicoba_group_summary_' . date('Y-m-d') . '.csv"');
    echo VICOBA_Reports::generate_group_summary_csv($group_id);
    exit;
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Ripoti & Statements</h1>
            <p class="text-xs text-slate-500 mt-1">Pakua ripoti za fedha za kikundi au taarifa ya mwanachama (Passbook)</p>
        </div>

        <div class="flex space-x-2">
            <button onclick="window.print()" class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition">
                <i class="fa-solid fa-print mr-2"></i> Chapisha (Print PDF)
            </button>
            <a href="<?php echo add_query_arg('export_csv', 'group_summary'); ?>" class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                <i class="fa-solid fa-file-excel mr-2"></i> Export Excel/CSV
            </a>
        </div>
    </div>

    <!-- Member Filter Selector -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200/80 no-print">
        <form method="GET" action="" class="flex items-center space-x-3">
            <label class="text-xs font-bold text-slate-700">Chagua Mwanachama wa kuona Statement:</label>
            <select name="member_id" onchange="this.form.submit()" class="px-3 py-1.5 border rounded-xl text-xs bg-white">
                <?php foreach ($members as $m) : ?>
                    <option value="<?php echo $m->id; ?>" <?php selected($selected_member_id, $m->id); ?>><?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)</option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Printable Passbook Statement Document -->
    <?php if ($statement && $statement['member']) : ?>
        <div class="glass-card p-8 rounded-2xl border border-slate-200/80 bg-white space-y-6">
            <!-- Document Header -->
            <div class="flex items-center justify-between border-b pb-6">
                <div>
                    <h2 class="text-2xl font-black text-vicoba-900"><?php echo esc_html($group ? $group->name : 'VICOBA'); ?></h2>
                    <p class="text-xs font-semibold text-slate-500 mt-1">TAARIFA YA MWANACHAMA (MEMBER PASSBOOK STATEMENT)</p>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p>Tarehe ya Ripoti: <strong><?php echo date('d/m/Y'); ?></strong></p>
                    <p>Namba ya Mwanachama: <strong class="text-slate-800"><?php echo esc_html($statement['member']->member_number); ?></strong></p>
                </div>
            </div>

            <!-- Member Information Summary Box -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                <div>
                    <span class="text-slate-400 block font-semibold">Majina Kamili</span>
                    <strong class="text-slate-800 text-sm"><?php echo esc_html($statement['member']->full_name); ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold">Simu</span>
                    <strong class="text-slate-800 text-sm"><?php echo esc_html($statement['member']->phone); ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold">Jumla ya Hisa</span>
                    <strong class="text-amber-600 text-sm"><?php echo number_format($statement['shares']['count']); ?> Hisa (TZS <?php echo number_format($statement['shares']['value']); ?>)</strong>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold">Status</span>
                    <strong class="text-emerald-600 text-sm uppercase"><?php echo esc_html($statement['member']->status); ?></strong>
                </div>
            </div>

            <!-- Transactions Log -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 mb-3"><i class="fa-solid fa-clock-rotate-left mr-2"></i> Historia ya Miamala ya Mwanachama</h3>
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b font-bold text-slate-600 uppercase text-[10px]">
                                <th class="py-2.5 px-3">Kodi</th>
                                <th class="py-2.5 px-3">Aina</th>
                                <th class="py-2.5 px-3">Maelezo</th>
                                <th class="py-2.5 px-3">Kiasi (TZS)</th>
                                <th class="py-2.5 px-3 text-right">Tarehe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            <?php if (empty($statement['transactions'])) : ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">Hakuna miamala iliyorekodiwa.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($statement['transactions'] as $tx) : ?>
                                    <tr>
                                        <td class="py-2.5 px-3 font-mono font-bold"><?php echo esc_html($tx->transaction_code); ?></td>
                                        <td class="py-2.5 px-3 uppercase text-[10px] font-extrabold"><?php echo str_replace('_', ' ', esc_html($tx->type)); ?></td>
                                        <td class="py-2.5 px-3"><?php echo esc_html($tx->description); ?></td>
                                        <td class="py-2.5 px-3 font-bold text-slate-900">TZS <?php echo number_format($tx->amount); ?></td>
                                        <td class="py-2.5 px-3 text-right text-slate-500"><?php echo date('d/m/Y', strtotime($tx->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
