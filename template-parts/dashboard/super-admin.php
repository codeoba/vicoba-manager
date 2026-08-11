<?php
/**
 * Dashboard Super Admin Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
if (!in_array('super_admin', $current_user->roles) && !in_array('administrator', $current_user->roles)) {
    echo '<div class="p-6 text-rose-600 font-bold">Huna ruhusa ya kufikia ukurasa huu.</div>';
    return;
}

$all_groups = VICOBA_Groups::get_all_groups();

// Handle status change
if (isset($_GET['action_group']) && isset($_GET['group_id'])) {
    $g_id = intval($_GET['group_id']);
    $act = sanitize_text_field($_GET['action_group']);
    VICOBA_Groups::update_status($g_id, $act);
    wp_redirect(home_url('/dashboard/super-admin/'));
    exit;
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Super Admin SaaS Control Panel</h1>
            <p class="text-xs text-slate-500 mt-1">Usimamizi wa vikundi vyote kwenye mfumo na hali ya huduma (Multi-Tenant SaaS)</p>
        </div>
    </div>

    <!-- Groups List Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-building-columns text-vicoba-600 mr-2"></i> Vikundi Vyote Vilivyosajiliwa (<?php echo count($all_groups); ?>)
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Jina la Kikundi</th>
                        <th class="py-3.5 px-4">Eneo (Mkoa/Wilaya)</th>
                        <th class="py-3.5 px-4">Bei ya Hisa</th>
                        <th class="py-3.5 px-4">Riba</th>
                        <th class="py-3.5 px-4">Hali (Status)</th>
                        <th class="py-3.5 px-4 text-right">Vitendo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($all_groups)) : ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Hakuna vikundi vilivyosajiliwa kwenye mfumo bado.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($all_groups as $g) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-vicoba-900"><?php echo esc_html($g->name); ?></td>
                                <td class="py-3.5 px-4"><?php echo esc_html($g->region); ?>, <?php echo esc_html($g->district); ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">TZS <?php echo number_format($g->share_price); ?></td>
                                <td class="py-3.5 px-4"><?php echo esc_html($g->interest_rate); ?>% (<?php echo ucfirst($g->interest_type); ?>)</td>
                                <td class="py-3.5 px-4">
                                    <?php if ($g->status === 'active') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    <?php else : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ($g->status === 'active') : ?>
                                        <a href="<?php echo add_query_arg(array('action_group' => 'suspended', 'group_id' => $g->id)); ?>" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px]">Suspend</a>
                                    <?php else : ?>
                                        <a href="<?php echo add_query_arg(array('action_group' => 'active', 'group_id' => $g->id)); ?>" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px]">Activate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
