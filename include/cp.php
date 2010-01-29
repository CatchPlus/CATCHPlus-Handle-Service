<?php

/*·************************************************************************
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
 **************************************************************************/

/**
 * File documentation.
 * @package CP
 */

/**
 * @package CP
 */
class CP {

  
const PORTAL_URL = '/catchplus/';

/**
 * @param $title string title in UTF-8
 * @return string
 */
public static function html_start($title) {
  $title = REST::htmlspecialchars($title);
  $t_index = REST::urlencode( dirname( $_SERVER['REQUEST_URI'] ) );
  if ($t_index != '/') $t_index .= '/';
  $t_index = REST::htmlspecialchars($t_index);
  $portalurl = self::PORTAL_URL;
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


/**
 * Outputs HTML end-tags
 * @return string
 */
public static function html_end() {
  $retval = '<div id="footer">';
//    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//      if ($_SERVER['SERVER_PORT'] == self::PORT_SSL) {
//        $switchto = 'X.509 Client Certificate Authentication';
//        $url = 'https://' . $_SERVER['SERVER_NAME'] . ':' . self::PORT_SSL_CSA .
//          $_SERVER['REQUEST_URI'];
//      } elseif ($_SERVER['SERVER_PORT'] == self::PORT_SSL_CSA) {
//        $switchto = 'Username/Password Authentication';
//        $url = 'https://' . $_SERVER['SERVER_NAME'] . ':' . self::PORT_SSL .
//          $_SERVER['REQUEST_URI'];
//      }
//      $retval .= '<div id="changeauth"><a href="' .
//        REST::htmlspecialchars($url) . '">Switch to ' . $switchto . '</a></div>';
//    }
  $retval .= '</div></body></html>';
  return $retval;
}


} // class CP
