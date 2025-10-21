<?php
declare(strict_types=1);

namespace App\Models;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use PDO;
use RuntimeException;

use function App\db;

final class User
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function updateProfile(int $userId, array $data): void
    {
        $allowedSocial = ['instagram', 'twitter', 'youtube', 'tiktok', 'github', 'linkedin'];

        $bio = trim($data['bio'] ?? '');
        $color = trim($data['profile_color'] ?? '');
        $questionsPublic = isset($data['questions_public']) ? 1 : 0;
        $social = $data['social_links'] ?? [];

        if ($color !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new RuntimeException('Geçersiz renk formatı.');
        }

        $filteredSocial = [];
        foreach ($social as $key => $value) {
            if (!in_array($key, $allowedSocial, true) || trim((string) $value) === '') {
                continue;
            }

            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('Geçersiz URL: ' . $value);
            }

            $filteredSocial[$key] = $value;
        }

        $avatarPath = $data['avatar'] ?? null;

        $stmt = db()->prepare('UPDATE users SET bio = :bio, profile_color = :profile_color, avatar = :avatar, social_links = :social_links, questions_public = :questions_public, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'bio' => $bio !== '' ? $bio : null,
            'profile_color' => $color !== '' ? $color : null,
            'avatar' => $avatarPath,
            'social_links' => $filteredSocial ? json_encode($filteredSocial, JSON_THROW_ON_ERROR) : null,
            'questions_public' => $questionsPublic,
            'id' => $userId,
        ]);
    }

    public static function toggleVisibility(int $userId, bool $visible): void
    {
        $stmt = db()->prepare('UPDATE users SET questions_public = :visible, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['visible' => $visible ? 1 : 0, 'id' => $userId]);
    }

    public static function canChangeUsername(array $user): bool
    {
        if (empty($user['username_changed_at'])) {
            return true;
        }

        $lastChange = new DateTimeImmutable($user['username_changed_at']);
        $nextAllowed = $lastChange->add(new DateInterval('P40D'));

        return $nextAllowed <= new DateTimeImmutable('now');
    }

    public static function usernameCooldown(array $user): ?DateTimeInterface
    {
        if (empty($user['username_changed_at'])) {
            return null;
        }

        $lastChange = new DateTimeImmutable($user['username_changed_at']);

        return $lastChange->add(new DateInterval('P40D'));
    }

    public static function changeUsername(int $userId, string $newUsername): void
    {
        $newUsername = trim($newUsername);
        if ($newUsername === '') {
            throw new RuntimeException('Kullanıcı adı boş olamaz.');
        }

        $stmt = db()->prepare('SELECT id FROM users WHERE username = :username AND id <> :id');
        $stmt->execute(['username' => $newUsername, 'id' => $userId]);
        if ($stmt->fetch()) {
            throw new RuntimeException('Bu kullanıcı adı zaten alınmış.');
        }

        $stmt = db()->prepare('UPDATE users SET username = :username, username_changed_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute(['username' => $newUsername, 'id' => $userId]);
    }

    public static function forAdminList(): array
    {
        $stmt = db()->query('SELECT id, email, username, name, role, created_at, questions_public FROM users ORDER BY created_at DESC LIMIT 100');

        return $stmt->fetchAll();
    }
}
