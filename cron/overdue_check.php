#!/usr/bin/env php
<?php
/**
 * VICOBA Cron Job — Overdue Loan Detection
 * Schedule this in system crontab:
 *   0 2 * * * /usr/bin/php /www/wwwroot/vikoba.mdandu.com/cron/overdue_check.php >> /var/log/vicoba_cron.log 2>&1
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require APP_PATH . '/helpers.php';
require APP_PATH . '/Core/Database.php';
require APP_PATH . '/Core/Auth.php';
require APP_PATH . '/Core/Response.php';
require APP_PATH . '/Core/Router.php';
foreach (glob(APP_PATH . '/Models/*.php') as $m) require $m;

echo "[" . date('Y-m-d H:i:s') . "] VICOBA Cron: Starting overdue check...\n";

date_default_timezone_set(config('app.timezone', 'Africa/Dar_es_Salaam'));

$loans = Database::all(
    "SELECT l.*, g.penalty_interest_multiplier, g.name as group_name,
            m.full_name as member_name, m.user_id as member_user_id
     FROM " . Database::t('loans') . " l
     JOIN " . Database::t('groups') . " g ON g.id = l.group_id
     JOIN " . Database::t('members') . " m ON m.id = l.member_id
     WHERE l.status IN ('active','overdue') AND l.due_date < CURDATE() AND l.balance_remaining > 0"
);

$marked_overdue = 0;
$penalty_applied = 0;

foreach ($loans as $loan) {
    // Mark as overdue
    if ($loan->status === 'active') {
        Database::update('loans', ['status' => 'overdue'], ['id' => $loan->id]);
        $marked_overdue++;
        echo "  ⚠️  Loan {$loan->loan_code} ({$loan->member_name}) marked OVERDUE\n";

        // Notify member
        Models\Notifications::create(
            $loan->group_id,
            $loan->member_user_id,
            '⚠️ Mkopo Umechelewa!',
            "Mkopo {$loan->loan_code} wa TZS " . number_format($loan->principal_amount) . " umefika tarehe. Tafadhali lipa haraka ili kuepuka riba ya ziada.",
            'loan_overdue'
        );
    }

    // Apply monthly penalty interest if it's been > 30 days overdue
    $days_overdue = days_ago($loan->due_date);
    if ($days_overdue > 30) {
        $multiplier    = (float)$loan->penalty_interest_multiplier ?: 1.5;
        $monthly_rate  = ((float)$loan->interest_rate / 100) * $multiplier / 12;
        $penalty       = (float)$loan->balance_remaining * $monthly_rate;

        if ($penalty > 0) {
            Database::update('loans', [
                'balance_remaining' => (float)$loan->balance_remaining + $penalty,
                'penalty_applied'   => (float)$loan->penalty_applied + $penalty,
            ], ['id' => $loan->id]);

            Models\Ledger::record($loan->group_id, $loan->member_id, 'penalty_interest', $penalty, 'cash', "Riba ya adhabu: {$loan->loan_code}", 0);
            $penalty_applied++;
            echo "  💰 Penalty TZS " . number_format($penalty) . " applied to {$loan->loan_code}\n";
        }
    }
}

// Meeting reminders — notify groups with meetings in next 2 days
$upcoming = Database::all(
    "SELECT mt.*, g.name as group_name, u.id as user_id
     FROM " . Database::t('meetings') . " mt
     JOIN " . Database::t('groups') . " g ON g.id = mt.group_id
     JOIN " . Database::t('members') . " mem ON mem.group_id = g.id AND mem.status = 'active'
     JOIN " . Database::t('users') . " u ON u.id = mem.user_id
     WHERE mt.status = 'scheduled' AND mt.meeting_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)"
);

$reminded = [];
foreach ($upcoming as $mt) {
    $key = $mt->id . '_' . $mt->user_id;
    if (isset($reminded[$key])) continue;
    $reminded[$key] = true;

    Models\Notifications::create(
        $mt->group_id,
        $mt->user_id,
        '📅 Kumbukumbu ya Mkutano',
        "Mkutano wa '{$mt->group_name}' utafanyika tarehe " . format_date($mt->meeting_date) . ". Tafadhali hudhuria.",
        'meeting_reminder'
    );
}

echo "[" . date('Y-m-d H:i:s') . "] Done: $marked_overdue marked overdue, $penalty_applied penalties applied, " . count($reminded) . " meeting reminders sent.\n";
