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

require_once( 'include/global.php' );

REST::require_method('HEAD', 'GET');
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
  REST::fatal(REST::HTTP_NOT_MODIFIED);
  
$directory = RESTDir::factory()->setHeaders('Description');
$directory->line( 'pools/',               array( 'Description' => 'A list of all pools. Forbidden for most users, for security reasons.' ) );
$directory->line( 'newPool',              array( 'Description' => 'Redirects to a new, empty pool.' ) );
$directory->line( 'reference_manual', array( 'Description' => 'The official reference manual for this version of ToPoS.' ) );
$directory->end();
