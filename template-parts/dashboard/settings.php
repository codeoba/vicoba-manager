<?php
/**
 * Dashboard: Group Settings - Complete with AJAX save
 */
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id     = $member ? $member->group_id : 1;
$group        = VICOBA_Groups::get_group($group_id);
$user_role    = !empty($current_user->roles) ? $current_user->roles[0] : 'member';
$can_edit     = in_array($user_role, ['super_admin','group_admin','administrator']);
?>
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mipangilio ya Kikundi</h1>
        <p class="text-xs text-slate-500 mt-1">Badilisha bei ya hisa, riba ya mikopo, vipimo vya mzunguko, na katiba ya kikundi</p>
    </div>

    <?php if (!$can_edit): ?>
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 font-bold text-xs flex items-center">
        <i class="fa-solid fa-lock text-lg mr-2 text-amber-600"></i> Una ruhusa ya kuona tu. Wasiliana na Mwenyekiti au Katibu kubadilisha mipangilio.
    </div>
    <?php endif; ?>

    <form id="groupSettingsForm" class="space-y-6">
        <!-- Basic Info -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200/80 bg-white space-y-4">
            <h3 class="text-sm font-extrabold text-vicoba-800 flex items-center border-b pb-3">
                <i class="fa-solid fa-building-columns mr-2 text-vicoba-600"></i> Taarifa za Msingi za Kikundi
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jina la Kikundi *</label>
                    <input name="name" type="text" value="<?php echo esc_attr($group->name ?? ''); ?>" required
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Namba ya Usajili</label>
                    <input name="registration_number" type="text" value="<?php echo esc_attr($group->registration_number ?? ''); ?>"
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mkoa</label>
                    <input name="region" type="text" value="<?php echo esc_attr($group->region ?? ''); ?>"
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Wilaya</label>
                    <input name="district" type="text" value="<?php echo esc_attr($group->district ?? ''); ?>"
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sarafu (Currency)</label>
                    <select name="currency" <?php echo !$can_edit ? 'disabled' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                        <option value="TZS" <?php selected($group->currency ?? 'TZS', 'TZS'); ?>>TZS - Shilingi ya Tanzania</option>
                        <option value="USD" <?php selected($group->currency ?? '', 'USD'); ?>>USD - Dollar ya Amerika</option>
                        <option value="KES" <?php selected($group->currency ?? '', 'KES'); ?>>KES - Shilingi ya Kenya</option>
                        <option value="UGX" <?php selected($group->currency ?? '', 'UGX'); ?>>UGX - Shilingi ya Uganda</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tarehe ya Kuanza Mzunguko</label>
                    <input name="cycle_start_date" type="date" value="<?php echo esc_attr($group->cycle_start_date ?? date('Y-01-01')); ?>"
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
            </div>
        </div>

        <!-- Shares & Loans Settings -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200/80 bg-white space-y-4">
            <h3 class="text-sm font-extrabold text-vicoba-800 flex items-center border-b pb-3">
                <i class="fa-solid fa-coins mr-2 text-amber-500"></i> Mipangilio ya Hisa na Mikopo
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bei ya Hisa 1 (TZS) *</label>
                    <input name="share_price" type="number" step="500" min="1000" value="<?php echo esc_attr($group->share_price ?? 10000); ?>" required
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hisa za Chini kwa Mkutano *</label>
                    <input name="min_shares_per_cycle" type="number" min="1" value="<?php echo esc_attr($group->min_shares_per_cycle ?? 1); ?>" required
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hisa za Juu kwa Mkutano *</label>
                    <input name="max_shares_per_cycle" type="number" min="1" value="<?php echo esc_attr($group->max_shares_per_cycle ?? 5); ?>" required
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Riba ya Mikopo (%) *</label>
                    <input name="loan_interest_rate" type="number" step="0.5" min="0" value="<?php echo esc_attr($group->loan_interest_rate ?? $group->interest_rate ?? 10); ?>" required
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aina ya Riba *</label>
                    <select name="loan_interest_type" <?php echo !$can_edit ? 'disabled' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                        <option value="flat" <?php selected($group->loan_interest_type ?? $group->interest_type ?? 'flat', 'flat'); ?>>Flat Rate (Kiasi Maalum)</option>
                        <option value="reducing" <?php selected($group->loan_interest_type ?? $group->interest_type ?? '', 'reducing'); ?>>Reducing Balance (Inayopungua)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kikomo cha Mkopo (x Hisa) *</label>
                    <input name="max_loan_multiplier" type="number" step="0.5" min="1" value="<?php echo esc_attr($group->max_loan_multiplier ?? $group->loan_multiplier ?? 3); ?>" required
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
            </div>
        </div>

        <!-- Social Fund & Fines -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200/80 bg-white space-y-4">
            <h3 class="text-sm font-extrabold text-vicoba-800 flex items-center border-b pb-3">
                <i class="fa-solid fa-heart-pulse mr-2 text-rose-500"></i> Mfuko wa Jamii na Faini
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mchango wa Mfuko wa Jamii (TZS/Mkutano)</label>
                    <input name="social_fund_per_cycle" type="number" step="500" min="0" value="<?php echo esc_attr($group->social_fund_per_cycle ?? 2000); ?>"
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Faini ya Kutokuja Mkutano (TZS)</label>
                    <input name="meeting_fine_amount" type="number" step="500" min="0" value="<?php echo esc_attr($group->meeting_fine_amount ?? 1000); ?>"
                        <?php echo !$can_edit ? 'readonly' : ''; ?>
                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>">
                </div>
            </div>
        </div>

        <!-- Constitution -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200/80 bg-white space-y-4">
            <h3 class="text-sm font-extrabold text-vicoba-800 flex items-center border-b pb-3">
                <i class="fa-solid fa-file-contract mr-2 text-slate-500"></i> Katiba na Sheria za Kikundi
            </h3>
            <textarea name="description" rows="5"
                <?php echo !$can_edit ? 'readonly' : ''; ?>
                class="block w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-vicoba-400 outline-none <?php echo !$can_edit ? 'bg-slate-50' : ''; ?>"
                placeholder="Andika sheria na katiba ya kikundi chako hapa..."><?php echo esc_textarea($group->description ?? $group->constitution_text ?? ''); ?></textarea>
        </div>

        <?php if ($can_edit): ?>
        <div class="flex justify-end">
            <button type="submit" id="saveSettingsBtn" class="inline-flex items-center px-6 py-3 rounded-2xl bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:opacity-90 text-white font-bold text-sm shadow-xl transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Hifadhi Mipangilio Yote
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>
