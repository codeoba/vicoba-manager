<?php
/**
 * Master Dashboard Layout Template
 * Sidebar, Header, Mobile Nav, and Subroute Dispatcher
 */

get_header();

$current_user = wp_get_current_user();
$member = VICOBA_Members::get_member_by_user_id($current_user->ID);
$group = $member ? VICOBA_Groups::get_group($member->group_id) : null;

// Determine active subroute
$subroute = get_query_var('vicoba_subroute');
if (empty($subroute) && isset($_GET['vicoba_subroute'])) {
    $subroute = sanitize_text_field($_GET['vicoba_subroute']);
}
if (empty($subroute)) {
    $subroute = 'overview';
}

$user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'member';

// Navigation items array with role restrictions
$nav_items = array(
    array('key' => 'overview', 'label' => 'Nyumbani (Overview)', 'icon' => 'fa-gauge-high', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'members', 'label' => 'Wanachama', 'icon' => 'fa-users', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'shares', 'label' => 'Hisa Zangu & Kikundi', 'icon' => 'fa-coins', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'loans', 'label' => 'Mikopo & Wadhamini', 'icon' => 'fa-hand-holding-dollar', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'fines', 'label' => 'Faini (Fines)', 'icon' => 'fa-gavel', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'meetings', 'label' => 'Mikutano & Mahudhurio', 'icon' => 'fa-calendar-check', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'social-fund', 'label' => 'Mfuko wa Jamii', 'icon' => 'fa-heart-pulse', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'ledger', 'label' => 'Ledger & Sanduku', 'icon' => 'fa-book-bookmark', 'roles' => array('super_admin', 'group_admin', 'treasurer')),
    array('key' => 'shareout', 'label' => 'Share-Out (Mgawanyo)', 'icon' => 'fa-chart-pie', 'roles' => array('super_admin', 'group_admin', 'treasurer')),
    array('key' => 'reports', 'label' => 'Ripoti & Export', 'icon' => 'fa-file-invoice-dollar', 'roles' => array('super_admin', 'group_admin', 'secretary', 'treasurer', 'member')),
    array('key' => 'settings', 'label' => 'Mipangilio ya Kikundi', 'icon' => 'fa-sliders', 'roles' => array('super_admin', 'group_admin')),
    array('key' => 'super-admin', 'label' => 'Super Admin (SaaS)', 'icon' => 'fa-user-gear', 'roles' => array('super_admin', 'administrator')),
);
?>

