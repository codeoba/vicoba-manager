<?php
/**
 * VICOBA Reports Manager
 * Statements generation, printable views, and CSV exports
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Reports {

    public static function get_member_statement($group_id, $member_id) {
        global $wpdb;

        $member = VICOBA_Members::get_member($member_id);
        $shares = VICOBA_Shares::get_member_total_shares($group_id, $member_id);
        $loans = VICOBA_Loans::get_member_loans($member_id);

        $table_t = $wpdb->prefix . 'vicoba_transactions';
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_t WHERE group_id = %d AND member_id = %d ORDER BY created_at DESC",
            $group_id, $member_id
        ));

        $table_f = $wpdb->prefix . 'vicoba_fines';
        $fines = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_f WHERE group_id = %d AND member_id = %d ORDER BY created_at DESC",
            $group_id, $member_id
        ));

        return array(
            'member'       => $member,
            'shares'       => $shares,
            'loans'        => $loans,
            'transactions' => $transactions,
            'fines'        => $fines,
        );
    }

    public static function generate_group_summary_csv($group_id) {
        $group = VICOBA_Groups::get_group($group_id);
        $members = VICOBA_Members::get_members_by_group($group_id);
        $ledger = VICOBA_Ledger::get_box_balance($group_id);

        $out = "VICOBA GROUP FINANCIAL SUMMARY REPORT\n";
        $out .= "Group Name: " . ($group ? $group->name : '') . "\n";
        $out .= "Generated Date: " . date('Y-m-d H:i:s') . "\n\n";

        $out .= "FINANCIAL OVERVIEW\n";
        $out .= "Total Inflows: TZS " . number_format($ledger['total_inflows'], 2) . "\n";
        $out .= "Total Outflows: TZS " . number_format($ledger['total_outflows'], 2) . "\n";
        $out .= "Box Balance: TZS " . number_format($ledger['box_balance'], 2) . "\n\n";

        $out .= "MEMBER ROSTER & SHARES SUMMARY\n";
        $out .= "Member No,Full Name,Phone,Status,Role,Total Shares,Shares Value (TZS)\n";

        foreach ($members as $m) {
            $m_shares = VICOBA_Shares::get_member_total_shares($group_id, $m->id);
            $out .= sprintf(
                '"%s","%s","%s","%s","%s",%d,%.2f' . "\n",
                $m->member_number,
                $m->full_name,
                $m->phone,
                $m->status,
                $m->role,
                $m_shares['count'],
                $m_shares['value']
            );
        }

        return $out;
    }
}
