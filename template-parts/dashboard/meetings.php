<?php
/**
 * Dashboard: Meetings & Attendance - Complete
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id     = $member ? $member->group_id : 1;
$members      = VICOBA_Members::get_members_by_group($group_id, 'active');
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_admin     = in_array($user_role, ['super_admin','group_admin','secretary','administrator']);

global $wpdb;
$meetings_table = $wpdb->prefix . 'vicoba_meetings';
$meetings = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $meetings_table WHERE group_id = %d ORDER BY meeting_date DESC LIMIT 50", $group_id
));
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mikutano & Mahudhurio</h1>
            <p class="text-xs text-slate-500 mt-1">Rekodi mikutano, weka mahudhurio, na angalia faini za kutokuja</p>
        </div>
        <?php if ($is_admin): ?>
        <button onclick="openModal('createMeetingModal')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:opacity-90 text-white font-bold text-xs shadow-lg transition">
            <i class="fa-solid fa-calendar-plus mr-2"></i> Ongeza Mkutano Mpya
        </button>
        <?php endif; ?>
    </div>

    <!-- Meetings List -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-calendar-check text-vicoba-600 mr-2"></i> Orodha ya Mikutano
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Tarehe</th>
                        <th class="py-3.5 px-4">Aina ya Mkutano</th>
                        <th class="py-3.5 px-4">Mahali (Venue)</th>
                        <th class="py-3.5 px-4">Waliohudhuria</th>
                        <th class="py-3.5 px-4">Hali</th>
                        <?php if ($is_admin): ?><th class="py-3.5 px-4 text-right">Vitendo</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($meetings)): ?>
                        <tr><td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-calendar-xmark text-3xl mb-3 block opacity-20"></i>Hakuna mikutano iliyorekodiwa bado.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($meetings as $mt): ?>
                        <?php
                        $att_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}vicoba_attendance WHERE meeting_id = %d AND status = 'present'", $mt->id
                        ));
                        ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-vicoba-800"><?php echo date('d M Y', strtotime($mt->meeting_date)); ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-vicoba-50 text-vicoba-700 border border-vicoba-200">
                                    <?php echo str_replace('_',' ',esc_html($mt->meeting_type ?? 'regular')); ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4"><?php echo esc_html($mt->venue ?? '—'); ?></td>
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-emerald-700"><?php echo (int)$att_count; ?></span>
                                <span class="text-slate-400">/ <?php echo count($members); ?></span>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if (($mt->status ?? 'scheduled') === 'completed'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Imekamilika</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Imepangwa</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($is_admin): ?>
                            <td class="py-3.5 px-4 text-right">
                                <button onclick="openAttendanceModal(<?php echo $mt->id; ?>, '<?php echo date('d/m/Y', strtotime($mt->meeting_date)); ?>')"
                                    class="px-2.5 py-1 rounded-lg bg-vicoba-50 hover:bg-vicoba-100 text-vicoba-700 font-bold text-xs transition">
                                    <i class="fa-solid fa-clipboard-user mr-1"></i> Weka Mahudhurio
                                </button>
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

<!-- Modal: Create Meeting -->
<div id="createMeetingModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 vicoba-modal-backdrop">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-calendar-plus mr-2 text-vicoba-600"></i> Rekodi Mkutano Mpya</h3>
            <button onclick="closeModal('createMeetingModal')" class="text-slate-400 hover:text-slate-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="createMeetingForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tarehe ya Mkutano *</label>
                <input name="meeting_date" type="date" required value="<?php echo date('Y-m-d'); ?>"
                    class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Aina ya Mkutano *</label>
                <select name="meeting_type" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <option value="regular">Mkutano wa Kawaida (Regular)</option>
                    <option value="emergency">Mkutano wa Dharura (Emergency)</option>
                    <option value="shareout">Mkutano wa Mgawanyo (Share-Out)</option>
                    <option value="agm">AGM (Mkutano Mkuu wa Mwaka)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mahali pa Mkutano (Venue)</label>
                <input name="venue" type="text" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="mf. Nyumba ya Amina Hassan">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ajenda / Maelezo</label>
                <textarea name="agenda" rows="2" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="Andika ajenda au muhtasari..."></textarea>
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('createMeetingModal')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
                <button type="submit" id="saveMeetingBtn" class="px-5 py-2 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 text-white font-bold text-xs shadow-md">Hifadhi Mkutano</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Record Attendance -->
<div id="attendanceModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 vicoba-modal-backdrop">
    <div class="glass-card rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-white/20 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-clipboard-user mr-2 text-vicoba-600"></i> Weka Mahudhurio</h3>
            <button onclick="closeModal('attendanceModal')" class="text-slate-400 hover:text-slate-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <p id="attendanceMeetingLabel" class="text-xs font-bold text-slate-500 mt-2">Mkutano: —</p>
        <form id="recordAttendanceForm" class="mt-4 space-y-3 max-h-80 overflow-y-auto pr-1">
            <input type="hidden" name="meeting_id" id="att_meeting_id" value="">
            <?php foreach ($members as $m): ?>
            <div class="attendance-row flex items-center justify-between py-2 border-b border-slate-100" data-member-id="<?php echo $m->id; ?>">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-vicoba-500 to-emerald-400 text-white font-bold flex items-center justify-center text-xs mr-3">
                        <?php echo strtoupper(substr($m->full_name, 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800"><?php echo esc_html($m->full_name); ?></p>
                        <p class="text-[10px] text-slate-400"><?php echo esc_html($m->member_number); ?></p>
                    </div>
                </div>
                <select class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <option value="present">✅ Alikuwepo</option>
                    <option value="absent">❌ Hakuwepo</option>
                    <option value="excused">⚠️ Ruhusa</option>
                </select>
            </div>
            <?php endforeach; ?>
        </form>
        <div class="pt-4 flex justify-end space-x-2">
            <button type="button" onclick="closeModal('attendanceModal')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
            <button type="button" id="saveAttendanceBtn" onclick="submitAttendance()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 text-white font-bold text-xs shadow-md">Hifadhi Mahudhurio</button>
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }

function openAttendanceModal(meetingId, meetingDate) {
    document.getElementById('att_meeting_id').value = meetingId;
    document.getElementById('attendanceMeetingLabel').textContent = 'Mkutano: ' + meetingDate;
    openModal('attendanceModal');
}

function submitAttendance() {
    const meetingId = document.getElementById('att_meeting_id').value;
    const btn = document.getElementById('saveAttendanceBtn');
    const attendance = [];
    document.querySelectorAll('.attendance-row').forEach(row => {
        attendance.push({ member_id: row.dataset.memberId, status: row.querySelector('select').value });
    });
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Inatuma...';
    fetch(vicobaData.root + 'vicoba/v1/meetings/attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': vicobaData.nonce },
        body: JSON.stringify({ meeting_id: meetingId, attendance: attendance, auto_fine: true })
    }).then(r => r.json()).then(res => {
        btn.disabled = false;
        btn.innerHTML = 'Hifadhi Mahudhurio';
        if (res.success) { closeModal('attendanceModal'); alert(res.message); location.reload(); }
        else { alert(res.message || 'Hitilafu!'); }
    });
}
</script>
