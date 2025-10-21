<?php
declare(strict_types=1);

namespace App;

use DateInterval;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Throwable;

use function App\db;
use function App\ensureSessionStarted;

const LOGIN_ATTEMPT_LIMIT = 5;
const LOGIN_ATTEMPT_WINDOW = 900; // 15 dakika

function current_user(): ?array
{
    ensureSessionStarted();

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $cache = $user;

    return $user;
}

function register(string $email, string $password, string $username, string $name = '', bool $isAdmin = false): int
{
    $email = trim($email);
    $username = trim($username);
    $name = trim($name);

    if ($email === '' || $password === '' || $username === '') {
        throw new RuntimeException('Zorunlu alanlar eksik.');
    }

    $pdo = db();

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email OR username = :username');
        $stmt->execute(['email' => $email, 'username' => $username]);
        if ($stmt->fetch()) {
            throw new RuntimeException('Bu e-posta veya kullanıcı adı zaten kullanılıyor.');
        }

        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, username, role, created_at, updated_at)
            VALUES (:email, :password_hash, :name, :username, :role, NOW(), NOW())');
        $stmt->execute([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'name' => $name !== '' ? $name : $username,
            'username' => $username,
            'role' => $isAdmin ? 'admin' : 'editor',
        ]);

        $userId = (int) $pdo->lastInsertId();
        $pdo->commit();

        return $userId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function login(string $identifier, string $password): void
{
    ensureSessionStarted();

    rate_limit_check();

    $stmt = db()->prepare('SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1');
    $stmt->execute(['identifier' => $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        register_failed_attempt();
        throw new RuntimeException('Geçersiz kimlik bilgileri.');
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['last_active'] = time();
    reset_attempts();
}

function logout(): void
{
    ensureSessionStarted();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_auth(bool $adminOnly = false): array
{
    $user = current_user();
    if (!$user) {
        redirect('/login.php');
    }

    if ($adminOnly && $user['role'] !== 'admin') {
        http_response_code(403);
        exit('Bu sayfaya erişim izniniz yok.');
    }

    return $user;
}

function rate_limit_check(): void
{
    ensureSessionStarted();
    $attempts = $_SESSION['login_attempts'] ?? [];
    $attempts = array_filter($attempts, static function ($timestamp): bool {
        return $timestamp >= (time() - LOGIN_ATTEMPT_WINDOW);
    });

    if (count($attempts) >= LOGIN_ATTEMPT_LIMIT) {
        $retry = reset($attempts) + LOGIN_ATTEMPT_WINDOW;
        $seconds = max(1, $retry - time());
        throw new RuntimeException('Çok fazla başarısız giriş. Lütfen ' . $seconds . ' saniye sonra tekrar deneyin.');
    }

    $_SESSION['login_attempts'] = $attempts;
}

function register_failed_attempt(): void
{
    ensureSessionStarted();
    $attempts = $_SESSION['login_attempts'] ?? [];
    $attempts[] = time();
    $_SESSION['login_attempts'] = $attempts;
}

function reset_attempts(): void
{
    ensureSessionStarted();
    unset($_SESSION['login_attempts']);
}

function password_reset(string $email): void
{
    // Basit bir placeholder: üretimde e-posta gönderimi gerekir.
    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Bu e-posta için hesap bulunamadı.');
    }

    // Normalde token oluşturulup e-posta ile gönderilir. Burada logluyoruz.
    $token = bin2hex(random_bytes(32));
    error_log('[password_reset] token=' . $token . ' email=' . $email);
}
