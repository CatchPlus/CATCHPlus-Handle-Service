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
 * $Id: usage.php 2471 2009-08-17 20:09:55Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';


$user = Portal_User::current();
$userid = $user->user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$user->user_spoofed())
    Portal_User::unauthorized();
    
  if (!isset($_POST['token']))
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Missing required parameter "token"');
  $token = (int)$_POST['token'];
  if (!isset($_POST['seconds']))
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Missing required parameter "seconds"');
  $seconds = (int)$_POST['seconds'];
  if (!isset($_POST['status']))
    REST::fatal(REST::HTTP_BAD_REQUEST, 'Missing required parameter "status"');
  $status = (int)$_POST['status'];
  Portal_MySQL::real_query(<<<EOS
INSERT INTO `Usage` (`user_id`, `usage_seconds`, `token_id`, `usage_status`)
VALUES ({$userid}, {$seconds}, {$token}, {$status});
EOS
  );
  REST::fatal(REST::HTTP_ACCEPTED);
}

REST::require_method('GET', 'HEAD');

$result = Portal_MySQL::query(<<<EOS
SELECT SUM(`usage_seconds`),
       DATE(`usage_timestamp`),
       `usage_status`
FROM `Usage`
WHERE `user_id` = {$userid}
GROUP BY 3,2
ORDER BY 3,2 ASC;
EOS
);

REST::header(REST::best_xhtml_type() . '; charset="UTF-8"');
echo REST::html_start('Usage statistics') . <<<EOS
<!--<form action="stats" method="post">
token: <input type="text" name="token" value=""/>
seconds: <input type="text" name="seconds" value=""/>
<input type="submit"/>
</form>-->
<table class="usagestats"><tbody>
<tr>
<th class="date">Date</th>
<th class="walltime">Walltime</th>
<th class="status">Status</th>
</tr>
EOS;
$STATUS_STRING = array( 0 => 'OK', 1 => 'Transient error', 2 => 'Fatal error' );
while ($row = $result->fetch_row())
  echo '<tr><td class="date">' . gmstrftime('%F', strtotime($row[1] . 'T00:00:00Z')) .
       '</td><td class="walltime">' .
       sprintf(
         '%d:%02d:%02d',
         floor($row[0] / 3600),
         floor($row[0] % 3600 / 60),
         ($row[0] % 60)
       ) .
       '</td><td class="status">' .
       $STATUS_STRING[$row[2]] .
       "</td></tr>\n";
echo '</tbody></table>' . REST::html_end();
