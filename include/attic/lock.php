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

$escLockUUID = Topos::escape_string($TOPOS_TOKEN);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  Topos::real_query(<<<EOS
UPDATE `Tokens`
SET `tokenLockTimeout` = 0, `tokenLockUUID` = null
WHERE `tokenLockUUID` = {$escLockUUID};
EOS
  );
  if (Topos::mysqli()->affected_rows) {
    REST::header(array(
      'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8'
    ));
    echo REST::html_start('Lock destroyed');
    echo '<p>Lock destroyed successfully.</p>';
    echo REST::html_end();
    exit;
  } else {
    REST::fatal(REST::HTTP_NOT_FOUND);
  }
}

REST::require_method('HEAD', 'GET');

if ( !empty($_GET['timeout']) &&
     ($timeout = (int)($_GET['timeout'])) > 0 ) {
  $description = isset($_GET['description'])
    ? ', `tokenLockDescription` = ' . Topos::escape_string($_GET['description'])
    : '';
  $loopflag = 1;
  while ($loopflag) {
    try {
      Topos::real_query(<<<EOS
UPDATE `Tokens`
SET `tokenLockTimeout` = UNIX_TIMESTAMP() + {$timeout}
    {$description}
WHERE `tokenLockUUID` = {$escLockUUID}
  AND `tokenLockTimeout` > UNIX_TIMESTAMP();
EOS
      );
      if (!Topos::mysqli()->affected_rows)
        REST::fatal(REST::HTTP_NOT_FOUND);
      $loopflag = 0;
    }
    catch (Topos_Retry $e) {
      $loopflag++;
    }
  } // while
}

$result = Topos::query(<<<EOS
SELECT `tokenId`,
       `tokenName`,
       `tokenLockTimeout` - UNIX_TIMESTAMP(),
       `tokenLockDescription`
FROM `Pools` NATURAL JOIN `Tokens`
WHERE `tokenLockUUID` = $escLockUUID
  AND `tokenLockTimeout` > UNIX_TIMESTAMP();
EOS
);
if (!($row = $result->fetch_row()))
  REST::fatal(REST::HTTP_NOT_FOUND);
$tokenURL = Topos::urlbase() . 'pools/' . REST::urlencode($TOPOS_POOL) .
  '/tokens/' . $row[0];
  
$xhtmltype = REST::best_xhtml_type();
$bct = REST::best_content_type(
  array( $xhtmltype => 1,
         'text/plain' => 1 ),
  $xhtmltype
);
if ($bct === 'text/plain') {
  REST::header(array(
    'Content-Type' => 'text/plain; charset=US-ASCII',
    'Cache-Control' => 'no-cache',
  ));
  if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
  echo <<<EOS
TokenId: {$row[0]}
TokenName: {$row[1]}
TokenURL: $tokenURL
Timeout: {$row[2]}
Description: {$row[3]}
EOS;
  exit;
}

REST::header(array(
  'Content-Type' => $xhtmltype . '; charset=UTF-8',
  'Cache-Control' => 'no-cache',
));
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
echo REST::html_start('Lock info');
?><h2>Lock info</h2>
<table class="lockinfo"><tbody>
<tr><th>TokenId:</th><td id="tokenId"><?php echo htmlentities($row[0]); ?></td></tr>
<tr><th>TokenName:</th><td id="tokenName"><?php echo htmlentities($row[1]); ?></td></tr>
<tr><th>TokenURL:</th><td id="tokenURL"><a href="<?php
  echo htmlspecialchars($tokenURL, ENT_QUOTES, 'UTF-8');
?>"><?php echo htmlspecialchars($tokenURL, ENT_QUOTES, 'UTF-8'); ?></a></td></tr>
<tr><th>Timeout:</th><td id="timeout"><?php echo htmlentities($row[2], ENT_QUOTES, 'UTF-8'); ?></td></tr>
<tr><th>Description:</th><td id="description"><?php echo htmlentities($row[3], ENT_QUOTES, 'UTF-8'); ?></td></tr>
</tbody></table><?php
echo REST::html_end();
