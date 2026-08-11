<?php
/**
 * VICOBA WP Cron Jobs
 * - Overdue loan detection & penalty interest
 * - Meeting reminders (SMS)
 * - Auto-backup hooks
 */

if (!defined('ABSPATH')) exit;

class VICOBA_Cron {

    public static function init() {
        // Register custom cron intervals
        add_filter('cron_schedules', [__CLASS__, 'add_schedules']);

        // Schedule daily overdue check if not already scheduled
        if (!wp_next_scheduled('vicoba_daily_overdue_check')) {
            wp_schedule_event(time(), 'daily', 'vicoba_daily_overdue_check');
        }

        // Schedule weekly meeting reminder
        if (!wp_next_scheduled('vicoba_weekly_meeting_reminder')) {
            wp_schedule_event(time(), 'weekly', 'vicoba_weekly_meeting_reminder');
        }

        // Hook handlers
        add_action('vicoba_daily_overdue_check',     [__CLASS__, 'check_overdue_loans']);
        add_action('vicoba_weekly_meeting_reminder', [__CLASS__, 'send_meeting_reminders']);
    }

    public static function add_schedules($schedules) {
        $schedules['weekly'] = [
            'interval' => 604800,
            'display'  => __('Once a week', 'vicoba-manager'),
        ];
        return $schedules;
    }

    /**
     * Daily: Mark overdue loans, apply penalty interest
     */
    public static function check_overdue_loans() {
        global $wpdb;
        $table_l = $wpdb->prefix . 'vicoba_loans';
        $table_r = $wpdb->prefix . 'vicoba_loan_repayments';
        $table_g = $wpdb->prefix . 'vicoba_groups';

        // Find all active loans past their due_date
        $overdue_loans = $wpdb->get_results(
            "SELECT l.*, g.interest_rate FROM $table_l l 
             JOIN $table_g g ON l.group_id = g.id
             WHERE l.status = 'active' 
               AND l.due_date IS NOT NULL 
               AND l.due_date < CURDATE() 
               AND l.balance_remaining > 0"
        );

        foreach ($overdue_loans as $loan) {
            // Update status to overdue
            $wpdb->update($table_l, ['status' => 'overdue'], ['id' => $loan->id]);

            // Apply 1.5x penalty interest on balance (per month overdue)
            $penalty_rate    = (floatval($loan->interest_rate) / 100) * 1.5;
            $penalty_amount  = floatval($loan->balance_remaining) * $penalty_rate;
            $penalty_amount  = round($penalty_amount, 2);

            if ($penalty_amount > 0) {
                // Add penalty to balance
                $new_balance = floatval($loan->balance_remaining) + $penalty_amount;
                $wpdb->update($table_l, ['balance_remaining' => $new_balance], ['id' => $loan->id]);

                // Log in audit
                VICOBA_Audit::log(
                    'penalty_interest_applied',
                    $loan->group_id,
                    'loan',
                    $loan->id,
                    $loan->balance_remaining,
                    $new_balance
                );

                // Notify the member
                $member = VICOBA_Members::get_member($loan->member_id);
                if ($member && $member->user_id) {
                    VICOBA_Notifications::create_notification(
                        $loan->group_id,
                        $member->user_id,
                        'Mkopo Umechelewa!',
                        sprintf(
                            'Mkopo wako %s umechelewa. Riba ya adhabu ya TZS %s imeongezwa. Salio jipya: TZS %s. Tafadhali lipa haraka.',
                            $loan->loan_code,
                            number_format($penalty_amount),
                            number_format($new_balance)
                        ),
                        'overdue_loan'
                    );

                    // SMS reminder
                    if (!empty($member->phone)) {
                        VICOBA_Notifications::send_sms(
                            $member->phone,
                            sprintf(
                                'VICOBA: Mkopo %s umechelewa. Riba ya adhabu TZS %s imeongezwa. Salio: TZS %s. Wasiliana na kikundi.',
                                $loan->loan_code,
                                number_format($penalty_amount),
                                number_format($new_balance)
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Weekly: Send meeting reminders to all active members
     */
    public static function send_meeting_reminders() {
        global $wpdb;
        $table_m = $wpdb->prefix . 'vicoba_meetings';
        $table_grp = $wpdb->prefix . 'vicoba_groups';

        // Upcoming meetings in next 7 days
        $upcoming = $wpdb->get_results(
            "SELECT mt.*, g.name as group_name FROM $table_m mt
             JOIN $table_grp g ON mt.group_id = g.id
             WHERE mt.meeting_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
               AND mt.status = 'scheduled'"
        );

        foreach ($upcoming as $mtg) {
            $members = VICOBA_Members::get_members_by_group($mtg->group_id, 'active');
            foreach ($members as $mem) {
                VICOBA_Notifications::create_notification(
                    $mtg->group_id,
                    $mem->user_id,
                    'Ukumbusho wa Mkutano',
                    sprintf(
                        'Kuna mkutano wa %s tarehe %s. Mkoa: %s. Tafadhali uhudhurie.',
                        $mtg->group_name,
                        date('d/m/Y', strtotime($mtg->meeting_date)),
                        $mtg->location ?? 'TBD'
                    ),
                    'meeting_reminder'
                );

                if (!empty($mem->phone)) {
                    VICOBA_Notifications::send_sms(
                        $mem->phone,
                        sprintf(
                            'VICOBA UKUMBUSHO: Mkutano wa %s - Tarehe %s. Mahali: %s. Kumbuka kulipa hisa.',
                            $mtg->group_name,
                            date('d/m/Y', strtotime($mtg->meeting_date)),
                            $mtg->location ?? 'TBA'
                        )
                    );
                }
            }
        }
    }

    /**
     * Cleanup: Deactivate all scheduled events on theme deactivation
     */
    public static function deactivate() {
        wp_clear_scheduled_hook('vicoba_daily_overdue_check');
        wp_clear_scheduled_hook('vicoba_weekly_meeting_reminder');
    }
}
