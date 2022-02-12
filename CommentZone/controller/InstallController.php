<?php

namespace controller;

use app\App;
use app\Helper;
use model\DB;
use model\User;

class InstallController
{  
  /**
   * Render view install
   *
   * @return string|false
   */
  public function index()
  {
    $App = new App();
    $config = App::config('common');
    return $App->render('view/install/index', array(
      'apiPath' => $config['api_path'],
    ));
  }
  
  /**
   * Get Mysql config
   *
   * @return string|false
   */
  public function getMysqlConfig()
  {
    return json_encode(App::config('db'));
  }
  
  /**
   * Install dtabase
   *
   * @param  object $post
   * @return string|false
   */
  public function installDtabase($post)
  {
    $config = App::config('db');

    foreach ($post->mysql as $key => $mysql) {
      $config[$key] = $mysql;
    }

    $data = '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($config, true) . ';';
    $pahtConfig = dirname(__DIR__) . '/config/db.php';
    $result = file_put_contents($pahtConfig, $data);

    $language = App::config('language/' . App::config('common', 'language'));

    $createRegistration = DB::create($config['prefix'] . 'registration', function ($table) {
      $table->id();
      $table->int('uid')->unique();
      $table->string('login', 50);
      $table->string('password');
      $table->string('salt');
      $table->datetime('date_update');
    });

    if (!$createRegistration->success) {
      return json_encode($createRegistration);
    }

    $createSignin = DB::create($config['prefix'] . 'signin', function ($table) {
      $table->id();
      $table->int('uid')->index();
      $table->string('ip', 20)->index();
      $table->string('agent', 255)->index();
      $table->string('token', 32)->index();
      $table->string('session_id', 64);
      $table->datetime('date_update');
    });

    if (!$createSignin->success) {
      return json_encode($createSignin);
    }

    $createBanIp = DB::create($config['prefix'] . 'ban_ip', function ($table) {
      $table->id();
      $table->string('ip', 20)->unique();
      $table->string('note')->nullable();
      $table->datetime('date_create');
    });

    if (!$createBanIp->success) {
      return json_encode($createBanIp);
    }

    $createComments = DB::create($config['prefix'] . 'comments', function ($table) {
      $table->id();
      $table->int('pid')->defaultValue(0)->index();
      $table->int('mid')->defaultValue(0)->index();
      $table->int('page_id')->index();
      $table->text('path')->nullable()->fulltext();
      $table->int('uid')->index();
      $table->string('type', 6)->index();
      $table->text('text')->defaultValue(null);
      $table->json('attach')->nullable();
      $table->int('rating')->defaultValue(0)->index();
      $table->int('new', 1)->defaultValue(1)->index();
      $table->int('posted', 1)->defaultValue(1)->index();
      $table->int('moderation', 1)->defaultValue(0)->index();
      $table->int('reports')->defaultValue(0)->index();
      $table->timestamp('date_create')->defaultValue('CURRENT_TIMESTAMP');
      $table->timestamp('date_update')->defaultValue('CURRENT_TIMESTAMP');
    });

    if (!$createComments->success) {
      return json_encode($createComments);
    }

    $createReports = DB::create($config['prefix'] . 'reports', function ($table) {
      $table->id();
      $table->int('uid')->index();
      $table->int('cid')->index();
      $table->string('text');
      $table->int('new', 1)->defaultValue(1);
      $table->timestamp('date')->defaultValue('CURRENT_TIMESTAMP');
    });

    if (!$createReports->success) {
      return json_encode($createReports);
    }

    $createFlood = DB::create($config['prefix'] . 'flood', function ($table) {
      $table->id();
      $table->int('uid')->index();
      $table->string('ip', 20)->index();
      $table->datetime('date_create');
      $table->datetime('date_update');
    });

    if (!$createFlood->success) {
      return json_encode($createFlood);
    }

    $createPages = DB::create($config['prefix'] . 'pages', function ($table) {
      $table->id();
      $table->string('url')->index();
      $table->binary('hash_url', 16)->index();
      $table->int('bind_id')->nullable()->index();
      $table->string('title');
      $table->int('count_main')->defaultValue(0);
      $table->int('count_answer')->defaultValue(0);
    });

    if (!$createPages->success) {
      return json_encode($createPages);
    }

    $createPermission = DB::create($config['prefix'] . 'permission', function ($table) {
      $table->id();
      $table->string('name', 50);
      $table->string('code', 20);
      $table->int('sort', 2);
    });

    if (!$createPermission->success) {
      return json_encode($createPermission);
    }

    $createRating = DB::create($config['prefix'] . 'rating', function ($table) {
      $table->id();
      $table->int('cid')->index();
      $table->json('uid_increase')->nullable();
      $table->int('increase')->defaultValue(0);
      $table->json('uid_decrease')->nullable();
      $table->int('decrease')->defaultValue(0);
    });

    if (!$createRating->success) {
      return json_encode($createRating);
    }

    $createResetPassword = DB::create($config['prefix'] . 'reset_password', function ($table) {
      $table->id();
      $table->int('uid')->index();
      $table->int('code')->index();
      $table->datetime('date')->defaultValue('CURRENT_TIMESTAMP');
    });

    if (!$createResetPassword->success) {
      return json_encode($createResetPassword);
    }

    $createRole = DB::create($config['prefix'] . 'role', function ($table) {
      $table->id();
      $table->string('name', 20);
      $table->json('permission');
      $table->int('sort', 2);
    });

    if (!$createRole->success) {
      return json_encode($createRole);
    }

    $createSocial = DB::create($config['prefix'] . 'social', function ($table) {
      $table->id();
      $table->int('uid')->index();
      $table->string('sid')->index();
      $table->string('name');
      $table->string('email')->nullable();
      $table->string('link')->nullable();
      $table->string('provider', 25);
    });

    if (!$createSocial->success) {
      return json_encode($createSocial);
    }

    $createSpam = DB::create($config['prefix'] . 'spam', function ($table) {
      $table->id();
      $table->string('ip', 20);
      $table->text('text');
      $table->binary('hash_text', 16)->unique();
      $table->datetime('date_create');
    });

    if (!$createSpam->success) {
      return json_encode($createSpam);
    }

    $createStopWords = DB::create($config['prefix'] . 'stop_words', function ($table) {
      $table->id();
      $table->string('word');
    });

    if (!$createStopWords->success) {
      return json_encode($createStopWords);
    }

    $createUsers = DB::create($config['prefix'] . 'users', function ($table) {
      $table->id();
      $table->string('puid', 12)->unique();
      $table->string('email', 100)->nullable()->index();
      $table->string('name', 100);
      $table->text('avatar')->nullable();
      $table->string('role', 20)->nullable()->index();
      $table->int('deleted')->defaultValue(0)->index();
      $table->int('banned', 1)->defaultValue(0)->index();
      $table->int('ban_datetime')->nullable();
      $table->string('ban_note')->nullable();
      $table->int('ban_count')->defaultValue(0);
      $table->timestamp('date_create', 32)->nullable()->defaultValue('CURRENT_TIMESTAMP');
    });

    if (!$createUsers->success) {
      return json_encode($createUsers);
    }

    $createUsersRelated = DB::create($config['prefix'] . 'users_related', function ($table) {
      $table->id();
      $table->int('uid')->index();
      $table->int('ruid')->unique();
    });

    if (!$createUsersRelated->success) {
      return json_encode($createUsersRelated);
    }


    DB::table($config['prefix'] . 'permission')->truncate();

    $insertPermission = DB::table($config['prefix'] . 'permission')
      ->values(
        array('name', 'code', 'sort'),
        array(
          array($language['permission_create_comments'], 'create_comment', 0),
          array($language['permission_reply_comments'], 'answer_comment', 1),
          array($language['permission_edit_comment'], 'update_comment', 2),
          array($language['permission_delete_comment'], 'delete_comment', 3),
          array($language['permission_manage_comments'], 'manage_comments', 4),
          array($language['permission_manage_users'], 'manage_users', 5),
          array($language['permission_manage_profiles'], 'manage_profile', 6),
          array($language['permission_manage_roles'], 'manage_role', 7),
          array($language['permission_delete_comments_database'], 'remove_comment', 8),
          array($language['permission_delete_users_database'], 'remove_user', 9),
          array($language['permission_influence_rating_comment'], 'rating_impact', 10),
          array($language['permission_access_admin_panel'], 'admin_panel_access', 11),
        )
      )
      ->insert();

    if (!$insertPermission->success) {
      return json_encode($insertPermission);
    }


    DB::table($config['prefix'] . 'role')->truncate();

    $insertRole = DB::table($config['prefix'] . 'role')
      ->values(
        array('name', 'permission', 'sort'),
        array(
          array('admin', json_encode(array(
            "admin_panel_access",
            "update_comment",
            "delete_comment",
            "manage_comments",
            "manage_profile",
            "manage_users",
            "manage_role",
            "answer_comment",
            "remove_comment",
            "remove_user",
            "edit_profile",
            "create_comment",
            "rating_impact"
          )), 0),
          array('moder', json_encode(array(
            "create_comment",
            "delete_comment",
            "update_comment",
            "answer_comment",
            "manage_comments",
            "manage_users",
            "rating_impact",
            "admin_panel_access"
          )), 1),
          array('user', json_encode(array(
            "create_comment",
            "update_comment",
            "delete_comment",
            "answer_comment",
            "rating_impact"
          )), 2),
          array('guest', json_encode(array(
            "create_comment",
            "update_comment",
            "delete_comment",
            "answer_comment"
          )), 3),
          array('anonim', json_encode(array(
            "create_comment",
            "answer_comment"
          )), 4),
        )
      )
      ->insert();

    if (!$insertRole->success) {
      return json_encode($insertRole);
    }

    return json_encode(array(
      'success' => true,
    ));
  }
  
