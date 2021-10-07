<?php

namespace app;

use app\SendMailSmtpClass;

class SendMail
{
  private $config;
  private $language;

  public function __construct()
  {
    $this->config = App::config('common');
    $this->language = App::config('language/' . $this->config['language']);
  }

  private function send($to, $subject, $message, $site)
  {
    if ($this->config['smtpMail']) {
      $SendMailSmtpClass = new SendMailSmtpClass($this->config['smtpMailLogin'], $this->config['smtpMailPassword'], $this->config['smtpMailHost'], $this->config['smtpMailPort'], $this->config['smtpMailEncoding']);
      $from = array(
        $site,
        "robot@" . $site
      );
      if ($SendMailSmtpClass->send($to, $subject, $message, $from) === true) {
        return true;
      } else {
        return false;
      }
    } else {
      $headers = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
      $headers .= 'From: ' . $site . ' <robot@' . $site . '>' . "\r\n";
      return mail($to, $subject, $message, $headers);
    }
  }

  /**
   * Send a message to the user about the received response to the comment
   * 
   * @param string $link
   * @param string $userName
   * @param string $title
   * @param string $text
   * @param string $email
   * @return bool
   */
  public function go($link, $userName, $title, $text, $email)
  {
    $site = $_SERVER['HTTP_HOST'];
    $link = $link;
    $to = $email;
    $subject = $title;
    $message = '
		<html>
		<head>
		<title>' . $subject . '</title>
		<style>
		body {
			font-family: Arial, sans-serif;
		}
		</style>
		</head>
		<body>
		<div style="font-size:18px;">' . $subject . '</div>
		<div style="padding:20px;">
			<div style="font-size:14px;color:#0b9df4;"><span dir="ltr">' . $userName . '</span> - <b style="color:#949ea7;font-size:12px;font-weight:normal;">' . date("d.m.Y H:i", time()) . '</b></div>
			<div>' . $text . '</div>
			<br/>
			<a href="' . $link . '">' . $this->language['look_on_site'] . '</a>
		</div>
		<br/><br/>
		' . $this->language['email_sign'] . ' ' . $site . '
		</body>
		</html>
	';

    return $this->send($to, $subject, $message, $site);
  }

  /**
   * Send a message to the user with a password recovery code
   * 
   * @param int|string $code
   * @param string $email
   * @return bool
   */
  public function sendCodeReset($code, $email)
  {
    $site = $_SERVER['HTTP_HOST'];
    $to = $email;
    $subject = $this->language['approval_code'];

    $message = '
    <html>
		<head>
		<title>' . $subject . '</title>
		<style>
		body {
			font-family: Arial, sans-serif;
		}
		</style>
		</head>
		<body>
		  <h2 style="color:#007788;">' . $subject . '</h2>
		  <div>' . $this->language['email_reset_password'] . ':</div>
		  <div style="margin: 20px;">
		    <div style="background: #039bb1;display: inline-block;padding: 10px 18px;font-size: 22px;color: #ffffff;text-transform: uppercase;">' . $code . '</div>
		  </div>
		  <div>' . $this->language['email_sign'] . ' ' . $site . '</div>
		</body>
		</html>
    ';

    return $this->send($to, $subject, $message, $site);
  }
}
