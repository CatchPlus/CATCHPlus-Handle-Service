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
 * $Id: applications_versions.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';
require_once 'portal_app.php';

REST::require_method('GET', 'HEAD');

$directory = RESTDir::factory();

list($appname, $dummy) = Portal::path_info();

foreach (Portal_App::versions($appname) as $version) {
  $directory->line($version, array( 'Description' => "$appname version $version" ));
}

$directory->end();
