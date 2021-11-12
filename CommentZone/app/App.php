<?php

namespace app;

use controller\CommentController;
use controller\PanelController;
use controller\ApiController;
use controller\InstallController;
use controller\SocialAuthController;
use app\Helper;
use model\User;

class App
{
  public $config = array();
  public static $go;

  public function __construct()
  {
  }

  public static function go()
  {
    if (!isset(self::$go)) {
      $className = __CLASS__;
      self::$go = new $className;
    }

    return self::$go;
  }

  /**
   * Install system
   */
  public function install()
  {
    if (!defined('COMMENTZONE')) {
      header("HTTP/1.1 403 Forbidden");
      return false;
    }

    $post = $this->getPost();
    $Install = new InstallController();

    if (isset($post->action) && method_exists($Install, $post->action)) {
      $method = $post->action;
      return $Install->$method($post);
    } else {
      return $Install->index();
    }
  }

  public function connectComments($url, $bindId)
  {
    if (!defined('COMMENTZONE')) {
      return false;
    }

    $User = new User();

    if (!$User->checkAuthorize()) {
      $User->cookieAuthorize();
    }

    if (!isset($_SESSION['Cz-Csrf'][0])) {
      $_SESSION['Cz-Csrf'][0] = Helper::csrfTokenGen(0);
    }

    $CommentController = new CommentController($url, $bindId);

    return $CommentController->index();
  }

  public function connectPanel()
  {
    if (!defined('COMMENTZONE')) {
      header("HTTP/1.1 403 Forbidden");
      return false;
    }

    $User = new User();

    if (!$User->checkAuthorize()) {
      $User->cookieAuthorize();
    }

    Session::start();

    if (!isset($_SESSION['Cz-Csrf'][1])) {
      $_SESSION['Cz-Csrf'][1] = Helper::csrfTokenGen(1);
    }

    $PanelController = new PanelController();

    return $PanelController->index();
  }

  public function connectApi()
  {
    if (!defined('COMMENTZONE') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
      header("HTTP/1.0 403 Forbidden");
      return false;
    }

    $post = $this->getPost();

    if (!isset($post->action)) {
      header("HTTP/1.0 404 Not Found");
      return false;
    }

    $ApiController = new ApiController();
    $requestValidate = $ApiController->requestValidate($post->action);

    if ($requestValidate !== true) {
      return $requestValidate;
    }

    if (method_exists($ApiController, $post->action)) {
      $method = $post->action;
      return $ApiController->$method($post);
    }
  }

  /**
   * Get count comments to page anons
   */
  public function countComments()
  {
    if (!defined('COMMENTZONE') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
      header("HTTP/1.0 403 Forbidden");
      return false;
    }

    $post = $this->getPost();

    $ApiController = new ApiController();

    if (method_exists($ApiController, 'getCountList')) {
      return $ApiController->getCountList($post);
    }
  }

  /**
   * Connect comments via ajax
   */
  public function connectByAjax()
  {
    if (!defined('COMMENTZONE') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
      header("HTTP/1.0 403 Forbidden");
      return false;
    }

    $post = $this->getPost();
    $urlComment = null;
    $bindIdComment = null;

    if (!empty($post->url)) {
      $urlComment = $post->url;
    }
    if (!empty($post->bind_id)) {
      $bindIdComment = $post->bind_id;
    }

    return $this->connectComments($urlComment, $bindIdComment);
  }

  /**
   * Connect to oAuth
   */
  public function connectOAuth()
  {
    if (!defined('COMMENTZONE')) {
      header("HTTP/1.1 403 Forbidden");
    }

    if (isset($_REQUEST['provider'])) {
      Session::start();

      if (!isset($_SESSION['Cz-Csrf'][3]['time']) || time() > $_SESSION['Cz-Csrf'][3]['time'] + 1) {
        $_SESSION['Cz-Csrf'][3] = Helper::csrfTokenGen(3);
      }

      $method = $_REQUEST['provider'];
      $SocialAuthController = new SocialAuthController();
      $SocialAuthController->$method($_REQUEST);
    }
  }

  /**
   * Get POST request
   */
  public function getPost()
  {
    if (isset($_SERVER['CONTENT_TYPE']) && preg_match("/multipart\/form-data|application\/x-www-form-urlencoded/", $_SERVER['CONTENT_TYPE'])) {
      $post = (object) $_POST;

      if (!empty($_FILES)) {
        $post->__files = (object) $_FILES;
      }
    } else {
      $request = file_get_contents('php://input');
      $post = json_decode($request, false, 512, JSON_BIGINT_AS_STRING);
    }

    return $post;
  }

  public static function config($path, $param = '')
  {
    self::go();

    if (!isset(self::$go->config[$path])) {
      self::$go->config[$path] = require dirname(__DIR__) . '/config/' . $path . '.php';
    }

    if (!empty($param)) {
      return self::$go->config[$path][$param];
    } else {
      return self::$go->config[$path];
    }
  }

  public function render($path, $params = array())
  {
    foreach ($params as $key => $value) {
      $$key = $value;
    }

    ob_start();

    if (file_exists(dirname(__DIR__) . '/' . $path . '.php')) {
      require_once(dirname(__DIR__) . '/' . $path . '.php');
    } elseif (file_exists(dirname(__DIR__) . '/' . $path . '.html')) {
      require_once(dirname(__DIR__) . '/' . $path . '.html');
    }

    return ob_get_clean();
  }
}
