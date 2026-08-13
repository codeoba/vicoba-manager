<?php
/**
 * VICOBA Dashboard Controller
 * Handles all page rendering for the dashboard
 */

class DashboardController
{
    /** Default — redirect to overview */
    public function index(array $params = []): void
    {
        $user = Auth::require();
        Response::redirect('/dashboard/overview');
    }

    /** Render dashboard sub-views via clean URL: /dashboard/{view} */
    public function show(array $params): void
    {
        $user = Auth::require();
        $view = $params['view'] ?? 'overview';

        $allowed = [
            'overview','members','shares','loans','fines',
            'meetings','social-fund','shareout','reports',
            'settings','super-admin','ledger','accounting','collateral',
        ];

        if (!in_array($view, $allowed)) {
            Response::redirect('/dashboard/overview');
        }

        // Super admin only pages
        if ($view === 'super-admin' && !Auth::hasRole('super_admin')) {
            Response::redirect('/dashboard/overview');
        }

        $member = null;
        $group  = null;
        $group_id = $user->group_id ?? 0;

        try {
            if (!$group_id) {
                $group_id = (int) Database::scalar('SELECT id FROM ' . Database::t('groups') . ' ORDER BY id ASC LIMIT 1');
            }

            if ($group_id) {
                $member = Database::get('SELECT * FROM ' . Database::t('members') . ' WHERE user_id = ?', [$user->id]);
                $group  = Database::get('SELECT * FROM ' . Database::t('groups') . ' WHERE id = ?', [$group_id]);
            }
        } catch (\Throwable $e) {
            error_log('Error loading group/member in DashboardController: ' . $e->getMessage());
        }

        // Unread notifications count
        $unread_count = 0;
        try {
            $unread_count = (int) Database::scalar(
                'SELECT COUNT(*) FROM ' . Database::t('notifications') . ' WHERE user_id = ? AND is_read = 0',
                [$user->id]
            );
        } catch (\Throwable $e) {
            $unread_count = 0;
        }

        // Overdue loans count (for admin banner)
        $overdue_count = 0;
        try {
            if ($group && Auth::hasRole('super_admin','group_admin','treasurer')) {
                $overdue_count = (int) Database::scalar(
                    'SELECT COUNT(*) FROM ' . Database::t('loans') . ' WHERE group_id = ? AND status = ?',
                    [$group->id, 'overdue']
                );
            }
        } catch (\Throwable $e) {
            $overdue_count = 0;
        }

        Response::dashboard($view, [
            'user'          => $user,
            'member'        => $member,
            'group'         => $group,
            'user_role'     => $user->role,
            'unread_count'  => $unread_count,
            'overdue_count' => $overdue_count,
            'current_view'  => $view,
            'page_title'    => ucfirst(str_replace('-', ' ', $view)) . ' — VICOBA Manager',
            'flash'         => get_flash(),
        ]);
    }
}
