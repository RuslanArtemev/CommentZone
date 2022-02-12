<?php

namespace model;

use app\App;
use app\Session;
use app\Helper;

class User extends Model
{
  /**
   * Check if the required session exists
   *
   * @return bool
   */
  public function checkAuthorize()
  {
    Session::start();

    if (
      isset($_SESSION['czuid']) && $_SESSION['cz_user']['deleted'] === 0 && $_SESSION['cz_user']['session_id'] === session_id() &&
      $_SESSION['cz_user']['ip'] === $this->getIp() && $_SESSION['cz_user']['agent'] === $_SERVER['HTTP_USER_AGENT']
    ) {
      return true;
    } else {
      $this->sessionUnset();
      return false;
    }
  }

  /**
   * Check user by cookie
   *
   * @return bool
   */
  public function cookieAuthorize()
  {
    if (isset($_COOKIE['czuid']) && isset($_COOKIE['czut'])) {
      $signin = DB::table($this->prefix . 'signin')
        ->select('uid', 'token')
        ->where(array(
          array('uid', $_COOKIE['czuid']),
          array('agent', $_SERVER['HTTP_USER_AGENT']),
          array('token', $_COOKIE['czut']),
        ))
        ->first();

      if ($signin->success && !empty($signin->result)) {
        // $czut = Helper::strGen(32);
        // $this->updateUserToken($data['id'], $czut);
        $this->authorize($signin->result['uid'], $signin->result['token']);
        return true;
      }
    }

    return false;
  }

  /**
   * Write authorization information to session and cookies
   *
   * @param  int $uid
   * @param  string $token
   * @param  bool $remember
   * @return void
   */
  public function authorize($uid, $token, $remember = true)
  {
    Session::start();

    if (!$this->existsSignIn($uid, $_SERVER['HTTP_USER_AGENT'])) {
      $this->createSignIn(array(
        'uid' => $uid,
        'ip' => $this->getIp(),
        'agent' => $_SERVER['HTTP_USER_AGENT'],
        'token' => $token,
        'session_id' => session_id(),
        'date_update' => date('Y-m-d H:i:s'),
      ));
    } else {
      $this->updateSignIn($uid, $_SERVER['HTTP_USER_AGENT'], array(
        'ip' => $this->getIp(),
        'token' => $token,
        'session_id' => session_id(),
        'date_update' => date('Y-m-d H:i:s'),
      ));
    }

    $_SESSION['czuid'] = $uid;
    $_SESSION['czut'] = $token;
    $_SESSION['cz_user'] = $this->getInfo($uid);

    if ($_SESSION['cz_user']['banned'] && $_SESSION['cz_user']['ban_datetime'] < time()) {
      $this->unban(array($uid));
      $this->unbanIp(array($_SESSION['cz_user']['ip']));

      $_SESSION['cz_user']['banned'] = 0;
      $_SESSION['cz_user']['ban_datetime'] = null;
    }

    if ($remember) {
      @setcookie(
        'czuid',
        $uid,
        time() + 60 * 60 * 24 * (int) App::config('common', 'cookie_time'),
        '/',
        App::config('common', 'cookie_domain'),
        isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? true : false,
        true
      );
      @setcookie(
        'czut',
        $token,
        time() + 60 * 60 * 24 * (int) App::config('common', 'cookie_time'),
        '/',
        App::config('common', 'cookie_domain'),
        isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? true : false,
        true
      );
    } else {
      @setcookie('czuid', '', time() - 3600, '/', App::config('common', 'cookie_domain'));
      @setcookie('czut', '', time() - 3600, '/', App::config('common', 'cookie_domain'));
    }
  }

  /**
   * Check exist sign in params
   *
   * @param  int $uid
   * @param  string $agent
   * @return bool
   */
  public function existsSignIn($uid, $agent)
  {
    $count = DB::table($this->prefix . 'signin')
      ->where(array(
        'uid' => $uid,
        'agent' => $agent,
      ))
      ->count();

    return $count->success && $count->result > 0 ? true : false;
  }

