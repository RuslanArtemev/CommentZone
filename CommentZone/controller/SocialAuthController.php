<?php

namespace controller;

use app\App;
use app\Helper;
use model\User;

class SocialAuthController
{
  private $config;
  private $language;

  public function __construct()
  {
    $this->config = App::config('common');
    $this->language = App::config('language/' . $this->config['language']);
  }

  /**
   * VK
   */
  public function vk($request)
  {
    if (!isset($request['code'])) {
      exit('Error');
    }

    $provider = $request['provider'];
    $code = $request['code'];

    $params = array(
      'client_id' => $this->config['vkClientId'],
      'client_secret' => $this->config['vkClientSecret'],
      'redirect_uri' => Helper::getHost() . $this->config['vkRedirectUri'] . '?provider=' . $this->config['vkProvider'],
      'code' => $code,
    );

    $user = $this->curl('https://oauth.vk.com/access_token', $params);

    if (!isset($user['access_token'])) {
      exit('Error');
    }

    $params = array(
      'uids' => $user['user_id'],
      'access_token' => $user['access_token'],
      'fields' => $this->config['vkFields'],
      'v' => $this->config['vkApiVersion'],
    );

    $userData = $this->curl('https://api.vk.com/method/users.get', $params);

    if (!isset($userData['response'][0]['id'])) {
      exit('Error');
    }

    $userDataArr = array(
      'sid' => $userData['response'][0]['id'],
      'name' => $userData['response'][0]['first_name'] . ' ' . $userData['response'][0]['last_name'],
      'email' => $user['email'],
      'avatar' => $userData['response'][0]['photo_50'],
      'link' => 'https://vk.com/' . $userData['response'][0]['screen_name'],
      'provider' => $provider
    );

    $User = new User();
    $App = new App();

    $socAuth = $User->socialAuth($userDataArr);

    echo $App->render('view/oauth/index', array(
      'user' => $userDataArr,
      'socAuth' => $socAuth === true ? 1 : $socAuth,
      'language' => $this->language,
      'csrf' => $_SESSION['Cz-Csrf'][3],
      'config' => array(
        'api_path' => $this->config['api_path'],
        'minPass' => $this->config['minPass'],
        'maxPass' => $this->config['maxPass'],
      )
    ));
  }

  /**
   * OK
   */
  public function ok($request)
  {
    if (!isset($request['code'])) {
      exit('Error');
    }

    $provider = $request['provider'];
    $code = $request['code'];

    $params = array(
      'client_id' => $this->config['okClientId'],
      'client_secret' => $this->config['okClientSecret'],
      'redirect_uri' => Helper::getHost() . $this->config['okRedirectUri'] . '?provider=' . $this->config['okProvider'],
      'code' => $code,
      'grant_type' => 'authorization_code',
    );

    $user = $this->curl('https://api.odnoklassniki.ru/oauth/token.do', urldecode(http_build_query($params)));

    if (!isset($user['access_token'])) {
      exit('Error');
    }

    $params = array(
      'application_key' => $this->config['okPublicKey'],
      'format' => 'json',
      'method' => 'users.getCurrentUser',
      'access_token' => $user['access_token'],
      'sig' => md5('application_key=' . $this->config['okPublicKey'] . 'format=jsonmethod=users.getCurrentUser' . md5($user['access_token'] . $this->config['okClientSecret'])),
    );

    $userData = $this->curl('https://api.odnoklassniki.ru/fb.do', urldecode(http_build_query($params)));

    if (!isset($userData['uid'])) {
      exit('Error');
    }

    $userDataArr = array(
      'sid' => $userData['uid'],
      'name' => $userData['name'],
      'email' => '',
      'avatar' => $userData['pic_1'],
      'link' => 'https://ok.ru/profile/' . $userData['uid'],
      'provider' => $provider
    );

    $User = new User();
    $App = new App();

    $socAuth = $User->socialAuth($userDataArr);

    echo $App->render('view/oauth/index', array(
      'user' => $userDataArr,
      'socAuth' => $socAuth === true ? 1 : $socAuth,
      'language' => $this->language,
      'csrf' => $_SESSION['Cz-Csrf'][3],
      'config' => array(
        'api_path' => $this->config['api_path'],
        'minPass' => $this->config['minPass'],
        'maxPass' => $this->config['maxPass'],
      )
    ));
  }