  /**
   * Create admin
   *
   * @param  object $post
   * @return string|false
   */
  public function createAdmin($post)
  {
    $name = trim($post->name);
    $email = trim($post->email);
    $password = trim($post->password);

    if (empty($name)) {
      return json_encode(array(
        'success' => false,
        'error' => 'error_name',
      ));
    }
    if (empty($email)) {
      return json_encode(array(
        'success' => false,
        'error' => 'error_email',
      ));
    }
    if (empty($password)) {
      return json_encode(array(
        'success' => false,
        'error' => 'error_password',
      ));
    }

    $User = new User();

    if ($User->existsId(1)) {
      return json_encode(array(
        'success' => false,
        'error' => 'admin-exists'
      ));
    }

    $uid = $User->createUser(array(
      'puid' => $User->generatePuid(),
      'email' => $email,
      'name' => $name,
      'avatar' => '',
      'role' => 'admin',
      'date_create' => date("Y-m-d H:i:s", time()),
    ));

    if ($uid === 0) {
      return json_encode(array(
        'success' => false,
        'error' => 'error-uid'
      ));
    }

    $salt = Helper::passwordSalt($password);
    $passCrypt = Helper::passwordCrypt($password, $salt);

    $authorizeParams = array(
      'uid' => $uid,
      'login' => '',
      'password' => $passCrypt,
      'salt' => $salt,
      'date_update' => date("Y-m-d H:i:s", time()),
    );

    $authCreate = $User->createRegistration($authorizeParams);

    if (!$authCreate) {
      return json_encode(array(
        'success' => false,
        'error' => 'error-auth'
      ));
    }

    return json_encode(array(
      'success' => true,
    ));
  }
  
