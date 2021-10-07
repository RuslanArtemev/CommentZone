<?php

namespace controller;

use app\App;
use app\Helper;
use model\Comment;
use model\User;

class CommentController
{
  private $url;
  private $bindId;

  public function __construct($url = null, $bindId = null)
  {
    $this->url = !empty($url) ? $url : $_SERVER['REQUEST_URI'];
    $this->bindId = !empty($bindId) ? $bindId : 0;
  }

  public function index()
  {
    $App = new App();
    $User = new User();
    $config = $App::config('common');
    $language = $App::config('language/' . $config['language']);
    $emoji = $App::config('emoji');

    $Comment = new Comment();
    $comments = $Comment->getViewByPage($this->url, $this->bindId, $config['sort'], 0);
    $count = $Comment->getCount($this->url, $this->bindId);

    return $App->render('view/theme/' . $App::config('common', 'theme') . '/index', array(
      'comments' => $comments,
      'count' => $count,
      'url' => $this->url,
      'bindId' => $this->bindId,
      'sort' => $config['sort'],
      'user' => isset($_SESSION['cz_user']) ? $User->infoView($_SESSION['cz_user']) : array(),
      'emoji' => $emoji,
      'currentTime' => time(),
      'csrf' => $_SESSION['Cz-Csrf'][0],
      'language' => $language,
      'config' => array(
        'api_path' => $config['api_path'],
        'guest' => $config['guest'],
        'anonimus' => $config['anonimus'],
        'folder' => $config['folder'],
        'theme' => $config['theme'],
        'resource' => $config['resource'],
        'authorize' => $config['authorize'],
        'guest_email_required' => $config['guest_email_required'],
        'nameReserve' => require dirname(__DIR__) . "/config/nameReserve.php",
        'delete_method' => $config['delete_method'],
        'limit' => (int)$config['limit'],
        'avatarSimbol' => $config['avatarSimbol'],
        'text_length' => $config['text_length'],
        'report_length' => $config['report_length'],
        'images' => $config['images'],
        'video' => $config['video'],
        'emoji' => $config['emoji'],
        'rating' => $config['rating'],
        'edit' => $config['edit'],
        'editTime' => $config['editTime'],
        'minName' => $config['minName'],
        'maxName' => $config['maxName'],
        'minPass' => $config['minPass'],
        'maxPass' => $config['maxPass'],
        'vkAuthUrl' => $config['vk'] ? 'https://oauth.vk.com/authorize?client_id=' . $config['vkClientId'] . '&redirect_uri=' . Helper::getHost() . $config['vkRedirectUri'] . '?provider=' . $config['vkProvider'] . '&scope=email&display=popup' : '',
        'okAuthUrl' => $config['ok'] ? 'http://www.odnoklassniki.ru/oauth/authorize?client_id=' . $config['okClientId'] . '&redirect_uri=' . Helper::getHost() . $config['okRedirectUri'] . '?provider=' . $config['okProvider'] . '&response_type=code' : '',
        'fbAuthUrl' => $config['fb'] ? 'https://www.facebook.com/dialog/oauth?client_id=' . $config['fbClientId'] . '&redirect_uri=' . Helper::getHost() . $config['fbRedirectUri'] . '?provider=' . $config['fbProvider'] . '&scope=email' : '',
        'gooAuthUrl' => $config['goo'] ? 'https://accounts.google.com/o/oauth2/v2/auth?client_id=' . $config['gooClientId'] . '&redirect_uri=' . Helper::getHost() . $config['gooRedirectUri'] . '?provider=' . $config['gooProvider'] . '&scope=https://www.googleapis.com/auth/userinfo.email%20https://www.googleapis.com/auth/userinfo.profile&response_type=code' : '',
        'mailAuthUrl' => $config['mail'] ? 'https://connect.mail.ru/oauth/authorize?client_id=' . $config['mailClientId'] . '&redirect_uri=' . Helper::getHost() . $config['mailRedirectUri'] . '?provider=' . $config['mailProvider'] . '&response_type=code' : '',
        'yaAuthUrl' => $config['ya'] ? 'https://oauth.yandex.ru/authorize?client_id=' . $config['yaClientId'] . '&redirect_uri=' . Helper::getHost() . $config['yaRedirectUri'] . '?provider=' . $config['yaProvider'] . '&response_type=code' : '',
        'recaptcha' => $config['recaptcha'],
        'recaptchaVersion' => $config['recaptchaVersion'],
        'recaptchaKeyV3' => $config['recaptchaKeyV3'],
        'recaptchaKeyV2' => $config['recaptchaKeyV2'],
      ),
    ));
  }
}
