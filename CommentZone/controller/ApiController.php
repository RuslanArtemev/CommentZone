<?php

namespace controller;

use app\App;
use app\Helper;
use app\Session;
use model\Comment;
use model\User;
use app\Image;
use app\SendMail;
use model\Pages;

class ApiController
{
  private $currentUser;

  public function __construct()
  {
  }

  /**
   * Request validate
   *
   * @param  string $action
   * @return mixed string or bool
   */
  public function requestValidate($action)
  {
    $methodsAllowedNoAuthorize = array(
      'getCount', 'getComments', 'userAuth', 'adminAuth', 'guestAuth', 'registration', 'logout', 'uploadAvatar',
      'oauthReg', 'oauthLogin', 'getConfig', 'recaptchaVerify', 'resetPassword', 'resetCodeVerify', 'newPassword',
      'getCountList',
    );

    $methodsAllowedAnonim = array(
      'setMainComment', 'setAnswerComment',
    );

    $User = new User();

    if (!in_array($action, $methodsAllowedNoAuthorize)) {
      if (!Helper::csrfTokenApprove()) {
        header("HTTP/1.0 403 Csrf Protection");
        return false;
      }

      $authorize = false;

      $ip = $User->getIp();
      if ($User->existsBanIp($ip)) {
        return 'ban-ip';
      }

      if (!$User->checkAuthorize()) {
        $config = App::config('common');

        if ($config['anonimus'] === true && in_array($action, $methodsAllowedAnonim)) {
          $uid = $User->generateGuest();
          if ($uid > 0) {
            $authorize = true;
          }
        }
      } else {
        $uid = $_SESSION['czuid'];
        $authorize = true;
      }

      if ($authorize) {
        $this->currentUser = $User->getInfo($uid);

        if ($this->currentUser['deleted'] === 1) {
          $User->logout();
          $authorize = false;
        }

        if ($this->currentUser['banned']) {
          return 'banned';
        }

        if (!empty($this->currentUser)) {
          if ($this->currentUser['role'] === 'anonim') {
            $User->authorize($uid, Helper::strGen(32), false);
          }
        } else $authorize = false;
      }

      if (!$authorize) {
        return 'no_authorize';
      }
    }

    return true;
  }

  /**
   * Reset password
   *
   * @param  object $post
   * @return mixed
   */
  public function resetPassword($post)
  {
    $email = trim($post->email);

    if (empty($email)) {
      return 'empty-email';
    }

    $User = new User();

    if (!$User->existsEmail($email)) {
      return 'email-not-exists';
    }

    $code = Helper::intGen(6);

    while ($User->resetCodeExists($code)) {
      $code = Helper::intGen(6);
    }

    $userId = $User->getUidByEmail($email);

    $save = $User->resetCodeSave($userId, $code);

    if (!$save) {
      return 'error_save_code';
    }

    $SendMail = new SendMail();

    if (!$SendMail->sendCodeReset($code, $email)) {
      return 'error_send_code';
    }

    return json_encode(true);
  }

  /**
   * Reset code verify for reset password
   *
   * @param  object $post
   * @return mixed
   */
  public function resetCodeVerify($post)
  {
    $email = trim($post->email);
    $code = trim($post->code);

    if (empty($email)) {
      return 'empty-email';
    }

    if (empty($code)) {
      return 'empty-code';
    }

    $User = new User();
    $userId = $User->getUidByEmail($email);

    if ($userId === null) {
      return 'error-email';
    }

    $resetCode = $User->getResetCode($userId, $code);

    if (empty($resetCode)) {
      return 'error-code';
    }

    if (strtotime($resetCode['date']) + (10 * 60) < time()) {
      $User->deleteResetCode($userId, $code);
      return 'time-over';
    }

    return json_encode(true);
  }

  /**
   * New password
   *
   * @param  object $post
   * @return mixed
   */
  public function newPassword($post)
  {
    $email = trim($post->email);
    $code = trim($post->code);
    $password = trim($post->password);
    $password_repeat = trim($post->password_repeat);

    if (empty($email)) {
      return 'empty-email';
    }

    if (empty($code)) {
      return 'empty-code';
    }

    if (empty($password)) {
      return 'empty-password';
    }

    if ($password !== $password_repeat) {
      return 'password_mismatch';
    }

    $User = new User();
    $userId = $User->getUidByEmail($email);

    if ($userId === null) {
      return 'error-email';
    }

    $resetCode = $User->getResetCode($userId, $code);

    if (empty($resetCode)) {
      return 'error-code';
    }

    if (strtotime($resetCode['date']) + (10 * 60) < time()) {
      return 'time-over';
    }

    $User->deleteResetCode($userId, $code);

    $config = App::config('common');
    if (empty($password) || !preg_match("/^[\w%*)?@#$~]+$/iu", $password) || $password !== $password_repeat || mb_strlen($password) < $config['minPass'] || mb_strlen($password) > $config['maxPass']) {
      return 'error-password';
    }

    $salt = Helper::passwordSalt($password);
    $passCrypt = Helper::passwordCrypt($password, $salt);

    $passwordParams = array(
      'password' => $passCrypt,
      'salt' => $salt,
      'date_update' => date('Y-m-d H:i:s', time()),
    );

    $passUpdate = $User->updateRegistration($userId, $passwordParams);

    if (!$passUpdate) {
      return json_encode('error-update');
    }

    return json_encode(true);
  }

  /**
   * Recaptcha verify
   *
   * @param  object $post
   * @return mixed
   */
  public function recaptchaVerify($post)
  {
    $User = new User();
    $recaptchaVerify = Helper::recaptchaVerify($post->recaptchaVersion, $post->recaptchaToken, $User->getIp());

    return json_encode($recaptchaVerify);
  }

  /**
   * Oauth login
   *
   * @param  object $post
   * @return string|false
   */
  public function oauthLogin($post)
  {
    $sid = $post->data->sid;
    $name = $post->data->name;
    $email = trim($post->data->email);
    $password = trim($post->data->password1);
    $link = $post->data->link;
    $provider = $post->data->provider;
    $remember = false;

    if (empty($email) || empty($password)) {
      return 'error-input-data';
    }

    $User = new User();

    if (!$User->existsEmail($email)) {
      return 'email-not-exists';
    }

    $person = $User->getByAuthorize($email, $password);

    if (!$person['id']) {
      return 'error-data-authorize';
    }

    $createSocial = $User->createSocial(array(
      'uid' => $person['id'],
      'sid' => $sid,
      'name' => $name,
      'email' => $email,
      'link' => $link,
      'provider' => $provider,
    ));

    if (!$createSocial) {
      return 'error-social-auth';
    }

    $token = Helper::strGen(32);

    $User->authorize($person['id'], $token, $remember);

    if (!$User->checkAuthorize()) {
      return json_encode('dont-authorize');
    }

    Session::start();

    return json_encode(array(
      'auth' => true,
      'user' => $_SESSION['cz_user'],
    ));
  }

