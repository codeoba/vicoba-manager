<?php namespace Services;

class WorkflowService {
    /**
     * Dual-Authorization (Maker-Checker) workflow manager
     */
    public static function requiresSecondaryApproval(float $amount, float $threshold = 500000): bool {
        return $amount >= $threshold;
    }

    public static function createPendingApproval(int $group_id, int $maker_id, string $type, float $amount, array $payload): int {
        return \Database::insert('audit_log', [
            'group_id'    => $group_id,
            'user_id'     => $maker_id,
            'action'      => 'workflow_maker_pending',
            'entity_type' => $type,
            'entity_id'   => 0,
            'details'     => json_encode(['amount' => $amount, 'payload' => $payload, 'status' => 'pending_checker'])
        ]);
    }
}
