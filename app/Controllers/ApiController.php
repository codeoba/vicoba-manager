<?php
/**
 * VICOBA API Controller
 * Handles all REST API endpoints — replaces WordPress REST API
 * All responses are JSON. All writes require CSRF token (X-CSRF-Token header).
 */

class ApiController
{
    private function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    private function requireAuth(): object
    {
        if (!Auth::check()) {
            $this->json(['success' => false, 'message' => 'Tafadhali ingia kwanza.'], 401);
        }
        $user = Auth::user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Mtumiaji hakupatikana.'], 401);
        }
        return $user;
    }

    private function requireRole(string ...$roles): object
    {
        $user = $this->requireAuth();
        if (!in_array($user->role, $roles, true)) {
            $this->json(['success' => false, 'message' => 'Huna ruhusa ya kitendo hiki.'], 403);
        }
        return $user;
    }

    private function body(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function verifyCsrf(): void
    {
        if (!Auth::verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'CSRF token batili.'], 403);
        }
    }

    private function groupId(object $user): int
    {
        return (int) ($user->group_id ?? Auth::groupId());
    }

    // ════════════════════════════════════════════════════════════
    // MEMBERS
    // ════════════════════════════════════════════════════════════

    public function getMembers(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $members  = Models\Members::getByGroup($group_id);
        $this->json(['success' => true, 'members' => $members]);
    }

    public function addMember(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary');
        $body = $this->body();
        $this->verifyCsrf();

        $group_id = $this->groupId($user);
        $result   = Models\Members::create($group_id, $body, $user->id);

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 422);
        }
        $this->json(['success' => true, 'message' => 'Mwanachama ameongezwa!', 'member_id' => $result]);
    }

    public function updateMember(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Members::update((int)($body['member_id'] ?? 0), $body);
        $this->json(['success' => (bool)$result, 'message' => 'Taarifa zimesasishwa!']);
    }

    public function updateMemberStatus(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Members::updateStatus((int)($body['member_id'] ?? 0), $body['status'] ?? '');
        $this->json(['success' => (bool)$result, 'message' => 'Hali ya mwanachama imebadilishwa!']);
    }

    public function updateMemberRole(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin');
        $body = $this->body();
        $this->verifyCsrf();

        $allowed = ['member','treasurer','secretary','group_admin','super_admin'];
        if (!in_array($body['role'] ?? '', $allowed)) {
            $this->json(['success' => false, 'message' => 'Jukumu halilo halali.'], 422);
        }
        $result = Models\Members::updateRole((int)($body['member_id'] ?? 0), $body['role']);
        $this->json(['success' => (bool)$result, 'message' => 'Jukumu limebadilishwa!']);
    }

    // ════════════════════════════════════════════════════════════
    // SHARES
    // ════════════════════════════════════════════════════════════

    public function getShares(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $data     = Models\Shares::getSummary($group_id);
        $this->json(['success' => true, ...$data]);
    }

    public function recordShares(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary','treasurer');
        $body = $this->body();
        $this->verifyCsrf();

        $group_id = $this->groupId($user);
        $result   = Models\Shares::record($group_id, $body, $user->id);

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 422);
        }
        $this->json(['success' => true, 'message' => 'Hisa zimerekodiwa!', 'share_id' => $result]);
    }

    // ════════════════════════════════════════════════════════════
    // LOANS
    // ════════════════════════════════════════════════════════════

    public function getLoans(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $loans    = Models\Loans::getByGroup($group_id);
        $this->json(['success' => true, 'loans' => $loans]);
    }

    public function applyLoan(array $p = []): never
    {
        $user = $this->requireAuth();
        $body = $this->body();
        $this->verifyCsrf();

        $group_id = $this->groupId($user);
        $result   = Models\Loans::apply($group_id, $body, $user->id);

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 422);
        }
        $this->json(['success' => true, 'message' => 'Ombi la mkopo limewasilishwa!', 'loan_id' => $result]);
    }

    public function guarantorRespond(array $p = []): never
    {
        $user = $this->requireAuth();
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Loans::guarantorRespond((int)($body['loan_id'] ?? 0), $user->id, $body['response'] ?? '');
        $this->json(['success' => (bool)$result, 'message' => 'Jibu la mdhamini limehifadhiwa!']);
    }

    public function disburseLoan(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Loans::disburse((int)($body['loan_id'] ?? 0), $body, $user->id);
        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 422);
        }
        $this->json(['success' => true, 'message' => 'Mkopo umetolewa!']);
    }

    public function repayLoan(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Loans::repay((int)($body['loan_id'] ?? 0), $body, $user->id);
        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 422);
        }
        $this->json(['success' => true, 'message' => 'Rejesho limerekodiwa!']);
    }

    public function getLoanSchedule(array $p = []): never
    {
        $this->requireAuth();
        $loan_id  = (int)get_param('loan_id', 0);
        $loan     = Database::get('SELECT * FROM ' . Database::t('loans') . ' WHERE id = ?', [$loan_id]);
        if (!$loan) $this->json(['success' => false, 'message' => 'Mkopo haukupatikana.'], 404);

        $schedule = calculate_loan_schedule(
            (float)$loan->principal_amount,
            (float)$loan->interest_rate,
            $loan->interest_type,
            (int)$loan->repayment_period_months
        );
        $this->json(['success' => true, 'loan_code' => $loan->loan_code, ...$schedule]);
    }

    // ════════════════════════════════════════════════════════════
    // FINES
    // ════════════════════════════════════════════════════════════

    public function getFines(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $fines    = Models\Fines::getByGroup($group_id);
        $this->json(['success' => true, 'fines' => $fines]);
    }

    public function issueFine(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary','treasurer');
        $body = $this->body();
        $this->verifyCsrf();

        $group_id = $this->groupId($user);
        $result   = Models\Fines::issue($group_id, $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Faini imetolewa!']);
    }

    public function payFine(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Fines::pay((int)($body['fine_id'] ?? 0), $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Faini imelipwa!']);
    }

    // ════════════════════════════════════════════════════════════
    // MEETINGS
    // ════════════════════════════════════════════════════════════

    public function getMeetings(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $meetings = Models\Meetings::getByGroup($group_id);
        $this->json(['success' => true, 'meetings' => $meetings]);
    }

    public function createMeeting(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary');
        $body = $this->body();
        $this->verifyCsrf();

        $group_id = $this->groupId($user);
        $result   = Models\Meetings::create($group_id, $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Mkutano umeundwa!', 'meeting_id' => $result]);
    }

    public function recordAttendance(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Meetings::recordAttendance($body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Mahudhurio yamerekodiwa!']);
    }

    public function updateMinutes(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','secretary');
        $body = $this->body();
        $this->verifyCsrf();

        $result = Models\Meetings::updateMinutes((int)($body['meeting_id'] ?? 0), $body['minutes'] ?? '');
        $this->json(['success' => (bool)$result, 'message' => 'Muhtasari umehifadhiwa!']);
    }

    // ════════════════════════════════════════════════════════════
    // SOCIAL FUND
    // ════════════════════════════════════════════════════════════

    public function getSocialFund(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $data     = Models\SocialFund::getSummary($group_id);
        $this->json(['success' => true, ...$data]);
    }

    public function socialContribute(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();
        $group_id = $this->groupId($user);
        $result   = Models\SocialFund::contribute($group_id, $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Mchango umerekodiwa!']);
    }

    public function socialRequest(array $p = []): never
    {
        $user = $this->requireAuth();
        $body = $this->body();
        $this->verifyCsrf();
        $group_id = $this->groupId($user);
        $result   = Models\SocialFund::request($group_id, $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Ombi limewasilishwa!']);
    }

    public function socialApprove(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();
        $result = Models\SocialFund::approve((int)($body['request_id'] ?? 0), $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Ombi limeidhinishwa!']);
    }

    // ════════════════════════════════════════════════════════════
    // LEDGER
    // ════════════════════════════════════════════════════════════

    public function addExpense(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();
        $group_id = $this->groupId($user);
        $result = Models\Ledger::addExpense($group_id, $body, $user->id);
        $this->json(['success' => (bool)$result, 'message' => 'Gharama imerekodiwa!']);
    }

    // ════════════════════════════════════════════════════════════
    // SHAREOUT
    // ════════════════════════════════════════════════════════════

    public function finalizeShareout(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin','treasurer');
        $body = $this->body();
        $this->verifyCsrf();
        $group_id = $this->groupId($user);
        $result = Models\Shareout::finalize($group_id, $body, $user->id);
        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 422);
        }
        $this->json(['success' => true, 'message' => 'Mgawanyo umekamilika!', 'shareout_id' => $result]);
    }

    // ════════════════════════════════════════════════════════════
    // SETTINGS
    // ════════════════════════════════════════════════════════════

    public function updateSettings(array $p = []): never
    {
        $user = $this->requireRole('super_admin','group_admin');
        $body = $this->body();
        $this->verifyCsrf();
        $group_id = $this->groupId($user);

        $allowed_fields = [
            'name','share_price','loan_interest_rate','loan_interest_type',
            'max_loan_multiplier','max_loan_period','social_fund_per_meeting',
            'currency','cycle_start','cycle_end',
        ];
        $update = [];
        foreach ($allowed_fields as $f) {
            if (isset($body[$f])) $update[$f] = $body[$f];
        }

        $result = Database::update('groups', $update, ['id' => $group_id]);
        $this->json(['success' => (bool)$result, 'message' => 'Mipangilio imehifadhiwa!']);
    }

    // ════════════════════════════════════════════════════════════
    // NOTIFICATIONS
    // ════════════════════════════════════════════════════════════

    public function getNotifications(array $p = []): never
    {
        $user  = $this->requireAuth();
        $notifs = Database::all(
            'SELECT * FROM ' . Database::t('notifications') . ' WHERE user_id = ? ORDER BY created_at DESC LIMIT 20',
            [$user->id]
        );
        $this->json(['success' => true, 'notifications' => $notifs]);
    }

    public function markNotificationRead(array $p = []): never
    {
        $user = $this->requireAuth();
        $body = $this->body();
        $result = Database::update('notifications', ['is_read' => 1], ['id' => (int)($body['notification_id'] ?? 0), 'user_id' => $user->id]);
        $this->json(['success' => (bool)$result, 'message' => 'Arifa imesomwa.']);
    }

    // ════════════════════════════════════════════════════════════
    // REPORTS & EXPORT
    // ════════════════════════════════════════════════════════════

    public function getReportsSummary(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = $this->groupId($user);
        $summary  = Models\Reports::getSummary($group_id, get_param('from'), get_param('to'));
        $this->json(['success' => true, ...$summary]);
    }

    public function exportLedgerCsv(array $p = []): never
    {
        $user     = $this->requireAuth();
        $group_id = (int)get_param('group_id', $this->groupId($user));
        if (!$group_id) {
            $group_id = (int)Database::scalar('SELECT id FROM ' . Database::t('groups') . ' ORDER BY id ASC LIMIT 1');
        }
        $from = get_param('from', '');
        $to   = get_param('to', '');
        Models\Export::streamLedgerCsv($group_id, $from ?: null, $to ?: null);
    }

    public function exportStatementCsv(array $p = []): never
    {
        $user      = $this->requireAuth();
        $group_id  = (int)get_param('group_id', $this->groupId($user));
        $member_id = (int)get_param('member_id', $user->member_id ?? 0);
        if (!$member_id) {
            $member_id = (int)Database::scalar('SELECT id FROM ' . Database::t('members') . ' WHERE group_id = ? LIMIT 1', [$group_id]);
        }
        Models\Export::streamStatementCsv($group_id, $member_id);
    }

    public function exportShareoutCsv(array $p = []): never
    {
        $this->requireAuth();
        $shareout_id = (int)get_param('shareout_id', 0);
        if (!$shareout_id) $this->json(['success' => false, 'message' => 'shareout_id inahitajika.'], 400);
        Models\Export::streamShareoutCsv($shareout_id);
    }

    // ════════════════════════════════════════════════════════════
    // SUPER ADMIN
    // ════════════════════════════════════════════════════════════

    public function superAdminGetGroups(array $p = []): never
    {
        $this->requireRole('super_admin');
        $groups = Database::all(
            'SELECT g.*, COUNT(m.id) as member_count FROM ' . Database::t('groups') . ' g
             LEFT JOIN ' . Database::t('members') . ' m ON m.group_id = g.id
             GROUP BY g.id ORDER BY g.created_at DESC'
        );
        $this->json(['success' => true, 'groups' => $groups]);
    }

    public function superAdminGroupStatus(array $p = []): never
    {
        $this->requireRole('super_admin');
        $body   = $this->body();
        $this->verifyCsrf();
        $result = Database::update('groups', ['status' => $body['status'] ?? 'active'], ['id' => (int)($body['group_id'] ?? 0)]);
        $this->json(['success' => (bool)$result, 'message' => 'Hali ya kikundi imebadilishwa!']);
    }

    public function superAdminCreateGroup(array $p = []): never
    {
        $this->requireRole('super_admin');
        $body = $this->body();
        $this->verifyCsrf();

        if (empty($body['name'])) $this->json(['success' => false, 'message' => 'Jina la kikundi linahitajika.'], 422);

        $group_id = Database::insert('groups', [
            'name'     => sanitize($body['name']),
            'region'   => sanitize($body['region'] ?? ''),
            'district' => sanitize($body['district'] ?? ''),
            'currency' => sanitize($body['currency'] ?? 'TZS'),
            'status'   => 'active',
        ]);

        $this->json(['success' => true, 'message' => 'Kikundi kimeundwa!', 'group_id' => $group_id]);
    }

    // ════════════════════════════════════════════════════════════
    // AUTH VIA API (for mobile / SPA)
    // ════════════════════════════════════════════════════════════

    public function login(array $p = []): never
    {
        $body = $this->body();
        $user = Auth::attempt($body['username'] ?? '', $body['password'] ?? '');
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Taarifa za kuingia si sahihi.'], 401);
        }
        Auth::login($user);
        $this->json(['success' => true, 'message' => 'Umeingia!', 'role' => $user->role, 'group_id' => $user->group_id]);
    }

    public function registerGroup(array $p = []): never
    {
        $body = $this->body();
        // Delegate to AuthController logic
        $ctrl = new AuthController();
        $_POST = $body;
        $ctrl->registerPost([]);
    }
}
