<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/../../app/helpers.php';
must_login();
require_once __DIR__.'/layout.php';
