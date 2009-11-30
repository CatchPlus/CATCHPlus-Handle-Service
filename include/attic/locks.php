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

// TODO: the DELETE handler was written by Evert, using a subquery. I'm used
// to doing this with a single JOIN query...
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') { 
  Topos::real_query(<<<EOS
UPDATE `Tokens`
SET `tokenLockTimeout` = 0, `tokenLockUUID` = null
WHERE `poolId` = (SELECT `poolId` FROM `Pools` WHERE `Pools`.`poolName` = {$escPool});
EOS
  ); 
  REST::header(array(
    'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8'
  ));
  echo REST::html_start('Locks'); 
  echo '<p>Locks destroyed successfully.</p>';
  echo REST::html_end(); 
  exit;
}

REST::require_method('HEAD', 'GET');

$result = Topos::query(<<<EOS
SELECT `tokenId`, `tokenName`, `tokenLockUUID`,
       `tokenLockTimeout` - UNIX_TIMESTAMP(), `tokenLockDescription`
FROM `Pools` NATURAL JOIN `Tokens`
WHERE `poolName` = {$escPool}
  AND `tokenLockTimeout` > UNIX_TIMESTAMP()
ORDER BY 1;
EOS
);

$directory = RESTDir::factory();
while ($row = $result->fetch_row())
  $directory->line(
    $row[2], array(
      'Token name' => $row[1],
      //'LockTokenHTML' => ($row[5] > 0 ? "<a href=\"../locks/{$row[4]}\">{$row[4]}</a>" : ''),
      'Timeout' => (
        $row[3] > 0
        ? sprintf( '%d:%02d:%02d',
                   ($row[3] / 3600),
                   ($row[3] / 60 % 60),
                   ($row[3] % 60)
          )
        : ''
      ),
      'LockDescription' => ($row[3] > 0 ? $row[4] : ''),
    )
  );
$directory->end();
