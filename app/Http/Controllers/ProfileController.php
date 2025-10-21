<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Services\AnswerService;
use App\Services\ProfileService;
use App\Http\Request;
use App\Support\Helpers;
use App\Support\Session;
use InvalidArgumentException;

final class ProfileController extends Controller
{
    private UserRepository $users;
    private ProfileService $profiles;
    private AnswerService $answers;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->profiles = new ProfileService();
        $this->answers = new AnswerService();
    }

    public function show(Request $request, string $username): void
    {
        $user = $this->users->findByEmailOrUsername($username);
        if (!$user) {
            Helpers::abort(404, 'Profil bulunamadı.');
        }
        $page = max(1, (int) $request->input('page', 1));
        $sort = in_array($request->input('sort', 'newest'), ['newest', 'popular'], true) ? $request->input('sort', 'newest') : 'newest';
        $answers = $this->answers->publicAnswers((int) $user['id'], $page, 20, $sort);
        $this->view('profile/show', [
            'title' => $user['username'] . ' profili',
            'profile' => $user,
            'answers' => $answers,
            'page' => $page,
            'sort' => $sort,
        ]);
    }

    public function settings(): void
    {
        $user = $this->requireAuth();
        $canChange = (new \App\Services\AuthService())->canChangeUsername($user);
        $this->view('settings/index', [
            'title' => 'Ayarlar',
            'user' => $user,
            'username_change' => $canChange,
        ]);
    }

    public function update(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        try {
            $updated = $this->profiles->updateProfile((int) $user['id'], $request->all());
            Session::put('user', $updated);
            $this->redirect('/settings?success=1');
        } catch (InvalidArgumentException $exception) {
            $this->view('settings/index', [
                'title' => 'Ayarlar',
                'user' => $user,
                'error' => $exception->getMessage(),
                'username_change' => (new \App\Services\AuthService())->canChangeUsername($user),
            ]);
        }
    }

    public function changeUsername(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        try {
            $updated = $this->profiles->changeUsername($user, (string) $request->input('new_username'));
            Session::put('user', $updated);
            $this->redirect('/settings?username_updated=1');
        } catch (InvalidArgumentException $exception) {
            $this->view('settings/index', [
                'title' => 'Ayarlar',
                'user' => $user,
                'error' => $exception->getMessage(),
                'username_change' => (new \App\Services\AuthService())->canChangeUsername($user),
            ]);
        }
    }
}
