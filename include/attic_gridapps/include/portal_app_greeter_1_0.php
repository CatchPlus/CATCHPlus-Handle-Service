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
 * $Id: portal_app_greeter_1_0.php 2463 2009-08-12 08:58:33Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once dirname(__FILE__) . '/portal_app.php';


/**
 * Class documentation.
 * @package Portal
 */
class Portal_App_greeter_1_0 extends Portal_App {
  
  public function doGET() {
?><p>Your name: <input name="name" type="text" /><br/>
<input type="submit" value="Get greetz!" /></p><?php
  }
  
  
  public function doPOST($sandbox, &$bashcode, &$database) {
    
    $name = $_POST['name'];
    if (empty($name))
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        'Missing required parameter "name"'
      );
    file_put_contents($sandbox . 'name.txt', $name);
  
    $bashcode = <<<EOS
\${APPDIR}greeter \$(< "\${INDIR}name.txt" )
EOS;
  
  } // function doPOST()
  
  
} // class Portal_App

