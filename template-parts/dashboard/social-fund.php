<?php
/**
 * Dashboard: Social Fund (Mfuko wa Jamii) - Complete
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id     = $member ? $member->group_id : 1;
$members      = VICOBA_Members::get_members_by_group($group_id, 'active');
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_admin     = in_array($user_role, ['super_admin','group_admin','treasurer','administrator']);

global $wpdb;
$sf_table = $wpdb->prefix . 'vicoba_social_fund';
$sc_table = $wpdb->prefix . 'vicoba_social_fund_contributions';
$mt       = $wpdb->prefix . 'vicoba_members';

$requests = $wpdb->get_results($wpdb->prepare(
    "SELECT r.*, m.full_name, m.member_number FROM $sf_table r JOIN $mt m ON r.member_id = m.id WHERE r.group_id = %d ORDER BY r.created_at DESC LIMIT 50", $group_id
));

$fund_balance = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $sc_table WHERE group_id = %d", $group_id
)) - (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $sf_table WHERE group_id = %d AND status = 'approved'", $group_id
));

$total_contributed = (float)$wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $sc_table WHERE group_id = %d", $group_id
));
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mfuko wa Jamii</h1>
            <p class="text-xs text-slate-500 mt-1">Simamia michango, maombi ya msaada, na idhinisho la malipo</p>
        </div>
        <div class="flex gap-2">
            <?php if ($is_admin): ?>
            <button onclick="openModal('socialContributeModal')" class="inline-flex items-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-plus mr-1.5"></i> Ingiza Mchango
            </button>
            <?php endif; ?>
            <button onclick="openModal('socialRequestModal')" class="inline-flex items-center px-3 py-2 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:opacity-90 text-white font-bold text-xs shadow-lg transition">
                <i class="fa-solid fa-hand-holding-heart mr-1.5"></i> Omba Msaada
            </button>
        </div>
    </div>

    <!-- Fund Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card p-6 rounded-2xl border border-emerald-200/60 shadow-sm flex items-center justify-between bg-emerald-50/30">
            <div>
                <span class="text-xs font-bold uppercase text-emerald-400">Bakaa ya Mfuko</span>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">TZS <?php echo number_format(max(0, $fund_balance)); ?></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl"><i class="fa-solid fa-vault"></i></div>
        </div>
        <div class="glass-card p-6 rounded-2xl border border-vicoba-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase text-vicoba-400">Jumla Zilizochangwa</span>
                <p class="text-2xl font-extrabold text-vicoba-700 mt-1">TZS <?php echo number_format($total_contributed); ?></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-vicoba-100 text-vicoba-600 flex items-center justify-center text-xl"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        </div>
        <div class="glass-card p-6 rounded-2xl border border-amber-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase text-amber-400">Maombi Yanayongojwa</span>
                <p class="text-2xl font-extrabold text-amber-700 mt-1">
                    <?php echo $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $sf_table WHERE group_id=%d AND status='pending'", $group_id)); ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl"><i class="fa-solid fa-clock"></i></div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-heart-pulse text-rose-500 mr-2"></i> Maombi ya Msaada (Requests)
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Mwanachama</th>
                        <th class="py-3.5 px-4">Aina ya Msaada</th>
                        <th class="py-3.5 px-4">Maelezo</th>
                        <th class="py-3.5 px-4">Kiasi Kilichoombiwa</th>
                        <th class="py-3.5 px-4">Tarehe</th>
                        <th class="py-3.5 px-4">Hali</th>
                        <?php if ($is_admin): ?><th class="py-3.5 px-4 text-right">Vitendo</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-heart-pulse text-3xl mb-3 block opacity-20"></i>Hakuna maombi ya msaada bado.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-800"><?php echo esc_html($r->full_name); ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                    <?php echo esc_html($r->category ?? 'dharura'); ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs truncate"><?php echo esc_html($r->description ?? '—'); ?></td>
                            <td class="py-3.5 px-4 font-extrabold text-vicoba-700">TZS <?php echo number_format($r->amount); ?></td>
                            <td class="py-3.5 px-4 text-slate-500"><?php echo date('d/m/Y', strtotime($r->created_at)); ?></td>
                            <td class="py-3.5 px-4">
                                <?php if ($r->status === 'approved'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Imeidhinishwa</span>
                                <?php elseif ($r->status === 'rejected'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Imekataliwa</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Inasubiri</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($is_admin && $r->status === 'pending'): ?>
                            <td class="py-3.5 px-4 text-right">
                                <button onclick="approveSocialRequest(<?php echo $r->id; ?>, this)"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    <i class="fa-solid fa-check mr-1"></i> Idhinisha & Toa
                                </button>
                            </td>
                            <?php elseif ($is_admin): ?>
                            <td class="py-3.5 px-4"></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Request Social Fund -->
<div id="socialRequestModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 vicoba-modal-backdrop">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-hand-holding-heart mr-2 text-rose-500"></i> Omba Msaada wa Mfuko wa Jamii</h3>
            <button onclick="closeModal('socialRequestModal')" class="text-slate-400 hover:text-slate-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="socialRequestForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mwanachama Anayeomba *</label>
                <select name="member_id" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <?php foreach ($members as $m): ?>
                    <option value="<?php echo $m->id; ?>" <?php selected($member && $m->id === $member->id); ?>>
                        <?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Aina ya Msaada *</label>
                <select name="category" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <option value="death">Msaada wa Mazishi (Kifo)</option>
                    <option value="medical">Msaada wa Hospitali (Matibabu)</option>
                    <option value="fire">Msaada wa Moto/Hasara</option>
                    <option value="education">Msaada wa Masomo</option>
                    <option value="emergency">Dharura Nyingine</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Maelezo *</label>
                <textarea name="description" rows="3" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="Eleza hali na sababu ya kuomba msaada..."></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kiasi Kinachohitajika (TZS) *</label>
                <input name="amount" type="number" step="1000" min="1000" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="mf. 50000">
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('socialRequestModal')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
                <button type="submit" id="saveSocialRequestBtn" class="px-5 py-2 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 text-white font-bold text-xs shadow-md">Tuma Ombi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Contribute to Social Fund -->
<div id="socialContributeModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 vicoba-modal-backdrop">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-plus mr-2 text-emerald-600"></i> Ingiza Mchango wa Mfuko wa Jamii</h3>
            <button onclick="closeModal('socialContributeModal')" class="text-slate-400 hover:text-slate-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="socialContributeForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Chagua Mwanachama *</label>
                <select name="member_id" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <option value="">-- Chagua --</option>
                    <?php foreach ($members as $m): ?>
                    <option value="<?php echo $m->id; ?>"><?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kiasi (TZS) *</label>
                <input name="amount" type="number" step="500" min="500" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none" placeholder="mf. 2000">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Njia ya Malipo</label>
                <select name="payment_method" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none">
                    <option value="cash">Pesa Taslimu</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('socialContributeModal')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
                <button type="submit" id="saveSocialContributeBtn" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">Hifadhi Mchango</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function approveSocialRequest(requestId, btn) {
    if (!confirm('Thibitisha kutoa msaada huu? Fedha zitachukuliwa kutoka Mfuko wa Jamii.')) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inatuma...';
    fetch(vicobaData.root + 'vicoba/v1/social-fund/approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': vicobaData.nonce },
        body: JSON.stringify({ request_id: requestId, payment_method: 'cash' })
    }).then(r => r.json()).then(res => {
        if (res.success) { location.reload(); }
        else { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Idhinisha & Toa'; alert(res.message); }
    });
}
</script>
