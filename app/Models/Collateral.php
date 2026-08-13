<?php namespace Models;

class Collateral {
    public static function getByGroup(int $group_id): array {
        $t_loans = \Database::t('loans');
        $t_members = \Database::t('members');

        return \Database::all(
            "SELECT l.id as loan_id, l.loan_code, l.principal_amount, l.purpose, m.full_name as member_name 
             FROM $t_loans l 
             JOIN $t_members m ON m.id = l.member_id 
             WHERE l.group_id=? AND l.purpose IS NOT NULL AND l.purpose != ''
             ORDER BY l.created_at DESC",
            [$group_id]
        );
    }
}
