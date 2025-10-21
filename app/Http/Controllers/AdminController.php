<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Services\AdminService;
use App\Support\Helpers;

final class AdminController extends Controller
{
    private AdminService $admin;

    public function __construct()
    {
        $this->admin = new AdminService();
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $this->view('admin/dashboard', [
            'title' => 'Yönetim',
            'metrics' => $this->admin->dashboardMetrics(),
        ]);
    }

    public function settings(): void
    {
        $this->requireAdmin();
        $this->view('admin/settings', [
            'title' => 'Site Ayarları',
            'settings' => $this->admin->settings(),
        ]);
    }

    public function updateSettings(Request $request): void
    {
        $this->requireAdmin();
        $this->validateCsrf($request);
        foreach ($request->all() as $key => $value) {
            if ($key === '_token') {
                continue;
            }
            $this->admin->updateSetting($key, (string) $value);
        }
        $this->redirect('/admin/settings?success=1');
    }

    private function requireAdmin(): void
    {
        $user = $this->requireAuth();
        if (($user['role'] ?? 'user') !== 'admin') {
            Helpers::abort(403, 'Bu alana erişim iznin yok.');
        }
    }
}
