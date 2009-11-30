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
 * $Id: databases_versions.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

REST::require_method('GET', 'HEAD');

$user_id = Portal_User::current()->user_id();
$path_info = Portal::path_info();
$dbname = Portal_MySQL::escape_string($path_info[0]);

$result = Portal_MySQL::query(<<<EOS
SELECT DISTINCT(`version`) FROM `Database`
WHERE `name` = {$dbname}
  AND ( `is_shared` > 0 OR `user_id` = {$user_id} );
EOS
);

$directory = RESTDir::factory("$path_info[0]: available versions");

while ($row = $result->fetch_row()) {
  $directory->line($row[0] . '/');
}

$directory->end();
