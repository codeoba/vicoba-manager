<?php namespace Models;

class Fines {
    public static function getByGroup(int $group_id): array {
        return \Database::all(
            'SELECT f.*, m.full_name as member_name, m.member_number, ft.name as fine_type_name
             FROM ' . \Database::t('fines') . ' f
             JOIN ' . \Database::t('members') . ' m ON m.id = f.member_id
             LEFT JOIN ' . \Database::t('fine_types') . ' ft ON ft.id = f.fine_type_id
             WHERE f.group_id = ? ORDER BY f.created_at DESC',
            [$group_id]
        );
    }
    public static function getTypes(int $group_id): array {
        return \Database::all('SELECT * FROM ' . \Database::t('fine_types') . ' WHERE group_id = ? AND is_active = 1', [$group_id]);
    }
    public static function issue(int $group_id, array $data, int $by): int {
        $member = \Database::get('SELECT * FROM ' . \Database::t('members') . ' WHERE id = ? AND group_id = ?', [(int)($data['member_id'] ?? 0), $group_id]);
        if (!$member) return 0;
        $amount = !empty($data['fine_type_id'])
            ? (float)\Database::scalar('SELECT amount FROM ' . \Database::t('fine_types') . ' WHERE id=?', [(int)$data['fine_type_id']])
            : (float)($data['amount'] ?? 0);
        if ($amount <= 0) return 0;
        $id = \Database::insert('fines', [
            'group_id'    => $group_id,
            'member_id'   => $member->id,
            'fine_type_id'=> $data['fine_type_id'] ?? null,
            'meeting_id'  => $data['meeting_id'] ?? null,
            'amount'      => $amount,
            'reason'      => \sanitize($data['reason'] ?? 'Faini'),
            'status'      => 'pending',
            'issued_by'   => $by,
        ]);
        Notifications::create($group_id, $member->user_id, 'Faini Imetolewa', "Umepewa faini ya TZS " . number_format($amount) . ": " . ($data['reason'] ?? ''), 'fine_issued');
        Audit::log($group_id, $by, 'fine_issued', 'fine', $id, null, "TZS $amount");
        return $id;
    }
    public static function pay(int $fine_id, array $data, int $by): int {
        $fine = \Database::get('SELECT * FROM ' . \Database::t('fines') . ' WHERE id = ?', [$fine_id]);
        if (!$fine || $fine->status !== 'pending') return 0;
        \Database::update('fines', ['status' => 'paid', 'paid_at' => \now(), 'payment_method' => $data['payment_method'] ?? 'cash'], ['id' => $fine_id]);
        Ledger::record($fine->group_id, $fine->member_id, 'fine_payment', (float)$fine->amount, $data['payment_method'] ?? 'cash', "Faini: {$fine->reason}", $by);
        return 1;
    }
    public static function seedDefaultTypes(int $group_id): void {
        $types = [
            ['Kutokuhudhuria Mkutano', 2000],
            ['Kuchelewa Mkutano', 1000],
            ['Kutolipa Hisa kwa Wakati', 1000],
            ['Tabia Mbaya Mktanoni', 5000],
        ];
        foreach ($types as [$name, $amount]) {
            \Database::insert('fine_types', ['group_id' => $group_id, 'name' => $name, 'amount' => $amount]);
        }
    }
}

class Meetings {
    public static function getByGroup(int $group_id): array {
        return \Database::all(
            'SELECT * FROM ' . \Database::t('meetings') . ' WHERE group_id = ? ORDER BY meeting_date DESC',
            [$group_id]
        );
    }
    public static function create(int $group_id, array $data, int $by): int {
        $code = generate_code('MTG');
        $id = \Database::insert('meetings', [
            'group_id'     => $group_id,
            'meeting_code' => $code,
            'meeting_date' => $data['meeting_date'] ?? \today(),
            'location'     => \sanitize($data['location'] ?? ''),
            'agenda'       => $data['agenda'] ?? null,
            'status'       => 'scheduled',
            'created_by'   => $by,
        ]);
        Audit::log($group_id, $by, 'meeting_created', 'meeting', $id, null, $code);
        return $id;
    }
    public static function recordAttendance(array $data, int $by): int {
        $meeting_id = (int)($data['meeting_id'] ?? 0);
        $records    = $data['attendance'] ?? [];
        $saved = 0;
        foreach ($records as $rec) {
            $member_id = (int)($rec['member_id'] ?? 0);
            $status    = $rec['status'] ?? 'present';
            // Upsert
            $exists = \Database::scalar('SELECT id FROM ' . \Database::t('meeting_attendance') . ' WHERE meeting_id=? AND member_id=?', [$meeting_id, $member_id]);
            if ($exists) {
                \Database::update('meeting_attendance', ['status' => $status], ['meeting_id' => $meeting_id, 'member_id' => $member_id]);
            } else {
                \Database::insert('meeting_attendance', ['meeting_id' => $meeting_id, 'member_id' => $member_id, 'status' => $status, 'recorded_by' => $by]);
            }
            // Auto-fine for absence
            if ($status === 'absent') {
                $meeting = \Database::get('SELECT * FROM ' . \Database::t('meetings') . ' WHERE id=?', [$meeting_id]);
                if ($meeting) {
                    $fine_amount = $meeting->absence_fine_amount ?? 0;
                    if ($fine_amount > 0) {
                        Fines::issue($meeting->group_id, ['member_id' => $member_id, 'meeting_id' => $meeting_id, 'amount' => $fine_amount, 'reason' => 'Kutokuhudhuria Mkutano'], $by);
                    }
                }
            }
            $saved++;
        }
        \Database::update('meetings', ['status' => 'completed'], ['id' => $meeting_id]);
        return $saved;
    }
    public static function updateMinutes(int $meeting_id, string $minutes): int {
        return \Database::update('meetings', ['minutes' => $minutes, 'status' => 'completed'], ['id' => $meeting_id]);
    }
}

