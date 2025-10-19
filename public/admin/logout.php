<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/../../app/helpers.php';
unset($_SESSION['admin']);
redirect('/admin/login.php');
