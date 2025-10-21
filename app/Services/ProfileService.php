<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\AppConfig;
use App\Repositories\UserRepository;
use App\Support\Helpers;
use InvalidArgumentException;

final class ProfileService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function updateProfile(int $userId, array $input): array
    {
        $data = [];
        if (isset($input['name'])) {
            $data['name'] = trim($input['name']);
        }
        if (isset($input['bio'])) {
            $bio = trim($input['bio']);
            if (mb_strlen($bio) > 2800) {
                throw new InvalidArgumentException('Biyografi 2800 karakteri geçemez.');
            }
            $data['bio'] = $bio;
        }
        if (isset($input['profile_color']) && $input['profile_color'] !== '') {
            $color = strtoupper($input['profile_color']);
            if (!preg_match('/^#[0-9A-F]{6}$/', $color)) {
                throw new InvalidArgumentException('Profil rengi #RRGGBB formatında olmalı.');
            }
            $data['profile_color'] = $color;
        }
        if (isset($input['questions_public'])) {
            $data['questions_public'] = (int) $input['questions_public'] === 1 ? 1 : 0;
        }
        if (isset($input['social_links'])) {
            $data['social_links'] = $this->validateSocialLinks($input['social_links']);
        }
        $data['updated_at'] = Helpers::now();
        $this->users->update($userId, $data);
        return $this->users->findById($userId);
    }

    public function changeUsername(array $user, string $newUsername): array
    {
        $newUsername = strtolower(trim($newUsername));
        if (!preg_match('/^[a-z0-9_]{3,32}$/', $newUsername)) {
            throw new InvalidArgumentException('Geçerli bir kullanıcı adı gir.');
        }

        $auth = new AuthService();
        $can = $auth->canChangeUsername($user);
        if (!$can['allowed']) {
            throw new InvalidArgumentException('Kullanıcı adını değiştirmek için ' . $can['remaining_days'] . ' gün daha beklemelisin.');
        }

        if ($this->users->findByEmailOrUsername($newUsername)) {
            throw new InvalidArgumentException('Bu kullanıcı adı zaten alınmış.');
        }

        $now = Helpers::now();
        $this->users->update((int) $user['id'], [
            'username' => $newUsername,
            'username_changed_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->users->findById((int) $user['id']);
    }

    private function validateSocialLinks(array $links): string
    {
        $allowedHosts = AppConfig::array('ALLOWED_SOCIALS');
        $clean = [];
        foreach ($links as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $url = trim($value);
            $parts = parse_url($url);
            if (!isset($parts['scheme'], $parts['host'])) {
                throw new InvalidArgumentException('Geçerli bir URL gir: ' . $url);
            }
            $host = strtolower($parts['host']);
            if (!in_array($host, $allowedHosts, true)) {
                throw new InvalidArgumentException('İzin verilmeyen alan adı: ' . $host);
            }
            $clean[$key] = $url;
        }

        return json_encode($clean, JSON_THROW_ON_ERROR);
    }
}
