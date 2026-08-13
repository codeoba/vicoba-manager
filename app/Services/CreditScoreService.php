<?php namespace Services;

class CreditScoreService {
    /**
     * Calculate Member Credit Score on a scale of 300 to 850
     */
    public static function calculate(int $member_id, int $group_id): array {
        $t_loans   = \Database::t('loans');
        $t_shares  = \Database::t('shares');
        $t_fines   = \Database::t('fines');
        $t_attend  = \Database::t('meeting_attendance');

        // 1. Share Savings Regularity (Max 300 pts)
        $share_count = (int)\Database::scalar("SELECT COUNT(*) FROM $t_shares WHERE member_id=? AND group_id=?", [$member_id, $group_id]);
        $share_score = min(300, $share_count * 25);

        // 2. Past Loan Repayment Promptness (Max 350 pts)
        $completed_loans = (int)\Database::scalar("SELECT COUNT(*) FROM $t_loans WHERE member_id=? AND group_id=? AND status='completed'", [$member_id, $group_id]);
        $overdue_loans   = (int)\Database::scalar("SELECT COUNT(*) FROM $t_loans WHERE member_id=? AND group_id=? AND status='overdue'", [$member_id, $group_id]);
        
        $loan_score = 250; // base score
        $loan_score += ($completed_loans * 30);
        $loan_score -= ($overdue_loans * 80);
        $loan_score = max(50, min(350, $loan_score));

        // 3. Meeting Attendance (Max 150 pts)
        $attended_count = (int)\Database::scalar("SELECT COUNT(*) FROM $t_attend WHERE member_id=? AND status='present'", [$member_id]);
        $attendance_score = min(150, $attended_count * 15);

        // 4. Pending Fines Penalty (Max 50 pts deduction)
        $pending_fines = (int)\Database::scalar("SELECT COUNT(*) FROM $t_fines WHERE member_id=? AND group_id=? AND status='pending'", [$member_id, $group_id]);
        $fine_penalty  = min(50, $pending_fines * 25);

        // Total Score
        $score = (int)round(300 + ($share_score * 0.3) + ($loan_score * 0.4) + ($attendance_score * 0.3) - $fine_penalty);
        $score = max(300, min(850, $score));

        $rating = match(true) {
            $score >= 750 => ['label' => 'EXCELLENT', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'color' => '#16a34a'],
            $score >= 650 => ['label' => 'GOOD',      'badge' => 'bg-blue-100 text-blue-800 border-blue-300',       'color' => '#2563eb'],
            $score >= 550 => ['label' => 'AVERAGE',   'badge' => 'bg-amber-100 text-amber-800 border-amber-300',    'color' => '#d97706'],
            default       => ['label' => 'POOR / HIGH RISK', 'badge' => 'bg-red-100 text-red-800 border-red-300', 'color' => '#dc2626'],
        };

        return [
            'score'  => $score,
            'label'  => $rating['label'],
            'badge'  => $rating['badge'],
            'color'  => $rating['color'],
            'details' => [
                'share_score'      => $share_score,
                'loan_score'       => $loan_score,
                'attendance_score' => $attendance_score,
                'fine_penalty'     => $fine_penalty
            ]
        ];
    }
}
