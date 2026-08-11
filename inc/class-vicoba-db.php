<?php
/**
 * VICOBA Database Manager
 * Handles creation and upgrades of custom MySQL tables using dbDelta()
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_DB {

    public static function init_db() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        // 1. Groups Table
        $table_groups = $wpdb->prefix . 'vicoba_groups';
        $sql_groups = "CREATE TABLE $table_groups (
            id bigint(20) NOT NULL AUTO_AUTO_INCREMENT,
            name varchar(150) NOT NULL,
            registration_number varchar(50) DEFAULT '',
            region varchar(100) NOT NULL,
            district varchar(100) NOT NULL,
            established_date date DEFAULT NULL,
            currency varchar(10) DEFAULT 'TZS',
            share_price decimal(12,2) NOT NULL DEFAULT '10000.00',
            min_shares_per_cycle int(11) NOT NULL DEFAULT 1,
            max_shares_per_cycle int(11) NOT NULL DEFAULT 5,
            interest_rate decimal(5,2) NOT NULL DEFAULT '5.00',
            interest_type varchar(20) NOT NULL DEFAULT 'reducing',
            loan_multiplier decimal(4,2) NOT NULL DEFAULT '3.00',
            social_fund_contribution decimal(12,2) NOT NULL DEFAULT '2000.00',
            constitution_text longtext DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Fix typo AUTO_AUTO_INCREMENT -> AUTO_INCREMENT if any
        $sql_groups = str_replace('AUTO_AUTO_INCREMENT', 'AUTO_INCREMENT', $sql_groups);
        dbDelta($sql_groups);

        // 2. Members Table
        $table_members = $wpdb->prefix . 'vicoba_members';
        $sql_members = "CREATE TABLE $table_members (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            member_number varchar(30) NOT NULL,
            full_name varchar(150) NOT NULL,
            phone varchar(30) NOT NULL,
            nida_number_encrypted text DEFAULT NULL,
            photo_url text DEFAULT NULL,
            emergency_contact varchar(150) DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'active',
            role varchar(30) NOT NULL DEFAULT 'member',
            joined_date date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_members);

        // 3. Shares Table
        $table_shares = $wpdb->prefix . 'vicoba_shares';
        $sql_shares = "CREATE TABLE $table_shares (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            meeting_id bigint(20) DEFAULT 0,
            share_count int(11) NOT NULL DEFAULT 1,
            unit_price decimal(12,2) NOT NULL,
            total_amount decimal(12,2) NOT NULL,
            payment_date date NOT NULL,
            recorded_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_shares);

        // 4. Loans Table
        $table_loans = $wpdb->prefix . 'vicoba_loans';
        $sql_loans = "CREATE TABLE $table_loans (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            loan_code varchar(40) NOT NULL,
            principal_amount decimal(12,2) NOT NULL,
            interest_rate decimal(5,2) NOT NULL,
            interest_type varchar(20) NOT NULL DEFAULT 'reducing',
            interest_amount decimal(12,2) NOT NULL DEFAULT '0.00',
            total_amount decimal(12,2) NOT NULL,
            balance_remaining decimal(12,2) NOT NULL,
            repayment_period_months int(11) NOT NULL DEFAULT 3,
            monthly_installment decimal(12,2) NOT NULL,
            purpose text DEFAULT NULL,
            status varchar(30) NOT NULL DEFAULT 'pending_guarantors',
            application_date date NOT NULL,
            approved_at datetime DEFAULT NULL,
            disbursed_at datetime DEFAULT NULL,
            due_date date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_loans);

        // 5. Loan Repayments Table
        $table_repayments = $wpdb->prefix . 'vicoba_loan_repayments';
        $sql_repayments = "CREATE TABLE $table_repayments (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            loan_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            meeting_id bigint(20) DEFAULT 0,
            amount_paid decimal(12,2) NOT NULL,
            principal_paid decimal(12,2) NOT NULL DEFAULT '0.00',
            interest_paid decimal(12,2) NOT NULL DEFAULT '0.00',
            penalty_paid decimal(12,2) NOT NULL DEFAULT '0.00',
            payment_date date NOT NULL,
            payment_method varchar(30) NOT NULL DEFAULT 'cash',
            transaction_ref varchar(100) DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'verified',
            verified_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY loan_id (loan_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_repayments);

        // 6. Guarantors Table
        $table_guarantors = $wpdb->prefix . 'vicoba_guarantors';
        $sql_guarantors = "CREATE TABLE $table_guarantors (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            loan_id bigint(20) NOT NULL,
            guarantor_member_id bigint(20) NOT NULL,
            amount_guaranteed decimal(12,2) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            response_date datetime DEFAULT NULL,
            comments text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY loan_id (loan_id),
            KEY guarantor_member_id (guarantor_member_id)
        ) $charset_collate;";
        dbDelta($sql_guarantors);

        // 7. Fine Types Table
        $table_fine_types = $wpdb->prefix . 'vicoba_fine_types';
        $sql_fine_types = "CREATE TABLE $table_fine_types (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            name varchar(100) NOT NULL,
            default_amount decimal(12,2) NOT NULL DEFAULT '1000.00',
            description text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id)
        ) $charset_collate;";
        dbDelta($sql_fine_types);

        // 8. Fines Table
        $table_fines = $wpdb->prefix . 'vicoba_fines';
        $sql_fines = "CREATE TABLE $table_fines (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            meeting_id bigint(20) DEFAULT 0,
            fine_type_id bigint(20) DEFAULT 0,
            amount decimal(12,2) NOT NULL,
            reason varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'unpaid',
            paid_at datetime DEFAULT NULL,
            recorded_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_fines);

        // 9. Meetings Table
        $table_meetings = $wpdb->prefix . 'vicoba_meetings';
        $sql_meetings = "CREATE TABLE $table_meetings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            meeting_number int(11) NOT NULL,
            meeting_date date NOT NULL,
            location varchar(150) DEFAULT '',
            agenda longtext DEFAULT NULL,
            minutes longtext DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'scheduled',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id)
        ) $charset_collate;";
        dbDelta($sql_meetings);

        // 10. Attendance Table
        $table_attendance = $wpdb->prefix . 'vicoba_attendance';
        $sql_attendance = "CREATE TABLE $table_attendance (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            meeting_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'present',
            fine_applied tinyint(1) NOT NULL DEFAULT 0,
            recorded_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY meeting_id (meeting_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_attendance);

        // 11. Social Fund Table
        $table_social = $wpdb->prefix . 'vicoba_social_fund';
        $sql_social = "CREATE TABLE $table_social (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            meeting_id bigint(20) DEFAULT 0,
            transaction_type varchar(20) NOT NULL DEFAULT 'contribution',
            amount decimal(12,2) NOT NULL,
            category varchar(50) DEFAULT 'general',
            description text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'approved',
            approved_by bigint(20) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_social);

        // 12. Financial Ledger Table (Single Source of Truth)
        $table_ledger = $wpdb->prefix . 'vicoba_transactions';
        $sql_ledger = "CREATE TABLE $table_ledger (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            member_id bigint(20) DEFAULT 0,
            meeting_id bigint(20) DEFAULT 0,
            transaction_code varchar(50) NOT NULL,
            type varchar(40) NOT NULL,
            amount decimal(12,2) NOT NULL,
            payment_method varchar(30) NOT NULL DEFAULT 'cash',
            description text NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_ledger);

        // 13. Shareouts Table
        $table_shareouts = $wpdb->prefix . 'vicoba_shareouts';
        $sql_shareouts = "CREATE TABLE $table_shareouts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            shareout_date date NOT NULL,
            total_shares_pool decimal(12,2) NOT NULL DEFAULT '0.00',
            total_interest_profit decimal(12,2) NOT NULL DEFAULT '0.00',
            total_fines_collected decimal(12,2) NOT NULL DEFAULT '0.00',
            total_expenses decimal(12,2) NOT NULL DEFAULT '0.00',
            total_bad_debt decimal(12,2) NOT NULL DEFAULT '0.00',
            net_distributable decimal(12,2) NOT NULL DEFAULT '0.00',
            status varchar(20) NOT NULL DEFAULT 'draft',
            finalized_by bigint(20) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id)
        ) $charset_collate;";
        dbDelta($sql_shareouts);

        // 14. Shareout Details Table
        $table_shareout_details = $wpdb->prefix . 'vicoba_shareout_details';
        $sql_shareout_details = "CREATE TABLE $table_shareout_details (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            shareout_id bigint(20) NOT NULL,
            member_id bigint(20) NOT NULL,
            total_member_shares int(11) NOT NULL DEFAULT 0,
            share_ratio decimal(8,6) NOT NULL DEFAULT '0.000000',
            gross_payout decimal(12,2) NOT NULL DEFAULT '0.00',
            active_loan_deduction decimal(12,2) NOT NULL DEFAULT '0.00',
            net_payout decimal(12,2) NOT NULL DEFAULT '0.00',
            payment_status varchar(20) NOT NULL DEFAULT 'unpaid',
            paid_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY shareout_id (shareout_id),
            KEY member_id (member_id)
        ) $charset_collate;";
        dbDelta($sql_shareout_details);

        // 15. Notifications Table
        $table_notifications = $wpdb->prefix . 'vicoba_notifications';
        $sql_notifications = "CREATE TABLE $table_notifications (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            title varchar(150) NOT NULL,
            message text NOT NULL,
            type varchar(30) DEFAULT 'system',
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_notifications);

        // 16. Audit Log Table
        $table_audit = $wpdb->prefix . 'vicoba_audit_log';
        $sql_audit = "CREATE TABLE $table_audit (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) DEFAULT 0,
            user_id bigint(20) NOT NULL,
            action varchar(100) NOT NULL,
            target_type varchar(50) DEFAULT '',
            target_id bigint(20) DEFAULT 0,
            old_value longtext DEFAULT NULL,
            new_value longtext DEFAULT NULL,
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY group_id (group_id)
        ) $charset_collate;";
        dbDelta($sql_audit);
    }
}