class SocialFund {
    public static function getSummary(int $group_id): array {
        $t = \Database::t('social_fund');
        $balance = (float)\Database::scalar("SELECT COALESCE(SUM(CASE WHEN type='contribution' THEN amount ELSE -amount END),0) FROM $t WHERE group_id=?", [$group_id]);
        $records = \Database::all("SELECT sf.*, m.full_name as member_name FROM $t sf LEFT JOIN " . \Database::t('members') . " m ON m.id=sf.member_id WHERE sf.group_id=? ORDER BY sf.created_at DESC LIMIT 30", [$group_id]);
        $pending = \Database::all("SELECT sf.*, m.full_name as member_name FROM $t sf LEFT JOIN " . \Database::t('members') . " m ON m.id=sf.member_id WHERE sf.group_id=? AND sf.status='pending'", [$group_id]);
        return compact('balance','records','pending');
    }
    public static function contribute(int $group_id, array $data, int $by): int {
        $id = \Database::insert('social_fund', ['group_id' => $group_id, 'member_id' => $data['member_id'] ?? null, 'meeting_id' => $data['meeting_id'] ?? null, 'type' => 'contribution', 'amount' => (float)$data['amount'], 'reason' => \sanitize($data['reason'] ?? 'Mchango'), 'status' => 'paid', 'approved_by' => $by, 'approved_at' => \now()]);
        Ledger::record($group_id, $data['member_id'] ?? null, 'social_fund_contribution', (float)$data['amount'], $data['payment_method'] ?? 'cash', 'Mchango wa Mfuko wa Jamii', $by);
        return $id;
    }
    public static function request(int $group_id, array $data, int $by): int {
        return \Database::insert('social_fund', ['group_id' => $group_id, 'member_id' => $data['member_id'] ?? null, 'type' => 'disbursement', 'amount' => (float)$data['amount'], 'reason' => \sanitize($data['reason'] ?? ''), 'status' => 'pending', 'requested_by' => $by]);
    }
    public static function approve(int $request_id, array $data, int $by): int {
        return \Database::update('social_fund', ['status' => 'approved', 'approved_by' => $by, 'approved_at' => \now(), 'payment_method' => $data['payment_method'] ?? 'cash'], ['id' => $request_id]);
    }
}

class Ledger {
    public static function record(int $group_id, ?int $member_id, string $type, float $amount, string $method, string $desc, int $by): int {
        $code = generate_code('TXN');
        return \Database::insert('transactions', ['transaction_code' => $code, 'group_id' => $group_id, 'member_id' => $member_id, 'type' => $type, 'amount' => $amount, 'payment_method' => $method, 'description' => $desc, 'account' => $method, 'recorded_by' => $by]);
    }
    public static function addExpense(int $group_id, array $data, int $by): int {
        return self::record($group_id, null, 'expense', (float)$data['amount'], $data['payment_method'] ?? 'cash', \sanitize($data['description'] ?? 'Gharama'), $by);
    }
    public static function getBalance(int $group_id): array {
        $t = \Database::t('transactions');
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN type IN ('share_purchase','loan_repayment','fine_payment','social_fund_contribution') THEN amount END),0) as income,
            COALESCE(SUM(CASE WHEN type IN ('loan_disbursement','expense') THEN amount END),0) as expenses
            FROM $t WHERE group_id=?";
        return (array)\Database::get($sql, [$group_id]);
    }
}

class Notifications {
    public static function create(int $group_id, mixed $user_id, string $title, string $message, string $type = ''): int {
        if (!$user_id) return 0;
        return \Database::insert('notifications', ['group_id' => $group_id, 'user_id' => (int)$user_id, 'title' => $title, 'message' => $message, 'type' => $type, 'is_read' => 0]);
    }
    public static function getUnread(int $user_id, int $limit = 10): array {
        return \Database::all('SELECT * FROM ' . \Database::t('notifications') . ' WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ' . $limit, [$user_id]);
    }
}

class Audit {
    public static function log(int $group_id, int $user_id, string $action, string $entity = '', int $entity_id = 0, ?string $old = null, ?string $new = null): void {
        \Database::insert('audit_log', ['group_id' => $group_id, 'user_id' => $user_id, 'action' => $action, 'entity_type' => $entity, 'entity_id' => $entity_id ?: null, 'old_value' => $old, 'new_value' => $new, 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null]);
    }
}
