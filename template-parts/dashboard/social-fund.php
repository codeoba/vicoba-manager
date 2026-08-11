<?php
/**
 * Dashboard Social Fund Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$records = VICOBA_Social_Fund::get_records_by_group($group_id);
$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_approve = in_array($user_role, array('super_admin', 'group_admin', 'treasurer'));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mfuko wa Jamii (Social Fund)</h1>
            <p class="text-xs text-slate-500 mt-1">Michango ya dharura na maombi ya msaada (ugonjwa, msiba, n.k.)</p>
        </div>

        <div class="flex space-x-2">
            <button onclick="document.getElementById('socialRequestModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-700 hover:to-amber-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-hand-holding-medical mr-2"></i> Omba Msaada wa Dharura
            </button>
        </div>
    </div>

    <!-- Social Fund Records Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-heart-pulse text-rose-500 mr-2"></i> Historia ya Mfuko wa Jamii
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Mwanachama</th>
                        <th class="py-3.5 px-4">Aina ya Muamala</th>
                        <th class="py-3.5 px-4">Aina / Sababu</th>
                        <th class="py-3.5 px-4">Kiasi (TZS)</th>
                        <th class="py-3.5 px-4">Hali (Status)</th>
                        <th class="py-3.5 px-4 text-right">Vitendo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($records)) : ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Hakuna kumbukumbu za Mfuko wa Jamii bado.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($records as $r) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800"><?php echo esc_html($r->full_name); ?></td>
                                <td class="py-3.5 px-4">
                                    <?php if ($r->transaction_type === 'contribution') : ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Mchango</span>
                                    <?php else : ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase">Msaada (Payout)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4"><?php echo esc_html(ucfirst($r->category)); ?> - <?php echo esc_html($r->description); ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">TZS <?php echo number_format($r->amount); ?></td>
                                <td class="py-3.5 px-4">
                                    <?php if ($r->status === 'approved') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Umeidhinishwa</span>
                                    <?php else : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ($r->status === 'pending' && $can_approve) : ?>
                                        <button onclick="approveSocialPayout(<?php echo $r->id; ?>)" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">Idhinisha Msaada</button>
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

<!-- Modal: Request Social Emergency -->
<div id="socialRequestModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-hand-holding-medical mr-2 text-rose-600"></i> Omba Msaada wa Mfuko wa Jamii</h3>
            <button onclick="document.getElementById('socialRequestModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="socialRequestForm" class="mt-4 space-y-4">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <input type="hidden" name="member_id" value="<?php echo $member ? $member->id : 0; ?>">

            <div>
                <label class="block text-xs font-semibold text-slate-700">Aina ya Msaada / Dharura *</label>
                <select name="category" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="illness">Ugonjwa (Illness)</option>
                    <option value="bereavement">Msiba (Bereavement)</option>
                    <option value="emergency">Dharura Nyingine (Emergency)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kiasi cha Msaada Unaoombwa (TZS) *</label>
                <input name="amount" type="number" step="1000" value="50000" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Maelezo ya Dharura *</label>
                <textarea name="description" rows="3" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="Eleza kwa ufupi dharura iliyotokea..."></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('socialRequestModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveSocialBtn" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">Tuma Ombi</button>
            </div>
        </form>
    </div>
</div>

<script>
async function approveSocialPayout(reqId) {
    const confirm = await Swal.fire({
        title: 'Unathibitisha?',
        text: 'Unathibitisha kuidhinisha na kutoa msaada huu kutoka Mfuko wa Jamii?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ndiyo, Idhinisha!'
    });

    if (confirm.isConfirmed) {
        try {
            const res = await fetch(vicobaData.root + 'vicoba/v1/social-fund/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': vicobaData.nonce,
                },
                body: JSON.stringify({ request_id: reqId })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Umeidhinishwa!', data.message, 'success').then(() => location.reload());
            }
        } catch(err) {
            Swal.fire('Hitilafu', 'Imeshindikana kuidhinisha msaada.', 'error');
        }
    }
}
</script>
