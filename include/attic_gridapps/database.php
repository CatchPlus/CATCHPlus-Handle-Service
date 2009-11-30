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
 * $Id: database.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

$path_info = Portal::path_info();
if (count($path_info) != 3)
  REST::fatal(
    REST::HTTP_NOT_FOUND
  );
$file = explode('.', $path_info[2], 2);
if ( !( $database_id = (int)($file[0]) ) )
  REST::fatal( REST::HTTP_NOT_FOUND );
$realfilepath = Portal_DB::DATABASE_DIR . $database_id;
  $user_id = Portal_User::current()->user_id();


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
  Portal_MySQL::real_query(<<<EOS
DELETE FROM `Database`
WHERE `database_id` = {$database_id}
  AND `user_id` = {$user_id};
EOS
  );
  if (!Portal_MySQL::mysql()->affected_rows)
    Portal_User::unauthorized();
  unlink($realfilepath);
  REST::fatal(REST::HTTP_NO_CONTENT);
}


REST::require_method('GET', 'HEAD');

$path_info = Portal::path_info();
if (count($path_info) != 3)
  REST::fatal(
    REST::HTTP_NOT_FOUND
  );
$file = explode('.', $path_info[2], 2);
if ( !( $database_id = (int)($file[0]) ) )
  REST::fatal( REST::HTTP_NOT_FOUND );

$user_id = Portal_User::current()->user_id();
$result = Portal_MySQL::query(<<<EOS
SELECT `d`.`name`,
       `d`.`version`,
       `d`.`type`,
       `d`.`checksum`,
       `u`.`user_name`
  FROM `Database` AS d LEFT JOIN `User` AS u USING(`user_id`)
 WHERE `d`.`database_id` = {$database_id}
   AND (`d`.`user_id` = {$user_id} OR `d`.`is_shared` = 1);
EOS
);

if (!($row = $result->fetch_row()))
  REST::fatal( REST::HTTP_NOT_FOUND );

$fileinfo = @stat($realfilepath);
$filename = "{$row[0]}-{$row[1]}." . Portal_DB::databaseTypeExtension($row[2]);

REST::header(array(
  'Content-Type' => Portal_DB::databaseTypeContentType($row[2]),
  'Content-Encoding' => 'identity',
  'Content-Disposition' => "attachment; filename=\"{$filename}\"",
  'Last-Modified' => REST::http_date( $fileinfo['mtime'] ),
  'ETag' => "\"{$row[3]}\"",
  'X-Creator-Name' => $row[4],
  'Content-Length' => $fileinfo['size'],
));
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
  readfile( $realfilepath );
