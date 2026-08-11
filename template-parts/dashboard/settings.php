<?php
/**
 * Dashboard Group Settings Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;
$group = VICOBA_Groups::get_group($group_id);

$saved_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    VICOBA_Groups::update_group($group_id, $_POST);
    $group = VICOBA_Groups::get_group($group_id);
    $saved_message = 'Mipangilio ya kikundi imehifadhiwa kikamilifu!';
}
?>

<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mipangilio ya Kikundi</h1>
        <p class="text-xs text-slate-500 mt-1">Badilisha bei ya hisa, riba ya mikopo, vipimo vya mzunguko, na katiba ya kikundi</p>
    </div>

    <?php if (!empty($saved_message)) : ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs flex items-center">
            <i class="fa-solid fa-circle-check text-lg mr-2 text-emerald-600"></i> <?php echo esc_html($saved_message); ?>
        </div>
    <?php endif; ?>

    <div class="glass-card p-8 rounded-2xl border border-slate-200/80 bg-white">
        <form method="POST" action="" class="space-y-6">
            <input type="hidden" name="save_settings" value="1">

            <h3 class="text-sm font-extrabold text-vicoba-800 border-b pb-2"><i class="fa-solid fa-sliders mr-2"></i> Mipangilio ya Msingi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Jina la Kikundi *</label>
                    <input name="name" type="text" value="<?php echo esc_attr($group ? $group->name : ''); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Namba ya Usajili</label>
                    <input name="registration_number" type="text" value="<?php echo esc_attr($group ? $group->registration_number : ''); ?>" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Mkoa *</label>
                    <input name="region" type="text" value="<?php echo esc_attr($group ? $group->region : ''); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Wilaya *</label>
                    <input name="district" type="text" value="<?php echo esc_attr($group ? $group->district : ''); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
            </div>

            <h3 class="text-sm font-extrabold text-vicoba-800 border-b pb-2 pt-2"><i class="fa-solid fa-coins mr-2"></i> Mipangilio ya Hisa na Mikopo</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Bei ya Hisa 1 (TZS) *</label>
                    <input name="share_price" type="number" value="<?php echo esc_attr($group ? $group->share_price : 10000); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hisa za Chini kwa Mzunguko *</label>
                    <input name="min_shares_per_cycle" type="number" value="<?php echo esc_attr($group ? $group->min_shares_per_cycle : 1); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hisa za Juu kwa Mzunguko *</label>
                    <input name="max_shares_per_cycle" type="number" value="<?php echo esc_attr($group ? $group->max_shares_per_cycle : 5); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Riba ya Mikopo (%) *</label>
                    <input name="interest_rate" type="number" step="0.1" value="<?php echo esc_attr($group ? $group->interest_rate : 5); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Aina ya Riba *</label>
                    <select name="interest_type" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                        <option value="reducing" <?php selected($group ? $group->interest_type : '', 'reducing'); ?>>Reducing Balance (Inayopungua)</option>
                        <option value="fixed" <?php selected($group ? $group->interest_type : '', 'fixed'); ?>>Fixed Interest (Yenye Kiasi Maalum)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Kikomo cha Mkopo (Multiplier) *</label>
                    <input name="loan_multiplier" type="number" step="0.5" value="<?php echo esc_attr($group ? $group->loan_multiplier : 3); ?>" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs">
                </div>
            </div>

            <h3 class="text-sm font-extrabold text-vicoba-800 border-b pb-2 pt-2"><i class="fa-solid fa-file-contract mr-2"></i> Katiba ya Kikundi</h3>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Maandishi ya Katiba</label>
                <textarea name="constitution_text" rows="5" class="mt-1 block w-full px-3 py-2 border rounded-lg text-xs" placeholder="Andika au weka sheria na katiba ya kikundi chako hapa..."><?php echo esc_textarea($group ? $group->constitution_text : ''); ?></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-vicoba-600 hover:bg-vicoba-700 text-white font-bold text-xs shadow-lg transition">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Hifadhi Mipangilio
                </button>
            </div>
        </form>
    </div>
</div>
