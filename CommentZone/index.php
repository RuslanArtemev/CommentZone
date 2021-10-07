<?php

use app\App;

require_once(__DIR__ . '/autoload.php');

$url = isset($urlComment) ? $urlComment : $_SERVER['REQUEST_URI'];
$bindId = isset($bindIdComment) ? $bindIdComment : '';

$app = new App();
echo $app->connectComments($url, $bindId);