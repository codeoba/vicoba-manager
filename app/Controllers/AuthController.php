<?php
/**
 * VICOBA Auth Controller
 * Handles login, logout, and group registration
 */

class AuthController
{
    public function loginPage(array $params = []): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard/overview');
        }
        Response::view('auth.login', [
            'page_title' => 'Ingia — VICOBA Manager',
            'flash'      => get_flash(),
            'csrf'       => Auth::csrf(),
        ]);
    }

    public function loginPost(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            flash('error', 'Ombi batili. Tafadhali jaribu tena.');
            Response::redirect('/login');
        }

        $username = sanitize(post('username', ''));
        $password = post('password', '');

        if (!$username || !$password) {
            flash('error', 'Tafadhali jaza jina la mtumiaji na nywila.');
            Response::redirect('/login');
        }

        $user = Auth::attempt($username, $password);

        if (!$user) {
            flash('error', 'Jina la mtumiaji au nywila si sahihi. Jaribu tena.');
            Response::redirect('/login');
        }

        Auth::login($user);
        Response::redirect('/dashboard/overview');
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        flash('success', 'Umefanikiwa kutoka mfumo.');
        Response::redirect('/login');
    }

    public function registerPage(array $params = []): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard/overview');
        }
        Response::view('auth.register', [
            'page_title' => 'Sajili Kikundi — VICOBA Manager',
            'flash'      => get_flash(),
            'csrf'       => Auth::csrf(),
        ]);
    }

    public function registerPost(array $params = []): void
    {
        if (!Auth::verifyCsrf()) {
            flash('error', 'Ombi batili. Tafadhali jaribu tena.');
            Response::redirect('/register');
        }

        $group_name    = sanitize(post('group_name', ''));
        $region        = sanitize(post('region', ''));
        $district      = sanitize(post('district', ''));
        $founding_date = sanitize(post('founding_date', ''));
        $currency      = sanitize(post('currency', 'TZS'));
        $full_name     = sanitize(post('full_name', ''));
        $phone         = sanitize(post('phone', ''));
        $email         = sanitize_email_addr(post('email', ''));
        $username      = sanitize(post('username', ''));
        $password      = post('password', '');
        $confirm_pass  = post('confirm_password', '');

        if (!$group_name || !$full_name || !$username || !$password) {
            flash('error', 'Tafadhali jaza sehemu zote zinazohitajika (*).');
            Response::redirect('/register');
        }

        if ($password !== $confirm_pass) {
            flash('error', 'Nywila hazifanani. Tafadhali jaribu tena.');
            Response::redirect('/register');
        }

        if (strlen($password) < 8) {
            flash('error', 'Nywila lazima iwe na herufi 8 au zaidi.');
            Response::redirect('/register');
        }

        // Check username uniqueness
        $exists = Database::scalar('SELECT id FROM ' . Database::t('users') . ' WHERE username = ? OR email = ?', [$username, $email]);
        if ($exists) {
            flash('error', 'Jina la mtumiaji au barua pepe tayari lipo. Jaribu jingine.');
            Response::redirect('/register');
        }

        Database::beginTransaction();
        try {
            // 1. Create group
            $group_id = Database::insert('groups', [
                'name'          => $group_name,
                'region'        => $region,
                'district'      => $district,
                'founding_date' => $founding_date ?: null,
                'currency'      => $currency,
                'status'        => 'active',
            ]);

            // 2. Create user
            $user_id = Database::insert('users', [
                'username'     => $username,
                'email'        => $email,
                'password'     => Auth::hashPassword($password),
                'display_name' => $full_name,
                'role'         => 'group_admin',
                'group_id'     => $group_id,
                'status'       => 'active',
            ]);

            // 3. Create member record
            $member_number = 'M-' . strtoupper(substr(preg_replace('/[^a-z0-9]/i','', $group_name), 0, 3)) . '-001';
            Database::insert('members', [
                'group_id'    => $group_id,
                'user_id'     => $user_id,
                'member_number'=> $member_number,
                'full_name'   => $full_name,
                'phone'       => $phone,
                'email'       => $email,
                'role'        => 'group_admin',
                'status'      => 'active',
                'joined_date' => today(),
            ]);

            // 4. Seed default fine types
            $fine_types = [
                ['group_id' => $group_id, 'name' => 'Kutokuhudhuria Mkutano', 'amount' => 2000],
                ['group_id' => $group_id, 'name' => 'Kuchelewa Mkutano',      'amount' => 1000],
                ['group_id' => $group_id, 'name' => 'Kutolipa Hisa kwa Wakati', 'amount' => 1000],
                ['group_id' => $group_id, 'name' => 'Tabia Mbaya Mktanoni',   'amount' => 5000],
            ];
            foreach ($fine_types as $ft) {
                Database::insert('fine_types', $ft);
            }

            Database::commit();

            // Log in the new user
            $new_user = Database::get('SELECT * FROM ' . Database::t('users') . ' WHERE id = ?', [$user_id]);
            Auth::login($new_user);

            flash('success', "Karibu! Kikundi '{$group_name}' kimesajiliwa kikamilifu.");
            Response::redirect('/dashboard/overview');

        } catch (\Throwable $e) {
            Database::rollback();
            error_log('Registration error: ' . $e->getMessage());
            flash('error', 'Hitilafu imetokea wakati wa usajili. Tafadhali jaribu tena.');
            Response::redirect('/register');
        }
    }
}
