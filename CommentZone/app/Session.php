<?php

namespace app;

class Session
{
  public static function start()
  {
    return @session_start();
  }
}
