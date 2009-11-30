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

REST::require_method('HEAD', 'GET');

if ( !preg_match('/^(?:145\\.100\\.(?:6|7|15)\\.|82\\.93\\.61\\.215)/',
     $_SERVER['REMOTE_ADDR']) )
  REST::fatal(
    REST::HTTP_FORBIDDEN,
    <<<EOS
<p>Sorry, for security reasons you're not allowed to get a directory
listing for this URL.</p>
<p>However, you <em>do</em> have access to any subdirectory,
such as <a href="example/">this</a>.</p>
EOS
  );

$result = Topos::query(<<<EOS
SELECT `poolName`, COUNT(*)
FROM `Pools` NATURAL JOIN `Tokens`
GROUP BY `poolId`
ORDER BY 1;
EOS
);

$directory = RESTDir::factory();
while ($row = $result->fetch_row())
  $directory->line(
    urlencode($row[0]) . '/',
    array( 'Size' => $row[1] . ' tokens' )
  );
$directory->end();