  /**
   * Get languages
   *
   * @return string|false
   */
  public function getLanguages()
  {
    $pathLanguage = dirname(__DIR__) . '/config/language';
    $dirLanguage = scandir($pathLanguage);
    $languages = array_splice($dirLanguage, 2);

    $languages = array_map(function ($a) {
      return str_replace('.php', '', $a);
    }, $languages);

    $config = App::config('common');

    return json_encode(array(
      'success' => true,
      'current' => $config['language'],
      'list' => $languages,
      'params' => App::config('language/' . $config['language']),
    ));
  }
  
  /**
   * Select languages
   *
   * @param  object $post
   * @return string|false
   */
  public function selectLanguages($post)
  {
    return json_encode(App::config('language/' . $post->language));
  }
  
  /**
   * Copy resources
   *
   * @param  string $path
   * @param  string $newPath
   * @return void
   */
  public function copyResources($path, $newPath)
  {
    $dir = array_slice(scandir($path), 2);

    if (!empty($dir) && is_array($dir)) {
      foreach ($dir as $value) {
        if (is_dir($path . '/' . $value)) {
          if (!file_exists($newPath . '/' . $value)) {
            mkdir($newPath . '/' . $value);
          }
          $this->copyResources($path . '/' . $value, $newPath . '/' . $value);
        }
        if (is_file($path . '/' . $value)) {
          copy($path . '/' . $value, $newPath . '/' . $value);
        }
      }
    }
  }
  
  /**
   * Start install
   *
   * @param  object $post
   * @return string|false
   */
  public function start($post)
  {
    $language = $post->language;

    $config = App::config('common');
    $config['language'] = $language;

    $data = '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($config, true) . ';';
    $pahtConfig = dirname(__DIR__) . '/config/common.php';
    $result = file_put_contents($pahtConfig, $data);

    $pahtResource = dirname(__DIR__) . '/resource';
    $cpyPahtResource = $_SERVER['DOCUMENT_ROOT'] . $config['resource'];

    if (!file_exists($cpyPahtResource)) {
      mkdir($cpyPahtResource);
    }

    $this->copyResources($pahtResource, $cpyPahtResource);

    return json_encode(array(
      'success' => true,
    ));
  }
}
