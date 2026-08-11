<?php
/**
 * Dashboard Loans Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;
$group = VICOBA_Groups::get_group($group_id);

$members = VICOBA_Members::get_members_by_group($group_id, 'active');
$loans = VICOBA_Loans::get_loans_by_group($group_id);

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$is_treasurer = in_array($user_role, array('super_admin', 'treasurer'));
$is_chairman = in_array($user_role, array('super_admin', 'group_admin'));

// Check pending guarantor requests for current logged-in member
global $wpdb;
$table_g = $wpdb->prefix . 'vicoba_guarantors';
$pending_guarantors = array();
if ($member) {
    $table_l = $wpdb->prefix . 'vicoba_loans';
    $table_m = $wpdb->prefix . 'vicoba_members';
    $pending_guarantors = $wpdb->get_results($wpdb->prepare(
        "SELECT g.*, l.loan_code, l.principal_amount, m.full_name as applicant_name FROM $table_g g 
         JOIN $table_l l ON g.loan_id = l.id 
         JOIN $table_m m ON l.member_id = m.id 
         WHERE g.guarantor_member_id = %d AND g.status = 'pending'",
        $member->id
    ));
}
?>

<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mikopo & Wadhamini</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kikomo cha Mkopo: <strong class="text-vicoba-700">Mara <?php echo $group ? $group->loan_multiplier : 3; ?> ya Hisa Zako</strong> | 
                Riba: <strong class="text-vicoba-700"><?php echo $group ? $group->interest_rate : 5; ?>% (<?php echo $group ? ucfirst($group->interest_type) : 'Reducing'; ?>)</strong>
            </p>
        </div>

        <button onclick="document.getElementById('applyLoanModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:from-vicoba-700 hover:to-emerald-700 text-white font-bold text-xs shadow-lg transition">
            <i class="fa-solid fa-hand-holding-dollar mr-2"></i> Omba Mkopo Kipya
        </button>
    </div>

    <!-- Pending Guarantor Approvals Alert Widget -->
    <?php if (!empty($pending_guarantors)) : ?>
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm space-y-3">
            <h3 class="text-sm font-extrabold text-amber-900 flex items-center">
                <i class="fa-solid fa-user-shield text-amber-600 mr-2 text-base"></i> Maombi ya Udhamini (Anasubiri Idhini Yako)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php foreach ($pending_guarantors as $pg) : ?>
                    <div class="p-3.5 bg-white rounded-xl border border-amber-200/80 flex items-center justify-between shadow-xs">
                        <div>
                            <p class="text-xs font-bold text-slate-800"><?php echo esc_html($pg->applicant_name); ?></p>
                            <p class="text-[11px] text-slate-500">Mkopo: <?php echo esc_html($pg->loan_code); ?> | Dhamana: TZS <?php echo number_format($pg->amount_guaranteed); ?></p>
                        </div>
                        <div class="flex space-x-1.5">
                            <button onclick="respondGuarantor(<?php echo $pg->id; ?>, 'approved')" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px]">Kukubali</button>
                            <button onclick="respondGuarantor(<?php echo $pg->id; ?>, 'rejected')" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px]">Kukataa</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Loans List Table -->
    <div class="glass-card rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-sm text-slate-800 flex items-center">
            <i class="fa-solid fa-list-check text-vicoba-600 mr-2"></i> Orodha ya Mikopo Yote
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4">Kodi ya Mkopo</th>
                        <th class="py-3.5 px-4">Mwanachama</th>
                        <th class="py-3.5 px-4">Kiasi cha Mkopo</th>
                        <th class="py-3.5 px-4">Salio Lililobaki</th>
                        <th class="py-3.5 px-4">Muda (Miezi)</th>
                        <th class="py-3.5 px-4">Hali (Status)</th>
                        <th class="py-3.5 px-4 text-right">Vitendo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($loans)) : ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Hakuna mikopo kwenye mfumo kwa sasa.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($loans as $l) : ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-vicoba-800"><?php echo esc_html($l->loan_code); ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800"><?php echo esc_html($l->full_name); ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">TZS <?php echo number_format($l->principal_amount); ?></td>
                                <td class="py-3.5 px-4 font-extrabold text-emerald-600">TZS <?php echo number_format($l->balance_remaining); ?></td>
                                <td class="py-3.5 px-4"><?php echo esc_html($l->repayment_period_months); ?> Miezi</td>
                                <td class="py-3.5 px-4">
                                    <?php if ($l->status === 'active') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    <?php elseif ($l->status === 'pending_guarantors') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">Pending Guarantors</span>
                                    <?php elseif ($l->status === 'pending_treasurer') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">Pending Treasurer</span>
                                    <?php elseif ($l->status === 'pending_chairman') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">Pending Chairman</span>
                                    <?php elseif ($l->status === 'closed') : ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    <?php if ($l->status === 'active') : ?>
                                        <button onclick="openRepayModal(<?php echo $l->id; ?>, '<?php echo esc_js($l->loan_code); ?>', <?php echo $l->balance_remaining; ?>)" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">Rejesha</button>
                                    <?php endif; ?>

                                    <?php if ($l->status === 'pending_chairman' && $is_chairman) : ?>
                                        <button onclick="disburseLoan(<?php echo $l->id; ?>)" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px]">Idhinisha & Utoe</button>
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

<!-- Modal: Apply Loan -->
<div id="applyLoanModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-hand-holding-dollar mr-2 text-vicoba-600"></i> Omba Mkopo Kipya</h3>
            <button onclick="document.getElementById('applyLoanModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="applyLoanForm" class="mt-4 space-y-4">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <input type="hidden" name="member_id" value="<?php echo $member ? $member->id : 0; ?>">

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kiasi cha Mkopo (TZS) *</label>
                <input name="principal_amount" type="number" step="1000" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="mf. 300000">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Muda wa Marejesho (Miezi) *</label>
                <select name="repayment_period_months" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="1">Mwezi 1</option>
                    <option value="2">Miezi 2</option>
                    <option value="3" selected>Miezi 3</option>
                    <option value="6">Miezi 6</option>
                    <option value="12">Miezi 12</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Chagua Wadhamini (Wanachama 2) *</label>
                <select name="guarantor_ids" multiple required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs h-24">
                    <?php foreach ($members as $m) : ?>
                        <?php if (!$member || $m->id != $member->id) : ?>
                            <option value="<?php echo $m->id; ?>"><?php echo esc_html($m->full_name); ?> (<?php echo esc_html($m->member_number); ?>)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Shikilia Ctrl/Cmd ili kuchagua wanachama wengi.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Sababu / Lengo la Mkopo *</label>
                <textarea name="purpose" rows="2" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="Eleza maelezo ya biashara au dharura..."></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('applyLoanModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="submitLoanBtn" class="px-4 py-2 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-md">Tuma Ombi la Mkopo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Repay Loan -->
<div id="repayLoanModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-md w-full p-6 border border-white/20 bg-white relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-extrabold text-slate-800"><i class="fa-solid fa-money-check-dollar mr-2 text-emerald-600"></i> Weka Rejesho la Mkopo</h3>
            <button onclick="document.getElementById('repayLoanModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="repayLoanForm" class="mt-4 space-y-4">
            <input type="hidden" id="repay_loan_id" name="loan_id" value="0">
            <div>
                <p class="text-xs font-semibold text-slate-500">Mkopo: <strong id="repay_loan_code" class="text-slate-800"></strong></p>
                <p class="text-xs font-semibold text-slate-500">Salio Lililobaki: <strong id="repay_balance" class="text-emerald-700"></strong></p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kiasi cha Rejesho (TZS) *</label>
                <input name="amount" type="number" step="500" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="Ingiza kiasi kilicholipwa">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Njia ya Malipo *</label>
                <select name="payment_method" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                    <option value="cash">Pesa Taslimu (Cash)</option>
                    <option value="mobile_money">Mobile Money (M-Pesa / Tigo / Airtel)</option>
                    <option value="bank">Akaunti ya Benki</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('repayLoanModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Ghairi</button>
                <button type="submit" id="saveRepayBtn" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">Hifadhi Rejesho</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRepayModal(loanId, loanCode, balance) {
    document.getElementById('repay_loan_id').value = loanId;
    document.getElementById('repay_loan_code').innerText = loanCode;
    document.getElementById('repay_balance').innerText = 'TZS ' + balance.toLocaleString();
    document.getElementById('repayLoanModal').classList.remove('hidden');
}

async function respondGuarantor(guarantorId, status) {
    try {
        const res = await fetch(vicobaData.root + 'vicoba/v1/loans/guarantor-respond', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': vicobaData.nonce,
            },
            body: JSON.stringify({ guarantor_id: guarantorId, status })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Imefanikiwa!', data.message, 'success').then(() => location.reload());
        }
    } catch(err) {
        Swal.fire('Hitilafu', 'Imeshindikana kusindika majibu.', 'error');
    }
}

async function disburseLoan(loanId) {
    const confirm = await Swal.fire({
        title: 'Unathibitisha?',
        text: 'Unathibitisha kutoa na kuidhinisha mkopo huu?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ndiyo, Idhinisha!'
    });

    if (confirm.isConfirmed) {
        try {
            const res = await fetch(vicobaData.root + 'vicoba/v1/loans/disburse', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': vicobaData.nonce,
                },
                body: JSON.stringify({ loan_id: loanId })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Umeidhinishwa!', data.message, 'success').then(() => location.reload());
            }
        } catch(err) {
            Swal.fire('Hitilafu', 'Imeshindikana kuidhinisha mkopo.', 'error');
        }
    }
}
</script>
