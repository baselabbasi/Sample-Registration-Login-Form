<?php declare(strict_types=1);

session_start();  // strict types: no automatic type conversion

function e($s): string  // e() escapes text for safe HTML output
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($to)  // redirect() sends the browser to another page using a Location header.
{
    header("Location: $to");
    exit;
}

define('USERS_FILE', dirname(__DIR__) . '/data/user.json');
define('STORAGE_ROOT', dirname(__DIR__) . '/storage/users');

function ensure_env(): void
{
    if (!is_dir(STORAGE_ROOT)) {
        mkdir(STORAGE_ROOT, 0775, true);
    }

    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, '[]');
    }
}

ensure_env();

function flash_set(string $type, string $msg): void  //  flash_set() stores a one-time message in the session
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array  // flash_get() reads it then deletes it.
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function load_users(): array
{
    $json = file_get_contents(USERS_FILE) ?: '[]';
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_users($users): void
{
    file_put_contents(
        USERS_FILE,
        json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function find_user_by_email(string $email): ?array
{
    $email = strtolower(trim($email));
    foreach (load_users() as $user) {
        if (strtolower($user['email'] ?? '') === $email) {
            return $user;
        }
        
    }
    return null;
}

function find_user_by_username(string $username): ?array
{
    $username = strtolower(trim($username));
    foreach (load_users() as $u) {
        if (strtolower((string) ($u['username'] ?? '')) === $username)
            return $u;
    }
    return null;
}

function add_user($user): void
{
    $users = load_users();
    $users[] = $user;
    save_users($users);
}



function email_ok(string $email): bool
{
    return (bool) preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i', $email);
}

function password_ok_msg(string $pass): string
{
    $len = strlen($pass);
    if ($len < 8 || $len > 20)
        return 'Password must be 8–20 characters.';
    if (!preg_match('/[a-z]/', $pass))
        return 'Password needs a lowercase letter.';
    if (!preg_match('/[A-Z]/', $pass))
        return 'Password needs an uppercase letter.';
    if (!preg_match('/\d/', $pass))
        return 'Password needs a number.';
    if (!preg_match('/[!@#$%^&*()_\-+=\[\]{};:\'",.<>\/?\\\\|`~]/', $pass))
        return 'Password needs a special character.';
    if (preg_match('/\s/', $pass))
        return 'Password must not contain spaces.';
    return '';
}

function validate_register(array $post): array
{
    $errors = [];

    $username = trim((string) ($post['username'] ?? ''));
    $password = (string) ($post['password'] ?? '');
    $name = trim((string) ($post['name'] ?? $post['Name'] ?? ''));
    $zipCode = trim((string) ($post['zipCode'] ?? $post['ZipCode'] ?? ''));
    $country = trim((string) ($post['country'] ?? ''));
    $email = strtolower(trim((string) ($post['email'] ?? '')));
    $sex = trim((string) ($post['sex'] ?? ''));
    $langs = $post['language'] ?? $post['languages'] ?? [];
    if (!is_array($langs))
        $langs = [];

    if (strlen($username) < 5 || strlen($username) > 12) {
        $errors['username'] = 'Username must be 5–12 chars.';
    }

    $pmsg = password_ok_msg($password);
    if ($pmsg !== '')
        $errors['password'] = $pmsg;

    if ($name === '' || !preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errors['name'] = 'Name: letters & spaces only.';
    }

    if ($country === '') {
        $errors['country'] = 'Please select a country.';
    }

    if ($zipCode === '' || !preg_match('/^\d+$/', $zipCode)) {
        $errors['zipCode'] = 'Zip: digits only.';
    }

    if ($email === '' || !email_ok($email)) {
        $errors['email'] = 'Enter a valid email.';
    }

    if ($sex === '') {
        $errors['sex'] = 'Required.';
    }

    if (count($langs) === 0) {
        $errors['language'] = 'Required.';
    }

    return $errors;
}

function validate_login(array $post): array
{
    $errors = [];

    $email = strtolower(trim((string) ($post['email'] ?? '')));
    $password = (string) ($post['password'] ?? '');
    $agreement = isset($post['agreement']) ? 1 : 0;

    if ($email === '' || !email_ok($email))
        $errors['login'] = 'Email not correct.';
    if ($password === '')
        $errors['login'] = 'Password not correct.';
    if (!$agreement)
        $errors['agreement'] = 'You must agree to the terms.';

    return $errors;
}

function require_login(): void
{
    if (empty($_SESSION['user']))
        redirect('Login.php');
}

function current_user(): array
{
    return $_SESSION['user'] ?? [];
}
