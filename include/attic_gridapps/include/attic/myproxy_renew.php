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
 * $Id: myproxy_renew.php 2378 2009-07-14 14:00:34Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once('include/global.php');

if ( Portal::user_dn() != @$_SERVER['SSL_SERVER_S_DN'] )
  REST::fatal(
    REST::HTTP_UNAUTHORIZED
  );

REST::require_method('GET');

foreach (glob(Portal::PROXY_DIR . '*.pem') as $fullfilename) {
  $escfullfilename = escapeshellarg($fullfilename);
  exec("grid-proxy-info -f {$escfullfilename} -exists -valid 1:00", $output, $returnval);
  if (!$returnval) continue; // The proxy is valid for at least another hour

  $user_dn_md5 = Portal_MySQL::escape_string( basename($fullfilename, '.pem') );
  $result = Portal_MySQL::query(<<<EOS
SELECT `proxy_server`, `proxy_username`, `proxy_password` FROM `User`
 WHERE `user_dn_md5` = {$user_dn_md5};
EOS
  );
  
  if ($row = $result->fetch_row()) {
    $escusername = escapeshellarg( $row[1] );
    $escpassword = escapeshellarg( $row[2] );
    $escserver   = escapeshellarg( $row[0] );
    exec("echo {$escpassword} | myproxy-logon -v -l {$escusername} -s {$escserver} -S -o {$escfullfilename} 2>&1", $output, $returnval);
    
    if ($returnval) {
      unlink($fullfilename);
      Portal_MySQL::query(<<<EOS
UPDATE `User` SET `proxy_server` = NULL, `proxy_username` = NULL, `proxy_password` = NULL
 WHERE `user_dn_md5` = {$user_dn_md5};
EOS
      );
    }
  }
  
//  exec("grid-proxy-info -f {$escfullfilename} -timeleft", $output, $returnval);
//  if ((int)$output[0] <= 0) { // The proxy has expired completely
//    unlink($fullfilename);
//    Portal_MySQL::query(<<<EOS
//UPDATE `User` SET `proxy_server` = NULL, `proxy_username` = NULL, `proxy_password` = NULL
// WHERE `user_dn_md5` = '{$user_dn_md5}';
//EOS
//    );
//  }

}

REST::header(array('status' => REST::HTTP_NO_CONTENT));
