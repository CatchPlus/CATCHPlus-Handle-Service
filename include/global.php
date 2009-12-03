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
 **************************************************************************/

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) );

// REST stuff:
require_once('REST/REST.php');
REST::handle_method_spoofing();
REST::setHTML(
  array('CatchPlus', 'html_start'),
  array('CatchPlus', 'html_end')
);

//$debug = fopen(dirname(__FILE__) . '/debug.txt', 'a');
//fwrite($debug, "\n\n" . var_export($_SERVER, true));
//fclose($debug);

//session_name('aanwezigheidsbord');
//session_set_cookie_params( 0, dirname($_SERVER['SCRIPT_NAME']) );
//session_start();

date_default_timezone_set('Europe/Amsterdam');

function sara_exception_handler(Exception $e) {
  REST::fatal(
    REST::HTTP_INTERNAL_SERVER_ERROR,
    '<pre id="message">' . $e->getMessage() . "</pre>\n<pre>" . $e->getTraceAsString() . '</pre>'
  );
}
set_exception_handler('sara_exception_handler');


require_once 'catchplus.php';
require_once 'catchplus_mysql.php';
