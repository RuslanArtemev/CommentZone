<?php

require_once(dirname(__DIR__) . '/autoload.php');

use app\Helper;
use model\User;

class Sso
{
  protected $ruid;
  protected $remember;

  /**
   * @param int $ruid user ID of your site
   * @param bool $remember remember user authorization
   */
  public function __construct($ruid = 0, $remember = true)
  {
    $this->ruid = $ruid;
    $this->remember = $remember;
  }

  /**
   * @param string $name user name
   * @param string $email user email
   * @param string $avatar user avatar
   * @param string $role user role
   */
  public function login($name, $email, $avatar = '', $role = 'user')
  {
    $User = new User();
    $token = Helper::strGen(32);
    $uid = $User->getRelatedUid($this->ruid);

    if ($uid === 0 || !$User->existsId($uid)) {
      $uid = $User->createUser(array(
        'puid' => $User->generatePuid(),
        'email' => $email,
        'name' => $name,
        'avatar' => !empty($avatar) ? json_encode($avatar) : '',
        'role' => $role,
      ));

      if ($uid === 0) {
        return 'error-uid';
      }

      $User->setReletedUid($this->ruid, $uid);
    } else {
      $User->updateById($uid, array(
        'email' => $email,
        'name' => $name,
        'avatar' => !empty($avatar) ? json_encode($avatar) : '',
        'role' => $role,
      ));
    }

    $User->authorize($uid, $token, $this->remember);
  }

  /**
   * @param int $uid admin ID in the comments system
   */
  public function loginAdmin($uid = 1)
  {
    $token = Helper::strGen(32);

    $User = new User();
    // $User->updateUserToken($uid, $token);
    // $User->updateDateTime($uid, date("Y-m-d H:i:s"));
    $User->authorize($uid, $token, $this->remember);
  }

  public function logout()
  {
    $User = new User();
    $User->logout();
  }
}
