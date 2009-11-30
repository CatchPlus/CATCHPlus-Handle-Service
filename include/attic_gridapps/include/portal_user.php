<?php

/*·*************************************************************************
 * Copyright ©2009 SARA Computing and Networking Services
 *                 Amsterdam, the Netherlands
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id: portal_user.php 2463 2009-08-12 08:58:33Z pieterb $
 **************************************************************************/

/**
 * File documentation
 * @package Portal
 */


/**
 * Portal User
 * Currently this class is a singleton with current() as its factory method.
 * @package Portal
 */
class Portal_User {
  

  /**
   * @var string
   */
  private $user_name;
  
  
  /**
   * @var int
   */
  private $user_dn;
  
  
  /**
   * @var string
   */
  private $user_email;
  
  
  /**
   * @var int
   */
  private $user_id;
  
  
  /**
   * @var bool
   */
  private $user_spoofed;
  
  
  /**
   * Private constructor.
   * Use Portal_User::current() to create a new User object.
   * @param int    $id
   * @param string $name
   * @param string $email
   */
  private function __construct($id, $name, $email, $dn, $spoofed = false) {
    $this->user_id = (int)$id;
    $this->user_name = "$name";
    $this->user_email = "$email";
    $this->user_dn = "$dn";
    $this->user_spoofed = (bool)$spoofed;
  }
  
  
  /**
   * @return string
   */
  public function user_name() {
    return $this->user_name;
  }
  
  
  /**
   * @return string
   */
  public function user_dn() {
    return $this->user_dn;
  }
  
  
  /**
   * @return string
   */
  public function user_email() {
    return $this->user_email;
  }
  
  
  /**
   * @return string
   */
  public function user_id() {
    return $this->user_id;
  }
  
  
  /**
   * @return bool
   */
  public function user_spoofed() {
    return $this->user_spoofed;
  }
  
  
  /**
   * @return string an 8-byte ascii string.
   */
  public static function createPassword() {
    // This string is exactly 64 bytes long:  
    $pwd_characters = '23456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz_-./?+';
    $retval = '';
    while (strlen($retval) < 8)
      $retval .= $pwd_characters{mt_rand(0, 63)};
    return $retval;
  }
  
  
  /**
   * Never returns
   */
  public static function unauthorized() {
    if ($_SERVER['SERVER_PORT'] == Portal::PORT_PLAIN)
      REST::redirect(
        REST::HTTP_TEMPORARY_REDIRECT,
        'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']
      );
    header('WWW-Authenticate: Basic realm="Grid Portal"');
    $register = REST::htmlspecialchars(
      'https://' . $_SERVER['SERVER_NAME'] . Portal::portalURL() . 'register?referrer=' .
      urlencode(REST::urlbase() . $_SERVER['REQUEST_URI'])
    );
    REST::fatal(
      REST::HTTP_UNAUTHORIZED,
      "<p>You can register <a href=\"{$register}\">here</a>.</p>"
    );
    exit;
  }
  
  
  // Evert's versie van authenticate():
  /*
  public static function user_id() {
    if (self::$current === null) {
      if ($_SERVER['SERVER_PORT'] == self::PORT_SSL_CSA) {
        if (Portal::user_dn() !== $_SERVER['SSL_SERVER_S_DN']) {
          REST::fatal(REST::HTTP_UNAUTHORIZED, "You are not authorized to view this resource");
        }
        return;
      }
      $required_location = 'https://' . $_SERVER['SERVER_NAME'] . ':' . self::PORT_SSL . $_SERVER['REQUEST_URI'];
      if (!isset($_SERVER['PHP_AUTH_USER'])) {
        self::unauthorized($required_location);
      }
      $user_email = Portal_MySQL::escape_string($_SERVER['PHP_AUTH_USER']);
      $user_password = md5($_SERVER['PHP_AUTH_PW']);
      $result = Portal_MySQL::query( <<<EOS
SELECT `user_id` 
  FROM `User` 
 WHERE `user_email`= {$user_email}
   AND `user_password`= '{$user_password}';
EOS
      );
      if (!($row = $result->fetch_row()))
        self::unauthorized($required_location);
      self::$current = $row[0];
  
    }
    return self::$current;
  }
  */
  
  
  /**
   * @return string|null
   */
  public static function csa_dn() {
    if (!isset($_SERVER['SSL_CLIENT_S_DN']))
      return null;
    if (!preg_match('@^((?:/[^/]+)+?)(?:/CN=\\d+)*$@', $_SERVER['SSL_CLIENT_S_DN'], $matches))
      REST::fatal(
        REST::HTTP_INTERNAL_SERVER_ERROR,
        "Couldn't parse client subject dn '{$_SERVER['SSL_CLIENT_S_DN']}'"
      );
    return $matches[1];
//    $user_dn = explode(' ', $_SERVER['GRST_CRED_0'], 5);
//    if (!isset($user_dn[4]))
//      REST::fatal(
//        REST::HTTP_INTERNAL_SERVER_ERROR,
//        "Couldn't understand your credential information."
//      );
//    return $user_dn[4];
  }
  

  /**
   * @var Portal_User
   */
  private static $current = null;
  
  
  /**
   * @param bool $required
   * @return Portal_User
   */
  public static function current() {
    if (self::$current === null) {
      switch ($_SERVER['SERVER_PORT']) {
        case Portal::PORT_PLAIN:
          self::unauthorized();
          break; // strictly unnecessary, but syntactically nicer.
        case Portal::PORT_SSL:
          if (!isset($_SERVER['PHP_AUTH_USER']))
            self::unauthorized();
          $user_email = Portal_MySQL::escape_string($_SERVER['PHP_AUTH_USER']);
          $user_password = md5($_SERVER['PHP_AUTH_PW']);
          $result = Portal_MySQL::query( <<<EOS
SELECT `user_id`, `user_name`, `user_dn` FROM `User`
WHERE `user_email`   =  {$user_email}
  AND `user_password`= '{$user_password}';
EOS
          );
          if (!($row = $result->fetch_row()))
            self::unauthorized();
          self::$current = new Portal_User(
            (int)$row[0], $_SERVER['PHP_AUTH_USER'], $row[1], $row[2]
          );
          break;
        case Portal::PORT_SSL_CSA:
          $user_dn = self::csa_dn();
          if ( isset($_SERVER['PHP_AUTH_USER']) &&
               (int)($_SERVER['PHP_AUTH_USER']) > 0 &&
               preg_match(
                 '@^/O=dutchgrid/O=users/O=sara/CN=(?:Evert Lammerts|Pieter van Beek)@',
                 $_SERVER['SSL_CLIENT_S_DN']
               ) ) {
            $esc_user_id = (int)($_SERVER['PHP_AUTH_USER']);
            $result = Portal_MySQL::query( <<<EOS
SELECT `user_email`, `user_name`, `user_dn` FROM `User`
WHERE `user_id` = {$esc_user_id};
EOS
            );
            if (!($row = $result->fetch_row()))
              REST::fatal(
                REST::HTTP_UNAUTHORIZED,
                "No such user id: {$esc_user_id}"
              );
            self::$current = new Portal_User(
              $esc_user_id, $row[1], $row[0], $row[2], true
            );
          } else {
            $esc_user_dn = Portal_MySQL::escape_string($user_dn);
            $result = Portal_MySQL::query( <<<EOS
SELECT `user_id`, `user_email`, `user_name` FROM `User`
WHERE `user_dn` =  {$esc_user_dn};
EOS
            );
            if (!($row = $result->fetch_row()))
              self::unauthorized();
            self::$current = new Portal_User(
              $row[0], $row[2], $row[1], $user_dn
            );
          }
          break;
        default:
          REST::fatal(REST::HTTP_INTERNAL_SERVER_ERROR);
      }
    }
    return self::$current;
  }


} // class Portal
