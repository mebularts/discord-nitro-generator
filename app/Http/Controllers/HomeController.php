<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Support\Helpers;
use App\Http\Request;

final class HomeController extends Controller
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function index(Request $request): void
    {
        $page = max(1, (int) ($request->input('page', 1)));
        $perPage = 20;
        $feed = $this->users->paginatePublicAnswers($page, $perPage);
        $this->view('feed/index', [
            'title' => 'SolveClone',
            'feed' => $feed,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
