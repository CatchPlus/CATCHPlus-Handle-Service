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
 * $Id: database_types.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @todo Implement retrieval of info about individual database types.
 * @package Portal
 */

require_once 'include/global.php';

REST::require_method('GET', 'HEAD');

$path_info = Portal::path_info();
if (!empty($path_info[0])) {
  $id = Portal_DB::databaseTypeIDByName($path_info[0]);
  if (empty($id))
    REST::fatal(REST::HTTP_NOT_FOUND);
  REST::header(REST::best_xhtml_type() . '; charset="UTF-8"');
  echo
    REST::html_start("Database type \"{$path_info[0]}\"") .
    Portal_DB::databaseTypeDescription($id) .
    REST::html_end();
  exit;
}
  
$directory = RESTDir::factory();
foreach (Portal_DB::databaseTypeIDs() as $id)
  $directory->line(
    Portal_DB::databaseTypeName($id), array(
      'Content-Type' => Portal_DB::databaseTypeContentType($id),
      'Extension' => Portal_DB::databaseTypeExtension($id),
    )
  );

$directory->end();