  /**
   * Insert sign in parsms
   *
   * @param  array $params
   * @return bool
   */
  public function createSignIn($params)
  {
    $insert = DB::table($this->prefix . 'signin')
      ->set($params)
      ->insert();

    return $insert->success && $insert->result->affected_rows > 0 ? true : false;
  }

  /**
   * Update sign in parsm
   *
   * @param  int $uid
   * @param  string $agent
   * @param  array $params
   * @return bool
   */
  public function updateSignIn($uid, $agent, $params)
  {
    $update = DB::table($this->prefix . 'signin')
      ->set($params)
      ->where(array(
        'uid' => $uid,
        'agent' => $agent
      ))
      ->update();

    return $update->success && $update->result->affected_rows !== -1 ? true : false;
  }

  /**
   * Check if the social network exists
   *
   * @param  string $sid social id
   * @param  string $provider
   * @return bool
   */
  public function existsSocial($sid, $provider)
  {
    $count = DB::table($this->prefix . 'social')
      ->where(array(
        array('sid', $sid),
        array('provider', $provider)
      ))
      ->count();

    return $count->success && $count->result > 0 ? true : false;
  }

  /**
   * Authorization via social network
   *
   * @param  array $params
   * @return true|string
   */
  public function socialAuth($params)
  {
    $social = DB::table($this->prefix . 'social')
      ->where(array(
        array('sid', $params['sid']),
        array('provider', $params['provider'])
      ))
      ->first();

    if ($social->success && !empty($social->result)) {
      $users = DB::table($this->prefix . 'users')
        ->select('id')
        ->where('id', $social->result['uid'])
        ->first();

      if ($users->success && !empty($users->result)) {
        $czut = Helper::strGen(32);
        $this->authorize($users->result['id'], $czut, false);
        return true;
      } else {
        return 'user-not-exists';
      }
    }

    if (empty($params['email'])) {
      return 'email-empty';
    }

    if ($this->existsEmail($params['email'])) {
      return 'email-exists';
    } else {
      return 'email-not-exists';
    }
  }

  /**
   * Create a social network
   *
   * @param  array $params
   * @return bool
   */
  public function createSocial($params)
  {
    $insert = DB::table($this->prefix . 'social')
      ->set($params)
      ->insert();

    return $insert->success && $insert->result->affected_rows > 0 ? true : false;
  }

  /**
   * Delete session values
   *
   * @return void
   */
  public function sessionUnset()
  {
    if (isset($_SESSION)) {
      unset($_SESSION['czuid']);
      unset($_SESSION['czut']);
      unset($_SESSION['cz_user']);
    }
  }

  /**
   * Delete session and cookies, refresh token
   * @return void
   */
  public function logout()
  {
    Session::start();

    $this->updateUserToken($_SESSION['czuid'], $_SERVER['HTTP_USER_AGENT'], Helper::strGen(32));

    $this->sessionUnset();

    @setcookie('czuid', '', time() - 3600, '/', App::config('common', 'cookie_domain'));
    @setcookie('czut', '', time() - 3600, '/', App::config('common', 'cookie_domain'));
  }

  /**
   * @param  int $uid
   * @param  string $token
   * @return void
   */
  public function updateUserToken($uid, $agent, $token)
  {
    DB::table($this->prefix . 'signin')
      ->set(array(
        'token' => $token
      ))
      ->where(array(
        array('id', (int) $uid),
        array('agent', $agent),
      ))
      ->limit(1)
      ->update();
  }

  /**
   * updateDateTime
   *
   * @param  int $uid
   * @param  string $datatime
   * @return void
   */
  public function updateDateTime($uid, $datatime)
  {
    $datatime = date("Y-m-d H:i:s");

    DB::table($this->prefix . 'registration')
      ->set(array(
        'date_update' => $datatime
      ))
      ->where('uid', (int) $uid)
      ->limit(1)
      ->update();
  }