  /**
   * Facebook
   */
  public function fb($request)
  {
    if (!isset($request['code'])) {
      exit('Error');
    }

    $provider = $request['provider'];
    $code = $request['code'];

    $params = array(
      'client_id' => $this->config['fbClientId'],
      'client_secret' => $this->config['fbClientSecret'],
      'redirect_uri' => Helper::getHost() . $this->config['fbRedirectUri'] . '?provider=' . $this->config['fbProvider'],
      'code' => $code,
    );

    $user = $this->curl('https://graph.facebook.com/oauth/access_token', $params);

    if (!isset($user['access_token'])) {
      exit('Error');
    }

    $params = array(
      'access_token' => $user['access_token'],
      'fields' => $this->config['fbFields'],
    );

    $userData = $this->curl('https://graph.facebook.com/me', $params);

    if (!isset($userData['id'])) {
      exit('Error');
    }

    $userDataArr = array(
      'sid' => $userData['id'],
      'name' => $userData['name'],
      'email' => $userData['email'],
      'avatar' => 'https://graph.facebook.com/' . $userData['id'] . '/picture?type=large',
      'link' => '',
      'provider' => $provider
    );

    $User = new User();
    $App = new App();

    $socAuth = $User->socialAuth($userDataArr);

    echo $App->render('view/oauth/index', array(
      'user' => $userDataArr,
      'socAuth' => $socAuth === true ? 1 : $socAuth,
      'language' => $this->language,
      'csrf' => $_SESSION['Cz-Csrf'][3],
      'config' => array(
        'api_path' => $this->config['api_path'],
        'minPass' => $this->config['minPass'],
        'maxPass' => $this->config['maxPass'],
      )
    ));
  }

  /**
   * Google
   */
  public function goo($request)
  {
    if (!isset($request['code'])) {
      exit('Error');
    }

    $provider = $request['provider'];
    $code = $request['code'];

    $params = array(
      'client_id' => $this->config['gooClientId'],
      'client_secret' => $this->config['gooClientSecret'],
      'redirect_uri' => Helper::getHost() . $this->config['gooRedirectUri'] . '?provider=' . $this->config['gooProvider'],
      'code' => $code,
      'grant_type' => 'authorization_code',
    );

    $user = $this->curl('https://accounts.google.com/o/oauth2/token', $params);

    if (!isset($user['access_token'])) {
      exit('Error');
    }

    $params = array(
      'access_token' => $user['access_token'],
    );

    $userData = $this->curl('https://www.googleapis.com/oauth2/v1/userinfo?' . urldecode(http_build_query($params)));

    if (!isset($userData['id'])) {
      exit('Error');
    }

    $userDataArr = array(
      'sid' => $userData['id'],
      'name' => $userData['name'],
      'email' => $userData['email'],
      'avatar' => $userData['picture'],
      'link' => $userData['link'],
      'provider' => $provider
    );

    $User = new User();
    $App = new App();

    $socAuth = $User->socialAuth($userDataArr);

    echo $App->render('view/oauth/index', array(
      'user' => $userDataArr,
      'socAuth' => $socAuth === true ? 1 : $socAuth,
      'language' => $this->language,
      'csrf' => $_SESSION['Cz-Csrf'][3],
      'config' => array(
        'api_path' => $this->config['api_path'],
        'minPass' => $this->config['minPass'],
        'maxPass' => $this->config['maxPass'],
      )
    ));
  }

