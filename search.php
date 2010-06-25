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
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
  REST::fatal(REST::HTTP_NOT_MODIFIED);

// max: maximum number of results to return. Only if GET param is set and bigger than zero.
$max = !empty($_GET['max']) && ((int)$_GET['max'] > 0) ?
  (int)$_GET['max'] :
  0;
unset($_GET['max']);

// page: the page of searchresults. Only if GET param is set, in combination with $max and bigger than or equal to 0
$page = $max > 0 && !empty($_GET['page']) && ((int)$_GET['page'] >= 0) ?
  (int)$_GET['page'] :
  0;
unset($_GET['page']);

// mode: searchmode, either regexp or not.
$mode = !empty($_GET['mode']) && strtolower($_GET['mode']) === "regexp" ?
  "REGEXP" :
  "=";
unset($_GET['mode']);

// Get only the correctly specified search fields and escape all keys / values
// Keys first, then values, because we will search for [KEY] = [VALUE]
$params = array();
foreach ($_GET as $key => $value) {
  $key = $key;
  $value = $value;
  if (!preg_match('/^[^\\^a-z.]+(?:\\.[^\\.]+)*$/', $key)) {
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      "Malformed data type '{$key}'. Make sure it applies to the Handle format and all of its alphabetical characters are UPPERCASE.");
  }
  if (strlen($value)) {
    // Deze manier van opslaan leek Evert handig (duh!) om de array later te
    // kunnen gebruiken in een "bind" met een prepared statement.
    $params[] = $key;
    $params[] = $value;
  }
}

// If no search params are supplied we have nothing to search for
if (sizeof($params) === 0) {
  REST::fatal(
    REST::HTTP_BAD_REQUEST,
    "Please specify at least one search parameter."
  );
}

// Preparing SQL statement
// Number of conditions a handle needs to apply for
$occurrences = (int)(sizeof($params) / 2);
// Conditions
$conditions = implode(
  " OR ",
  array_fill(
    0,
    $occurrences,
    "(type {$mode} ? AND data {$mode} ? )"
  )
);
// Limits
$limit = $max > 0 ? "LIMIT " . ($page * $max) . ", " . $max : "";
// Repeating 's' for the prepared statement
$type = str_repeat('s', 2 * $occurrences);
// Perparing statement
$search_stmt = CP_MySQL::mysql()->prepare(<<<EOS
SELECT DISTINCT `handle`, COUNT(`handle`) as `occurrences`
FROM `handles`
WHERE {$conditions}
GROUP BY `handle`
HAVING `occurrences` = {$occurrences}
ORDER BY `handle` DESC
{$limit};
EOS
);

// Bind search parameters to the query
call_user_func_array(
  'mysqli_stmt_bind_param',
  array_merge (
    array($search_stmt, $type),
    $params
  )
);

// Execute query
if (!$search_stmt->execute()) {
  switch ( CP_MySQL::mysql()->errno ) {
  case 1139:
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      CP_MySQL::mysql()->error
    );
  default:
    throw new CP_MySQL_Exception(
      CP_MySQL::mysql()->error,
      CP_MySQL::mysql()->errno
    );
  }
}

// Bind results to a variable
$search_stmt->bind_result($handle, $tmp_occurrences);

// TODO Onderstaande misschien nog omschrijven naar REST_Directory oid.

//... And print everything
$xhtml_type = REST::best_xhtml_type() . '; charset=UTF-8';
$content_type = REST::best_content_type( array(
    $xhtml_type => 1.0,
    'application/json' => 1.0,
    'application/x-www-form-urlencoded' => 1.0,
    'text/plain; charset=US-ASCII' => 0.5,
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
