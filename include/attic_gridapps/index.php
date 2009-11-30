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
 * $Id: index.php 2471 2009-08-17 20:09:55Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

REST::require_method('GET', 'HEAD');

$d = RESTDir::factory();

$d->line('applications/');
$d->line('databases/');
$d->line('databaseTypes/');
//$d->line('delegation/');
$d->line('jobstates/');
//$d->line('login');
$d->line('register');
$d->line('usage');
$d->end();