  /**
   * Delete user session
   * 
   * @param  mixed $sessionId
   * @return void
   */
  public function deleteSession($sessionId)
  {
    if (session_id()) {
      session_commit();
    }

    #Save id current session
    Session::start();
    $current_session_id = session_id();
    session_commit();

    #Destroy user session
    session_id($sessionId);
    Session::start();
    session_destroy();
    session_commit();

    #Return to current session
    session_id($current_session_id);
    Session::start();
    session_commit();
  }

  /**
   * Generate public user id
   * 
   * @return int puid
   */
  public function generatePuid()
  {
    $puid = Helper::strGen(12);

    while ($this->puidExists($puid)) {
      $puid = Helper::strGen(12);
    }

    return $puid;
  }

  /**
   * Generate guest accaunt
   * 
   * @return int guest uid
   */
  public function generateGuest()
  {
    $uid = $this->createUser(array(
      'puid' => $this->generatePuid(),
      'email' => '',
      'name' => 'Anonim',
      'avatar' => '',
      'role' => 'anonim',
    ));

    return $uid;
  }

  /**
   * Get user id from your site database
   * 
   * @param int $ruid uid from your site database
   * @return int uid from comment system database
   */
  public function getRelatedUid($ruid)
  {
    $user = DB::table($this->prefix . 'users_related')
      ->select('uid')
      ->where('ruid', $ruid)
      ->first();

    return $user->success && !empty($user->result) ? $user->result['uid'] : 0;
  }

  /**
   * Add user id from your site database
   * 
   * @param int $ruid uid from your site database
   * @param int $uid uid from comment system database
   * @return bool
   */
  public function setReletedUid($ruid, $uid)
  {
    $insert = DB::table($this->prefix . 'users_related')
      ->set(array(
        'ruid' => $ruid,
        'uid' => $uid,
      ))
      ->insertOrUpdate();

    return $insert->success && $insert->result->affected_rows > 0 ? true : false;
  }

  /**
   * Add user in DB
   * 
   * @param array $params
   * @return int insert_id from DB
   */
  public function createUser($params)
  {
    $id = DB::table($this->prefix . 'users')
      ->set($params)
      ->insertGetId();

    return $id->success ? $id->result : 0;
  }

  /**
   * Update user by id
   * 
   * @param int $id
   * @param array $params
   * @return bool
   */
  public function updateById($id, $params)
  {
    $update = DB::table($this->prefix . 'users')
      ->set($params)
      ->where('id', $id)
      ->limit(1)
      ->update();

    return $update->success && $update->result->affected_rows !== -1 ? true : false;
  }

  /**
   * Add registration info in DB
   * 
   * @param array $params
   * @return int insert_id from DB
   */
  public function createRegistration($params)
  {
    $id = DB::table($this->prefix . 'registration')
      ->set($params)
      ->insertGetId();

    return $id->success ? $id->result : 0;
  }

  /**
   * Update registration info
   * 
   * @param int $uid
   * @param array $set
   * @return bool
   */
  public function updateRegistration($uid, $set)
  {
    $update = DB::table($this->prefix . 'registration')
      ->set($set)
      ->where('uid', (int) $uid)
      ->update();

    return $update->success && $update->result->affected_rows !== -1 ? true : false;
  }

