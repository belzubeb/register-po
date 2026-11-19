<?php
class Auth
{
    private static $user = false;

    public static function user()
    {
        if (self::$user === false) {
            self::$user = null;
            if (!empty($_SESSION['uid'])) {
                self::$user = DB::one(
                    'SELECT u.*, d.code AS dept_code, d.name AS dept_name FROM users u
                     LEFT JOIN departments d ON d.id = u.department_id WHERE u.id = ?',
                    [$_SESSION['uid']]
                );
            }
        }
        return self::$user;
    }

    public static function check() { return self::user() !== null; }

    public static function attempt($email, $password)
    {
        $u = DB::one('SELECT * FROM users WHERE email = ?', [$email]);
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int) $u['id'];
            self::$user = false;
            return true;
        }
        usleep(400000); // perlambat tebakan password
        return false;
    }

    public static function logout()
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) { session_destroy(); }
        self::$user = false;
    }

    public static function roleLabel($u)
    {
        $m = ['admin' => 'Admin dept', 'manager' => 'Manager lokal', 'dev' => 'Developer'];
        return isset($m[$u['role']]) ? $m[$u['role']] : $u['role'];
    }
}
