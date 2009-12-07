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


/**
 * @param $title string title in UTF-8
 * @return string
 */
public static function html_start($title) {
  $t_title = htmlspecialchars($title, ENT_COMPAT, "UTF-8");
  $t_index = REST::urlencode( dirname( $_SERVER['REQUEST_URI'] ) );
  if ($t_index != '/') $t_index .= '/';
  $t_stylesheet = self::urlbase() . 'style.css';
  $t_icon       = self::urlbase() . 'favicon.png';
  return REST::xml_header() . <<<EOS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
<head>
  <title>{$t_title}</title>
  <link rel="stylesheet" type="text/css" href="{$t_stylesheet}" />
  <link rel="index" rev="child" type="application/xhtml+xml" href="{$t_index}"/>
  <link rel="icon" type="image/png" href="{$t_icon}" />
</head><body>
<div id="div_header">
<div id="div_index"><a rel="index" rev="child" href="{$t_index}">index</a></div>
<h1>{$t_title}</h1>
</div>
EOS;
}


/**
 * Outputs HTML end-tags
 * @return string
 */
public static function html_end() {
  return '</body></html>';
}


/**
 * Cache for urlbase().
 * @var string
 */
private static $URLBASE = null;
/**
 * Returns the base URI.
 * The base URI is 'protocol://server.name:port'
 * @return string
 */
public static function urlbase() {
  if ( is_null( self::$URLBASE ) ) {
    //DAV::debug('$_SERVER: ' . var_export($_SERVER, true));
    self::$URLBASE = REST::urlbase() . '/catchplus/';
  }
  return self::$URLBASE;
}


/**
 * @param $handle string
 * @return bool
 * @todo optimization by preparsed statements.
 */
public static function handleDelete($handle) {
  $eschandle = CP_MySQL::escape_string($handle);
  CP_MySQL::real_query(<<<EOS
DELETE FROM `handles` WHERE `handle` = $eschandle;
EOS
  );
  return CP_MySQL::mysql()->affected_rows;
}


} // class CP
