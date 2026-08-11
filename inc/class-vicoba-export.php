<?php
/**
 * VICOBA Export Engine
 * CSV/Excel downloads for member statements, group reports, and share-out summaries
 */

if (!defined('ABSPATH')) exit;

class VICOBA_Export {

    /**
     * Export all group transactions as CSV
     */
    public static function export_group_csv($group_id, $from = null, $to = null) {
        global $wpdb;
        $table_t = $wpdb->prefix . 'vicoba_transactions';
        $table_m = $wpdb->prefix . 'vicoba_members';

        $date_filter = '';
        if ($from && $to) {
            $date_filter = $wpdb->prepare(" AND DATE(t.created_at) BETWEEN %s AND %s", $from, $to);
        }

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT t.transaction_code, t.type, t.amount, t.payment_method, t.description, 
                    m.full_name as member_name, m.member_number, t.created_at
             FROM $table_t t
             LEFT JOIN $table_m m ON t.member_id = m.id
             WHERE t.group_id = %d $date_filter
             ORDER BY t.created_at DESC",
            $group_id
        ));

        $group = VICOBA_Groups::get_group($group_id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vicoba-ledger-' . sanitize_title($group->name ?? 'group') . '-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

        // Header row
        fputcsv($output, ['Kodi', 'Aina ya Muamala', 'Kiasi (TZS)', 'Njia ya Malipo', 'Mwanachama', 'Namba ya Mwanachama', 'Maelezo', 'Tarehe']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row->transaction_code,
                $row->type,
                number_format($row->amount, 2),
                $row->payment_method,
                $row->member_name ?? 'N/A',
                $row->member_number ?? 'N/A',
                $row->description,
                date('d/m/Y H:i', strtotime($row->created_at)),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export individual member statement as CSV
     */
    public static function export_member_statement_csv($group_id, $member_id) {
        global $wpdb;
        $member = VICOBA_Members::get_member($member_id);
        if (!$member || $member->group_id != $group_id) wp_die('Hakuna ruhusa');

        $shares_table = $wpdb->prefix . 'vicoba_shares';
        $loans_table  = $wpdb->prefix . 'vicoba_loans';
        $repay_table  = $wpdb->prefix . 'vicoba_loan_repayments';
        $fines_table  = $wpdb->prefix . 'vicoba_fines';

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT 'Hisa' as type, payment_date as date, total_amount as amount, CONCAT(share_count,' hisa') as description
             FROM $shares_table WHERE group_id=%d AND member_id=%d
             UNION ALL
             SELECT 'Rejesho la Mkopo' as type, payment_date as date, amount_paid as amount, 'Rejesho la Mkopo' as description
             FROM $repay_table WHERE loan_id IN (SELECT id FROM $loans_table WHERE group_id=%d AND member_id=%d)
             UNION ALL
             SELECT 'Faini' as type, paid_at as date, amount, reason as description
             FROM $fines_table WHERE group_id=%d AND member_id=%d AND status='paid'
             ORDER BY date DESC",
            $group_id, $member_id,
            $group_id, $member_id,
            $group_id, $member_id
        ));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="statement-' . sanitize_title($member->full_name) . '-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Mwanachama:', $member->full_name, 'Namba:', $member->member_number, 'Tarehe ya Ripoti:', date('d/m/Y')]);
        fputcsv($output, []);
        fputcsv($output, ['Tarehe', 'Aina', 'Maelezo', 'Kiasi (TZS)']);

        $total = 0;
        foreach ($rows as $row) {
            fputcsv($output, [
                $row->date ? date('d/m/Y', strtotime($row->date)) : 'N/A',
                $row->type,
                $row->description,
                number_format($row->amount, 2),
            ]);
            $total += floatval($row->amount);
        }

        fputcsv($output, []);
        fputcsv($output, ['', '', 'JUMLA YA MUAMALA:', number_format($total, 2)]);
        fclose($output);
        exit;
    }

    /**
     * Export share-out distribution as CSV
     */
    public static function export_shareout_csv($shareout_id) {
        global $wpdb;
        $table_s = $wpdb->prefix . 'vicoba_shareouts';
        $table_d = $wpdb->prefix . 'vicoba_shareout_details';
        $table_m = $wpdb->prefix . 'vicoba_members';

        $shareout = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_s WHERE id=%d", $shareout_id));
        if (!$shareout) wp_die('Mgawanyo haukupatikana');

        $details = $wpdb->get_results($wpdb->prepare(
            "SELECT d.*, m.full_name, m.member_number, m.phone FROM $table_d d JOIN $table_m m ON d.member_id=m.id WHERE d.shareout_id=%d ORDER BY d.net_payout DESC",
            $shareout_id
        ));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="shareout-' . $shareout->shareout_date . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['RIPOTI YA MGAWANYO (SHARE-OUT) - ' . date('d/m/Y', strtotime($shareout->shareout_date))]);
        fputcsv($output, ['Pool ya Jumla:', number_format($shareout->net_distributable, 2), 'TZS']);
        fputcsv($output, []);
        fputcsv($output, ['Namba', 'Jina Kamili', 'Simu', 'Hisa', 'Uwiano (%)', 'Kiasi Halisi (TZS)', 'Mkopo Uliodaiwa', 'Kiasi cha Kulipwa (TZS)']);

        $i = 1;
        foreach ($details as $d) {
            fputcsv($output, [
                $i++,
                $d->full_name,
                $d->phone,
                $d->total_member_shares,
                number_format($d->share_ratio * 100, 2) . '%',
                number_format($d->gross_payout, 2),
                number_format($d->active_loan_deduction, 2),
                number_format($d->net_payout, 2),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Handle download requests via GET params
     */
    public static function handle_download_request() {
        if (!isset($_GET['vicoba_export'])) return;
        if (!is_user_logged_in()) wp_die('Tafadhali ingia kwanza.');

        $type     = sanitize_text_field($_GET['vicoba_export']);
        $member   = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $member ? $member->group_id : 0;

        if (!$group_id) wp_die('Hakuna kikundi kilichopatikana.');

        if ($type === 'ledger_csv') {
            $from = sanitize_text_field($_GET['from'] ?? '');
            $to   = sanitize_text_field($_GET['to'] ?? '');
            self::export_group_csv($group_id, $from ?: null, $to ?: null);
        }

        if ($type === 'member_statement_csv') {
            $member_id = absint($_GET['member_id'] ?? 0);
            if (!$member_id) $member_id = $member->id;
            self::export_member_statement_csv($group_id, $member_id);
        }

        if ($type === 'shareout_csv') {
            $shareout_id = absint($_GET['shareout_id'] ?? 0);
            if ($shareout_id) self::export_shareout_csv($shareout_id);
        }
    }
}
