<?php
/**
 * Dashboard Overview Subroute Template
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group_id = $member ? $member->group_id : 1;

$ledger = VICOBA_Ledger::get_box_balance($group_id);
$shares = VICOBA_Shares::get_group_total_shares($group_id);
$active_loans = VICOBA_Loans::get_loans_by_group($group_id, 'active');
$fines = VICOBA_Fines::get_fines_by_group($group_id, 'unpaid');

$total_active_loan_amount = 0;
foreach ($active_loans as $l) {
    $total_active_loan_amount += floatval($l->balance_remaining);
}

$unpaid_fines_total = 0;
foreach ($fines as $f) {
    $unpaid_fines_total += floatval($f->amount);
}
?>

<div class="space-y-8">
    <!-- Welcome Header Banner -->
    <div class="rounded-3xl bg-gradient-to-r from-vicoba-800 via-vicoba-700 to-emerald-700 p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/5 skew-x-12"></div>
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">
                Habari, <?php echo esc_html($current_user->display_name); ?>! 👋
            </h1>
            <p class="text-vicoba-100 text-sm max-w-xl">
                Karibu kwenye Mfumo wa VICOBA. Hapa kuna muhtasari wa hali ya fedha za kikundi chako kwa sasa.
            </p>
        </div>
    </div>

    <!-- 4 Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Box Balance -->
        <div class="glass-card p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Salio la Sanduku</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-vault"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-800 mt-3">TZS <?php echo number_format($ledger['box_balance']); ?></p>
            <div class="mt-3 flex items-center text-xs text-emerald-600 font-semibold">
                <i class="fa-solid fa-circle-arrow-down mr-1"></i> Inflows: TZS <?php echo number_format($ledger['total_inflows']); ?>
            </div>
        </div>

        <!-- Card 2: Total Group Shares -->
        <div class="glass-card p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Jumla ya Hisa</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-800 mt-3">TZS <?php echo number_format($shares['value']); ?></p>
            <div class="mt-3 text-xs text-amber-600 font-semibold">
                Idadi ya Hisa: <?php echo number_format($shares['count']); ?> Hisa
            </div>
        </div>

        <!-- Card 3: Active Loans -->
        <div class="glass-card p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Mikopo Hai (Active)</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-800 mt-3">TZS <?php echo number_format($total_active_loan_amount); ?></p>
            <div class="mt-3 text-xs text-blue-600 font-semibold">
                Mikopo Hai: <?php echo count($active_loans); ?> Mikopo
            </div>
        </div>

        <!-- Card 4: Unpaid Fines -->
        <div class="glass-card p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Faini Zisizolipwa</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-gavel"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-800 mt-3">TZS <?php echo number_format($unpaid_fines_total); ?></p>
            <div class="mt-3 text-xs text-rose-600 font-semibold">
                Faini <?php echo count($fines); ?> Hazijalipwa
            </div>
        </div>
    </div>

    <!-- Graph Section & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Visual Chart -->
        <div class="lg:col-span-2 glass-card p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <h3 class="text-base font-extrabold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-chart-line text-vicoba-600 mr-2"></i> Mwenendo wa Fedha za Kikundi
            </h3>
            <div class="h-64">
                <canvas id="overviewFinancialChart"></canvas>
            </div>
        </div>

        <!-- Account Distribution Breakdown -->
        <div class="glass-card p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-wallet text-vicoba-600 mr-2"></i> Mchanganuo wa Akaunti (Multi-Account)
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center">
                            <i class="fa-solid fa-money-bill-wave text-emerald-600 text-lg mr-3"></i>
                            <span class="text-sm font-semibold text-slate-700">Pesa Taslimu (Cash)</span>
                        </div>
                        <span class="text-sm font-bold text-slate-800">TZS <?php echo number_format($ledger['cash_balance']); ?></span>
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center">
                            <i class="fa-solid fa-mobile-screen-button text-sky-600 text-lg mr-3"></i>
                            <span class="text-sm font-semibold text-slate-700">Mobile Money</span>
                        </div>
                        <span class="text-sm font-bold text-slate-800">TZS <?php echo number_format($ledger['mobile_balance']); ?></span>
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center">
                            <i class="fa-solid fa-building-columns text-indigo-600 text-lg mr-3"></i>
                            <span class="text-sm font-semibold text-slate-700">Akaunti ya Benki</span>
                        </div>
                        <span class="text-sm font-bold text-slate-800">TZS <?php echo number_format($ledger['bank_balance']); ?></span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="<?php echo home_url('/dashboard/ledger/'); ?>" class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl bg-vicoba-50 hover:bg-vicoba-100 text-vicoba-700 font-bold text-xs transition">
                    <i class="fa-solid fa-book-bookmark mr-2"></i> Fungua Ledger Kamili
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('overviewFinancialChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Hisa (Shares)', 'Mikopo Hai', 'Salio Sanduku', 'Faini Zilizolipwa'],
            datasets: [{
                label: 'Kiasi (TZS)',
                data: [
                    <?php echo $shares['value']; ?>,
                    <?php echo $total_active_loan_amount; ?>,
                    <?php echo $ledger['box_balance']; ?>,
                    <?php echo $ledger['total_inflows'] * 0.1; ?>
                ],
                backgroundColor: [
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(59, 130, 246, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(244, 63, 94, 0.85)'
                ],
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
