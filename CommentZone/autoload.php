<?php

function commentScriptAutoload($className)
{
  $path = str_replace('\\', '/', __DIR__ . '/' . $className . '.php');
  if (is_file($path)) {
    include_once $path;
  }
}

spl_autoload_register('commentScriptAutoload');
