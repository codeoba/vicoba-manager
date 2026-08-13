<?php namespace Models;

class Accounting {
    public static array $chartOfAccounts = [
        '1010' => ['name' => 'Cash in Hand / Bank',           'type' => 'ASSET'],
        '1020' => ['name' => 'Loans Receivable (Active)',     'type' => 'ASSET'],
        '2010' => ['name' => 'Member Savings / Shares Pool',  'type' => 'LIABILITY'],
        '2020' => ['name' => 'Social Fund Reserves',         'type' => 'LIABILITY'],
        '3010' => ['name' => 'Retained Earnings & Reserves', 'type' => 'EQUITY'],
        '4010' => ['name' => 'Loan Interest Income',         'type' => 'REVENUE'],
        '4020' => ['name' => 'Fine & Penalty Income',        'type' => 'REVENUE'],
        '5010' => ['name' => 'Administrative & Office Expense', 'type' => 'EXPENSE'],
        '5020' => ['name' => 'Allowance & Transport Expense','type' => 'EXPENSE'],
    ];

    public static function postJournal(int $group_id, string $debit_code, string $credit_code, float $amount, string $description): bool {
        if ($amount <= 0) return false;
        
        $t_txn = \Database::t('transactions');
        \Database::insert('transactions', [
            'group_id'         => $group_id,
            'member_id'        => null,
            'type'             => 'journal_entry',
            'amount'           => $amount,
            'payment_method'   => 'system',
            'transaction_code' => 'JE-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'description'      => "DR: $debit_code | CR: $credit_code — $description",
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    public static function getFinancialStatements(int $group_id): array {
        $stats = Reports::getSummary($group_id);

        $trial_balance = [
            ['code' => '1010', 'account' => 'Cash & Bank Balance',       'debit' => $stats['balance'], 'credit' => 0],
            ['code' => '1020', 'account' => 'Loans Receivable',          'debit' => $stats['active_loans'], 'credit' => 0],
            ['code' => '2010', 'account' => 'Member Savings (Shares)',   'debit' => 0, 'credit' => $stats['total_shares']],
            ['code' => '4020', 'account' => 'Fine & Penalty Income',     'debit' => 0, 'credit' => $stats['fines_paid']],
        ];

        $income_statement = [
            'revenue' => [
                'fines_income' => $stats['fines_paid'],
                'total_revenue' => $stats['fines_paid']
            ],
            'expenses' => [
                'operating_expenses' => 0,
                'total_expenses' => 0
            ],
            'net_income' => $stats['fines_paid']
        ];

        $balance_sheet = [
            'assets' => [
                'cash'  => $stats['balance'],
                'loans' => $stats['active_loans'],
                'total_assets' => $stats['balance'] + $stats['active_loans']
            ],
            'liabilities' => [
                'shares' => $stats['total_shares'],
                'fines_pending' => $stats['fines_pending'],
                'total_liabilities' => $stats['total_shares']
            ],
            'equity' => [
                'retained_earnings' => ($stats['balance'] + $stats['active_loans']) - $stats['total_shares'],
                'total_equity' => ($stats['balance'] + $stats['active_loans']) - $stats['total_shares']
            ]
        ];

        return compact('trial_balance', 'income_statement', 'balance_sheet');
    }
}
