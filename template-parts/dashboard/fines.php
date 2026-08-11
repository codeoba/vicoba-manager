<?php
/**
 * Dashboard Fines Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$members = VICOBA_Members::get_members_by_group($group_id, 'active');
$fine_types = VICOBA_Fines::get_fine_types($group_id);
$fines = VICOBA_Fines::get_fines_by_group($group_id);

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_manage = in_array($user_role, array('super_admin', 'group_admin', 'secretary', 'treasurer'));
?>

<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Faini za Kikundi</h1>
            <p class="text-xs text-slate-500 mt-1">Uingizaji wa faini, aina za adhabu, na ufuatiliaji wa malipo ya faini</p>
        </div>

        <?php if ($can_manage) : ?>
            <button onclick="document.getElementById('issueFineModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-700 hover:to-amber-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-gavel mr-2"></i> Toza Faini kwa Mwanachama
            </button>
        <?php endif; ?>
    </div>

    <!-- Fine Types Config Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($fine_types as $ft) : ?>
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800"><?php echo esc_html($ft->name); ?></h4>
                    <p class="text-sm font-extrabold text-rose-600 mt-1">TZS <?php echo number_format($ft->default_amount); ?></p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Fines Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-receipt text-vicoba-600 mr-2"></i> Orodha ya Faini Zote
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Mwanachama</th>
                        <th class="py-3.5 px-4">Sababu ya Faini</th>
                        <th class="py-3.5 px-4">Kiasi (TZS)</th>
                        <th class="py-3.5 px-4">Hali</th>
                        <th class="py-3.5 px-4 text-right">Tarehe / Vitendo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($fines)) : ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Hakuna faini iliyotozwa kwenye kikundi kwa sasa.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($fines as $f) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800"><?php echo esc_html($f->full_name); ?></td>
                                <td class="py-3.5 px-4"><?php echo esc_html($f->reason); ?></td>
                                <td class="py-3.5 px-4 font-extrabold text-rose-600">TZS <?php echo number_format($f->amount); ?></td>
                                <td class="py-3.5 px-4">
                                    <?php if ($f->status === 'paid') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Ililipwa</span>
                                    <?php else : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Haijalipwa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ($f->status === 'unpaid' && $can_manage) : ?>
                                        <button onclick="payFine(<?php echo $f->id; ?>)" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">Pokea Malipo</button>
                                    <?php else : ?>
                                        <span class="text-slate-400 text-[11px]"><?php echo date('d/m/Y', strtotime($f->created_at)); ?></span>
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

<!-- Modal: Issue Fine -->
<div id="issueFineModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-gavel mr-2 text-rose-600"></i> Toza Faini kwa Mwanachama</h3>
            <button onclick="document.getElementById('issueFineModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="issueFineForm" class="mt-4 space-y-4">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Chagua Mwanachama *</label>
                <select name="member_id" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="">-- Chagua Mwanachama --</option>
                    <?php foreach ($members as $m) : ?>
                        <option value="<?php echo $m->id; ?>"><?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Aina ya Faini *</label>
                <select name="fine_type_id" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <?php foreach ($fine_types as $ft) : ?>
                        <option value="<?php echo $ft->id; ?>"><?php echo esc_html($ft->name); ?> (TZS <?php echo number_format($ft->default_amount); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kiasi cha Faini (TZS) *</label>
                <input name="amount" type="number" value="1000" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Sababu ya Faini *</label>
                <input name="reason" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="mf. Kuchelewa kufika mkutanoni">
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('issueFineModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveFineBtn" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">Toza Faini</button>
            </div>
        </form>
    </div>
</div>

<script>
async function payFine(fineId) {
    const confirm = await Swal.fire({
        title: 'Unathibitisha?',
        text: 'Unathibitisha kupokea malipo ya faini hii?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ndiyo, Thabitisha!'
    });

    if (confirm.isConfirmed) {
        try {
            const res = await fetch(vicobaData.root + 'vicoba/v1/fines/pay', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': vicobaData.nonce,
                },
                body: JSON.stringify({ fine_id: fineId })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Imelipwa!', data.message, 'success').then(() => location.reload());
            }
        } catch(err) {
            Swal.fire('Hitilafu', 'Imeshindikana kusindika malipo ya faini.', 'error');
        }
    }
}
</script>
