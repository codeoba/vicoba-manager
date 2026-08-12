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
            'settings','super-admin','ledger',
        ];

        if (!in_array($view, $allowed)) {
            Response::redirect('/dashboard/overview');
        }

        // Super admin only pages
        if ($view === 'super-admin' && !Auth::hasRole('super_admin')) {
            Response::redirect('/dashboard/overview');
        }

        $group_id = $user->group_id;
        if (!$group_id) {
            $group_id = (int) Database::scalar('SELECT id FROM ' . Database::t('groups') . ' ORDER BY id ASC LIMIT 1');
        }

        if ($group_id) {
            $member = Database::get('SELECT * FROM ' . Database::t('members') . ' WHERE user_id = ?', [$user->id]);
            $group  = Database::get('SELECT * FROM ' . Database::t('groups') . ' WHERE id = ?', [$group_id]);
        }

        // Unread notifications count
        $unread_count = (int) Database::scalar(
            'SELECT COUNT(*) FROM ' . Database::t('notifications') . ' WHERE user_id = ? AND is_read = 0',
            [$user->id]
        );

        // Overdue loans count (for admin banner)
        $overdue_count = 0;
        if ($group && Auth::hasRole('super_admin','group_admin','treasurer')) {
            $overdue_count = (int) Database::scalar(
                'SELECT COUNT(*) FROM ' . Database::t('loans') . ' WHERE group_id = ? AND status = ?',
                [$group->id, 'overdue']
            );
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
