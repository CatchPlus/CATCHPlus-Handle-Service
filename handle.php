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


// Parse the PATH_INFO string, if present:
if ( empty($_SERVER['PATH_INFO']) ||
     !preg_match( '@^/(\d+)/(.+)$@',
                 $_SERVER['PATH_INFO'], $matches ) )
  throw new Exception("Bad PATH_INFO");
$CP_PREFIX = @$matches[1];
$CP_SUFFIX = @$matches[2];


// Check if the right prefix is queried
if ($CP_PREFIX != '10574')
  REST::error(REST::HTTP_NOT_FOUND);
  

if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ||
     $_SERVER['REQUEST_METHOD'] === 'POST' ) {
       
  // If it's a POST request, the PATH_INFO string contains a "template". We must
  // convert the template in a proper, unique Handle:
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // A template is a Handle containing an asterisk '*' character.
    // The user can set eir own escape character:
//    $escape = isset($_GET['escape']) ? $_GET['escape'] : '\\';
//    if (strlen($escape) !== 1)
//      REST::fatal(REST::HTTP_BAD_REQUEST, 'Invalid escape character');
//    $escape = preg_quote($escape);
    
    // We use mysql's UUID function to create a unique string:
    $result = CP_MySQL::query('SELECT UUID()');
    $row = $result->fetch_row();
    // remove all non-hexadecimal characters (mysql adds dashes):
    $uuid = preg_replace('/[^\\da-f]/i', '', $row[0]);
    $result->free();
    
    // Parse the template and replace the asterisk with the new $uuid:
    if (!preg_match("/^((?:[^~]|~.)*)\\*((?:[^~]|~.)*)\$/s", $CP_SUFFIX, $matches))
      REST::fatal(REST::HTTP_BAD_REQUEST, 'Invalid Handle template');
    $CP_SUFFIX =
      preg_replace("/~(.)/", '$1', $matches[1]) . $uuid .
      preg_replace("/~(.)/", '$1', $matches[2]);
  }
  
  // OK, let's parse the input. We accept form data...
  if ($_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded') {
    if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) {
      $data = ''; $input = REST::inputhandle();
      while (!feof($input)) $data .= fread($input, 4096);
      fclose($input);
      $data = explode('&', $data);
      foreach($data as $value) {
        $value = split('=', $value);
        $key = urldecode($value[0]);
        if (empty($value[0]) || count($value) != 2)
          REST::fatal(REST::HTTP_BAD_REQUEST, 'Error parsing form data');
        if (preg_match('/^([^\\[]+)\\[(.+)\\]$/', $key, $matches))
          $_POST[$matches[1]][$matches[2]] = urldecode($value[1]);
        else
          $_POST[$key] = urldecode($value[1]);
      }
    }
    $input_handle = $_POST;
  }
    
  // ... and JSON:
  elseif($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $json = ''; $input = REST::inputhandle();
    while (!feof($input)) $json .= fread($input, 4096);
    fclose($input);
    $json = json_decode($json, true);
    if (!is_array($json)) REST::fatal(
      REST::HTTP_BAD_REQUEST,
      'Couldn\'t parse JSON request body.'
    );
    foreach($json as $idx => $value) {
      $input_handle['type'][$idx] = @$value['type'];
      $input_handle['data'][$idx] = @$value['data'];
      $input_handle['refs'][$idx] = @$value['refs'];
    }
    
  }
  else
    REST::fatal(
      REST::HTTP_BAD_REQUEST, var_export($_POST, true) . 
      "Content-Type {$_SERVER['CONTENT_TYPE']} not supported"
    );
    
  // Validate the input and fill $handle as we go:
  $handle = new CP_Handle("{$CP_PREFIX}/{$CP_SUFFIX}");  
  if (!is_array($input_handle['type']))
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Invalid input (no types?)');
  foreach ($input_handle['type'] as $idx => $type) {
    if (!preg_match('/^\\d+$/', $idx))
      REST::fatal(REST::HTTP_BAD_REQUEST, "Illegal key in JSON request body: '$idx'");
    if ( !preg_match('/^[^\\.]+(?:\\.[^\\.]+)*$/', $type) )
      REST::fatal(REST::HTTP_BAD_REQUEST, "Illegal type for index $idx");
    $handle->type[$idx] = $type;
    if ( !isset($input_handle['data'][$idx]) )
      REST::fatal(REST::HTTP_BAD_REQUEST, "Missing required data field for index $idx");
    $handle->data[$idx] = (string)($input_handle['data'][$idx]);
    if ( isset($input_handle['refs'][$idx]) ) {
      if ( !preg_match( '/^(?:[^\\t:]+:[^\\t]+(?:\\t[^\\t:]+:[^\\t]+)*)?$/',
                        $input_handle['refs'][$idx]) )
        REST::fatal(REST::HTTP_BAD_REQUEST, "Illegal refs for index $idx");
      $handle->refs[$idx] = (string)($input_handle['refs'][$idx]);
    }
  }
  
  // Check user-supplied precondition (create or update):
  if ( ( $_SERVER['REQUEST_METHOD'] === 'PUT' &&
         @$_GET['mode'] === 'create' ) &&
       $handle->read() )
    REST::fatal(REST::HTTP_PRECONDITION_FAILED, 'Handle exists.');
  elseif ( $_SERVER['REQUEST_METHOD'] === 'PUT' &&
       @$_GET['mode'] === 'update' &&
       !$handle->delete() )
    REST::fatal(REST::HTTP_PRECONDITION_FAILED, 'Handle doesn\'t exist.');
  else $handle->delete();
    
  // Store the data...
  $handle->create();
  // and return the proper response:
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'PUT':
      REST::fatal( REST::HTTP_OK );
      break;
    case 'POST':
      REST::created( REST::urlbase() . CP::PORTAL_URL . urlencode($CP_PREFIX) . 
                     '/' . urlencode($CP_SUFFIX));
      break;
    default: // this shouldn't happen
      REST::fatal(REST::HTTP_INTERNAL_SERVER_ERROR);
  }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $handle = new CP_Handle("$CP_PREFIX/$CP_SUFFIX");
  REST::fatal(
    $handle->delete() ?
      REST::HTTP_OK : REST::HTTP_NOT_FOUND
  );
}

