<?php
declare(strict_types=1);

use function App\ensureSessionStarted;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Question.php';
require_once __DIR__ . '/models/Answer.php';
require_once __DIR__ . '/models/Notification.php';

ensureSessionStarted();