  /**
   * Mail
   */
  public function mail($request)
  {
    if (!isset($request['code'])) {
      exit('Error');
    }

    $provider = $request['provider'];
    $code = $request['code'];

    $params = array(
      'client_id' => $this->config['mailClientId'],
      'client_secret' => $this->config['mailClientSecret'],
      'redirect_uri' => Helper::getHost() . $this->config['mailRedirectUri'] . '?provider=' . $this->config['mailProvider'],
      'code' => $code,
      'grant_type' => 'authorization_code',
    );

    $user = $this->curl('https://connect.mail.ru/oauth/token', $params);

    if (!isset($user['access_token'])) {
      exit('Error');
    }

    $params = array(
      'session_key' => $user['access_token'],
      'method' => 'users.getInfo',
      'app_id' => $this->config['mailClientId'],
      'secure' => '1',
      'uids' => $user['x_mailru_vid'],
      'sig' => md5("app_id=" . $this->config['mailClientId'] . "method=users.getInfosecure=1session_key=" . $user['access_token'] . "uids=" . $user['x_mailru_vid'] . $this->config['mailClientSecret']),
    );

    $userData = $this->curl('http://www.appsmail.ru/platform/api', $params);

    if (!isset($userData[0]['uid'])) {
      exit('Error');
    }

    $userDataArr = array(
      'sid' => $userData[0]['uid'],
      'name' => $userData[0]['nick'],
      'email' => $userData[0]['email'],
      'avatar' => str_replace('http://', 'https://', $userData[0]['pic_small']),
      'link' => $userData[0]['link'],
      'provider' => $provider
    );

    $User = new User();
    $App = new App();

    $socAuth = $User->socialAuth($userDataArr);

    echo $App->render('view/oauth/index', array(
      'user' => $userDataArr,
      'socAuth' => $socAuth === true ? 1 : $socAuth,
      'language' => $this->language,
      'csrf' => $_SESSION['Cz-Csrf'][3],
      'config' => array(
        'api_path' => $this->config['api_path'],
        'minPass' => $this->config['minPass'],
        'maxPass' => $this->config['maxPass'],
      )
    ));
  }

  /**
   * Yandex
   */
  public function ya($request)
  {
    if (!isset($request['code'])) {
      exit('Error');
    }

    $provider = $request['provider'];
    $code = $request['code'];

    $params = array(
      'client_id' => $this->config['yaClientId'],
      'client_secret' => $this->config['yaClientSecret'],
      'redirect_uri' => Helper::getHost() . $this->config['yaRedirectUri'] . '?provider=' . $this->config['yaProvider'],
      'code' => $code,
      'grant_type' => 'authorization_code',
    );

    $user = $this->curl('https://oauth.yandex.ru/token', $params);

    if (!isset($user['access_token'])) {
      exit('Error');
    }

    $params = array(
      'oauth_token' => $user['access_token'],
      'format' => 'json',
    );

    $userData = $this->curl('https://login.yandex.ru/info', $params);

    if (!isset($userData['id'])) {
      exit('Error');
    }

    $userDataArr = array(
      'sid' => $userData['id'],
      'name' => $userData['real_name'],
      'email' => $userData['default_email'],
      'avatar' => 'https://avatars.mds.yandex.net/get-yapic/' . $userData['default_avatar_id'] . '/islands-middle',
      'link' => '',
      'provider' => $provider
    );

    $User = new User();
    $App = new App();

    $socAuth = $User->socialAuth($userDataArr);

    echo $App->render('view/oauth/index', array(
      'user' => $userDataArr,
      'socAuth' => $socAuth === true ? 1 : $socAuth,
      'language' => $this->language,
      'csrf' => $_SESSION['Cz-Csrf'][3],
      'config' => array(
        'api_path' => $this->config['api_path'],
        'minPass' => $this->config['minPass'],
        'maxPass' => $this->config['maxPass'],
      )
    ));
  }

  protected function curl($url, $params = null)
  {
    $curl = curl_init();

    if ($params === null) {
      curl_setopt($curl, CURLOPT_URL, $url);
    } else {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, TRUE);
  }
}