else {
  // If we're here, the request method should be GET or HEAD. Otherwise, quit:
  REST::require_method('GET', 'HEAD');
  // Create a CP_Handle object...
  $handle = new CP_Handle("$CP_PREFIX/$CP_SUFFIX");
  // and read its contents from the database. If it's not there...
  if (!$handle->read())
    // return a 404 Not Found to the client.
    REST::fatal(REST::HTTP_NOT_FOUND);
    
  // The simplest response is the HTTP/1.1 307 Moved Temporarily.
  // The client can suppress this behaviour by sending a redirect=no query
  // parameter:
  if ( !isset($_GET['redirect']) ||
       !in_array( strtolower($_GET['redirect']),
                  array('', 'no', 'false', '0') ) ) {
    // The client MAY specify an index=n query parameter, to select a specific
    // URL:
    $index = isset($_GET['index']) ? (int)$_GET['index'] : null;
    // If the client didn't specify an index, find the URL with the lowest index:
    if ($index === null) {
      foreach ($handle->type as $idx => $type)
        if ( $type == 'URL' &&
             ( ( $index === null ) || ( $idx < $index ) ) )
          $index = $idx;
      // If there's no URL, return a 404 Not Found:
      if ($index === null)
        REST::fatal(REST::HTTP_NOT_FOUND, 'No URL for this handle');
    }
    // If the user-specified index doesn't exist or doesn't point to a URL
    // field, return a 404 Not Found:
    elseif ( !isset($handle->type[$index]) ||
               $handle->type[$index] != 'URL')
      REST::fatal(REST::HTTP_NOT_FOUND, 'No URL with that index');
    // Finally, get the URL:
    $url = $handle->data[$index];
    // Append the URL with the current query string.
    // We leave out the "index" and "redirect" fields, though.
    unset($_GET['index']); unset($_GET['redirect']);
    if (!empty($_GET))
      $url .= '?' . http_build_query($_GET);
    // Finally, perform the actual redirect:
    REST::redirect(REST::HTTP_TEMPORARY_REDIRECT, $url);
  }
  
  $xhtml_type = REST::best_xhtml_type() . '; charset=UTF-8';
  $content_type = REST::best_content_type( array(
      $xhtml_type => 1.0,
      'application/json' => 1.0,
      'application/x-www-form-urlencoded' => 1.0,
      'text/plain; charset=US-ASCII' => 0.5,
    ), $xhtml_type
  );
  // When was this handle last modified?
  $modified = 0;
  foreach($handle->timestamp as $idx => $timestamp)
    if ($timestamp > $modified)
      $modified = $timestamp;
  REST::check_if_modified_since($modified);
  REST::header(array(
    'status' => REST::HTTP_OK,
    'Content-Type' => $content_type,
    'Modified' => REST::http_date($modified),
  ));
  // For a HEAD request, we can quit now:
  if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
  
  if ($content_type == $xhtml_type) {
    echo REST::html_start('Metadata for handle ' . htmlspecialchars($handle->handle(), ENT_COMPAT, 'UTF-8'));
    echo <<<EOS
<table class="handledata"><tbody><tr>
<th class="idx">idx</th>
<th class="type">type</th>
<th class="data">data</th>
<th class="data">refs</th>
<th class="modified">timestamp</th>
</tr>
EOS;
    foreach ($handle->type as $idx => $type) {
      if (strpos($type, 'HS_') === 0) continue;
      echo '<tr><td class="idx">' . $idx . '</td><td class="type">' .
        htmlspecialchars($type, ENT_COMPAT, 'UTF-8') . '</td><td class="data">';
      if ($type == 'URL' && REST::isValidURI($handle->data[$idx]) )
        echo '<a href="' . $handle->data[$idx] . '">' . htmlspecialchars($handle->data[$idx]) . '</a>';
      elseif ($type == 'EMAIL' )
        echo '<a href="mailto:' . $handle->data[$idx] . '">' . htmlspecialchars($handle->data[$idx]) . '</a>';
      else
        echo $handle->data[$idx] === mb_convert_encoding( 
               mb_convert_encoding( $handle->data[$idx], 'UTF-32', 'UTF-8' ),
               'UTF-8', 'UTF-32'
             )
          ? htmlspecialchars($handle->data[$idx], ENT_COMPAT, 'ISO-8859-1')
          : '<pre>' . htmlspecialchars(
              addcslashes( $handle->data[$idx], "\\\x00..\x09\x0b..\x1f\x7f..\xff" ),
              ENT_COMPAT, 'ISO-8859-1'
            ) . '</pre>';
      echo '</td><td class="refs">' . htmlspecialchars($handle->refs[$idx]) . '</td><td class="modified">' . REST::http_date($handle->timestamp[$idx]) . "</td></tr>\n";
    }
    echo '</tbody></table>' . REST::html_end();
  }
  
  elseif ($content_type == 'application/json') {
    $json = array();
    foreach ($handle->type as $idx => $type)
      if (strpos($type, 'HS_') !== 0) // Was: $type != 'HS_ADMIN'
        $json[$idx] = array(
          // De velden die het interne authorisatie-systeem van Handle betreffen
          // heb ik uitgecommentarieerd, omdat deze service (voorlopig) helemaal
          // geen gebruik maakt van Handle's authorisatie.
          'type' => (string)($type),
          'data' => (string)($handle->data[$idx]),
          //'ttl_type' => (int)($handle->ttl_type[$idx]),
          //'ttl' => (int)($handle->ttl[$idx]),
          'timestamp' => (int)($handle->timestamp[$idx]),
          'refs' => (string)($handle->refs[$idx]),
          //'admin_read' => (int)($handle->admin_read[$idx]),
          //'admin_write' => (int)($handle->admin_write[$idx]),
          //'pub_read' => (int)($handle->pub_read[$idx]),
          //'pub_write' => (int)($handle->pub_write[$idx]),
        );
    echo json_encode($json);
    exit;
  }
  
  elseif ($content_type == 'application/x-www-form-urlencoded') {
    $pairs = array();
    foreach ($handle->type as $idx => $type)
      if (strpos($type, 'HS_') !== 0) {
        $pairs[] = urlencode("type[$idx]") . '=' . urlencode($type);
        $pairs[] = urlencode("data[$idx]") . '=' . urlencode($handle->data[$idx]);
        $pairs[] = urlencode("timestamp[$idx]") . '=' . urlencode($handle->timestamp[$idx]);
        $pairs[] = urlencode("refs[$idx]") . '=' . urlencode($handle->refs[$idx]);
      }
    echo implode('&', $pairs);
    exit;
  }
  
  elseif ($content_type == 'text/plain; charset=US-ASCII') {
    foreach ($handle->type as $idx => $type)
      if ($type == 'URL')
        echo $handle->data[$idx] . "\r\n";
    exit;
  }
}