  /**
   * Check if the email exists
   * 
   * @param string $email
   * @return bool
   */
  public function existsEmail($email)
  {
    $users = DB::table($this->prefix . 'users')
      ->select('id')
      ->where(array(
        array('email', $email),
        array('role', '!=', 'guest'),
      ))
      ->limit(1)
      ->get();

    if (!$users->success || empty($users->result)) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Check if the id exists
   * 
   * @param int $id
   * @return bool
   */
  public function existsId($id)
  {
    $users = DB::table($this->prefix . 'users')
      ->select('id')
      ->where('id', $id)
      ->limit(1)
      ->get();

    if (!$users->success || empty($users->result)) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Get all roles
   *
   * @return array
   */
  public function getRoles()
  {
    $role = DB::table($this->prefix . 'role')
      ->select('name', 'permission')
      ->orderBy('sort', 'ASC')
      ->get();

    $data = array();

    if ($role->success && !empty($role->result)) {
      foreach ($role->result as $value) {
        $data[$value['name']] = json_decode($value['permission']);
      }
    }

    return $data;
  }

  /**
   * Update all roles
   *
   * @param  array $roles
   * @return bool
   */
  public function updateRoles($roles)
  {
    if (empty($roles)) {
      return false;
    }

    DB::table($this->prefix . 'role')->truncate();

    $set = array();
    $i = 0;
    foreach ($roles as $key => $value) {
      $permission = json_encode($value);
      $set[] = array(
        $key,
        $permission,
        $i
      );
      $i++;
    }

    DB::table($this->prefix . 'role')
      ->values(array('name', 'permission', 'sort'), $set)
      ->insert();

    return true;
  }

  /**
   * Get all permissions
   *
   * @return array
   */
  public function getPermissions()
  {
    $permission = DB::table($this->prefix . 'permission')
      ->orderBy('sort', 'ASC')
      ->get();

    return $permission->success ? $permission->result : array();
  }

  /**
   * Update all permissions
   *
   * @param  array $permissions
   * @return bool
   */
  public function updatePermissions($permissions)
  {
    if (empty($permissions)) {
      return false;
    }

    DB::table($this->prefix . 'permission')->truncate();

    $set = array();
    $i = 0;
    foreach ($permissions as $key => $value) {
      $set[] = array(
        $value->name,
        $value->code,
        (!empty($value->sort) ? $value->sort : $i),
      );
      $i++;
    }

    DB::table($this->prefix . 'permission')
      ->values(array('name', 'code', 'sort'), $set)
      ->insert();

    return true;
  }

  /**
   * Get uid by email
   *
   * @param  string $email
   * @return int|null
   */
  public function getUidByEmail($email)
  {
    $users = DB::table($this->prefix . 'users')
      ->select('id')
      ->where(array(
        array('email', $email),
        array('role', '!=', 'guest'),
      ))
      ->first();

    return $users->success && isset($users->result['id']) ? (int) $users->result['id'] : null;
  }

  /**
   * Get user by cmmnet id
   *
   * @param  int $id
   * @return array
   */
  public function getByCommnetId($id)
  {
    $users = DB::table($this->prefix . 'users', 'tu')
      ->select('tu.*')
      ->where('tu.id', DB::table($this->prefix . 'comments', 'tc')
        ->select('tc.uid')
        ->where('tc.id', $id))
      ->first();

    return $users->success ? $users->result : array();
  }

  /**
   * Get info
   *
   * @param  int $id
   * @return array
   */
  public function getInfo($id)
  {
    $users = DB::table($this->prefix . 'users', 'tu')
      ->select(array(
        'tu.*',
        'ts.ip',
        'ts.agent',
        'ts.token',
        'ts.session_id',
        'tr.permission',
        'commentsCount' => DB::table($this->prefix . 'comments', 'tc')
          ->select('COUNT(tc.id)')
          ->where(array(
            'tc.uid = tu.id',
            'tc.posted' => 1,
          )),
        'reportsCount' => DB::table($this->prefix . 'reports', 'tcmpl')
          ->select('COUNT(tcmpl.id)')
          ->where('tcmpl.uid = tu.id'),
      ))
      ->leftJoin($this->prefix . 'role AS tr', 'tr.name', '=', 'tu.role')
      ->leftJoin($this->prefix . 'signin AS ts', array(
        array('ts.uid', '=', 'tu.id'),
        array("ts.agent = '" . $_SERVER['HTTP_USER_AGENT'] . "'")
      ))
      ->where('tu.id', (int) $id)
      ->orderBy('ts.id', 'DESC')
      ->first();

    if ($users->success && !empty($users->result)) {
      $users->result['avatar'] = !empty($users->result['avatar']) ? json_decode($users->result['avatar']) : null;
      $users->result['permission'] = json_decode($users->result['permission']);
      $users->result['ban_datetime_format'] = !empty($users->result['ban_datetime']) ? Helper::changeDate(date('d.m.Y H:i', $users->result['ban_datetime'])) : '';
      $users->result['datePublished'] = array(
        'origin' => $users->result['date_create'],
        'seconds' => strtotime($users->result['date_create']),
        'title' => (isset($_SESSION['time_zone'])) ?
          Helper::changeDate(date('d.m.Y H:i', strtotime($users->result['date_create']) + ($_SESSION['time_zone'] * 60 * 60))) :
          Helper::changeDate(date('d.m.Y H:i T', strtotime($users->result['date_create']))),
        'view' => Helper::changeTime($users->result['date_create']),
      );
    }

    return $users->success ? $users->result : array();
  }

  /**
   * Get sign in params
   *
   * @param  int $id
   * @return array
   */
  public function getSignIn($id)
  {
    $signin = DB::table($this->prefix . 'signin')
      ->where('uid', $id)
      ->orderBy('date_update', 'DESC')
      ->limit(10)
      ->get();

    if ($signin->success && !empty($signin->result)) {
      foreach ($signin->result as $key => &$value) {
        $value['date_update'] = array(
          'origin' => $value['date_update'],
          'seconds' => strtotime($value['date_update']),
          'title' => (isset($_SESSION['time_zone'])) ?
            Helper::changeDate(date('d.m.Y H:i', strtotime($value['date_update']) + ($_SESSION['time_zone'] * 60 * 60))) :
            Helper::changeDate(date('d.m.Y H:i T', strtotime($value['date_update']))),
          'view' => Helper::changeTime($value['date_update']),
        );
      }
    }

    return $signin->success ? $signin->result : array();
  }

  /**
   * Get users by ids
   *
   * @param  array $idList
   * @return array
   */
  public function getByIdList($idList)
  {
    $users = DB::table($this->prefix . 'users', 'tu')
      ->select(array(
        'tu.*',
        'tr.permission',
        'ip' => DB::table($this->prefix . 'signin', 'ts')
          ->select('ts.ip')
          ->where('ts.uid = tu.id')
          ->orderBy('ts.date_update', 'DESC')
          ->limit(1)
      ))
      ->leftJoin($this->prefix . 'role AS tr', 'tr.name', '=', 'tu.role')
      ->whereIn('tu.id', $idList)
      ->get();

    if ($users->success && !empty($users->result)) {
      foreach ($users->result as &$value) {
        $value['permission'] = json_decode($value['permission']);
      }
    }

    return $users->success ? $users->result : array();
  }

  /**
   * Get user by authorize params
   *
   * @param  string $email
   * @param  string $password
   * @return array
   */
  public function getByAuthorize($email, $password)
  {
    $users = DB::table($this->prefix . 'users', 'tu')
      ->select('tu.*', 'tr.permission')
      ->leftJoin($this->prefix . 'role AS tr', 'tr.name', '=', 'tu.role')
      ->where(array('tu.email' => $email, 'tu.deleted' => 0))
      ->first();

    if (!$users->success || empty($users->result)) {
      return false;
    }

    $users->result['permission'] = json_decode($users->result['permission']);

    // $uid = $users['id'];

    $auth = DB::table($this->prefix . 'registration')
      ->select()
      ->where('uid', (int) $users->result['id'])
      ->first();

    if (!$auth->success || empty($auth->result)) {
      return false;
    }

    if (!Helper::passwordCheck($password, $auth->result['password'], $auth->result['salt'])) {
      return false;
    }

    return $users->result;
  }

  /**
   * Filetr info from view
   *
   * @param  array $data
   * @return array
   */
  public function infoView($data)
  {
    if (!empty($data)) {
      unset($data['ip']);
      unset($data['token']);
      unset($data['date_create']);
    }

    return $data;
  }

  /**
   * check exists publick uid (puid)
   *
   * @param  string $puid
   * @return bool
   */
  public function puidExists($puid)
  {
    $count = 0;
    $users = DB::table($this->prefix . 'users')
      ->select('COUNT(id) AS count')
      ->where('puid', $puid)
      ->first();

    if ($users->success && !empty($users->result)) {
      $count = $users->result['count'];
    }

    return $count > 0 ? true : false;
  }

  /**
   * Check exists reset code for reset password
   *
   * @param  string $code
   * @return bool
   */
  public function resetCodeExists($code)
  {
    $count = 0;
    $resetCode = DB::table($this->prefix . 'reset_password')
      ->select('COUNT(id) AS count')
      ->where('code', $code)
      ->first();

    if ($resetCode->success && !empty($resetCode->result)) {
      $count = $resetCode->result['count'];
    }

    return $count > 0 ? true : false;
  }

  /**
   * Insert reset code for reset password
   *
   * @param  int $uid
   * @param  string $code
   * @return bool
   */
  public function resetCodeSave($uid, $code)
  {
    $insert = DB::table($this->prefix . 'reset_password')
      ->set(array(
        'uid' => $uid,
        'code' => $code,
        'date' => date("Y-m-d H:i:s", time())
      ))
      ->insert();

    return $insert->success && $insert->result->affected_rows > 0 ? true : false;
  }

  /**
   * Delete reset code for reset password
   *
   * @param  int $uid
   * @param  string $code
   * @return bool
   */
  public function deleteResetCode($uid, $code)
  {
    $delete = DB::table($this->prefix . 'reset_password')
      ->where(array(
        array('uid', $uid),
        array('code', $code),
      ))
      ->delete();

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }

  /**
   * Get reset code for reset password
   *
   * @param  int $uid
   * @param  string $code
   * @return array
   */
  public function getResetCode($uid, $code)
  {
    $select = DB::table($this->prefix . 'reset_password')
      ->where(array(
        array('uid', $uid),
        array('code', $code),
      ))
      ->orderBy('id', 'DESC')
      ->first();

    return $select->success ? $select->result : array();
  }

  /**
   * Get permissions
   *
   * @return array
   */
  public function getPermission()
  {
    $permission = DB::table($this->prefix . 'permission')->get();

    $data = array();

    if ($permission->success && !empty($permission->result)) {
      foreach ($permission->result as $value) {
        $data[$value['code']] = $value['name'];
      }
    }

    return $data;
  }

  /**
   * Get all users
   *
   * @param  array $params
   * @return array
   */
  public function getAll($params = array())
  {
    $limit = $params['limit'];
    $offset = isset($params['listId']) ? $limit * $params['listId'] : 0;

    $permission = $this->getPermission();

    $where = array();

    if (isset($params['filters'])) {
      foreach ($params['filters'] as $key => $value) {
        if ($value !== false) {
          if ($key === 'ip') {
            $where[] = array(
              "tu1.id", 'IN', DB::table($this->prefix . 'signin')
                ->select('uid')
                ->where('ip', $value)
                ->orderBy('id', 'DESC')
            );
          } else {
            $where[] = array("tu1.$key", '=', $value);
          }
        }
      }
    }

    $users = DB::table(
      DB::table($this->prefix . 'users', 'tu1')
        ->select()
        ->where($where)
        ->orderBy('tu1.id', 'DESC')
        ->limit($limit)
        ->offset($offset),
      'tu'
    )->select(array(
      'tu.*',
      'tr.permission',
      'commentsCount' => DB::table($this->prefix . 'comments', 'tc')
        ->select('COUNT(tc.id)')
        ->where(array(
          'tc.uid = tu.id',
          'tc.posted' => 1
        ))
    ))
      ->leftJoin($this->prefix . 'role AS tr', 'tr.name', '=', 'tu.role')
      ->orderBy('tu.id', 'DESC')
      ->get();

    if ($users->success && !empty($users->result)) {
      foreach ($users->result as $key => &$value) {
        $value['avatar'] = isset($value['avatar']) ? json_decode($value['avatar']) : null;
        $value['permission'] = $value['permission'] ? json_decode($value['permission']) : array();
        if (isset($value['permission'])) {
          $value['permissionView'] = '';
          foreach ($value['permission'] as $v) {
            if (isset($permission[$v])) {
              $value['permissionView'] .= '- ' . $permission[$v] . PHP_EOL;
            }
          }
        }
        $value['date_create'] = Helper::changeDate(date('d.m.Y H:i T', strtotime($value['date_create'])));
      }
    }

    return $users->success ? $users->result : array();
  }

  /**
   * Remove user by ids
   *
   * @param  array $listId
   * @param  string $removeComments
   * @return bool
   */
  public function remove($listId, $removeComments = 'leave')
  {
    if ($removeComments !== 'leave') {
      $comments = DB::table($this->prefix . 'comments')
        ->select('id', 'type')
        ->whereIn('uid', $listId)
        ->get();

      if ($comments->success && !empty($comments->result)) {
        foreach ($comments->result as $value) {
          $Comment = new Comment();

          if ($removeComments === 'remove') {
            $delete = $Comment->delete($value['id'], $value['type']);
          } elseif ($removeComments === 'unposted') {
            $delete = $Comment->unposted($value['id'], $value['type']);
          }

          if (!$delete) {
            return false;
          }
        }
      }
    }

    DB::table($this->prefix . 'users')->whereIn('id', $listId)->delete();

    return true;
  }

  /**
   * Delete user by ids
   *
   * @param  array $listId
   * @param  string $removeComments
   * @return bool
   */
  public function delete($listId, $removeComments = 'leave')
  {
    if ($removeComments !== 'leave') {
      $comments = DB::table($this->prefix . 'comments')
        ->select('id', 'type')
        ->whereIn('uid', $listId)
        ->get();

      if ($comments->success && !empty($comments->result)) {
        $Comment = new Comment();
        foreach ($comments->result as $value) {
          if ($removeComments === 'remove') {
            $Comment->delete($value['id'], $value['type']);
          } elseif ($removeComments === 'unposted') {
            $Comment->unposted($value['id'], $value['type']);
          }
        }
      }
    }

    #Change token
    $token = Helper::strGen(32);

    DB::table($this->prefix . 'users')
      ->set(array(
        'deleted' => 1,
        'token' => $token
      ))
      ->whereIn('id', $listId)
      ->update();

    #Get id users session
    $users = DB::table($this->prefix . 'users')
      ->select('session_id')
      ->whereIn('id', $listId)
      ->get();

    if ($users->success && !empty($users->result)) {
      foreach ($users->result as $value) {
        if (!empty($value['session_id'])) {
          $this->deleteSession($value['session_id']);
        }
      }
    }

    return true;
  }

  /**
   * Recover deleted users by ids
   *
   * @param  array $listId
   * @return bool
   */
  public function recover($listId)
  {
    DB::table($this->prefix . 'users')
      ->set(array('deleted' => 0))
      ->whereIn('id', $listId)
      ->update();

    return true;
  }

  /**
   * Get count users
   *
   * @param  array $params
   * @return int
   */
  public function getCount($params = array())
  {
    $where = array();

    if (isset($params['filters'])) {
      foreach ($params['filters'] as $key => $value) {
        if ($value !== false) {
          // $where[] = "`tu`.`$key` = '$value'";
          if ($key === 'ip') {
            $where[] = array(
              "tu.id", 'IN', DB::table($this->prefix . 'signin')
                ->select('uid')
                ->where('ip', $value)
            );
          } else {
            $where[] = array("tu.$key", '=', $value);
          }
        }
      }
    }

    $count = DB::table($this->prefix . 'users', 'tu')->where($where)->count();

    return $count->success ? $count->result : 0;
  }

  /**
   * Ban by ids
   *
   * @param  array $idList
   * @param  string $datatime
   * @param  string $note
   * @return bool
   */
  public function ban($idList, $datatime, $note)
  {
    $datatime = isset($datatime) ? $datatime : NULL;
    $note = isset($note) ? $note : NULL;

    $update = DB::table($this->prefix . 'users')
      ->set(array(
        'banned' => 1,
        'ban_count' => array('ban_count', '+', 1),
        'ban_datetime' => $datatime,
        'ban_note' => $note,
      ))
      ->whereIn('id', $idList)
      ->update();

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }

  /**
   * Unban by ids
   *
   * @param  array $idList
   * @return bool
   */
  public function unban($idList)
  {
    $update = DB::table($this->prefix . 'users')
      ->set(array(
        'banned' => 0,
        'ban_datetime' => null,
      ))
      ->whereIn('id', $idList)
      ->update();

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }

  /**
   * Ban user ip by ids
   *
   * @param  array $ipList
   * @return bool
   */
  public function banIp($ipList)
  {
    $date = date("Y-m-d H:i:s");

    $set = array();
    foreach ($ipList as $value) {
      $set[] = array(
        $value['ip'],
        $value['note'],
        $date
      );
    }

    $insert = DB::table($this->prefix . 'ban_ip')
      ->values(array('ip', 'note', 'date_create'), $set)
      ->insert();

    return $insert->success && $insert->result->affected_rows > 0 ? true : false;
  }

  /**
   * Unban user ip by ids
   *
   * @param  array $ipList
   * @return bool
   */
  public function unbanIp($ipList)
  {
    $delete = DB::table($this->prefix . 'ban_ip')
      ->whereIn('ip', $ipList)
      ->delete();

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }

  /**
   * Unban user ip by ID
   *
   * @param  int $id
   * @return bool
   */
  public function unbanIpById($id)
  {
    $delete = DB::table($this->prefix . 'ban_ip')
      ->where('id', $id)
      ->delete();

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }

  public function existsBanIp($ip)
  {
    $ban = DB::table($this->prefix . 'ban_ip')
      ->where('ip', $ip)
      ->count();

    return $ban->success && $ban->result > 0 ? true : false;
  }

  /**
   * Get count banned IP
   *
   * @param  array $params
   * @return int
   */
  public function getCountBanIp($params = array())
  {
    $where = array();

    if (isset($params['filters'])) {
      foreach ($params['filters'] as $key => $value) {
        if ($value !== false) {
          $where[] = array($key, '=', $value);
        }
      }
    }

    $select = DB::table($this->prefix . 'ban_ip');
    if (!empty($where)) {
      $select->where($where);
    }
    $count = $select->count();

    return $count->success ? $count->result : 0;
  }

  /**
   * Get banned IP
   *
   * @param  array $params
   * @return array
   */
  public function getBanIp($params = array())
  {
    $limit = $params['limit'];
    $offset = isset($params['listId']) ? $limit * $params['listId'] : 0;

    $where = array();

    if (isset($params['filters'])) {
      foreach ($params['filters'] as $key => $value) {
        if ($value !== false) {
          $where[] = array($key, '=', $value);
        }
      }
    }

    $select = DB::table($this->prefix . 'ban_ip');
    if (!empty($where)) {
      $select->where($where);
    }
    $ips = $select->orderBy('id', 'DESC')
      ->limit($limit)
      ->offset($offset)
      ->get();

    return $ips->success ? $ips->result : array();
  }

  /**
   * Update user info
   *
   * @param  int $uid
   * @param  array $set
   * @return bool
   */
  public function setProfile($uid, $set)
  {
    $update = DB::table($this->prefix . 'users')
      ->set($set)
      ->where('id', (int) $uid)
      ->limit(1)
      ->update();

    return $update->success && $update->result->affected_rows !== -1 ? true : false;
  }

  /**
   * Get uid by IP
   *
   * @param  string $ip
   * @return int
   */
  public function getUidByIp($ip)
  {
    $users = DB::table($this->prefix . 'users')
      ->select('id')
      ->where('ip', $ip)
      ->orderBy('id', 'DESC')
      ->first();

    return $users->success && !empty($users->result) ? $users->result['id'] : 0;
  }

  /**
   * Get IP
   *
   * @return string
   */
  public function getIp()
  {
    switch (true) {
      case (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP'])):
        return $_SERVER['HTTP_X_REAL_IP'];
      case (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])):
        return $_SERVER['HTTP_CLIENT_IP'];
      case (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
      default:
        return $_SERVER['REMOTE_ADDR'];
    }
  }
}
