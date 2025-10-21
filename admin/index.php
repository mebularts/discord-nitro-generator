<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!admin_auth()) {
    redirect('/admin/login.php');
}

redirect('/admin/dashboard.php');
