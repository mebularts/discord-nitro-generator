<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Request;
use App\Repositories\UserRepository;

final class FeedController extends Controller
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function home(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
        $feed = $this->users->paginatePublicAnswers($page, $perPage);
        $this->json([
            'success' => true,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $feed['total'],
            'items' => $feed['items'],
        ]);
    }
}
