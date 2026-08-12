<?php
/**
 * VICOBA Export Engine
 * CSV downloads for member statements, group reports, and share-out summaries
 */

if (!defined('ABSPATH')) exit;

class VICOBA_Export {

    /**
     * Clean all output buffers to ensure clean CSV file stream
     */
    private static function clean_buffers() {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }

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
        $group_name = $group ? $group->name : 'kikundi';

        self::clean_buffers();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vicoba-ledger-' . sanitize_title($group_name) . '-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Microsoft Excel compatibility

        // Header row
        fputcsv($output, ['Kodi ya Muamala', 'Aina ya Muamala', 'Kiasi (TZS)', 'Njia ya Malipo', 'Mwanachama', 'Namba ya Mwanachama', 'Maelezo', 'Tarehe na Muda']);

        if (!empty($rows)) {
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->transaction_code,
                    $row->type,
                    number_format($row->amount, 2, '.', ''),
                    $row->payment_method,
                    $row->member_name ?? 'N/A',
                    $row->member_number ?? 'N/A',
                    $row->description,
                    date('d/m/Y H:i', strtotime($row->created_at)),
                ]);
            }
        } else {
            fputcsv($output, ['Hakuna miamala iliyopatikana katika kipindi hiki.']);
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
        if (!$member) {
            // Fallback: try finding member by ID without strict group filter
            $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vicoba_members WHERE id = %d", $member_id));
        }

        if (!$member) {
            wp_die('Mwanachama hakupatikana kwa ajili ya ripoti hii.');
        }

        $group_id = $member->group_id;
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

        self::clean_buffers();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="statement-' . sanitize_title($member->full_name) . '-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['TAARIFA YA MWANACHAMA (STATEMENT)']);
        fputcsv($output, ['Jina Kamili:', $member->full_name]);
        fputcsv($output, ['Namba ya Mwanachama:', $member->member_number]);
        fputcsv($output, ['Simu:', $member->phone]);
        fputcsv($output, ['Tarehe ya Kupakuliwa:', date('d/m/Y H:i')]);
        fputcsv($output, []);
        fputcsv($output, ['Tarehe', 'Aina ya Muamala', 'Maelezo', 'Kiasi (TZS)']);

        $total = 0;
        if (!empty($rows)) {
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->date ? date('d/m/Y', strtotime($row->date)) : 'N/A',
                    $row->type,
                    $row->description,
                    number_format($row->amount, 2, '.', ''),
                ]);
                $total += floatval($row->amount);
            }
        }
        fputcsv($output, []);
        fputcsv($output, ['', '', 'JUMLA YA FEDHA:', number_format($total, 2, '.', '')]);

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
        if (!$shareout) {
            wp_die('Taarifa za Mgawanyo (Share-Out) hazikupatikana.');
        }

        $details = $wpdb->get_results($wpdb->prepare(
            "SELECT d.*, m.full_name, m.member_number, m.phone FROM $table_d d JOIN $table_m m ON d.member_id=m.id WHERE d.shareout_id=%d ORDER BY d.net_payout DESC",
            $shareout_id
        ));

        self::clean_buffers();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="shareout-' . $shareout->shareout_date . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['RIPOTI YA MGAWANYO WA SHARA (SHARE-OUT)']);
        fputcsv($output, ['Tarehe ya Mgawanyo:', date('d/m/Y', strtotime($shareout->shareout_date))]);
        fputcsv($output, ['Pool ya Distributable Amount:', number_format($shareout->net_distributable, 2, '.', ''), 'TZS']);
        fputcsv($output, []);
        fputcsv($output, ['Namba', 'Jina Kamili', 'Simu', 'Idadi ya Hisa', 'Uwiano (%)', 'Gross Payout (TZS)', 'Deni la Mkopo (TZS)', 'Net Payout (TZS)']);

        $i = 1;
        foreach ($details as $d) {
            fputcsv($output, [
                $i++,
                $d->full_name,
                $d->phone,
                $d->total_member_shares,
                number_format($d->share_ratio * 100, 2) . '%',
                number_format($d->gross_payout, 2, '.', ''),
                number_format($d->active_loan_deduction, 2, '.', ''),
                number_format($d->net_payout, 2, '.', ''),
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
        if (!is_user_logged_in()) {
            wp_redirect(VICOBA_Router::get_url('login'));
            exit;
        }

        $type     = sanitize_text_field($_GET['vicoba_export']);
        $user_id  = get_current_user_id();
        $member   = VICOBA_Members::get_member_by_user_id($user_id);
        
        global $wpdb;

        // Resolve group_id
        $group_id = isset($_GET['group_id']) ? absint($_GET['group_id']) : 0;
        if (!$group_id && $member) {
            $group_id = $member->group_id;
        }
        if (!$group_id) {
            $group_id = (int)$wpdb->get_var("SELECT id FROM {$wpdb->prefix}vicoba_groups ORDER BY id ASC LIMIT 1");
        }
        if (!$group_id) $group_id = 1;

        if ($type === 'ledger_csv') {
            $from = sanitize_text_field($_GET['from'] ?? '');
            $to   = sanitize_text_field($_GET['to'] ?? '');
            self::export_group_csv($group_id, $from ?: null, $to ?: null);
        }

        if ($type === 'member_statement_csv') {
            $member_id = isset($_GET['member_id']) ? absint($_GET['member_id']) : 0;
            if (!$member_id && $member) {
                $member_id = $member->id;
            }
            if (!$member_id) {
                $member_id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}vicoba_members WHERE group_id = %d ORDER BY id ASC LIMIT 1", $group_id));
            }
            if ($member_id) {
                self::export_member_statement_csv($group_id, $member_id);
            } else {
                wp_die('Hakuna mwanachama aliyepatikana kwa ajili ya ripoti hii.');
            }
        }

        if ($type === 'shareout_csv') {
            $shareout_id = isset($_GET['shareout_id']) ? absint($_GET['shareout_id']) : 0;
            if ($shareout_id) {
                self::export_shareout_csv($shareout_id);
            }
        }
    }
}