  /**
   * oAuth registration
   *
   * @param  object $post
   * @return string|false
   */
  public function oauthReg($post)
  {
    $sid = $post->data->sid;
    $name = $post->data->name;
    $email = trim($post->data->email);
    $password1 = trim($post->data->password1);
    $password2 = trim($post->data->password2);
    $avatar = isset($post->data->avatar) ? $post->data->avatar : '';
    $link = $post->data->link;
    $provider = $post->data->provider;

    $App = new App();
    $config = $App->config('common');

    if (empty($email) || !preg_match("/^[^@]+@[^@\.]+\.[^@\.]+$/iu", $email)) {
      return 'error-email';
    }

    if (empty($password1) || !preg_match("/^[\w%*)?@#$~]+$/iu", $password1) || $password1 !== $password2 || mb_strlen($password1) < $config['minPass'] || mb_strlen($name) > $config['maxPass']) {
      return 'error-password';
    }

    $User = new User();

    if ($User->existsEmail($email)) {
      return 'email-exists';
    }

    if (!empty($avatar)) {
      $userFolder = md5($email . $name);

      if (!empty($avatar)) {
        $Image = new Image();
        $avatar = array(
          'small' => $Image->name(md5($avatar) . '-small')->uploadLink($userFolder, $avatar),
        );
      }
    }

    $uid = $User->createUser(array(
      'puid' => $User->generatePuid(),
      'email' => $email,
      'name' => $name,
      'avatar' => !empty($avatar) ? json_encode($avatar) : '',
      'role' => 'user',
    ));

    if ($uid === 0) {
      return json_encode('error-uid');
    }

    $salt = Helper::passwordSalt($password1);
    $passCrypt = Helper::passwordCrypt($password1, $salt);

    $authId = $User->createRegistration(array(
      'uid' => $uid,
      'login' => '',
      'password' => $passCrypt,
      'salt' => $salt,
      'date_update' => date('Y-m-d H:i:s', time()),
    ));

    $createSocial = $User->createSocial(array(
      'uid' => $uid,
      'sid' => $sid,
      'name' => $name,
      'email' => $email,
      'link' => $link,
      'provider' => $provider,
    ));

    if ($authId === 0 || !$createSocial) {
      return json_encode('error-auth');
    }

    $User->authorize($uid, Helper::strGen(32), false);

    if (!$User->checkAuthorize()) {
      return json_encode('no-authorize');
    }

    Session::start();

    return json_encode(array(
      'auth' => true,
      'user' => $_SESSION['cz_user'],
    ));
  }

  /**
   * Get pages
   *
   * @param  object $post
   * @return string|false
   */
  public function getPages($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Pages = new Pages();
    $pages = $Pages->getAll(array(
      'limit' => $post->limit,
      'listId' => $post->listId,
      'filters' => $post->filters,
    ));

    return json_encode($pages);
  }

  /**
   * Get page
   *
   * @param  object $post
   * @return string|false
   */
  public function getPage($post)
  {
    $Pages = new Pages();
    $page = $Pages->getById($post->id);

    return json_encode($page);
  }

  /**
   * Set page
   *
   * @param  object $post
   * @return string|false
   */
  public function setPage($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Pages = new Pages();
    $page = $Pages->set($post->id, $post->set);

    return json_encode($page);
  }

  /**
   * Get pages count
   *
   * @param  object $post
   * @return string|false
   */
  public function getPagesCount($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Pages = new Pages();
    $pagesCount = $Pages->getCount(array(
      'filters' => $post->filters,
    ));

    return json_encode($pagesCount);
  }

  /**
   * Delete pages
   *
   * @param  object $post
   * @return string|false
   */
  public function deletePages($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Pages = new Pages();
    $delete = $Pages->delete($post->idsList);

    return json_encode($delete);
  }

  /**
   * Move comments
   *
   * @param  object $post
   * @return string|false
   */
  public function moveComments($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    if (empty($post->toId)) {
      return 'empty-id';
    }

    if (!is_int($post->toId)) {
      return 'not-number';
    }

    $Comment = new Comment();
    $update = $Comment->moveToPage($post->fromIds, $post->toId);

    return json_encode($update);
  }

  /**
   * Recount comments
   *
   * @param  object $post
   * @return string|false
   */
  public function recountComments($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $recount = $Comment->recountForPages($post->idsList);

    return json_encode($recount);
  }

  /**
   * Delete spam
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteSpam($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $delete = $Comment->deleteSpamById($post->id);

    return json_encode($delete);
  }

  /**
   * Set spam
   *
   * @param  object $post
   * @return string|false
   */
  public function setSpam($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    if ($Comment->existsSpam($post->spam)) {
      return 'spam_exists';
    }

    $spam = $Comment->setSpam($post->spam);

    return json_encode($spam);
  }

  /**
   * Get count spam
   *
   * @return string|false
   */
  public function getCountSpam()
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $count = $Comment->getCountSpam();

