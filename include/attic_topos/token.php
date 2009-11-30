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
 * 
 * $Id$
 **************************************************************************/

require_once('include/global.php');

$escPool = Topos::escape_string($TOPOS_POOL);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $retries = 1;
  while ($retries) {
    try {
      $query = <<<EOS
DELETE `Tokens`, `TokenValues`
FROM `Tokens` NATURAL JOIN `Pools` NATURAL JOIN `TokenValues`
WHERE `Tokens`.`tokenId` = {$TOPOS_TOKEN}
  AND `poolName` = {$escPool};
EOS;
      Topos::real_query($query);
      $retries = 0;
    }
    catch (Topos_Retry $e) {
      $retries++;
    }
  }
  if (Topos::mysqli()->affected_rows) {
    REST::header(array(
      'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8'
    ));
    echo REST::html_start('Token destroyed');
    echo '<p>Token destroyed successfully.</p>';
    echo REST::html_end();
    exit;
  } else {
    REST::fatal(REST::HTTP_NOT_FOUND);
  }
}

REST::require_method('HEAD', 'GET');
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
  REST::fatal(REST::HTTP_NOT_MODIFIED);

$query = <<<EOS
SELECT `tokenValue`, `tokenType`, `tokenCreated`, `tokenName`,
       IF(`tokenLockTimeout` > UNIX_TIMESTAMP(),`tokenLockUUID`,NULL)
FROM `Pools` NATURAL JOIN `Tokens` NATURAL JOIN `TokenValues`
WHERE `poolName`  = {$escPool}
  AND `tokenId`   = {$TOPOS_TOKEN};
EOS;
$result = Topos::query($query);
if (!($row = $result->fetch_row()))
  REST::fatal(REST::HTTP_NOT_FOUND);

$headers = array(
  'Content-Type' => $row[1],
  'Content-Length' => strlen($row[0]),
  'Last-Modified' => REST::http_date($row[2]),
);
if (!empty($row[3]))
  $headers['Content-Disposition'] = 'inline; filename="' . $row[3] . '"';
  
if ($row[4]) {
  $headers['X-Topos-OpaqueLockToken'] = "opaquelocktoken:{$row[4]}";
  $headers['X-Topos-LockURL'] = Topos::urlbase() . 'pools/' . REST::urlencode($TOPOS_POOL) .
    '/locks/' . $row[4];
}
REST::header($headers);
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
echo $row[0];
