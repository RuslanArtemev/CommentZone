<?php

namespace controller;

use app\App;

class PanelController
{
  public function index()
  {
    $App = new App();
    $config = App::config('common');
    
    return $App->render('view/panel/index', array(
      'apiPath' => $config['api_path'],
      'resource' => $config['resource'],
      'csrf' => $_SESSION['Cz-Csrf'][1],
    )); 
  }
}