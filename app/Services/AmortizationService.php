<?php namespace Services;

class AmortizationService {
    /**
     * Generate Loan Amortization Schedule
     */
    public static function generateSchedule(float $principal, float $interest_rate_annual, int $duration_months, string $interest_type = 'flat', string $start_date = 'now'): array {
        $schedule = [];
        $balance = $principal;
        $start = new \DateTime($start_date);

        if ($interest_type === 'flat') {
            $total_interest = $principal * ($interest_rate_annual / 100) * ($duration_months / 12);
            $monthly_principal = $principal / $duration_months;
            $monthly_interest  = $total_interest / $duration_months;
            $monthly_payment   = $monthly_principal + $monthly_interest;

            for ($i = 1; $i <= $duration_months; $i++) {
                $due_date = clone $start;
                $due_date->modify("+$i month");

                $ending_balance = max(0, $balance - $monthly_principal);
                $schedule[] = [
                    'installment'       => $i,
                    'due_date'          => $due_date->format('Y-m-d'),
                    'beginning_balance' => round($balance, 2),
                    'principal_payment' => round($monthly_principal, 2),
                    'interest_payment'  => round($monthly_interest, 2),
                    'total_payment'     => round($monthly_payment, 2),
                    'ending_balance'    => round($ending_balance, 2)
                ];
                $balance = $ending_balance;
            }
        } else {
            // Reducing Balance (EMI Formula: P * r * (1+r)^n / ((1+r)^n - 1))
            $monthly_rate = ($interest_rate_annual / 100) / 12;
            if ($monthly_rate > 0) {
                $emi = $principal * ($monthly_rate * pow(1 + $monthly_rate, $duration_months)) / (pow(1 + $monthly_rate, $duration_months) - 1);
            } else {
                $emi = $principal / $duration_months;
            }

            for ($i = 1; $i <= $duration_months; $i++) {
                $due_date = clone $start;
                $due_date->modify("+$i month");

                $interest_payment  = $balance * $monthly_rate;
                $principal_payment = $emi - $interest_payment;
                $ending_balance    = max(0, $balance - $principal_payment);

                $schedule[] = [
                    'installment'       => $i,
                    'due_date'          => $due_date->format('Y-m-d'),
                    'beginning_balance' => round($balance, 2),
                    'principal_payment' => round($principal_payment, 2),
                    'interest_payment'  => round($interest_payment, 2),
                    'total_payment'     => round($emi, 2),
                    'ending_balance'    => round($ending_balance, 2)
                ];
                $balance = $ending_balance;
            }
        }

        return $schedule;
    }
}
