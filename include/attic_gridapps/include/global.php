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
 * $Id: global.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @todo - The call register_shutdown_function at the end of this document
 * doesn't look too good.
 * @package Portal
 */

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) );

require_once 'REST/REST.php';
REST::handle_method_spoofing();
REST::setHTML(
  array('Portal', 'html_start'),
  array('Portal', 'html_end')
);

require_once 'portal.php';
require_once 'portal_db.php';
require_once 'portal_mysql.php';
require_once 'portal_user.php';

//register_shutdown_function(
//  array('Portal', 'recordRequest'),
//  ( !empty($_SERVER['HTTPS']) ? 'https://' : 'http://' ) .
//    $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] .
//    $_SERVER['REQUEST_URI'],
//    $_SERVER['REMOTE_ADDR']
//);
