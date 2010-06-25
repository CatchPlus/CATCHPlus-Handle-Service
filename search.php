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

require_once( 'include/global.php' );

REST::require_method('HEAD', 'GET');
//if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
//  REST::fatal(REST::HTTP_NOT_MODIFIED);

/**
 * maximum number of results to return.
 * Only if GET param is set and bigger than zero.
 * @var integer
 */
$p_max = 100;
if (isset($_GET['max'])) {
  if (!preg_match('/^\\d+$/', $_GET['max'])) // pos.int.: '/^0*[1-9]\\d*$/'
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Bad value for GET-parameter "max".');
  $p_max = (int)$_GET['max'];
  unset($_GET['max']);
}

/**
 * the page of searchresults.
 * Only if GET param is set, in combination with $max and bigger than or equal
 * to 0
 * @var integer
 */
$p_page = 0;
if (isset($_GET['page'])) {
  if (!preg_match('/^\\d+$/', $_GET['page']))
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Bad value for GET-parameter "page".');
  if ($p_max > 0) $p_page = (int)$_GET['page'];
  unset($_GET['page']);
}

/**
 * searchmode.
 * values can be '=', 'LIKE' and maybe even 'REGEXP'
 * @var $p_mode string
 */
$p_mode = '=';
if (isset($_GET['mode'])) {
  switch( strtolower( $_GET['mode'] ) ) {
    case 'wildcard':
      $p_mode = 'LIKE';
      break;
  }
  unset($_GET['mode']);
}

// Get only the correctly specified search fields and escape all keys / values
// Keys first, then values, because we will search for [KEY] = [VALUE]
$parampairs = array();
foreach ($_GET as $key => $value) {
  if (!preg_match('/^[^\\.]+(?:\\.[^\\.]+)*$/', $key))
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      "Malformed data type '{$key}'."
    );
  $parampairs[] = array($key, $value);
}

// Hier komt het uiteindelijke resultaat in:
$handles = null;
$statement = CP_MySQL::mysql()->prepare(<<<EOS
SELECT DISTINCT `handle`
FROM `handles`
WHERE `type` = ? AND `data` {$p_mode} ?
EOS
);
$statement->bind_param( 'ss', $key, $value );

foreach ($parampairs as $parampair) {
  list($key, $value) = $parampair;
  if ($p_mode == 'LIKE') {
    preg_match_all('/([^~]|~.|~)/s', $value, $matches);
    $value = implode(
      preg_replace(
        array('/%/', '/_/', '/^\\*/', '/^~(.+)/s'),
        array('\\%', '\\_', '%',      '$1'       ),
        $matches[0]
      )
    );
  }
  if (!$statement->execute()) {
    switch ( CP_MySQL::mysql()->errno ) {
    case 1139:
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        CP_MySQL::mysql()->error
      );
      break;
    default:
      throw new CP_MySQL_Exception(
        CP_MySQL::mysql()->error,
        CP_MySQL::mysql()->errno
      );
    }
  }
  $r_handle = null;
  $statement->bind_result( $r_handle );
  $r_handles = array();
  while ($statement->fetch())
    $r_handles[$r_handle] = 1;
  if ($handles === null)
    $handles = $r_handles;
  else
    $handles = array_intersect_key($handles, $r_handles);
}

ksort($handles);
$handles = array_keys($handles);
if ($p_max > 0)
  $handles = array_slice($handles, $p_page * $p_max, $p_max);

//... And print everything
$xhtml_type = REST::best_xhtml_type() . '; charset=UTF-8';
$content_type = REST::best_content_type( array(
    $xhtml_type => 1.0,
    'application/json' => 1.0,
  ), $xhtml_type
);

REST::header(array(
  'status' => REST::HTTP_OK,
  'Content-Type' => $content_type,
));

// For a HEAD request, we can quit now:
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;

if ($content_type == $xhtml_type) {
  echo REST::html_start('Searchresults');
  echo <<<EOS
<table class="searchresults"><tbody><tr>
<th class="handle">Handle</th>
</tr>
EOS;
  $num_rows = 0;
  while ($search_stmt->fetch()) {
    $num_rows++;
    echo "<tr class=\"handle\"><td><a href=\"" . CP::PORTAL_URL . "{$handle}\">{$handle}</a></td></tr>";
  }
  if (!$num_rows) {
    echo "<tr class=\"handle\"><td>No results found</td></tr>";
  }
  echo "</tbody></table>";
  echo REST::html_end();
}

elseif ($content_type == 'application/json') {
  $json = array();
  while ($search_stmt->fetch()) {
    $json[] = $handle;
  }
  echo json_encode($json);
  exit;
}

elseif ($content_type == 'application/x-www-form-urlencoded') {
  $pairs = array();
  $index = 0;
  while ($search_stmt->fetch()) {
    $pairs[] = urlencode("handle[]") . '=' . urlencode($handle);
  }
  echo implode('&', $pairs);
  exit;
}

elseif ($content_type == 'text/plain; charset=US-ASCII') {
  while ($search_stmt->fetch()) {
    echo $handle . "\r\n";
  }
  exit;
}