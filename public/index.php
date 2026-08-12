<?php
/**
 * VICOBA — Front Controller
 * All HTTP requests go through this single file
 * Nothing else is publicly accessible except /assets/
 */

declare(strict_types=1);

// ── Constants ─────────────────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('VIEW_PATH',    APP_PATH  . '/Views');

// ── Autoload Core Classes ──────────────────────────────────────────────────────
require APP_PATH . '/helpers.php';
require APP_PATH . '/Core/Database.php';
require APP_PATH . '/Core/Auth.php';
require APP_PATH . '/Core/Router.php';
require APP_PATH . '/Core/Response.php';

// ── Autoload Services & Models ──────────────────────────────────────────────────
foreach (glob(APP_PATH . '/Services/*.php') as $svc) {
    require $svc;
}
foreach (glob(APP_PATH . '/Models/*.php') as $model) {
    require $model;
}

// ── Autoload Controllers ────────────────────────────────────────────────────────
foreach (glob(APP_PATH . '/Controllers/*.php') as $ctrl) {
    require $ctrl;
}

// ── Bootstrap ──────────────────────────────────────────────────────────────────
date_default_timezone_set(config('app.timezone', 'Africa/Dar_es_Salaam'));

if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Start session
Auth::start();

// ── Define Routes ─────────────────────────────────────────────────────────────

// --- Auth ---
Router::get('/login',    [AuthController::class, 'loginPage']);
Router::post('/login',   [AuthController::class, 'loginPost']);
Router::get('/logout',   [AuthController::class, 'logout']);
Router::get('/register', [AuthController::class, 'registerPage']);
Router::post('/register',[AuthController::class, 'registerPost']);

// --- Dashboard (SPA-style, sidebar loads sub-views) ---
Router::get('/',                        [DashboardController::class, 'index']);
Router::get('/dashboard',               [DashboardController::class, 'index']);
Router::get('/dashboard/{view}',        [DashboardController::class, 'show']);

// --- REST API: Auth ---
Router::post('/api/auth/login',         [ApiController::class, 'login']);
Router::post('/api/auth/register-group',[ApiController::class, 'registerGroup']);

// --- REST API: Members ---
Router::get ('/api/members',            [ApiController::class, 'getMembers']);
Router::post('/api/members/add',        [ApiController::class, 'addMember']);
Router::post('/api/members/update',     [ApiController::class, 'updateMember']);
Router::post('/api/members/update-status',[ApiController::class, 'updateMemberStatus']);
Router::post('/api/members/update-role', [ApiController::class, 'updateMemberRole']);

// --- REST API: Shares ---
Router::get ('/api/shares',             [ApiController::class, 'getShares']);
Router::post('/api/shares/record',      [ApiController::class, 'recordShares']);

// --- REST API: Loans ---
Router::get ('/api/loans',              [ApiController::class, 'getLoans']);
Router::post('/api/loans/apply',        [ApiController::class, 'applyLoan']);
Router::post('/api/loans/guarantor-respond', [ApiController::class, 'guarantorRespond']);
Router::post('/api/loans/disburse',     [ApiController::class, 'disburseLoan']);
Router::post('/api/loans/repay',        [ApiController::class, 'repayLoan']);
Router::get ('/api/loans/schedule',     [ApiController::class, 'getLoanSchedule']);

// --- REST API: Fines ---
Router::get ('/api/fines',              [ApiController::class, 'getFines']);
Router::post('/api/fines/issue',        [ApiController::class, 'issueFine']);
Router::post('/api/fines/pay',          [ApiController::class, 'payFine']);

// --- REST API: Meetings ---
Router::get ('/api/meetings',           [ApiController::class, 'getMeetings']);
Router::post('/api/meetings/create',    [ApiController::class, 'createMeeting']);
Router::post('/api/meetings/attendance',[ApiController::class, 'recordAttendance']);
Router::post('/api/meetings/update-minutes',[ApiController::class, 'updateMinutes']);

// --- REST API: Social Fund ---
Router::get ('/api/social-fund',        [ApiController::class, 'getSocialFund']);
Router::post('/api/social-fund/contribute', [ApiController::class, 'socialContribute']);
Router::post('/api/social-fund/request',    [ApiController::class, 'socialRequest']);
Router::post('/api/social-fund/approve',    [ApiController::class, 'socialApprove']);

// --- REST API: Ledger ---
Router::post('/api/ledger/expense',     [ApiController::class, 'addExpense']);

// --- REST API: Share-Out ---
Router::post('/api/shareout/finalize',  [ApiController::class, 'finalizeShareout']);

// --- REST API: Settings ---
Router::post('/api/settings/update',    [ApiController::class, 'updateSettings']);

// --- REST API: Notifications ---
Router::get ('/api/notifications',      [ApiController::class, 'getNotifications']);
Router::post('/api/notifications/mark-read', [ApiController::class, 'markNotificationRead']);

// --- REST API: Reports & Export ---
Router::get ('/api/reports/summary',    [ApiController::class, 'getReportsSummary']);
Router::get ('/export/ledger-csv',      [ApiController::class, 'exportLedgerCsv']);
Router::get ('/export/statement-csv',   [ApiController::class, 'exportStatementCsv']);
Router::get ('/export/statement-pdf',   [ApiController::class, 'exportStatementPdf']);
Router::get ('/export/annual-report-pdf',[ApiController::class, 'exportAnnualReportPdf']);
Router::get ('/export/shareout-csv',    [ApiController::class, 'exportShareoutCsv']);
Router::post('/api/loans/send-otp',     [ApiController::class, 'sendDisbursementOtp']);
Router::post('/api/loans/verify-otp',   [ApiController::class, 'verifyDisbursementOtp']);

// --- REST API: Super Admin ---
Router::get ('/api/superadmin/groups',         [ApiController::class, 'superAdminGetGroups']);
Router::post('/api/superadmin/group-status',   [ApiController::class, 'superAdminGroupStatus']);
Router::post('/api/superadmin/create-group',   [ApiController::class, 'superAdminCreateGroup']);
Router::post('/api/superadmin/reset-password', [ApiController::class, 'superAdminResetPassword']);

// ── Dispatch ───────────────────────────────────────────────────────────────────
Router::dispatch();
