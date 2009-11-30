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
 * $Id: portal.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @todo - The call register_shutdown_function at the end of this document
 * doesn't look too good.
 * @package Portal
 */

/**
 * Just a namespace for common portal tasks.
 * @package Portal
 */
class Portal {
  

  //const PROXY_DIR      = '/space/gridapps/proxies/';
  const JOBRESULTS_DIR = '/space/gridapps/resultdata/';
  
  const PORT_PLAIN     = 80;  // No authentication
  const PORT_SSL       = 443; // Optional basic authentication
  const PORT_SSL_CSA   = 444; // Port for Client Side SSL Authentication


  public static function humanReadableDN($dn) {
    $dn = explode('/', $dn);
    $retval = array();
    for ($i = count($dn) - 1; $i >= 0; $i--) {
      $boom = explode('=', $dn[$i], 2);
      if (!empty($boom[1])) $retval[] = $boom[1];
    }
    return implode(', ', $retval);
  }
  
  
  /**
   * Returns the relative URL to the portal root, including the trailing slash.
   * For example: "/apps/"
   * @return string
   */
  public static function portalURL() {
    return dirname($_SERVER['SCRIPT_NAME']) . '/';
  }
  

  private static $path_info = null;
  /**
   * Parses $_SERVER['PATH_INFO'] and returns an array.
   * @return array
   */
  public static function path_info() {
    if (self::$path_info === null) {
      self::$path_info = empty($_SERVER['PATH_INFO'])
       ? array()
       : explode('/', substr($_SERVER['PATH_INFO'], 1));
    }
    return self::$path_info;
  }

  
  public static function html_start($title) {
    $title = REST::htmlspecialchars($title);
    $t_index = REST::urlencode( dirname( $_SERVER['REQUEST_URI'] ) );
    if ($t_index != '/') $t_index .= '/';
    $t_index = REST::htmlspecialchars($t_index);
    $portalurl = REST::htmlspecialchars(self::portalURL());
    $retval = REST::xml_header() . <<<EOS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
<head>
  <title>{$title}</title>
  <link rel="stylesheet" type="text/css" href="{$portalurl}style.css" />
  <link rel="index" rev="child" type="application/xhtml+xml" href="{$t_index}" />
</head><body>
<div id="header"><p><a rel="index" rev="child" href="{$t_index}"><img border="0" src="{$portalurl}dirup.png"/> UP</a></p>
<h1>{$title}</h1></div>
EOS;
    return $retval;
  }


