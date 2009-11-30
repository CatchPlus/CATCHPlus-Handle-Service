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

$escRealm = Topos::escape_string($TOPOS_REALM);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ( empty($_POST['pool']) ||
       empty($_POST['tokens']) )
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Missing one or more required parameters');
  $pool = $_POST['pool'];
  $tokens = (int)($_POST['tokens']);
  if ( !preg_match('/^[\\w\\-.]+$/', $pool) ||
       !$tokens || $tokens > 1000000)
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Illegal parameter value(s)');
  $escPoolName = Topos::escape_string($pool);
  Topos::real_query(
    "CALL `createTokens`({$escRealm}, {$escPoolName}, {$tokens});"
  );
  Topos::log('populate', array(
    'realmName' => $TOPOS_REALM,
    'poolName' => $TOPOS_POOL,
    'tokens' => $tokens
  ));
  REST::header(array(
    'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8'
  ));
  echo REST::html_start('Realm');
  echo '<p>Pool populated successfully.</p>' .
       '<p><a href="./" rel="index">Back</a></p>';
  echo REST::html_end();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  Topos::real_query('START TRANSACTION;');
  try {
    Topos::real_query(<<<EOS
DELETE `Tokens`.* FROM `Tokens` NATURAL JOIN `Pools`
WHERE `Pools`.`realmName` = {$escRealm};
EOS
    );
    Topos::log('delete', array(
      'realm' => $TOPOS_REALM,
      'tokens' => Topos::mysqli()->affected_rows
    ));
  }
  catch (Topos_MySQL $e) {
    Topos::mysqli()->rollback();
    throw $e;
  }
  if (!Topos::mysqli()->commit())
    REST::fatal(
      REST::HTTP_SERVICE_UNAVAILABLE,
      'Transaction failed: ' . htmlentities( Topos::mysqli()->error )
    );
  REST::header(array(
    'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8'
  ));
  echo REST::html_start('Pool');
  echo '<p>Realm destroyed successfully.</p>';
  echo REST::html_end();
  exit;
}

REST::require_method('HEAD', 'GET');
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
  REST::fatal(REST::HTTP_NOT_MODIFIED);


$directory = ToposDirectory::factory(<<<EOS
<h2>Delete</h2>
<form action="./?http_method=DELETE" method="post">
<input type="submit" value="Delete this realm"/>
</form>
<h2>Populate new pool</h2>
<form action="./" method="post">
<input type="text" name="pool"/> Pool name<br/>
<input type="text" name="tokens"/> #tokens<br/>
<input type="submit" value="Populate"/>
</form>
<h2>Getting the next token</h2>
<form action="nextToken" method="get">
<input type="text" name="pool"/> Pool name RegExp<br/>
<input type="text" name="token"/> Token value RegExp<br/>
<input type="text" name="timeout"/> Timeout in seconds (leave empty for shared tokens)<br/>
<input type="submit" value="Get next token"/>
</form>
EOS
);
$directory->line('locks/');
$directory->line('pools/');
$directory->line('nextToken', '', 'GET or PUT the next token');
$directory->end();
