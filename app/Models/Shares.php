<?php namespace Models;

class Shares {

    public static function getSummary(int $group_id): array {
        $t = \Database::t('shares');
        $m = \Database::t('members');

        $total_pool  = (float)\Database::scalar("SELECT COALESCE(SUM(total_amount),0) FROM $t WHERE group_id=?", [$group_id]);
        $this_month  = (float)\Database::scalar("SELECT COALESCE(SUM(total_amount),0) FROM $t WHERE group_id=? AND MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE())", [$group_id]);

        $member_summary = \Database::all(
            "SELECT m.id, m.full_name, m.member_number,
                    COALESCE(SUM(s.share_count),0) as total_shares,
                    COALESCE(SUM(s.total_amount),0) as total_amount
             FROM $m m
             LEFT JOIN $t s ON s.member_id = m.id AND s.group_id = m.group_id
             WHERE m.group_id = ? AND m.status = 'active'
             GROUP BY m.id ORDER BY m.full_name", [$group_id]
        );

        $recent = \Database::all(
            "SELECT s.*, m.full_name as member_name FROM $t s
             JOIN $m m ON m.id = s.member_id
             WHERE s.group_id = ? ORDER BY s.created_at DESC LIMIT 20", [$group_id]
        );

        return compact('total_pool','this_month','member_summary','recent');
    }

    public static function record(int $group_id, array $data, int $by): int|array {
        if (empty($data['member_id']))  return ['error' => 'Mwanachama anahitajika.'];
        if (empty($data['share_count'])) return ['error' => 'Idadi ya hisa inahitajika.'];

        $group      = \Database::get('SELECT * FROM ' . \Database::t('groups') . ' WHERE id=?', [$group_id]);
        $share_price = $group ? (float)$group->share_price : 0;
        $share_count = (int)$data['share_count'];
        $total       = $share_price * $share_count;

        $share_id = \Database::insert('shares', [
            'group_id'      => $group_id,
            'member_id'     => (int)$data['member_id'],
            'meeting_id'    => $data['meeting_id'] ?? null,
            'share_count'   => $share_count,
            'share_price'   => $share_price,
            'total_amount'  => $total,
            'payment_method'=> $data['payment_method'] ?? 'cash',
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_date'  => $data['payment_date'] ?? \today(),
            'recorded_by'   => $by,
        ]);

        // Record in ledger
        Ledger::record($group_id, (int)$data['member_id'], 'share_purchase', $total, $data['payment_method'] ?? 'cash', "Hisa $share_count @ TZS " . number_format($share_price), $by);
        Audit::log($group_id, $by, 'shares_recorded', 'share', $share_id, null, "$share_count hisa = TZS " . number_format($total));

        return $share_id;
    }

    public static function getMemberTotal(int $group_id, int $member_id): int {
        return (int)\Database::scalar(
            'SELECT COALESCE(SUM(share_count),0) FROM ' . \Database::t('shares') . ' WHERE group_id=? AND member_id=?',
            [$group_id, $member_id]
        );
    }
}
