<?php
/**
 * Dashboard Meetings Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$meetings = VICOBA_Meetings::get_meetings_by_group($group_id);
$members = VICOBA_Members::get_members_by_group($group_id, 'active');

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_manage = in_array($user_role, array('super_admin', 'group_admin', 'secretary'));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mikutano & Mahudhurio</h1>
            <p class="text-xs text-slate-500 mt-1">Ratiba ya mikutano ya kikundi, ajenda, mahudhurio ya wanachama, na minutes</p>
        </div>

        <?php if ($can_manage) : ?>
            <button onclick="document.getElementById('createMeetingModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:from-vicoba-700 hover:to-emerald-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-calendar-plus mr-2"></i> Panga Mkutano Kipya
            </button>
        <?php endif; ?>
    </div>

    <!-- Meetings Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if (empty($meetings)) : ?>
            <div class="col-span-full py-12 text-center text-slate-400 glass-card rounded-2xl">
                <i class="fa-solid fa-calendar-xmark text-4xl mb-2 text-slate-300"></i>
                <p>Hakuna ratiba ya mikutano iliyowekwa bado.</p>
            </div>
        <?php else : ?>
            <?php foreach ($meetings as $m) : ?>
                <div class="glass-card p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-vicoba-50 text-vicoba-800 border border-vicoba-200">
                                Mkutano #<?php echo $m->meeting_number; ?>
                            </span>
                            <span class="text-xs font-bold text-slate-400">
                                <i class="fa-solid fa-calendar-day mr-1"></i> <?php echo date('d/m/Y', strtotime($m->meeting_date)); ?>
                            </span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-800 mt-3 flex items-center">
                            <i class="fa-solid fa-location-dot text-rose-500 mr-2 text-sm"></i> <?php echo esc_html($m->location); ?>
                        </h3>

                        <div class="mt-3 p-3 bg-slate-50 rounded-xl text-xs text-slate-600 border border-slate-100">
                            <strong>Ajenda:</strong>
                            <p class="mt-1 line-clamp-2"><?php echo esc_html($m->agenda ?: 'Hakuna ajenda iliyoandikwa.'); ?></p>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Hali: <?php echo ucfirst($m->status); ?></span>

                        <?php if ($can_manage) : ?>
                            <button onclick="openAttendanceModal(<?php echo $m->id; ?>, <?php echo $m->meeting_number; ?>)" class="px-3 py-1.5 rounded-lg bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-xs">
                                <i class="fa-solid fa-user-check mr-1"></i> Mahudhurio
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Create Meeting -->
<div id="createMeetingModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-calendar-plus mr-2 text-vicoba-600"></i> Panga Mkutano Kipya</h3>
            <button onclick="document.getElementById('createMeetingModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="createMeetingForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Tarehe ya Mkutano *</label>
                <input name="meeting_date" type="date" value="<?php echo date('Y-m-d'); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Eneo la Mkutano (Location) *</label>
                <input name="location" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="mf. Ukumbi wa Jamii / Nyumbani kwa Mwenyekiti">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Ajenda za Mkutano *</label>
                <textarea name="agenda" rows="3" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="1. Kufungua mkutano&#10;2. Malipo ya hisa&#10;3. Ombi la mikopo"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('createMeetingModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveMeetingBtn" class="px-4 py-2 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-md">Hifadhi Ratiba</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Attendance -->
<div id="attendanceModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-user-check mr-2 text-vicoba-600"></i> Chukua Mahudhurio (Mkutano #<span id="attMeetingNum"></span>)</h3>
            <button onclick="document.getElementById('attendanceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="attendanceForm" class="mt-4 space-y-4">
            <input type="hidden" id="attMeetingId" name="meeting_id" value="0">
            
            <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                <span class="font-bold text-amber-900">Auto-Fine kwa asiyehudhuria (Missed meeting fine):</span>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="auto_fine" value="1" checked class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 relative"></div>
                </label>
            </div>

            <div class="max-h-72 overflow-y-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b font-bold text-slate-600">
                        <tr>
                            <th class="py-2.5 px-3">Mwanachama</th>
                            <th class="py-2.5 px-3 text-center">Present (Yupo)</th>
                            <th class="py-2.5 px-3 text-center">Absent (Hayupo)</th>
                            <th class="py-2.5 px-3 text-center">Excused (Udhuru)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($members as $m) : ?>
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-slate-800"><?php echo esc_html($m->full_name); ?></td>
                                <td class="py-2.5 px-3 text-center">
                                    <input type="radio" name="att_<?php echo $m->id; ?>" value="present" checked class="text-vicoba-600">
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <input type="radio" name="att_<?php echo $m->id; ?>" value="absent" class="text-rose-600">
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <input type="radio" name="att_<?php echo $m->id; ?>" value="excused" class="text-amber-600">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('attendanceModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveAttBtn" class="px-4 py-2 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-md">Hifadhi Mahudhurio</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAttendanceModal(meetingId, meetingNum) {
    document.getElementById('attMeetingId').value = meetingId;
    document.getElementById('attMeetingNum').innerText = meetingNum;
    document.getElementById('attendanceModal').classList.remove('hidden');
}
</script>