<div class="min-h-screen flex bg-slate-100">
    <!-- Desktop Sidebar Navigation -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-vicoba-900 text-white shrink-0 shadow-2xl relative z-20">
        <!-- Brand Header -->
        <div class="h-20 flex items-center px-6 border-b border-vicoba-800 bg-vicoba-950/50">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-vicoba-500 to-emerald-400 flex items-center justify-center text-white shadow-lg mr-3">
                <i class="fa-solid fa-piggy-bank text-xl"></i>
            </div>
            <div>
                <h1 class="font-extrabold text-lg text-white leading-tight">VICOBA SaaS</h1>
                <p class="text-xs text-vicoba-300 font-medium truncate max-w-[130px]">
                    <?php echo $group ? esc_html($group->name) : 'Super Admin'; ?>
                </p>
            </div>
        </div>

        <!-- Role Badge -->
        <div class="px-6 py-3 bg-vicoba-800/60 border-b border-vicoba-800/40 flex items-center justify-between text-xs">
            <span class="text-vicoba-300">Jukumu:</span>
            <span class="px-2.5 py-0.5 rounded-full font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider text-[10px]">
                <?php echo str_replace('_', ' ', strtoupper($user_role)); ?>
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <?php foreach ($nav_items as $item) : ?>
                <?php if (in_array($user_role, $item['roles']) || in_array('administrator', $current_user->roles)) : ?>
                    <?php $is_active = ($subroute === $item['key']); ?>
                    <a href="<?php echo VICOBA_Router::get_url('dashboard', $item['key']); ?>" 
                       class="flex items-center px-3.5 py-3 text-sm font-semibold rounded-xl transition-all duration-150 <?php echo $is_active ? 'bg-gradient-to-r from-vicoba-600 to-emerald-600 text-white shadow-lg shadow-vicoba-950/50 translate-x-1' : 'text-vicoba-100 hover:bg-vicoba-800/60 hover:text-white'; ?>">
                        <i class="fa-solid <?php echo $item['icon']; ?> w-6 text-center text-lg mr-3 <?php echo $is_active ? 'text-white' : 'text-vicoba-400'; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <!-- User Profile Footer -->
        <div class="p-4 border-t border-vicoba-800/80 bg-vicoba-950/30 flex items-center justify-between">
            <div class="flex items-center min-w-0">
                <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold mr-2 text-sm shadow">
                    <?php echo strtoupper(substr($current_user->display_name, 0, 1)); ?>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-white truncate"><?php echo esc_html($current_user->display_name); ?></p>
                    <p class="text-[10px] text-vicoba-300 truncate"><?php echo $member ? esc_html($member->member_number) : 'Admin'; ?></p>
                </div>
            </div>
            <a href="<?php echo wp_logout_url(VICOBA_Router::get_url('login')); ?>" class="text-vicoba-300 hover:text-rose-400 p-2 rounded-lg transition" title="Toka (Logout)">
                <i class="fa-solid fa-right-from-bracket text-lg"></i>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Mobile & Desktop Navbar -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 shadow-sm">
            <div class="flex items-center space-x-3">
                <button id="mobileNavToggle" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight capitalize">
                    <?php 
                    $current_item = array_filter($nav_items, function($i) use ($subroute) { return $i['key'] === $subroute; });
                    $current_label = !empty($current_item) ? reset($current_item)['label'] : 'Dashboard';
                    echo esc_html($current_label);
                    ?>
                </h2>
            </div>

            <!-- Group Indicator, Notifications Bell & Logout -->
            <div class="flex items-center space-x-3">
                <?php if ($group) : ?>
                    <div class="hidden sm:flex items-center px-3 py-1.5 rounded-full bg-vicoba-50 border border-vicoba-200 text-vicoba-800 text-xs font-bold">
                        <i class="fa-solid fa-building-columns mr-2 text-vicoba-600"></i>
                        <span><?php echo esc_html($group->name); ?> (<?php echo esc_html($group->currency ?? 'TZS'); ?>)</span>
                    </div>
                <?php endif; ?>

                <!-- Notifications Bell -->
                <?php
                $unread_notifs = VICOBA_Notifications::get_user_notifications($current_user->ID, true);
                $unread_count  = count($unread_notifs);
                ?>
                <button id="notifBell" onclick="toggleNotifPanel()" class="relative p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Arifa">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <?php if ($unread_count > 0): ?>
                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold flex items-center justify-center"><?php echo min($unread_count, 9); ?><?php echo $unread_count > 9 ? '+' : ''; ?></span>
                    <?php endif; ?>
                </button>

                <!-- Notification Dropdown Panel -->
                <div id="notifPanel" class="hidden absolute right-4 top-20 z-50 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden">
                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-700">Arifa Zangu (<?php echo $unread_count; ?> mpya)</span>
                        <button onclick="toggleNotifPanel()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                        <?php if (empty($unread_notifs)): ?>
                        <p class="py-8 text-center text-xs text-slate-400"><i class="fa-solid fa-bell-slash block text-2xl mb-2 opacity-30"></i>Hakuna arifa mpya</p>
                        <?php else: ?>
                        <?php foreach (array_slice($unread_notifs, 0, 10) as $notif): ?>
                        <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer" onclick="markNotifRead(<?php echo $notif->id; ?>, this)">
                            <p class="text-xs font-bold text-slate-800"><?php echo esc_html($notif->title); ?></p>
                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2"><?php echo esc_html($notif->message); ?></p>
                            <p class="text-[10px] text-slate-400 mt-1"><?php echo date('d/m/Y H:i', strtotime($notif->created_at)); ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="<?php echo wp_logout_url(VICOBA_Router::get_url('login')); ?>" class="flex items-center px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition">
                    <i class="fa-solid fa-right-from-bracket mr-1.5"></i> Toka
                </a>
            </div>
        </header>

        <!-- Main Body Workspace -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-8 bg-slate-50">
            <?php
            // Overdue loans alert banner
            global $wpdb;
            $overdue_count = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}vicoba_loans WHERE group_id=%d AND status='overdue'",
                $group ? $group->id : 0
            ));
            if ($overdue_count > 0 && in_array($user_role, ['super_admin','group_admin','treasurer','administrator'])):
            ?>
            <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-between gap-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm font-extrabold text-rose-800">Mikopo <?php echo $overdue_count; ?> imechelewa!</p>
                        <p class="text-xs text-rose-600">Riba ya adhabu inaendelea kuongezeka. Wasiliana na wanachama wanaohusika mara moja.</p>
                    </div>
                </div>
                <a href="<?php echo VICOBA_Router::get_url('dashboard', 'loans'); ?>" class="shrink-0 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition">Angalia Mikopo</a>
            </div>
            <?php endif; ?>

            <?php
            $template_file = get_template_directory() . '/template-parts/dashboard/' . sanitize_file_name($subroute) . '.php';
            if (file_exists($template_file)) {
                include $template_file;
            } else {
                include get_template_directory() . '/template-parts/dashboard/overview.php';
            }
            ?>
        </main>
    </div>
</div>

<script>
function toggleNotifPanel() {
    document.getElementById('notifPanel')?.classList.toggle('hidden');
}
function markNotifRead(id, el) {
    fetch(vicobaData.root + 'vicoba/v1/notifications/mark-read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': vicobaData.nonce },
        body: JSON.stringify({ notification_id: id })
    }).then(() => el.style.opacity = '0.5');
}
document.addEventListener('click', function(e) {
    const panel = document.getElementById('notifPanel');
    const bell  = document.getElementById('notifBell');
    if (panel && !panel.contains(e.target) && bell && !bell.contains(e.target)) {
        panel.classList.add('hidden');
    }
});
</script>

<?php get_footer(); ?>
