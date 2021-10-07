<?php

namespace app;

use model\User;

class Helper
{    
  /**
   * Generate CSRF token
   *
   * @return array
   */
  public static function csrfTokenGen($tag = '')
  {
    $User = new User();
    $salt = self::strGen(32);

    return array(
      'token' => ($tag !== '' ? $tag . '.' : '') . hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $User->getIp() . $salt),
      'salt' => $salt,
      'time' => time(),
    );
  }
  /**
   * Generate string
   *
   * @param  int $len
   * @return string
   */
  public static function strGen($len = 0)
  {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $res = '';

    for ($i = 0; $i < $len; $i++) {
      $res .= substr($chars, rand(0, strlen($chars)), 1);
    }

    return $res;
  }
 
  /**
   * Generate number
   *
   * @param  int $len
   * @return int
   */
  public static function intGen($len = 0)
  {
    $res = '';

    for ($i = 0; $i < $len; $i++) {
      $res .= rand(0, 9);
    }

    return $res;
  }
 
  /**
   * Split url into parameters
   *
   * @param  string $url
   * @return array
   */
  public static function urlParams($url)
  {
    preg_match('/([^\?]+)(\?.*)?/iu', $url, $matches);

    return array(
      'origin' => isset($matches[0]) ? $matches[0] : '',
      'url' => isset($matches[1]) ? $matches[1] . (App::config('common', 'url_query') && isset($matches[2]) ? $matches[2] : '') : '',
      'query' => App::config('common', 'url_query') && isset($matches[2]) ? $matches[2] : '',
    );
  }

  /**
   * Declination of numbers
   * @param int $number
   * @param array $suffix
   * @return string
   */
  public static function declensionWord($number, $suffix)
  {
    $keys = array(count($suffix) - 1, 0, 1, 1, 1, count($suffix) - 1);
    $mod = $number % 100;
    $suffix_key = ($mod > 7 && $mod < 20) ? count($suffix) - 1 : $keys[min($mod % 10, 5)];

    return $suffix[$suffix_key];
  }
  
  /**
   * Format date
   *
   * @param  string $date
   * @return string
   */
  public static function changeDate($date)
  {
    $date = date_parse($date);
    $month = App::config('language/' . App::config('common', 'language'), 'month_name_list');
    $month = explode(',', $month);
    array_unshift($month, '');

    $format = App::config('common', 'date_format');
    $result = preg_replace_callback('/(dd|mm|yy|hh|ii|ss|T)/', function ($matches) use ($date, $month) {
      $item = '';
      switch ($matches[1]) {
        case 'dd':
          $item = $date['day'];
          break;
        case 'mm':
          $item = $month[$date['month']];
          break;
        case 'yy':
          $item = $date['year'];
          break;
        case 'hh':
          $item = $date['hour'];
          break;
        case 'ii':
          $item = $date['minute'] < 10 ? '0' . $date['minute'] : $date['minute'];
          break;
        case 'ss':
          $item = $date['second'] < 10 ? '0' . $date['second'] : $date['second'];
          break;
        case 'T':
          $item = $date['tz_abbr'];
          break;

        default:
          $item = $matches[1];
          break;
      }
      return $item;
    }, $format);

    return $result;
  }
 
  /**
   * Format time
   *
   * @param  string $time
   * @return string
   */
  public static function changeTime($time)
  {
    $echoTime = null;
    $timeNow = time();
    $timeSql = strtotime($time);
    $language = App::config('language/' . App::config('common', 'language'));

    $sec = floor($timeNow - $timeSql);
    $minute = floor($sec / 60);
    $hour = floor($sec / 60 / 60);
    $day = floor($sec / 60 / 60 / 24);
    $week = floor($sec / 60 / 60 / 24 / 7);
    $month = floor($sec / 60 / 60 / 24 / 7 / 4);
    $year = floor($sec / 60 / 60 / 24 / 7 / 4 / 12);

    if ($sec == 0) {
      $echoTime = $language['just_now'];
    }
    if ($sec < 60 && $sec > 0) {
      $echoTime = $sec . ' ' . self::declensionWord($sec, explode(',', $language['declination_seconds'])) . ' ' . $language['ago'];
    }
    if ($sec >= 60) {
      $echoTime = $minute . ' ' . self::declensionWord($minute, explode(',', $language['declination_minute'])) . ' ' . $language['ago'];
    }
    if ($minute >= 60) {
      $echoTime = $hour . ' ' . self::declensionWord($hour, explode(',', $language['declination_hour'])) . ' ' . $language['ago'];
    }
    if ($hour >= 24) {
      $echoTime = $day . ' ' . self::declensionWord($day, explode(',', $language['declination_day'])) . ' ' . $language['ago'];
    }
    if ($day >= 7) {
      $echoTime = $week . ' ' . self::declensionWord($week, explode(',', $language['declination_week'])) . ' ' . $language['ago'];
    }
    if ($week >= 4) {
      $echoTime = $month . ' ' . self::declensionWord($month, explode(',', $language['declination_month'])) . ' ' . $language['ago'];
    }
    if ($month >= 12) {
      $echoTime = $year . ' ' . self::declensionWord($year, explode(',', $language['declination_year'])) . ' ' . $language['ago'];
    }

    return $echoTime;
  }
  
  /**
   * @param  string $str
   * @return string
   */
  public static function passwordSalt($str)
  {
    return password_hash($str, PASSWORD_BCRYPT);
  }
  
  /**
   * @param  string $str
   * @param  string $salt
   * @return string
   */
  public static function passwordCrypt($str, $salt)
  {
    return crypt(sha1($str . '/' . $salt), $salt);
  }
  
  /**
   * @param  string $inputPassword
   * @param  string $cryptPassword
   * @param  string $salt
   * @return string
   */
  public static function passwordCheck($inputPassword, $cryptPassword, $salt)
  {
    return hash_equals(self::passwordCrypt($inputPassword, $salt), $cryptPassword);
  }
  
  /**
   * @return string
   */
  public static function getProtocol()
  {
    return isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? 'https' : 'http';
  }
  
  /**
   * @return string
   */
  public static function getHost()
  {
    return self::getProtocol() . '://' . $_SERVER['HTTP_HOST'];
  }
  
  /**
   * @param  string $version
   * @param  string $token
   * @param  string $userIp
   * @return array
   */
  public static function recaptchaVerify($version, $token, $userIp)
  {
    $App = new App();
    $config = $App->config('common');
    $secretKey = $version === 'v3' ? $config['recaptchaSecretKeyV3'] : $config['recaptchaSecretKeyV2'];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array(
      'secret' => $secretKey,
      'response' => $token,
      'remoteip' => $userIp,
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($curl, CURLOPT_TIMEOUT, 6);
    $checkRecaptcha = curl_exec($curl);
    curl_close($curl);

    $checkRecaptcha = json_decode($checkRecaptcha, TRUE);

    if (!isset($checkRecaptcha['success']) || !$checkRecaptcha['success']) {
      return array(
        'success' => false,
        'error' => 'error-recaptcha'
      );
    }

    if ($version === 'v3' && $checkRecaptcha['score'] < $config['recaptchaScoreV3']) {
      return array(
        'success' => false,
        'error' => 'bad-recaptcha'
      );
    }

    return array(
      'success' => true
    );
  }
}
