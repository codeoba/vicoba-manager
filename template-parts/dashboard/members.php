<?php
/**
 * Dashboard Members Management Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$members = VICOBA_Members::get_members_by_group($group_id);
$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_admin = in_array($user_role, array('super_admin', 'group_admin', 'secretary'));
?>

<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Orodha ya Wanachama</h1>
            <p class="text-xs text-slate-500 mt-1">Simamia wanachama, majukumu yao, na namba zao za uanachama</p>
        </div>

        <?php if ($is_admin) : ?>
            <button onclick="document.getElementById('addMemberModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:from-vicoba-700 hover:to-emerald-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-user-plus mr-2"></i> Ongeza Mwanachama Kipya
            </button>
        <?php endif; ?>
    </div>

    <!-- Members Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Namba</th>
                        <th class="py-3.5 px-4">Majina Kamili</th>
                        <th class="py-3.5 px-4">Simu</th>
                        <th class="py-3.5 px-4">Jukumu (Role)</th>
                        <th class="py-3.5 px-4">Hali (Status)</th>
                        <th class="py-3.5 px-4 text-right">Vitendo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($members)) : ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Hakuna wanachama waliosajiliwa bado.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($members as $m) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-vicoba-800"><?php echo esc_html($m->member_number); ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center mr-2.5 text-xs">
                                            <?php echo strtoupper(substr($m->full_name, 0, 1)); ?>
                                        </div>
                                        <span><?php echo esc_html($m->full_name); ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4"><?php echo esc_html($m->phone); ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700 border">
                                        <?php echo str_replace('_', ' ', esc_html($m->role)); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($m->status === 'active') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    <?php elseif ($m->status === 'suspended') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>
                                    <?php else : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border">Alumni</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?php echo home_url('/dashboard/reports/?member_id=' . $m->id); ?>" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-vicoba-50 hover:bg-vicoba-100 text-vicoba-700 font-bold text-xs mr-1 transition" title="Taarifa / Statement">
                                        <i class="fa-solid fa-file-lines mr-1"></i> Statement
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Member -->
<div id="addMemberModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-user-plus mr-2 text-vicoba-600"></i> Ongeza Mwanachama Kipya</h3>
            <button onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="addMemberForm" class="mt-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Majina Kamili *</label>
                    <input name="full_name" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="Amani Hassan">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Namba ya Simu *</label>
                    <input name="phone" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="0789123456">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Barua Pepe (Email) *</label>
                    <input name="email" type="email" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="mwanachama@gmail.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Jukumu (Role) *</label>
                    <select name="role" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                        <option value="member">Mwanachama</option>
                        <option value="treasurer">Mweka Hazina (Treasurer)</option>
                        <option value="secretary">Katibu (Secretary)</option>
                        <option value="group_admin">Mwenyekiti (Group Admin)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Username *</label>
                    <input name="username" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="amani_h">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nenosiri *</label>
                    <input name="password" type="password" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="••••••••">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Namba ya NIDA (Hiari / Encrypted)</label>
                    <input name="nida_number" type="text" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="19900101-12345-00001-12">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
                <button type="submit" id="saveMemberBtn" class="px-4 py-2 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-md">Hifadhi Mwanachama</button>
            </div>
        </form>
    </div>
</div>
