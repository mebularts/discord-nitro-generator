<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Request;
use App\Services\AuthService;
use App\Services\ProfileService;
use App\Support\Session;
use InvalidArgumentException;

final class ProfileController extends Controller
{
    private ProfileService $profiles;

    public function __construct()
    {
        $this->profiles = new ProfileService();
    }

    public function me(): void
    {
        $user = $this->requireAuth();
        $this->json(['success' => true, 'user' => $user]);
    }

    public function update(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        try {
            $updated = $this->profiles->updateProfile((int) $user['id'], $request->all());
            Session::put('user', $updated);
            $this->json(['success' => true, 'user' => $updated]);
        } catch (InvalidArgumentException $exception) {
            $this->json([
                'success' => false,
                'error_code' => 'validation_error',
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function changeUsername(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        try {
            $updated = $this->profiles->changeUsername($user, (string) $request->input('new_username'));
            Session::put('user', $updated);
            $this->json(['success' => true, 'user' => $updated]);
        } catch (InvalidArgumentException $exception) {
            $auth = new AuthService();
            $can = $auth->canChangeUsername($user);
            $this->json([
                'success' => false,
                'error_code' => 'username_not_allowed',
                'message' => $exception->getMessage(),
                'remaining_days' => $can['remaining_days'] ?? 0,
            ], 422);
        }
    }

    public function visibility(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        $updated = $this->profiles->updateProfile((int) $user['id'], [
            'questions_public' => (int) $request->input('questions_public', 1),
        ]);
        Session::put('user', $updated);
        $this->json(['success' => true, 'questions_public' => $updated['questions_public']]);
    }
}
