<?php

namespace model;

use app\App;

class Model
{
  protected $preffix;
  protected $config;

  public function __construct()
  {
    $this->config = App::config('common');
    $this->language = App::config('language/' . $this->config['language']);
    $this->prefix = App::config('db', 'prefix');
  }
}
