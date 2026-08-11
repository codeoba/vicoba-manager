<?php
/**
 * Dashboard: Super Admin (SaaS) - Complete multi-group management
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';

if (!in_array($user_role, ['super_admin','administrator'])) {
    echo '<div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 font-bold text-sm"><i class="fa-solid fa-shield-halved mr-2"></i> Hakuna ruhusa ya kufikia sehemu hii.</div>';
    return;
}

global $wpdb;
// Quick stats
$total_groups  = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}vicoba_groups");
$total_members = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}vicoba_members");
$total_shares  = (float)$wpdb->get_var("SELECT COALESCE(SUM(total_amount),0) FROM {$wpdb->prefix}vicoba_shares");
$total_loans   = (float)$wpdb->get_var("SELECT COALESCE(SUM(principal_amount),0) FROM {$wpdb->prefix}vicoba_loans WHERE status != 'closed'");
$groups        = $wpdb->get_results("SELECT g.*, COUNT(m.id) as member_count FROM {$wpdb->prefix}vicoba_groups g LEFT JOIN {$wpdb->prefix}vicoba_members m ON g.id = m.group_id GROUP BY g.id ORDER BY g.created_at DESC");
?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Super Admin — SaaS Control Panel</h1>
        <p class="text-xs text-slate-500 mt-1">Simamia vikundi vyote, angalia takwimu za mfumo, na dhibiti watumiaji</p>
    </div>

    <!-- System Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-vicoba-100 text-vicoba-700 flex items-center justify-center text-lg shrink-0"><i class="fa-solid fa-sitemap"></i></div>
            <div><p class="text-xl font-extrabold text-slate-800"><?php echo $total_groups; ?></p><p class="text-xs text-slate-500 font-semibold">Vikundi Vyote</p></div>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0"><i class="fa-solid fa-users"></i></div>
            <div><p class="text-xl font-extrabold text-slate-800"><?php echo $total_members; ?></p><p class="text-xs text-slate-500 font-semibold">Wanachama Wote</p></div>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg shrink-0"><i class="fa-solid fa-coins"></i></div>
            <div><p class="text-xl font-extrabold text-slate-800"><?php echo number_format($total_shares); ?></p><p class="text-xs text-slate-500 font-semibold">Jumla ya Hisa (TZS)</p></div>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg shrink-0"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <div><p class="text-xl font-extrabold text-slate-800"><?php echo number_format($total_loans); ?></p><p class="text-xs text-slate-500 font-semibold">Jumla ya Mikopo (TZS)</p></div>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800 flex items-center"><i class="fa-solid fa-sitemap text-vicoba-600 mr-2"></i> Vikundi Vilivyosajiliwa</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">Jina la Kikundi</th>
                        <th class="py-3.5 px-4">Mkoa</th>
                        <th class="py-3.5 px-4">Wanachama</th>
                        <th class="py-3.5 px-4">Bei ya Hisa</th>
                        <th class="py-3.5 px-4">Tarehe ya Kujisajili</th>
                        <th class="py-3.5 px-4">Hali</th>
                        <th class="py-3.5 px-4 text-right">Vitendo</th>
                    </tr>
                </thead>
                <tbody id="saasGroupsTableBody" class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($groups)): ?>
                        <tr><td colspan="8" class="py-12 text-center text-slate-400"><i class="fa-solid fa-sitemap text-3xl mb-3 block opacity-20"></i>Hakuna vikundi vilivyosajiliwa bado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($groups as $g): ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-vicoba-800">#<?php echo $g->id; ?></td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <div>
                                    <p><?php echo esc_html($g->name); ?></p>
                                    <?php if (!empty($g->registration_number)): ?>
                                    <p class="text-[10px] text-slate-400"><?php echo esc_html($g->registration_number); ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3.5 px-4"><?php echo esc_html($g->region ?? '—'); ?>, <?php echo esc_html($g->district ?? ''); ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 border"><?php echo (int)$g->member_count; ?> wanachama</span>
                            </td>
                            <td class="py-3.5 px-4">TZS <?php echo number_format($g->share_price); ?></td>
                            <td class="py-3.5 px-4 text-slate-500"><?php echo date('d/m/Y', strtotime($g->created_at)); ?></td>
                            <td class="py-3.5 px-4">
                                <?php if (($g->status ?? 'active') === 'active'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <?php if (($g->status ?? 'active') === 'active'): ?>
                                <button onclick="toggleGroupStatus(<?php echo $g->id; ?>,'suspended',this)"
                                    class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition">
                                    <i class="fa-solid fa-ban mr-1"></i> Simamisha
                                </button>
                                <?php else: ?>
                                <button onclick="toggleGroupStatus(<?php echo $g->id; ?>,'active',this)"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs transition">
                                    <i class="fa-solid fa-check mr-1"></i> Amilisha
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Info -->
    <div class="glass-card p-6 rounded-2xl border border-slate-200/80 bg-white">
        <h3 class="text-sm font-extrabold text-slate-800 border-b pb-3 mb-4 flex items-center">
            <i class="fa-solid fa-server mr-2 text-slate-500"></i> Taarifa za Mfumo
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border">
                <p class="font-semibold text-slate-500">WordPress Version</p>
                <p class="font-extrabold text-slate-800 mt-1"><?php echo get_bloginfo('version'); ?></p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <p class="font-semibold text-slate-500">PHP Version</p>
                <p class="font-extrabold text-slate-800 mt-1"><?php echo phpversion(); ?></p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <p class="font-semibold text-slate-500">Theme Version</p>
                <p class="font-extrabold text-slate-800 mt-1">2.0.0</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <p class="font-semibold text-slate-500">DB Tables</p>
                <p class="font-extrabold text-slate-800 mt-1"><?php echo $wpdb->get_var("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE '{$wpdb->prefix}vicoba_%'"); ?> tables</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleGroupStatus(groupId, newStatus, btn) {
    const label = newStatus === 'active' ? 'kuamilisha' : 'kusimamisha';
    if (!confirm('Je, unathibitisha ' + label + ' kikundi hiki?')) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inaendelea...';
    fetch(vicobaData.root + 'vicoba/v1/superadmin/group-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': vicobaData.nonce },
        body: JSON.stringify({ group_id: groupId, status: newStatus })
    }).then(r => r.json()).then(res => {
        if (res.success) { location.reload(); }
        else { btn.disabled = false; alert(res.message || 'Hitilafu!'); }
    }).catch(() => { btn.disabled = false; alert('Hitilafu ya mtandao!'); });
}
</script>
