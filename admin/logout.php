<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

logout_admin();
redirect('/admin/login.php');
