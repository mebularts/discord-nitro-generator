<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Support\Helpers;
use App\Support\Session;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function register(string $email, string $username, string $password): array
    {
        $email = strtolower(trim($email));
        $username = strtolower(trim($username));
        $this->validateRegistration($email, $username, $password);

        if ($this->users->findByEmailOrUsername($email) || $this->users->findByEmailOrUsername($username)) {
            throw new RuntimeException('Bu e-posta veya kullanıcı adı zaten kullanılıyor.');
        }

        $now = Helpers::now();
        $userId = $this->users->create([
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => $now,
            'updated_at' => $now,
            'questions_public' => 1,
        ]);

        $user = $this->users->findById($userId);
        $this->storeSession($user);

        return $user;
    }

    public function login(string $identifier, string $password): array
    {
        $user = $this->users->findByEmailOrUsername(trim($identifier));
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Geçersiz kimlik bilgileri.');
        }

        Session::regenerate();
        $this->storeSession($user);
        return $user;
    }

    public function logout(): void
    {
        Session::flush();
    }

    private function storeSession(array $user): void
    {
        unset($user['password_hash']);
        Session::put('user', $user);
    }

    private function validateRegistration(string $email, string $username, string $password): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Geçerli bir e-posta gir.');
        }
        if (!preg_match('/^[a-z0-9_]{3,32}$/', $username)) {
            throw new InvalidArgumentException('Kullanıcı adı yalnızca harf, rakam ve alt çizgi içerebilir.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Parola en az 8 karakter olmalı.');
        }
    }

    public function canChangeUsername(array $user): array
    {
        $lastChanged = $user['username_changed_at'] ?? null;
        if ($lastChanged === null) {
            return ['allowed' => true, 'remaining_days' => 0];
        }
        $last = new DateTimeImmutable($lastChanged);
        $next = $last->add(new DateInterval('P40D'));
        $now = new DateTimeImmutable();
        if ($now >= $next) {
            return ['allowed' => true, 'remaining_days' => 0];
        }
        $remaining = $now->diff($next);
        return ['allowed' => false, 'remaining_days' => (int) $remaining->format('%a')];
    }
}