  public static function html_end() {
    $retval = '<div id="footer">';
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      if ($_SERVER['SERVER_PORT'] == self::PORT_SSL) {
        $switchto = 'X.509 Client Certificate Authentication';
        $url = 'https://' . $_SERVER['SERVER_NAME'] . ':' . self::PORT_SSL_CSA .
          $_SERVER['REQUEST_URI'];
      } elseif ($_SERVER['SERVER_PORT'] == self::PORT_SSL_CSA) {
        $switchto = 'Username/Password Authentication';
        $url = 'https://' . $_SERVER['SERVER_NAME'] . ':' . self::PORT_SSL .
          $_SERVER['REQUEST_URI'];
      }
      $retval .= '<div id="changeauth"><a href="' .
        REST::htmlspecialchars($url) . '">Switch to ' . $switchto . '</a></div>';
    }
//    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//      if ($_SERVER['SERVER_PORT'] == Portal::PORT_SSL) {
//        $switchto = 'X.509 Client Certificate Authentication';
//        $url = 'https://' . $_SERVER['SERVER_NAME'] . ':' . Portal::PORT_SSL_CSA .
//          $_SERVER['REQUEST_URI'];
//      } else {
//        $switchto = 'Username/Password Authentication';
//        $url = 'https://' . $_SERVER['SERVER_NAME'] . ':' . Portal::PORT_SSL .
//          $_SERVER['REQUEST_URI'];
//      }
//      $retval .= '<a href="' . REST::htmlspecialchars($url) . '">Switch to ' .
//        $switchto . '</a>';
//    }
    $retval .= '</div></body></html>';
    return $retval;
  }
  
  
  /**
   * @return string
   */
  /*public static function realmName($user_email = null) {
    if ($user_email === null) $user_email = self::user_email();
    return md5(
      "kinWZ9jReDjVBQQ4ach+nqOe1K/t5ppaDUaBjIqyKQkhS1s1ToP{$user_email}"
    );
  }*/
  
  
  public static function normalizeFiles() {
    foreach($_FILES as $key => &$value) {
      if (!is_array($value['error'])) {
        $value['error']    = array($value['error']);
        $value['name']     = array($value['name']);
        $value['type']     = array($value['type']);
        $value['size']     = array($value['size']);
        $value['tmp_name'] = array($value['tmp_name']);
      }
    }
  }
  

  /**
   * Checks if a file is uploaded. Calls REST::fatal if:
   * - the file is $required but not uploaded
   * - several files are uploaded while only one is allowed
   * - an UPLOAD_ERR_* is specified
   * 
   * @param $name
   * @param $required
   * @param $allow_multiple
   * @return true if uploaded, false if !$required && !uploaded
   */
  public static function isUploaded($p_name) {
    preg_match('@^(.*)(\\[\\])?@', $p_name, $matches);
    $name = $matches[1];
    $allow_multiple = !empty($matches[2]);
    if (empty($_FILES[$name]))
      return false;
    
    $errors = $_FILES[$name]['error'];
    if (!is_array($errors)) {
      if ($allow_multiple)
        REST::fatal(
          REST::HTTP_BAD_REQUEST,
          "You should use {$name}[] instead of {$name} for uploading files."
        );
      $errors = array($errors);
    } else {
      if (!$allow_multiple)
        REST::fatal(
          REST::HTTP_BAD_REQUEST,
          "You should use {$name} instead of {$name}[] for uploading a file."
        );
    }
    $counter = 0;
    foreach ($errors as $error) {
      switch ($error) {
        case UPLOAD_ERR_OK:
          $counter++;
        case UPLOAD_ERR_NO_FILE:
          break;
        case UPLOAD_ERR_INI_SIZE:
          REST::fatal(
            REST::HTTP_BAD_REQUEST,
            "The size of your upload exeeds the maximum size as specified in PHP_INI"
          );
        case UPLOAD_ERR_FORM_SIZE:
          REST::fatal(
            REST::HTTP_BAD_REQUEST,
            "The size of your upload exeeds the maximum size as specified in the upload form"
          );
        case UPLOAD_ERR_PARTIAL:
          REST::fatal(
            REST::HTTP_BAD_REQUEST,
            "The uploaded file was only partially uploaded"
          );
        case UPLOAD_ERR_NO_TMP_DIR:
          REST::fatal(
            REST::HTTP_INTERNAL_SERVER_ERROR,
            "The server has no temporary directory specified"
          );
        case UPLOAD_ERR_CANT_WRITE:
          REST::fatal(
            REST::HTTP_INTERNAL_SERVER_ERROR,
            "The file cannot be written to disk"
          );
        case UPLOAD_ERR_EXTENSION:
          REST::fatal(
            REST::HTTP_BAD_REQUEST,
            "The extension of your file was not accepted by the server"
          );
      }
    }
    return $counter > 0;
  }
  
  
  public static function removeDir($dir) {
    exec('rm -rf ' . escapeshellarg($dir));
//    if (!file_exists($dir)) return true;
//    foreach (glob($dir . '/*') as $file) {
//      if (is_dir($file))
//        self::removeDir($file);
//      else
//        unlink($file);
//    }
//    if (rmdir($dir)) return true;
  }
  
  
  public static function debug($string) {
    mail(
      'pieterb@sara.nl',
      'PORTAL DEBUG MESSAGE',
      "$string\n\n" .
      var_export(debug_backtrace(), true) . "\n\n" .
      var_export($_SERVER, true)
    );
  }
  
  
  public static function exception_handler($e) {
    self::debug($e->getMessage() . "\n\n" . $e->getTraceAsString());
    REST::fatal(
      REST::HTTP_INTERNAL_SERVER_ERROR,
      $e->getMessage()
    );
  }
  
  
  public static function recordRequest($url, $ip='') {
    $user_id = Portal_MySQL::escape_string(Portal_User::current()->user_id());
    $esc_url = Portal_MySQL::escape_string($url);
    $esc_ip = Portal_MySQL::escape_string($ip);
    Portal_MySQL::real_query(<<<EOS
INSERT INTO `Statistics` (`requested_url`, `request_origin`, `user_id`)
     VALUES ({$esc_url}, {$esc_ip}, {$user_id});
EOS
    );
  }

} // class Portal


// Initialization code:
set_exception_handler(array('Portal', 'exception_handler'));


