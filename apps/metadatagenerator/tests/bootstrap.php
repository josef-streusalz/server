<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../tests/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

\OC_App::loadApp(OCA\MetaDataGenerator\AppInfo\Application::APP_ID);
OC_Hook::clear();
