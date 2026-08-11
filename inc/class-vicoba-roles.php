<?php
/**
 * VICOBA Roles & Access Control
 * Registers custom WordPress roles and maps capabilities
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Roles {

    public static function setup_roles() {
        // 1. Super Admin Role
        if (!get_role('super_admin')) {
            add_role('super_admin', __('Super Admin (SaaS Owner)', 'vicoba-manager'), array(
                'read' => true,
                'manage_vicoba_all_groups' => true,
                'manage_vicoba_settings' => true,
            ));
        }

        // 2. Group Admin / Chairman
        if (!get_role('group_admin')) {
            add_role('group_admin', __('Group Admin / Mwenyekiti', 'vicoba-manager'), array(
                'read' => true,
                'manage_vicoba_group' => true,
                'approve_vicoba_loans' => true,
                'run_vicoba_shareout' => true,
                'view_vicoba_reports' => true,
            ));
        }

        // 3. Secretary / Katibu
        if (!get_role('secretary')) {
            add_role('secretary', __('Katibu (Secretary)', 'vicoba-manager'), array(
                'read' => true,
                'manage_vicoba_meetings' => true,
                'record_vicoba_attendance' => true,
                'manage_vicoba_members' => true,
            ));
        }

        // 4. Treasurer / Mweka Hazina
        if (!get_role('treasurer')) {
            add_role('treasurer', __('Mweka Hazina (Treasurer)', 'vicoba-manager'), array(
                'read' => true,
                'record_vicoba_shares' => true,
                'verify_vicoba_loans' => true,
                'record_vicoba_fines' => true,
                'manage_vicoba_ledger' => true,
                'view_vicoba_reports' => true,
            ));
        }

        // 5. Member / Mwanachama
        if (!get_role('member')) {
            add_role('member', __('Mwanachama (Member)', 'vicoba-manager'), array(
                'read' => true,
                'view_vicoba_own_data' => true,
                'apply_vicoba_loan' => true,
                'guarantee_vicoba_loan' => true,
            ));
        }
    }
}
