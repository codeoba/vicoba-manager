<?php namespace Models;

class Members {

    public static function getByGroup(int $group_id, string $status = ''): array {
        $sql = 'SELECT m.*, u.email, u.username FROM ' . \Database::t('members') . ' m
                JOIN ' . \Database::t('users') . ' u ON u.id = m.user_id
                WHERE m.group_id = ?';
        $params = [$group_id];
        if ($status) { $sql .= ' AND m.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY m.full_name ASC';
        return \Database::all($sql, $params);
    }

    public static function getByUserId(int $user_id): ?\stdClass {
        return \Database::get('SELECT * FROM ' . \Database::t('members') . ' WHERE user_id = ?', [$user_id]) ?: null;
    }

    public static function get(int $member_id): ?\stdClass {
        return \Database::get('SELECT * FROM ' . \Database::t('members') . ' WHERE id = ?', [$member_id]) ?: null;
    }

    public static function create(int $group_id, array $data, int $by): int|array {
        // Validate required fields
        if (empty($data['full_name'])) return ['error' => 'Jina kamili linahitajika.'];
        if (empty($data['phone']))     return ['error' => 'Namba ya simu inahitajika.'];

        // Check phone uniqueness in group
        $exists = \Database::scalar('SELECT id FROM ' . \Database::t('members') . ' WHERE group_id = ? AND phone = ?', [$group_id, $data['phone']]);
        if ($exists) return ['error' => 'Namba ya simu tayari ipo katika kikundi hiki.'];

        // Generate member number
        $count  = (int)\Database::scalar('SELECT COUNT(*) FROM ' . \Database::t('members') . ' WHERE group_id = ?', [$group_id]);
        $prefix = \Database::scalar('SELECT LEFT(name,3) FROM ' . \Database::t('groups') . ' WHERE id = ?', [$group_id]);
        $member_number = strtoupper($prefix) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Create WordPress-style user account
        $username = preg_replace('/[^a-z0-9._]/', '', strtolower(str_replace(' ', '.', $data['full_name'])));
        $username = $username . '_' . $group_id;
        $password = !empty($data['password']) ? $data['password'] : bin2hex(random_bytes(4)); // random temp password

        $user_id = \Database::insert('users', [
            'username'     => $username,
            'email'        => $data['email'] ?? $username . '@vicoba.local',
            'password'     => \Auth::hashPassword($password),
            'display_name' => $data['full_name'],
            'role'         => $data['role'] ?? 'member',
            'group_id'     => $group_id,
            'status'       => 'active',
        ]);

        $nida_enc = !empty($data['nida_number']) ? encrypt_data($data['nida_number']) : null;

        $member_id = \Database::insert('members', [
            'group_id'     => $group_id,
            'user_id'      => $user_id,
            'member_number'=> $member_number,
            'full_name'    => \sanitize($data['full_name']),
            'phone'        => \sanitize($data['phone']),
            'email'        => $data['email'] ?? null,
            'nida_number_enc' => $nida_enc,
            'gender'       => $data['gender'] ?? null,
            'address'      => $data['address'] ?? null,
            'role'         => $data['role'] ?? 'member',
            'status'       => 'active',
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'emergency_phone'   => $data['emergency_phone'] ?? null,
            'joined_date'  => \today(),
        ]);

        \Database::update('users', ['group_id' => $group_id], ['id' => $user_id]);

        Audit::log($group_id, $by, 'member_created', 'member', $member_id, null, $data['full_name']);
        return $member_id;
    }

    public static function update(int $member_id, array $data): int {
        $fields = ['full_name','phone','email','gender','address','role','emergency_contact','emergency_phone'];
        $update = [];
        foreach ($fields as $f) { if (isset($data[$f])) $update[$f] = \sanitize($data[$f]); }
        if (empty($update)) return 0;
        return \Database::update('members', $update, ['id' => $member_id]);
    }

    public static function updateStatus(int $member_id, string $status): int {
        $allowed = ['active','suspended','alumni'];
        if (!in_array($status, $allowed)) return 0;
        return \Database::update('members', ['status' => $status], ['id' => $member_id]);
    }

    public static function updateRole(int $member_id, string $role): int {
        \Database::update('members', ['role' => $role], ['id' => $member_id]);
        $member = self::get($member_id);
        if ($member) \Database::update('users', ['role' => $role], ['id' => $member->user_id]);
        return 1;
    }
}
