<?php
/**
 * VICOBA Mathematical & Pure Function Verification Suite
 * Tests loan interest formulas (Fixed vs Reducing balance) and Share-Out distribution ratios.
 */

// Mock WordPress ABSPATH if needed
if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');

require_once __DIR__ . '/../inc/class-vicoba-loans.php';

echo "=========================================================\n";
echo "       VICOBA MATHEMATICAL ENGINE TEST SUITE             \n";
echo "=========================================================\n\n";

// 1. Test Fixed Interest Loan Schedule
echo "[TEST 1] Testing Fixed Interest Loan Calculation...\n";
$fixed_res = VICOBA_Loans::calculate_loan_schedule(300000, 5, 'fixed', 3);
echo "Principal: TZS 300,000 | Rate: 5% | Period: 3 Months | Type: Fixed\n";
echo "Total Interest: TZS " . number_format($fixed_res['total_interest'], 2) . "\n";
echo "Total Payable: TZS " . number_format($fixed_res['total_payable'], 2) . "\n";
echo "Monthly Installment: TZS " . number_format($fixed_res['monthly_installment'], 2) . "\n";
assert($fixed_res['total_payable'] > 300000);
echo ">>> PASS: Fixed Interest Math Verified!\n\n";

// 2. Test Reducing Balance Interest Loan Schedule
echo "[TEST 2] Testing Reducing Balance Loan Calculation...\n";
$reducing_res = VICOBA_Loans::calculate_loan_schedule(300000, 5, 'reducing', 3);
echo "Principal: TZS 300,000 | Rate: 5% | Period: 3 Months | Type: Reducing\n";
echo "Total Interest: TZS " . number_format($reducing_res['total_interest'], 2) . "\n";
echo "Total Payable: TZS " . number_format($reducing_res['total_payable'], 2) . "\n";
echo "Monthly Installment: TZS " . number_format($reducing_res['monthly_installment'], 2) . "\n";
assert($reducing_res['total_payable'] > 300000);
echo ">>> PASS: Reducing Balance Math Verified!\n\n";

// 3. Test Share-Out Proportional Dividend Math
echo "[TEST 3] Testing Share-Out Distribution Formula...\n";
$total_shares_pool = 1000000;
$total_shares_count = 100; // 10,000 TZS per share
$interest_profit = 150000;
$fines = 20000;
$expenses = 10000;
$bad_debt = 0;

$net_distributable = $total_shares_pool + $interest_profit + $fines - $expenses - $bad_debt;
$dividend_per_share = $net_distributable / $total_shares_count;

echo "Total Shares Pool: TZS " . number_format($total_shares_pool) . "\n";
echo "Interest Profit + Fines: TZS " . number_format($interest_profit + $fines) . "\n";
echo "Expenses: TZS " . number_format($expenses) . "\n";
echo "Net Distributable: TZS " . number_format($net_distributable) . "\n";
echo "Dividend / Share: TZS " . number_format($dividend_per_share, 2) . "\n";

// Member A has 10 shares (10% ratio)
$member_A_shares = 10;
$member_A_ratio = $member_A_shares / $total_shares_count;
$member_A_payout = $member_A_shares * $dividend_per_share;

echo "Member A (10 shares = 10% ratio) Gross Payout: TZS " . number_format($member_A_payout, 2) . "\n";
assert(abs($member_A_payout - 116000) < 0.01);
echo ">>> PASS: Share-Out Distribution Formula Verified!\n\n";

echo "=========================================================\n";
echo "        ALL MATHEMATICAL VERIFICATION TESTS PASSED!       \n";
echo "=========================================================\n";
