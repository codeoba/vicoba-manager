<?php
/**
 * Dashboard: Members Management - Complete
 */
if (!defined('ABSPATH')) exit;

$current_user   = wp_get_current_user();
$member         = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id       = $member ? $member->group_id : 1;
$members        = VICOBA_Members::get_members_by_group($group_id);
$user_role      = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_admin       = in_array($user_role, ['super_admin','group_admin','secretary','administrator']);
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Orodha ya Wanachama</h1>
            <p class="text-xs text-slate-500 mt-1"><?php echo count($members); ?> wanachama waliosajiliwa katika kikundi hiki</p>
        </div>
        <?php if ($is_admin): ?>
        <button onclick="openModal('addMemberModal')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:opacity-90 text-white font-bold text-xs shadow-lg transition">
            <i class="fa-solid fa-user-plus mr-2"></i> Ongeza Mwanachama Kipya
        </button>
        <?php endif; ?>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php
        $active_count    = count(array_filter($members, fn($m) => $m->status === 'active'));
        $suspended_count = count(array_filter($members, fn($m) => $m->status === 'suspended'));
        $alumni_count    = count(array_filter($members, fn($m) => $m->status === 'alumni'));
        ?>
        <div class="glass-card p-4 rounded-2xl border border-slate-200/80 shadow-sm text-center">
            <p class="text-2xl font-extrabold text-vicoba-700"><?php echo count($members); ?></p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Jumla</p>
        </div>
        <div class="glass-card p-4 rounded-2xl border border-slate-200/80 shadow-sm text-center">
            <p class="text-2xl font-extrabold text-emerald-600"><?php echo $active_count; ?></p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Wanaofanya Kazi</p>
        </div>
        <div class="glass-card p-4 rounded-2xl border border-slate-200/80 shadow-sm text-center">
            <p class="text-2xl font-extrabold text-rose-500"><?php echo $suspended_count; ?></p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Waliozuiwa</p>
        </div>
        <div class="glass-card p-4 rounded-2xl border border-slate-200/80 shadow-sm text-center">
            <p class="text-2xl font-extrabold text-slate-400"><?php echo $alumni_count; ?></p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Alumni</p>
        </div>
    </div>

    <!-- Members Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Namba</th>
                        <th class="py-3.5 px-4">Majina</th>
                        <th class="py-3.5 px-4">Simu</th>
                        <th class="py-3.5 px-4">Jukumu</th>
                        <th class="py-3.5 px-4">Hali</th>
                        <th class="py-3.5 px-4">Tarehe ya Kujiunga</th>
                        <?php if ($is_admin): ?><th class="py-3.5 px-4 text-right">Vitendo</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($members)): ?>
                        <tr><td colspan="7" class="py-12 text-center text-slate-400"><i class="fa-solid fa-users text-3xl mb-3 block opacity-30"></i>Hakuna wanachama waliosajiliwa bado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-vicoba-800"><?php echo esc_html($m->member_number); ?></td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-vicoba-500 to-emerald-500 text-white font-bold flex items-center justify-center mr-2.5 text-xs shrink-0">
                                        <?php echo strtoupper(substr($m->full_name, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p><?php echo esc_html($m->full_name); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4"><?php echo esc_html($m->phone); ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-vicoba-50 text-vicoba-700 border border-vicoba-200">
                                    <?php echo str_replace('_', ' ', esc_html($m->role)); ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if ($m->status === 'active'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                <?php elseif ($m->status === 'suspended'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border">Alumni</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500"><?php echo date('d/m/Y', strtotime($m->joined_date ?? $m->created_at)); ?></td>
                            <?php if ($is_admin): ?>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="<?php echo VICOBA_Router::get_url('dashboard','reports') . '&member_id=' . $m->id; ?>" 
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg bg-vicoba-50 hover:bg-vicoba-100 text-vicoba-700 font-bold text-xs transition" title="Statement">
                                    <i class="fa-solid fa-file-lines mr-1"></i> Statement
                                </a>
                                <?php if ($m->status === 'active'): ?>
                                <button class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs transition"
                                    onclick="vicobaUpdateStatus(<?php echo $m->id; ?>,'suspended')" title="Simamisha">
                                    <i class="fa-solid fa-ban mr-1"></i> Simamisha
                                </button>
                                <?php else: ?>
                                <button class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs transition"
                                    onclick="vicobaUpdateStatus(<?php echo $m->id; ?>,'active')" title="Amilisha">
                                    <i class="fa-solid fa-check mr-1"></i> Amilisha
                                </button>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Member -->
<div id="addMemberModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 vicoba-modal-backdrop">
    <div class="glass-card rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-user-plus mr-2 text-vicoba-600"></i> Ongeza Mwanachama Kipya</h3>
            <button onclick="closeModal('addMemberModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="addMemberForm" class="mt-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Majina Kamili *</label>
                    <input name="full_name" type="text" required class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="Amani Hassan">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Namba ya Simu *</label>
                    <input name="phone" type="text" required class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="0789123456">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Barua Pepe *</label>
                    <input name="email" type="email" required class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="amani@gmail.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jukumu *</label>
                    <select name="role" class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                        <option value="member">Mwanachama</option>
                        <option value="treasurer">Mweka Hazina</option>
                        <option value="secretary">Katibu</option>
                        <option value="group_admin">Mwenyekiti</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                    <input name="username" type="text" required class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="amani_h">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nenosiri *</label>
                    <input name="password" type="password" required class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="••••••••">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Namba ya NIDA (Hiari)</label>
                    <input name="nida_number" type="text" class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="19900101-12345-00001-12">
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('addMemberModal')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
                <button type="submit" id="saveMemberBtn" class="px-5 py-2 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 text-white font-bold text-xs shadow-md">Hifadhi Mwanachama</button>
            </div>
        </form>
    </div>
</div>

<script>
function vicobaUpdateStatus(memberId, status) {
    if (!confirm('Je, uko tayari kubadilisha hali ya mwanachama huyu?')) return;
    fetch(vicobaData.root + 'vicoba/v1/members/update-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': vicobaData.nonce },
        body: JSON.stringify({ member_id: memberId, status: status })
    }).then(r => r.json()).then(res => {
        if (res.success) { location.reload(); }
        else { alert(res.message || 'Hitilafu!'); }
    });
}
function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
</script>
