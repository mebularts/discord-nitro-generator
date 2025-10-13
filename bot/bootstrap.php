<?php
require_once __DIR__ . '/../src/Support/helpers.php';

use App\Support\Config;
use App\Support\Database;
use App\Support\Env;
use App\Support\Settings;

Env::load(__DIR__ . '/../.env');

Config::load(require __DIR__ . '/../config/config.php');
Database::boot();
Settings::hydrateConfig();
