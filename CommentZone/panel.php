<?php

use app\App;

require_once(__DIR__ . '/autoload.php');

$app = new App();
echo $app->connectPanel();