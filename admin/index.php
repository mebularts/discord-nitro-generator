<?php
declare(strict_types=1);
session_start();
if (!empty($_SESSION['admin'])) {
    header('Location: /admin/dashboard.php');
} else {
    header('Location: /admin/login.php');
}
exit;