    return json_encode($count);
  }

  /**
   * Get spam
   *
   * @param  object $post
   * @return string|false
   */
  public function getSpam($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $spam = $Comment->getSpam(array('limit' => $post->limit, 'listId' => $post->listId));

    return json_encode($spam);
  }

  /**
   * Get count ban IP
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountBanIp($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();
    $count = $User->getCountBanIp(array('filters' => $post->filters));

    return json_encode($count);
  }

  /**
   * Get ban IP
   *
   * @param  object $post
   * @return string|false
   */
  public function getBanIp($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();
    $ip = $User->getBanIp(array(
      'limit' => $post->limit,
      'listId' => $post->listId,
      'filters' => $post->filters
    ));

    return json_encode($ip);
  }

  /**
   * Set ban IP
   *
   * @param  object $post
   * @return string|false
   */
  public function setBanIp($post)
  {
    if (!in_array('manage_users', $this->currentUser['permission'])) {
      return 'permission';
    }

    $post->ip = trim($post->ip);

    if (empty($post->ip)) {
      return 'ip_empty';
    } else if (!preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $post->ip)) {
      return 'ip_incorrect';
    } else if (mb_strlen($post->note) > 255) {
      return 'long_note';
    }

    $User = new User();

    if ($User->existsBanIp($post->ip)) {
      return 'ip_exists';
    }

    $banIp = $User->banIp(array(
      array(
        'ip' => $post->ip,
        'note' => $post->note
      )
    ));

    return json_encode($banIp);
  }

  /**
   * Unban IP
   *
   * @param  object $post
   * @return string|false
   */
  public function unbanIp($post)
  {
    if (!in_array('manage_users', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();
    $unban = $User->unbanIpById($post->id);

    return json_encode($unban);
  }

  /**
   * Set rating
   *
   * @param  object $post
   * @return string|false
   */
  public function setRating($post)
  {
    if (!in_array('rating_impact', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $rating = $Comment->setRating($post->exp, $post->id, $this->currentUser['id']);

    return json_encode(array(
      'success' => true,
      'result' => $rating
    ));
  }

  /**
   * Send report
   *
   * @param  object $post
   * @return string|false
   */
  public function sendReport($post)
  {
    $Comment = new Comment();
    $compaint = $Comment->sendReport($post->cid, $this->currentUser['id'], $post->text);

    return json_encode($compaint);
  }

  /**
   * Get reports
   *
   * @param  object $post
   * @return string|false
   */
  public function getReports($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $reports = $Comment->getReports(array(
      'idComment' => $post->idComment,
      'limit' => $post->limit,
      'listId' => $post->listId,
    ));

    return json_encode($reports);
  }

  /**
   * Read report
   *
   * @param  object $post
   * @return string|false
   */
  public function readReport($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $read = $Comment->readReport($post->id);

    return json_encode($read);
  }

  /**
   * Read all report
   *
   * @param  object $post
   * @return string|false
   */
  public function readAllReport($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $read = $Comment->readAllReport($post->cid);

    return json_encode($read);
  }

  /**
   * Get count reports
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountReports($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $count = $Comment->getCountReports(isset($post->cid) ? $post->cid : '');

    return json_encode($count);
  }

  /**
   * Delete report by ID
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteReport($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $reports = $Comment->deleteReport($post->id);

    return json_encode($reports);
  }

  /**
   * Delete reports by comment id (cid)
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteReports($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $reports = $Comment->deleteReports($post->cid);

    return json_encode($reports);
  }

  /**
   * Get comments for pages
   *
   * @param  object $post
   * @return string|false
   */
  public function getComments($post)
  {
    $Comment = new Comment();

    $comments = $Comment->getViewByPage($post->url, $post->bindId, $post->sort, $post->listId);

    return json_encode($comments);
  }

  /**
   * Get comment by ID
   *
   * @param  object $post
   * @return string|false
   */
  public function getCommentById($post)
  {
    $Comment = new Comment();

    $comment = $Comment->getViewById($post->id);

    return json_encode($comment);
  }

  /**
   * Get comments for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getCommentsPanel($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $comments = $Comment->getAll(array(
      'limit' => $post->limit,
      'listId' => $post->listId,
      'page' => $post->page,
      'bindId' => $post->bindId,
      'posted' => $post->posted,
      'moderation' => $post->moderation,
      'new' => $post->new,
      'reports' => $post->reports
    ));

    return json_encode($comments);
  }

  /**
   * Get comments chain for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getCommentsChain($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $comments = $Comment->getAll(array(
      'limit' => $post->limit,
      'listId' => $post->listId,
      'id' => $post->id,
      'sort' => $post->sort,
    ));

    return json_encode($comments);
  }

  /**
   * Get comments for panel by user ID
   *
   * @param  object $post
   * @return string|false
   */
  public function getCommentsUser($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $comments = $Comment->getAll(array(
      'limit' => $post->limit,
      'listId' => $post->listId,
      'uid' => $post->uid,
      'page' => $post->page,
      'bindId' => $post->bindId,
      'posted' => $post->posted,
      'moderation' => $post->moderation,
      'new' => $post->new,
      'reports' => $post->reports
    ));

    return json_encode($comments);
  }

  /**
   * Get count comments
   *
   * @param  object $post
   * @return string|false
   */
  public function getCount($post)
  {
    $Comment = new Comment();

    $count = $Comment->getCount($post->url, $post->bindId);

    return json_encode($count);
  }

  /**
   * Get count comments list
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountList($post)
  {
    $Comment = new Comment();

    $count = $Comment->getCountList($post->params);

    return json_encode($count);
  }

  /**
   * Get roles for panel
   *
   * @return string|false
   */
  public function getRoles()
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $roles = $User->getRoles();

    return json_encode($roles);
  }

  /**
   * Update roles
   *
   * @param  object $post
   * @return string|false
   */
  public function updateRoles($post)
  {
    if (!in_array('manage_role', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $update = $User->updateRoles($post->roles);

    return json_encode($update);
  }

  /**
   * Get permissions for panel
   *
   * @return string|false
   */
  public function getPermissions()
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $permissions = $User->getPermissions();

    return json_encode($permissions);
  }

  /**
   * Update permissions
   *
   * @param  object $post
   * @return string|false
   */
  public function updatePermissions($post)
  {
    if (!in_array('manage_role', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $update = $User->updatePermissions($post->permissions);

    return json_encode($update);
  }

  /**
   * Get languages for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getLanguages($post)
  {
    if ($this->currentUser['role'] !== 'admin') {
      return 'permission';
    }

    $config = App::config('common');

    $pathLanguages = dirname(__DIR__) . '/config/language';
    $languagesDir = scandir($pathLanguages);
    $languageList = array_splice($languagesDir, 2);

    $languageList = array_map(function ($a) {
      return str_replace('.php', '', $a);
    }, $languageList);

    $languages = array();
    foreach ($languageList as $value) {
      $languages[$value] = App::config('language/' . $value);
    }

    return json_encode(array(
      'languageName' => $config['language'],
      'languages' => $languages,
    ));
  }

  /**
   * Update Languages
   *
   * @param  object $post
   * @return string|false
   */
  public function setLanguage($post)
  {
    if ($this->currentUser['role'] !== 'admin') {
      return 'permission';
    }

    if (isset($post->deleteItems) && !empty($post->deleteItems)) {
      $pathDir = dirname(__DIR__) . '/config/language/';
      foreach ($post->deleteItems as $value) {
        if (file_exists($pathDir . $value . '.php')) {
          unlink($pathDir . $value . '.php');
        }
      }
    }

    $update = false;
    foreach ($post->languages as $key => $value) {
      $data = '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export((array) $value, true) . ';';
      $pahtConfig = dirname(__DIR__) . '/config/language/' . $key . '.php';

      $update = file_put_contents($pahtConfig, $data);

      if (!$update) {
        break;
      }
    }

    return json_encode(!$update ? false : true);
  }

  /**
   * Get count comments for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountCommentsPanel($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $count = $Comment->getCountForPanel(array(
      'page' => $post->page,
      'bindId' => $post->bindId,
      'posted' => $post->posted,
      'moderation' => $post->moderation,
      'new' => $post->new,
      'reports' => $post->reports,
    ));

    return json_encode($count);
  }

  /**
   * Get count comments for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountCommentsChain($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $count = $Comment->getCountForPanel(array(
      'id' => $post->id,
    ));

    return json_encode($count);
  }

  /**
   * Get count comments for panel by user ID
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountCommentsUser($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $count = $Comment->getCountForPanel(array(
      'uid' => $post->uid,
      'page' => $post->page,
      'bindId' => $post->bindId,
      'posted' => $post->posted,
      'moderation' => $post->moderation,
      'new' => $post->new,
      'reports' => $post->reports
    ));

    return json_encode($count);
  }

  /**
   * Get count users
   *
   * @param  object $post
   * @return string|false
   */
  public function getCountUsers($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $count = $User->getCount(array('filters' => $post->filters));

    return json_encode($count);
  }

  /**
   * Get profile for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getProfile($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $currentUser = $User->getInfo($post->id);
    $currentUser['signin'] = $User->getSignIn($post->id);

    return json_encode($currentUser);
  }

  /**
   * Get users for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getUsers($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $users = $User->getAll(array(
      'limit' => $post->limit,
      'listId' => $post->listId,
      'filters' => $post->filters
    ));

    return json_encode($users);
  }

  /**
   * Delete selected users
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteUsers($post)
  {
    if (!in_array('manage_users', $this->currentUser['permission'])) {
      return 'permission';
    }

    $post->listId = array_diff($post->listId, array(''));

    $idList = array();

    $User = new User();

    $users = $User->getByIdList($post->listId);

    if (!empty($users)) {
      foreach ($users as $user) {
        if (!in_array('admin_panel_access', $user['permission'])) {
          $idList[] = $user['id'];
        }
      }
    } else {
      return 'no_exists';
    }

    if (empty($idList)) {
      return 'permission';
    }

    $delete = $User->delete($idList, $post->removeComments);

    return json_encode($delete);
  }

  /**
   * Remove selected users from DB
   *
   * @param  object $post
   * @return string|false
   */
  public function removeUsers($post)
  {
    if (!in_array('remove_user', $this->currentUser['permission'])) {
      return 'permission';
    }

    $idList = array();

    $User = new User();

    $users = $User->getByIdList($post->listId);

    if (!empty($users)) {
      foreach ($users as $user) {
        if (!in_array('admin_panel_access', $user['permission'])) {
          $idList[] = $user['id'];
        }
      }
    }

    if (empty($idList)) {
      return 'permission';
    }

    $delete = $User->remove($idList, $post->removeComments);

    return json_encode($delete);
  }

  /**
   * Recover selected users
   *
   * @param  object $post
   * @return string|false
   */
  public function recoverUsers($post)
  {
    if (!in_array('manage_users', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    $recover = $User->recover($post->listId);
    return json_encode($recover);
  }

  /**
   * Ban users
   *
   * @param  object $post
   * @return string|false
   */
  public function banUser($post)
  {
    if (!in_array('manage_users', $this->currentUser['permission'])) {
      return 'permission';
    }

    $idList = array();
    $ipList = array();

    $User = new User();

    $users = $User->getByIdList($post->idList);

    if (!empty($users)) {
      foreach ($users as $user) {
        if (!in_array('admin_panel_access', $user['permission'])) {
          $idList[] = $user['id'];
          $ipList[] = array(
            'ip' => $user['ip'],
            'note' => $post->banParams->note
          );
        }
      }
    }

    if (empty($idList)) {
      return 'permission';
    }

    $datetime = null;

    if (count($post->idList) === 0) {
      return false;
    }

    if (!$post->banParams->permanent && !$post->banParams->countDays && !$post->banParams->date) {
      return false;
    }

    if ($post->banParams->countDays && !is_int($post->banParams->countDays) && !$post->banParams->date) {
      return false;
    }

    if ($post->banParams->date) {
      $datetime = strtotime($post->banParams->date);
    }

    if ($post->banParams->countDays) {
      $datetime = time() + (60 * 60 * 24 * $post->banParams->countDays);
    }

    if ($post->banParams->permanent) {
      $datetime = null;
    }

    $usersUpdate = $User->ban($idList, $datetime, $post->banParams->note);

    if ($post->banParams->banIp) {
      $User->banIp($ipList);
    }

    return json_encode($usersUpdate);
  }

  /**
   * Unban users
   *
   * @param  object $post
   * @return string|false
   */
  public function unbanUser($post)
  {
    if (!in_array('manage_users', $this->currentUser['permission'])) {
      return 'permission';
    }

    if (count($post->idList) === 0) {
      return false;
    }

    $User = new User();

    $usersUpdate = $User->unban($post->idList);

    return json_encode($usersUpdate);
  }

  /**
   * Get emoji from panel setting
   *
   * @param object $post
   * @return string|false
   */
  public function getEmoji($post)
  {
    $emoji = require_once(dirname(__DIR__) . "/config/emoji.php");

    if (file_exists(dirname(__DIR__) . "/config/emojiView.php")) {
      $emojiView = require_once(dirname(__DIR__) . "/config/emojiView.php");
      if (!is_array($emojiView)) {
        $emojiView = array();
      }
    } else {
      $emojiView = array();
    }

    $data = array(
      'emoji' => $emoji,
      'emojiView' => $emojiView
    );

    return json_encode($data);
  }

  /**
   * Save selected emoji to a file
   *
   * @param object $post
   * @return bool
   */
  public function setEmoji($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }
    
    $emji = [];

    if (!empty($post->data)) {
      foreach ($post->data as $key => $value) {
        $emji[$key] = (array) $value;
      }
    }
    

    $data = '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($emji, true) . ';';
    $pahtConfig = dirname(__DIR__) . '/config/emojiView.php';
    $result = file_put_contents($pahtConfig, $data);

    return json_encode(!$result ? false : true);
  }

  /**
   * Get setting for panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getSetting($post)
  {
    $data = array();
    $config = App::config('common');

    $pathTheme = dirname(__DIR__) . '/view/theme';
    $themeDir = scandir($pathTheme);
    $themes = array_splice($themeDir, 2);

    $pathLanguage = dirname(__DIR__) . '/config/language';
    $languageDir = scandir($pathLanguage);
    $language = array_splice($languageDir, 2);

    $language = array_map(function ($a) {
      return str_replace('.php', '', $a);
    }, $language);

    $data = array(
      'themes' => $themes,
      'language' => $language,
      'setting' => $config
    );

    return json_encode($data);
  }

  /**
   * Save setting
   *
   * @param  object $post
   * @return string|false
   */
  public function setSetting($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $config = App::config('common');

    foreach ($post->setting as $key => $setting) {
      $config[$key] = $setting;
    }

    $data = '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($config, true) . ';';
    $pahtConfig = dirname(__DIR__) . '/config/common.php';
    $result = file_put_contents($pahtConfig, $data);

    return json_encode(!$result ? false : true);
  }

  /**
   * Get config
   *
   * @param  object $post
   * @return string|false
   */
  public function getConfig($post)
  {
    $config = App::config('common');
    $emoji = App::config($config['emojiFileName']);
    $language = App::config('language/' . $config['language']);

    return json_encode(array(
      'api_path' => $config['api_path'],
      'anonimus' => $config['anonimus'],
      'folder' => $config['folder'],
      'resource' => $config['resource'],
      'tmpFolder' => $config['tmpFolder'],
      'guest_email_required' => $config['guest_email_required'],
      'delete_method' => $config['delete_method'],
      'avatarSimbol' => $config['avatarSimbol'],
      'edit' => $config['edit'],
      'editTime' => $config['editTime'],
      'currentTime' => time(),
      'emojiView' => $emoji,
      'language' => $language,
    ));
  }

  /**
   * Get current user
   *
   * @param  object $post
   * @return string|false
   */
  public function getCurrentUser($post)
  {
    if (in_array('admin_panel_access', $this->currentUser['permission'])) {
      return json_encode($this->currentUser);
    } else {
      return 'no_authorize';
    }
  }

  /**
   * Set profile
   *
   * @param  object $post
   * @return string|false
   */
  public function setProfile($post)
  {
    if (!in_array('manage_profile', $this->currentUser['permission'])) {
      return 'permission';
    }

    $User = new User();

    if (!empty($post->set->avatar)) {
      $Image = new Image();

      $user = $User->getInfo($post->uid);

      $userFolder = md5($post->uid . $user['puid']);
      $post->set->avatar->long = $Image->moveInStorage($userFolder, $post->set->avatar->long);
      $post->set->avatar->middle = $Image->moveInStorage($userFolder, $post->set->avatar->middle);
      $post->set->avatar->small = $Image->moveInStorage($userFolder, $post->set->avatar->small);
      $post->set->avatar = json_encode($post->set->avatar);
    }

    $update = $User->setProfile($post->uid, $post->set);

    if (isset($post->password) && !empty($post->password)) {
      $salt = Helper::passwordSalt($post->password);
      $passCrypt = Helper::passwordCrypt($post->password, $salt);

      $authSet = array(
        'login' => '',
        'password' => $passCrypt,
        'salt' => $salt,
        'date_update' => date('Y-m-d H:i:s', time()),
      );

      $User->updateRegistration($post->uid, $authSet);
    }

    if ($update && !empty($post->prevAvatar)) {
      $App = new App();
      $Image = new Image();

      $resource = $App->config('common', 'resource');

      $delete = $Image->deleteImage($_SERVER['DOCUMENT_ROOT'] . '/' . $resource . '/' . $post->prevAvatar->long);
      if ($delete) {
        $delete = $Image->deleteImage($_SERVER['DOCUMENT_ROOT'] . '/' . $resource . '/' . $post->prevAvatar->middle);
        if ($delete) {
          $delete = $Image->deleteImage($_SERVER['DOCUMENT_ROOT'] . '/' . $resource . '/' . $post->prevAvatar->small);
        }
      }
    }

    return json_encode($update);
  }

  /**
   * Get stop words
   *
   * @param  object $post
   * @return string|false
   */
  public function getStopWords($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $words = $Comment->getStopWords();

    return json_encode($words);
  }

  /**
   * Set Stop-Words
   *
   * @param  object $post
   * @return string|false
   */
  public function setStopWords($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $insertId = $Comment->setStopWords($post->word);

    return json_encode($insertId);
  }

  /**
   * Delete Stop-Words
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteStopWords($post)
  {
    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $delete = $Comment->deleteStopWords($post->id);

    return json_encode($delete);
  }

  /**
   * Write main comment
   *
   * @param  object $post
   * @return string|false
   */
  public function setMainComment($post)
  {
    if (!in_array('create_comment', $this->currentUser['permission'])) {
      return 'permission';
    }

    $post->text = trim($post->text);
    $text = isset($post->text) ? $post->text : "";
    $attach = isset($post->attach) ? $post->attach : null;

    if (empty($text) && empty($attach)) {
      return 'empty-text';
    }

    if (mb_strlen($text) > App::config('common', 'text_length')) {
      return 'limit-text';
    }

    $config = App::config('common');
    $Comment = new Comment();

    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      $stopStr = $Comment->checkStopWords($text);

      if ($stopStr) {
        return json_encode($stopStr);
      }

      $spam = $Comment->checkSpam($text, $this->currentUser['id']);

      if ($spam) {
        return json_encode(array(
          'status' => 'spam',
        ));
      }

      if ($config['antiflood']) {
        $antiflood = $Comment->antiFlood($this->currentUser['id']);

        if ($antiflood) {
          return json_encode($antiflood);
        }
      }
    }

    $userFolder = md5($this->currentUser['id'] . $this->currentUser['puid']);

    if (!empty($attach)) {
      $attach = array_values(array_filter((array) $attach));

      $Image = new Image();

      foreach ($attach as $key => &$value) {
        if (isset($value) && isset($value->long) && isset($value->middle) && isset($value->small) && $value->type === 'image') {
          $value->long = $Image->moveInStorage($userFolder, $value->long);
          $value->middle = $Image->moveInStorage($userFolder, $value->middle);
          $value->small = $Image->moveInStorage($userFolder, $value->small);
          $value->preview = $value->small;
        }

        if (!isset($value->long) && !isset($value->middle) && !isset($value->small) && !isset($value->preview)) {
          unset($attach[$key]);
        }
      }

      $attach = array_values($attach);

      if (empty($text) && empty($attach)) {
        return json_encode(array(
          'status' => 'error-upload-images',
        ));
      }
    }

    $id = $Comment->writeMain(array(
      'url' => $post->url,
      'bindId' => (int)$post->bindId,
      'text' => $text,
      'attach' => !empty($attach) ? json_encode($attach) : null,
      'type' => 'main',
      'uid' => (int)$this->currentUser['id'],
      'title' => $post->title,
      'new' => in_array('manage_comments', $this->currentUser['permission']) ? 0 : 1,
      'moderation' => ($config['moderation'] && !in_array('manage_comments', $this->currentUser['permission'])) || ($this->currentUser['role'] === 'anonim' && $config['moderationAnonimus']) || ($this->currentUser['role'] === 'guest' && $config['moderationGuest']) ? 1 : 0,
    ));

    $main = array();
    if ($id) {
      $main = $Comment->getViewById($id);
    }

    $User = new User();
    $currentUser = $User->infoView($this->currentUser);

    $response = array(
      'status' => false,
      'currentUser' => $currentUser,
    );

    if (($config['moderation'] && !in_array('manage_comments', $this->currentUser['permission'])) || ($this->currentUser['role'] === 'anonim' && $config['moderationAnonimus']) || ($this->currentUser['role'] === 'guest' && $config['moderationGuest'])) {
      $response['status'] = 'moderation';
    } else {
      if ($config['notifyUsers'] || $config['notifyAdmin']) {
        $link = Helper::getHost() . $main['pageUrl'] . (isset($main['url_query']) ? $main['pageQuery'] : '') . '#' . $id;
        $language = App::config('language/' . $config['language']);

        $SendMail = new SendMail();
        if ($config['notifyAdmin'] && (int) $this->currentUser['id'] !== 1) {
          $admin = $User->getInfo(1);
          if (!empty($admin['email'])) {
            $SendMail->go($link, $main['authorName'], $language['email_title_new_comment'], $main['text'], $admin['email']);
          }
        }
      }

      $response['status'] = 'success';
      $response['main'] = $main;
    }

    return json_encode($response);
  }

  /**
   * Write answer comment
   *
   * @param  object $post
   * @return string|false
   */
  public function setAnswerComment($post)
  {
    if (!in_array('answer_comment', $this->currentUser['permission'])) {
      return 'permission';
    }

    $text = isset($post->text) ? trim($post->text) : "";
    $attach = isset($post->attach) ? $post->attach : null;

    if (empty($text) && empty($attach)) {
      return 'empty-text';
    }

    if (mb_strlen($text) > App::config('common', 'text_length')) {
      return 'limit-text';
    }

    $Comment = new Comment();

    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      $stopStr = $Comment->checkStopWords($text);

      if ($stopStr) {
        return json_encode($stopStr);
      }

      $spam = $Comment->checkSpam($text, $this->currentUser['id']);

      if ($spam) {
        return json_encode(array(
          'status' => 'spam',
        ));
      }

      $antiflood = $Comment->antiFlood($this->currentUser['id']);

      if ($antiflood) {
        return json_encode($antiflood);
      }
    }

    $userFolder = md5($this->currentUser['id'] . $this->currentUser['puid']);

    if (!empty($attach)) {
      $attach = array_values(array_filter((array) $attach));

      $Image = new Image();

      foreach ($attach as $key => &$value) {
        if (isset($value) && isset($value->long) && isset($value->middle) && isset($value->small) && $value->type === 'image') {
          $value->long = $Image->moveInStorage($userFolder, $value->long);
          $value->middle = $Image->moveInStorage($userFolder, $value->middle);
          $value->small = $Image->moveInStorage($userFolder, $value->small);
          $value->preview = $value->small;
        }

        if (!isset($value->long) && !isset($value->middle) && !isset($value->small) && !isset($value->preview)) {
          unset($attach[$key]);
        }
      }

      $attach = array_values($attach);

      if (empty($text) && empty($attach)) {
        return json_encode(array(
          'status' => 'error-upload-images',
        ));
      }
    }

    $config = App::config('common');

    $id = $Comment->writeAnswer(array(
      'parent' => array(
        'pid' => (int)$post->pid,
        'type' => $post->parentType,
      ),
      'url' => $post->url,
      'bindId' => (int)$post->bindId,
      'text' => $text,
      'type' => 'answer',
      'uid' => (int)$this->currentUser['id'],
      'title' => $post->title,
      'attach' => !empty($attach) ? json_encode($attach) : null,
      'new' => in_array('manage_comments', $this->currentUser['permission']) ? 0 : 1,
      'moderation' => ($config['moderation'] && !in_array('manage_comments', $this->currentUser['permission'])) || ($this->currentUser['role'] === 'anonim' && $config['moderationAnonimus']) || ($this->currentUser['role'] === 'guest' && $config['moderationGuest']) ? 1 : 0,
    ));

    $answer = array();
    if ($id) {
      $answer = $Comment->getViewById($id);
    }

    $User = new User();
    $currentUser = $User->infoView($this->currentUser);

    $response = array(
      'status' => false,
      'currentUser' => $currentUser,
    );

    if (($config['moderation'] && !in_array('manage_comments', $this->currentUser['permission'])) || ($this->currentUser['role'] === 'anonim' && $config['moderationAnonimus']) || ($this->currentUser['role'] === 'guest' && $config['moderationGuest'])) {
      $response['status'] = 'moderation';
    } else {
      if ($config['notifyUsers'] || $config['notifyAdmin']) {
        $link = Helper::getHost() . $answer['pageUrl'] . (isset($answer['url_query']) ? $answer['pageQuery'] : '') . '#' . $id;
        $language = App::config('language/' . $config['language']);
        $parentUser = $User->getByCommnetId((int) $post->pid);

        $SendMail = new SendMail();
        if ($config['notifyUsers'] && $parentUser['id'] !== $currentUser['id']) {
          if (!empty($parentUser['email'])) {
            $SendMail->go($link, $answer['authorName'], $language['email_title_answer'], $answer['text'], $parentUser['email']);
          }
        }

        if ($config['notifyAdmin'] && $this->currentUser['id'] !== 1 && $parentUser['id'] !== 1) {
          $admin = $User->getInfo(1);
          if (!empty($admin['email'])) {
            $SendMail->go($link, $answer['authorName'], $language['email_title_new_comment'], $answer['text'], $admin['email']);
          }
        }
      }

      $response['status'] = 'success';
      $response['answer'] = $answer;
    }

    return json_encode($response);
  }

  /**
   * Uodate comment
   *
   * @param  object $post
   * @return string|false
   */
  public function updateComment($post)
  {
    $config = App::config('common');
    $Comment = new Comment();
    $currentComment = $Comment->getById($post->id);

    $attach = isset($post->attach) ? $post->attach : null;

    if (((int)$this->currentUser['id'] !== (int)$currentComment['uid'] || !in_array('update_comment', $this->currentUser['permission'])) && !in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    if (!in_array('manage_comments', $this->currentUser['permission']) && (!$config['edit'] || ($config['edit'] && (time() - strtotime($post->datePublished)) >= ($config['editTime'] * 60)))) {
      return 'time-expired';
    }

    $text = isset($post->text) ? trim($post->text) : "";

    if (empty($text) && empty($attach)) {
      return 'empty-text';
    }

    if (!in_array('admin_panel_access', $this->currentUser['permission'])) {
      $stopStr = $Comment->checkStopWords($text);

      if ($stopStr) {
        return json_encode($stopStr);
      }

      $spam = $Comment->checkSpam($text, $this->currentUser['id']);

      if ($spam) {
        return json_encode(array(
          'status' => 'spam',
        ));
      }
    }

    $userFolder = md5($this->currentUser['id'] . $this->currentUser['puid']);

    if (!empty($attach)) {
      $attach = array_values(array_filter((array) $attach));

      $Image = new Image();

      foreach ($attach as $key => &$value) {
        if (isset($value) && isset($value->long) && isset($value->middle) && isset($value->small) && $value->type === 'image') {
          $value->long = $Image->moveInStorage($userFolder, $value->long);
          $value->middle = $Image->moveInStorage($userFolder, $value->middle);
          $value->small = $Image->moveInStorage($userFolder, $value->small);
          $value->preview = $value->small;
        }

        if ((!isset($value->long) && !isset($value->middle) && !isset($value->small) && !isset($value->preview)) || !isset($value->type)) {
          unset($attach[$key]);
        }
      }

      $attach = array_values($attach);

      if (empty($text) && empty($attach)) {
        return json_encode(array(
          'status' => 'error-upload-images',
        ));
      }
    }

    $update = $Comment->update(array(
      'id' => $post->id,
      'url' => $post->url,
      'bindId' => (int)$post->bindId,
      'text' => $text,
      'type' => $post->type,
      'title' => $post->title,
      'attach' => !empty($attach) ? json_encode($attach) : null,
      'new' => in_array('manage_comments', $this->currentUser['permission']) ? 0 : 1,
      'moderation' => ($config['moderation'] && !in_array('manage_comments', $this->currentUser['permission'])) || ($this->currentUser['role'] === 'anonim' && $config['moderationAnonimus']) || ($this->currentUser['role'] === 'guest' && $config['moderationGuest']) ? 1 : 0,
    ));


    $response = array(
      'status' => false,
      'text' => '',
      'attach' => array()
    );

    if ($update) {
      if (($config['moderation'] && !in_array('manage_comments', $this->currentUser['permission'])) || ($this->currentUser['role'] === 'anonim' && $config['moderationAnonimus']) || ($this->currentUser['role'] === 'guest' && $config['moderationGuest'])) {
        $response['status'] = 'moderation';
      } else {
        if ($config['notifyUsers'] || $config['notifyAdmin']) {
          $updatedComment = $Comment->getViewById($post->id);

          $link = Helper::getHost() . $post->url . '#' . $post->id;
          $language = App::config('language/' . $config['language']);
          $User = new User();

          $SendMail = new SendMail();
          if ($post->type === 'answer') {
            $parentUser = $User->getByCommnetId((int) $updatedComment['pid']);
            if ($config['notifyUsers'] && (int) $parentUser['id'] !== (int) $this->currentUser['id']) {
              if (!empty($parentUser['email'])) {
                $SendMail->go($link, $updatedComment['authorName'], $language['email_title_answer_update'], $Comment->filter($updatedComment['text']), $parentUser['email']);
              }
            }
          }

          if ($config['notifyAdmin'] && $this->currentUser['id'] !== 1) {
            $admin = $User->getInfo(1);
            if (!empty($admin['email'])) {
              $SendMail->go($link, $updatedComment['authorName'], $language['email_title_comment_update'], $Comment->filter($updatedComment['text']), $admin['email']);
            }
          }
        }

        $response['status'] = 'success';
        $response['textOrigin'] = $text;
        $response['text'] = $Comment->filter($text);
        $response['attach'] = $attach;
      }
    }

    return json_encode($response);
  }

  /**
   * Approve comment
   *
   * @param  object $post
   * @return string|false
   */
  public function approveComment($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $approve = $Comment->approve($post->id, $post->type);

    $config = App::config('common');

    if ($config['notifyUsers'] || $config['notifyAdmin']) {
      $User = new User();
      $SendMail = new SendMail();
      $approvedComment = $Comment->getViewById($post->id);
      $commentUser = $User->getInfo((int) $approvedComment['uid']);

      $link = Helper::getHost() . $post->url . '#' . $post->id;
      $language = App::config('language/' . $config['language']);

      /**
       * Send a notification to the parent about the response to his comment
       */
      if ($post->type === 'answer') {
        $parentUser = $User->getByCommnetId((int) $approvedComment['pid']);
        if ($config['notifyUsers'] && (int) $parentUser['id'] !== (int) $commentUser['id']) {
          if (!empty($parentUser['email'])) {
            $SendMail->go($link, $approvedComment['authorName'], $language['email_title_answer'], $Comment->filter($approvedComment['text']), $parentUser['email']);
          }
        }
      }

      /**
       * Send a notification of approval by moderation
       */
      if ($config['notifyUsers']) {
        if (!empty($commentUser['email'])) {
          $SendMail->go($link, $approvedComment['authorName'], $language['email_title_comment_approve'], $Comment->filter($approvedComment['text']), $commentUser['email']);
        }
      }

      /**
       * Send a notification to the admin about a new comment
       */
      if ($config['notifyAdmin'] && $this->currentUser['id'] !== 1) {
        $admin = $User->getInfo(1);
        if (!empty($admin['email'])) {
          $SendMail->go($link, $approvedComment['authorName'], $language['email_title_new_comment'], $Comment->filter($approvedComment['text']), $admin['email']);
        }
      }
    }

    return json_encode($approve);
  }

  /**
   * Read comment
   *
   * @param  object $post
   * @return string|false
   */
  public function readComment($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $approve = $Comment->read($post->id);

    return json_encode($approve);
  }

  /**
   * Delete selected comments
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteSelectedComments($post)
  {
    $ids = $post->ids;

    $Comment = new Comment();

    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $delete_method = App::config('common', 'delete_method');

    if ($delete_method === 'unposted') {
      $delete = $Comment->unpostedSelected($ids);
    }
    if ($delete_method === 'delete') {
      $delete = $Comment->deleteSelected($ids);
    }

    if (!$delete) {
      return 'error_' . $delete_method;
    }

    return json_encode(array(
      'success' => $delete
    ));
  }

  /**
   * Remove comment complete
   *
   * @param  object $post
   * @return string|false
   */
  public function removeSelectedComments($post)
  {
    $ids = $post->ids;

    $Comment = new Comment();

    if (!in_array('remove_comment', $this->currentUser['permission'])) {
      return 'permission';
    }

    $delete = $Comment->deleteSelected($ids);

    if (!$delete) {
      return 'error_remove';
    }

    return json_encode(array(
      'success' => $delete,
    ));
  }

  /**
   * Read selected comments
   *
   * @param  object $post
   * @return string|false
   */
  public function readSelectedComments($post)
  {
    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();
    $approve = $Comment->readSelected($post->ids);

    return json_encode($approve);
  }

  /**
   * Recover selected comment
   *
   * @param  object $post
   * @return string|false
   */
  public function recoverSelectedComments($post)
  {
    $ids = $post->ids;

    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $Comment = new Comment();

    $recover = $Comment->postedSelected($ids);

    if (!$recover) {
      return 'error_recover';
    }

    return json_encode(array(
      'success' => $recover,
    ));
  }

  /**
   * Delete comment
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteComment($post)
  {
    $id = $post->id;
    $type = $post->type;

    $Comment = new Comment();

    $currentComment = $Comment->getById($id);

    if (!$currentComment) {
      return 'not_exists';
    }

    if (((int)$this->currentUser['id'] !== (int)$currentComment['uid'] || !in_array('delete_comment', $this->currentUser['permission'])) && !in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $delete_method = App::config('common', 'delete_method');

    if ($delete_method === 'unposted') {
      $delete = $Comment->unposted($id, $type);
      $currentComment = $Comment->getViewById($id);
    }
    if ($delete_method === 'delete') {
      $delete = $Comment->delete($id, $type);
    }

    if (!$delete) {
      return 'error_' . $delete_method;
    }

    if (isset($post->url) || isset($post->bindId)) {
      $count = $Comment->getCount($post->url, $post->bindId);
    } else {
      $count = $Comment->getCountForPanel();
    }

    return json_encode(array(
      'delete' => $delete,
      'count' => $count,
      $type => $delete_method === 'unposted' ? $currentComment : array(),
    ));
  }

  /**
   * Remove comment complete
   *
   * @param  object $post
   * @return string|false
   */
  public function removeComment($post)
  {
    $id = $post->id;
    $type = $post->type;

    $Comment = new Comment();

    $currentComment = $Comment->getById($id);

    if (!$currentComment) {
      return 'not_exists';
    }

    if (!in_array('remove_comment', $this->currentUser['permission'])) {
      return 'permission';
    }

    $delete = $Comment->delete($id, $type);


    if (!$delete) {
      return 'error_remove';
    }

    $count = $Comment->getCountForPanel();

    return json_encode(array(
      'delete' => $delete,
      'count' => $count,
      $type => array(),
    ));
  }

  /**
   * Recover comment
   *
   * @param  object $post
   * @return string|false
   */
  public function recoverComment($post)
  {
    $id = $post->id;
    $type = $post->type;

    $Comment = new Comment();

    $currentComment = $Comment->getById($id);

    if (!$currentComment) {
      return 'not_exists';
    }

    if (!in_array('manage_comments', $this->currentUser['permission'])) {
      return 'permission';
    }

    $recover = $Comment->posted($id, $type);
    $currentComment = $Comment->getViewById($id);

    if (!$recover) {
      return 'error_recover';
    }

    // $count = $Comment->getCount($post->url, $post->bindId);

    return json_encode(array(
      'recover' => $recover,
      // 'count' => $count,
      $type => $currentComment,
    ));
  }

  /**
   * Guest authorize
   *
   * @param  object $post
   * @return string|false
   */
  public function guestAuth($post)
  {
    $App = new App();
    $config = $App->config('common');

    $name = trim($post->name);
    $email = trim($post->email);
    $remember = (bool) $post->remember;

    $colors = $App->config('avatarColor');
    $nameReserve = $App->config('nameReserve');

    if (empty($name) || !preg_match("/[\w\s\-]/iu", $name) || in_array($name, $nameReserve) || mb_strlen($name) < $config['minName'] || mb_strlen($name) > $config['maxName']) {
      return 'error-name';
    }

    if ($config['guest_email_required'] === 'on' && (empty($email) || !preg_match("/^[^@]+@[^@\.]+\.[^@\.]+$/iu", $email))) {
      return 'error-email';
    }

    $User = new User();

    // if (!$user->existsEmail($email)) {
    //   return 'email-not-exists';
    // }

    Session::start();

    $uid = $User->createUser(array(
      'puid' => $User->generatePuid(),
      'email' => $email,
      'name' => $name,
      'avatar' => $colors[rand(0, count($colors) - 1)],
      'role' => 'guest',
    ));

    if ($uid > 0) {
      $User->authorize($uid, Helper::strGen(32), $remember);
    }

    $result = array(
      'auth' => false,
      'user' => array(),
    );

    if ($User->checkAuthorize()) {
      $result = array(
        'auth' => true,
        'user' => $_SESSION['cz_user'],
      );
    }

    return json_encode($result);
  }

  /**
   * Admin auth
   *
   * @param  object $post
   * @return string|false
   */
  public function adminAuth($post)
  {
    $post->type = 'admin';

    return $this->userAuth($post);
  }

  /**
   * User authorize
   *
   * @param  object $post
   * @return string|false
   */
  public function userAuth($post)
  {
    $email = trim($post->email);
    $password = trim($post->password);
    $remember = (bool) $post->remember;

    if (empty($email) || empty($password)) {
      return 'error-input-data';
    }

    $User = new User();
    $person = $User->getByAuthorize($email, $password);

    if (!$person['id']) {
      return 'error-data-authorize';
    }

    if (isset($post->type) && $post->type === 'admin' && !in_array('admin_panel_access', $person['permission'])) {
      return 'permission';
    }

    $token = Helper::strGen(32);

    $User->authorize($person['id'], $token, $remember);

    if (!$User->checkAuthorize()) {
      return json_encode('dont-authorize');
    }

    Session::start();

    return json_encode(array(
      'auth' => true,
      'user' => $_SESSION['cz_user'],
    ));
  }

  /**
   * User registration
   *
   * @param  object $post
   * @return string|false
   */
  public function registration($post)
  {
    $App = new App();
    $config = $App->config('common');

    $name = trim($post->name);
    $email = trim($post->email);
    $password1 = trim($post->password1);
    $password2 = trim($post->password2);
    $avatar = isset($post->avatar) ? $post->avatar : '';

    $nameReserve = $App->config('nameReserve');

    if (empty($name) || !preg_match("/[\w\s\-]/iu", $name) || in_array($name, $nameReserve) || mb_strlen($name) < $config['minName'] || mb_strlen($name) > $config['maxName']) {
      return 'error-name';
    }

    if (empty($email) || !preg_match("/^[^@]+@[^@\.]+\.[^@\.]+$/iu", $email)) {
      return 'error-email';
    }

    if (empty($password1) || !preg_match("/^[\w%*)?@#$~]+$/iu", $password1) || $password1 !== $password2 || mb_strlen($password1) < $config['minPass'] || mb_strlen($password1) > $config['maxPass']) {
      return 'error-password';
    }

    $User = new User();

    if ($User->existsEmail($email)) {
      return 'email-exists';
    }

    if (!empty($avatar)) {
      $userFolder = md5($email . $name);
      $Image = new Image();
      foreach ($avatar as $key => &$value) {
        $value = $Image->moveInStorage($userFolder, $value);
      }
    }

    $uid = $User->createUser(array(
      'puid' => $User->generatePuid(),
      'email' => $email,
      'name' => $name,
      'avatar' => !empty($avatar) ? json_encode($avatar) : '',
      'role' => 'user',
    ));

    if ($uid === 0) {
      return json_encode('error-uid');
    }

    $salt = Helper::passwordSalt($password1);
    $passCrypt = Helper::passwordCrypt($password1, $salt);

    $authorizeParams = array(
      'uid' => $uid,
      'login' => '',
      'password' => $passCrypt,
      'salt' => $salt,
      'date_update' => date('Y-m-d H:i:s', time()),
    );

    $authId = $User->createRegistration($authorizeParams);

    if ($authId === 0) {
      return json_encode('error-auth');
    }

    $User->authorize($uid, Helper::strGen(32), false);

    if (!$User->checkAuthorize()) {
      return json_encode('no-authorize');
    }

    Session::start();

    return json_encode(array(
      'auth' => true,
      'user' => $_SESSION['cz_user'],
    ));
  }

  /**
   * Upload avatar for registration
   *
   * @param  object $post
   * @return string|false
   */
  public function uploadAvatar($post)
  {
    $file = $post->__files->file;

    $App = new App();

    $config = $App->config('common');

    $sizeKb = $file['size'] / 1024;

    if ($sizeKb > $config['avatarSize']) {
      return json_encode(false);
    }

    $size = array(
      'long' => explode(',', $config['avatarSizeLong']),
      'middle' => explode(',', $config['avatarSizeMiddle']),
      'small' => explode(',', $config['avatarSizeSmall']),
    );

    $Image = new Image();

    $images = array(
      'long' => $Image->name(md5($file['tmp_name']) . '-long')->size($size['long'][0], $size['long'][1])->upload($file['tmp_name']),
      'middle' => $Image->name(md5($file['tmp_name']) . '-middle')->size($size['middle'][0], $size['middle'][1])->upload($file['tmp_name']),
      'small' => $Image->name(md5($file['tmp_name']) . '-small')->size($size['small'][0], $size['small'][1])->upload($file['tmp_name']),
    );

    if (!$images['small']) {
      return json_encode(false);
    }

    return json_encode($images);
  }

  /**
   * Upload images for comment
   *
   * @param  object $post
   * @return string|false
   */
  public function uploadImages($post)
  {
    $file = $post->__files->file;

    $App = new App();

    $config = $App->config('common');

    $sizeKb = $file['size'] / 1024;

    if ($sizeKb > $config['uploadImageSize']) {
      return json_encode('limit_size');
    }

    $size = array(
      'long' => explode(',', $config['uploadImageLongSize']),
      'middle' => explode(',', $config['uploadImageMiddleSize']),
      'small' => explode(',', $config['uploadImageSmallSize']),
    );

    $Image = new Image();

    $images = array(
      'long' => $Image->name(md5($file['tmp_name']) . '-long')->size($size['long'][0], $size['long'][1])->upload($file['tmp_name']),
      'middle' => $Image->name(md5($file['tmp_name']) . '-middle')->size($size['middle'][0], $size['middle'][1])->upload($file['tmp_name']),
      'small' => $Image->name(md5($file['tmp_name']) . '-small')->size($size['small'][0], $size['small'][1])->upload($file['tmp_name']),
    );

    if (!$images['small']) {
      return json_encode(false);
    }

    $filesPath[] = array(
      'type' => 'image',
      'preview' => $images['small'],
      'long' => $images['long'],
      'middle' => $images['middle'],
      'small' => $images['small'],
    );

    return json_encode($filesPath);
  }

  /**
   * Delete images
   *
   * @param  object $post
   * @return string|false
   */
  public function deleteImages($post)
  {
    $attach = $post->attach;

    if (!isset($attach->long) && !isset($attach->middle) && !isset($attach->small)) {
      return json_encode(false);
    }

    $App = new App();
    $Image = new Image();

    $resource = $App->config('common', 'resource');

    $delete = $Image->deleteImage($_SERVER['DOCUMENT_ROOT'] . '/' . $resource . '/' . $attach->long);
    if ($delete) {
      $delete = $Image->deleteImage($_SERVER['DOCUMENT_ROOT'] . '/' . $resource . '/' . $attach->middle);
      if ($delete) {
        $delete = $Image->deleteImage($_SERVER['DOCUMENT_ROOT'] . '/' . $resource . '/' . $attach->small);
      }
    }

    return json_encode($delete);
  }

  /**
   * Add video
   *
   * @param  object $post
   * @return string|false
   */
  public function addVideo($post)
  {
    $link = trim($post->link);

    if (empty($link)) {
      return json_encode(false);
    }

    $videoCode = '';
    $typeVideo = '';
    if (preg_match('/youtube\.com\/watch/iu', $link)) {
      $urlQuery = parse_url($link, PHP_URL_QUERY);
      parse_str($urlQuery, $urlParams);
      $videoCode = isset($urlParams['v']) ? $urlParams['v'] : '';
      $typeVideo = 'youtube';
      $link = '//www.youtube.com/embed/' . $videoCode;
    } elseif (preg_match('/youtu\.be/iu', $link)) {
      preg_match('/^(?:https?:\/\/)?(?:www\.)?youtu\.be\/([\w-]{11})$/', $link, $matches);
      $videoCode = isset($matches[1]) ? $matches[1] : '';
      $typeVideo = 'youtube';
      $link = '//www.youtube.com/embed/' . $videoCode;
    } elseif (preg_match('/vimeo\.com/iu', $link)) {
      preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com(?:\/channels\/staffpicks|\/video)?\/([\w\/\-]+)(?:\?.*)?$/iu', $link, $matches);
      $videoCode = isset($matches[1]) ? $matches[1] : '';
      $typeVideo = 'vimeo';
      $link = '//player.vimeo.com/video/' . $videoCode;
    } elseif (preg_match('/tiktok\.com/iu', $link)) {
      preg_match('/^(?:https?:\/\/)?(?:www\.)?tiktok\.com\/(@.+)\/video\/([\w-]+?)(?:\?.*)?$/iu', $link, $matches);
      $nickname = isset($matches[1]) ? $matches[1] : '';
      $videoCode = isset($matches[2]) ? $matches[2] : '';
      $typeVideo = 'tiktok';
      $link = 'https://www.tiktok.com/embed/v2/' . $videoCode;
      $videoUrl = 'https://www.tiktok.com/' . $nickname . '/video/' . $videoCode;
    } elseif (preg_match('/<blockquote.+tiktok\.com/iu', $link)) {
      preg_match('/cite="(.+?)".+?data-video-id="([\w-]+?)"/iu', $link, $matches);
      $videoUrl = isset($matches[1]) ? $matches[1] : '';
      $videoCode = isset($matches[2]) ? $matches[2] : '';
      $typeVideo = 'tiktok';
      $link = 'https://www.tiktok.com/embed/v2/' . $videoCode;
    }

    if (empty($videoCode)) return json_encode(false);

    $imagePreviewLink = '';
    if ($typeVideo == 'youtube') {
      $imagePreviewLink = 'https://i.ytimg.com/vi/' . $videoCode . '/hqdefault.jpg';
    } elseif ($typeVideo == 'vimeo') {
      $dataUrl = file_get_contents('https://vimeo.com/api/oembed.json?url=https%3A//vimeo.com/' . $videoCode);
      $dataUrl = json_decode($dataUrl);
      $imagePreviewLink = isset($dataUrl->thumbnail_url) ? $dataUrl->thumbnail_url : '';
      $videoCode = $dataUrl->video_id;
    } elseif ($typeVideo == 'tiktok') {
      $dataUrl = file_get_contents('https://www.tiktok.com/oembed?url=' . $videoUrl);
      $dataUrl = json_decode($dataUrl);
      $imagePreviewLink = isset($dataUrl->thumbnail_url) ? $dataUrl->thumbnail_url : '';

      $userFolder = md5($this->currentUser['email'] . $this->currentUser['name']);

      $Image = new Image();
      $imagePreviewLink = $Image->name(md5($imagePreviewLink))->uploadLink($userFolder, $imagePreviewLink);
    }

    return json_encode(array(
      'type' => 'video',
      'resource' => $typeVideo,
      'code' => $videoCode,
      'preview' => $imagePreviewLink,
      'link' => $link,
    ));
  }

  /**
   * logout
   *
   * @return string|false
   */
  public function logout()
  {
    $User = new User();

    $User->logout();

    return json_encode(true);
  }

  /**
   * Get comments
   *
   * @param  object $post
   * @return string|false
   */
  public function getComment($post)
  {
    if (!empty($post->url)) {
      $urlComment = $post->url;
    }
    if (!empty($post->bind_id)) {
      $bindIdComment = $post->bind_id;
    }

    require dirname(__DIR__) . '/index.php';
  }

  /**
   * Get admin panel
   *
   * @param  object $post
   * @return string|false
   */
  public function getPanel($post)
  {
    $App = new App();
    echo $App->render('view/panel/index');
  }
}
