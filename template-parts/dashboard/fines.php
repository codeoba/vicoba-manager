<?php
/**
 * Dashboard: Fines Management - Complete
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id     = $member ? $member->group_id : 1;
$members      = VICOBA_Members::get_members_by_group($group_id, 'active');
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_admin     = in_array($user_role, ['super_admin','group_admin','secretary','treasurer','administrator']);

global $wpdb;
$ft = $wpdb->prefix . 'vicoba_fines';
$mt = $wpdb->prefix . 'vicoba_members';
$fines = $wpdb->get_results($wpdb->prepare(
    "SELECT f.*, m.full_name, m.member_number FROM $ft f JOIN $mt m ON f.member_id = m.id WHERE f.group_id = %d ORDER BY f.created_at DESC LIMIT 100", $group_id
));

$total_pending = $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $ft WHERE group_id = %d AND status = 'pending'", $group_id
));
$total_paid = $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM $ft WHERE group_id = %d AND status = 'paid'", $group_id
));
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Faini (Fines)</h1>
            <p class="text-xs text-slate-500 mt-1">Simamia faini za wanachama, weka na pokea malipo</p>
        </div>
        <?php if ($is_admin): ?>
        <button onclick="openModal('issueFineModal')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-pink-600 hover:opacity-90 text-white font-bold text-xs shadow-lg transition">
            <i class="fa-solid fa-gavel mr-2"></i> Toa Faini Mpya
        </button>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="glass-card p-6 rounded-2xl border border-rose-200/60 shadow-sm flex items-center justify-between bg-rose-50/30">
            <div>
                <span class="text-xs font-bold uppercase text-rose-400">Faini Zinazongojwa</span>
                <p class="text-2xl font-extrabold text-rose-700 mt-1">TZS <?php echo number_format($total_pending); ?></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>
        <div class="glass-card p-6 rounded-2xl border border-emerald-200/60 shadow-sm flex items-center justify-between bg-emerald-50/30">
            <div>
                <span class="text-xs font-bold uppercase text-emerald-400">Faini Zilizolipwa</span>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">TZS <?php echo number_format($total_paid); ?></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <!-- Fines Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-gavel text-rose-600 mr-2"></i> Orodha ya Faini Zote
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Mwanachama</th>
                        <th class="py-3.5 px-4">Sababu ya Faini</th>
                        <th class="py-3.5 px-4">Kiasi</th>
                        <th class="py-3.5 px-4">Tarehe</th>
                        <th class="py-3.5 px-4">Hali</th>
                        <?php if ($is_admin): ?><th class="py-3.5 px-4 text-right">Vitendo</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($fines)): ?>
                        <tr><td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-gavel text-3xl mb-3 block opacity-20"></i>Hakuna faini zilizorekodiwa.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($fines as $f): ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <?php echo esc_html($f->full_name); ?>
                                <span class="text-[10px] text-slate-400 ml-1">(<?php echo esc_html($f->member_number); ?>)</span>
                            </td>
                            <td class="py-3.5 px-4"><?php echo esc_html($f->reason ?? '—'); ?></td>
                            <td class="py-3.5 px-4 font-extrabold text-rose-700">TZS <?php echo number_format($f->amount); ?></td>
                            <td class="py-3.5 px-4 text-slate-500"><?php echo date('d/m/Y', strtotime($f->created_at)); ?></td>
                            <td class="py-3.5 px-4">
                                <?php if ($f->status === 'paid'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Imelipwa</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">Inangoja</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($is_admin && $f->status === 'pending'): ?>
                            <td class="py-3.5 px-4 text-right">
                                <button onclick="payFine(<?php echo $f->id; ?>, this)"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    <i class="fa-solid fa-money-bill mr-1"></i> Pokea Malipo
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

<!-- Modal: Issue Fine -->
<div id="issueFineModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 vicoba-modal-backdrop">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-gavel mr-2 text-rose-600"></i> Toa Faini Kwa Mwanachama</h3>
            <button onclick="closeModal('issueFineModal')" class="text-slate-400 hover:text-slate-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="issueFineForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Chagua Mwanachama *</label>
                <select name="member_id" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-400 outline-none">
                    <option value="">-- Chagua Mwanachama --</option>
                    <?php foreach ($members as $m): ?>
                    <option value="<?php echo $m->id; ?>"><?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Sababu ya Faini *</label>
                <select name="reason" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-400 outline-none">
                    <option value="Kutokuja Mkutano">Kutokuja Mkutano</option>
                    <option value="Kuchelewa Mkutano">Kuchelewa Mkutano</option>
                    <option value="Kutolipa Hisa kwa Wakati">Kutolipa Hisa kwa Wakati</option>
                    <option value="Kutolipa Mkopo kwa Wakati">Kutolipa Mkopo kwa Wakati</option>
                    <option value="Kukiuka Sheria za Kikundi">Kukiuka Sheria za Kikundi</option>
                    <option value="Nyingine">Nyingine (Andika hapa chini)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Maelezo Zaidi (Hiari)</label>
                <input name="description" type="text" class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-400 outline-none" placeholder="Maelezo zaidi...">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kiasi cha Faini (TZS) *</label>
                <input name="amount" type="number" step="500" min="500" required class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-400 outline-none" placeholder="mf. 1000">
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('issueFineModal')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Funga</button>
                <button type="submit" id="saveFineBtn" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">Weka Faini</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function payFine(fineId, btn) {
    if (!confirm('Thibitisha kupokea malipo ya faini hii?')) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inatuma...';
    fetch(vicobaData.root + 'vicoba/v1/fines/pay', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': vicobaData.nonce },
        body: JSON.stringify({ fine_id: fineId, payment_method: 'cash' })
    }).then(r => r.json()).then(res => {
        if (res.success) { location.reload(); }
        else { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-money-bill mr-1"></i> Pokea Malipo'; alert(res.message); }
    });
}
</script>
