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
 * $Id: myproxy.php 2378 2009-07-14 14:00:34Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once('include/global.php');

REST::require_method('GET', 'HEAD', 'POST');

//$output = exec("echo coeE8421vk | myproxy-logon -v -l '/O=dutchgrid/O=users/O=sara/CN=Evert Lammerts' -S");
//echo $output;

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
  if ( empty($_POST['username']) ||
       empty($_POST['password']) ||
       empty($_POST['server'])) {
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      'Missing required parameter.'
    );
  }
  //$output = array();
  $userdnmd5 = md5( Portal::user_dn() );
  $username = escapeshellarg( trim( $_POST['username'] ) );
  $password = escapeshellarg( trim( $_POST['password'] ) );
  $server   = escapeshellarg( trim( $_POST['server']   ) );
  $filename = escapeshellarg( Portal::PROXY_DIR . $userdnmd5 . '.pem' );
  exec("echo {$password} | myproxy-logon -v -l {$username} -s {$server} -S -o {$filename} 2>&1", $output, $returnval);
  $output = implode("\n", $output);
  if (preg_match('/^(?:invalid pass phrase|No credentials exist for username .*)$/m', $output))
    REST::fatal(
      REST::HTTP_UNAUTHORIZED,
      'Invalid username and/or pass phrase'
    );
  if ($returnval) {
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      '<pre>' . htmlentities($output) . '</pre>'
    );
  }
  
  $escserver = Portal_MySQL::escape_string($_POST['server']);
  $escusername = Portal_MySQL::escape_string($_POST['username']);
  $escpassword = Portal_MySQL::escape_string($_POST['password']);
  Portal_MySQL::real_query("UPDATE `User` SET `proxy_server` = {$escserver}, `proxy_username` = {$escusername}, `proxy_password` = {$escpassword} WHERE `user_dn_md5` = '{$userdnmd5}'");
  
  $best_xhtml_type = REST::best_xhtml_type();
  $type = REST::best_content_type(
    array(
      $best_xhtml_type => 1.0,
      'text/plain' => 1.0,
    ), $best_xhtml_type
  );
  $relurl = REST::urlencode(dirname($_SERVER['REDIRECT_URL'])) . '/proxy';
  REST::header(array(
    'status' => REST::HTTP_CREATED,
    'Location' => REST::urlbase() . $relurl,
    'Content-Type' => "{$type}; charset=UTF-8"
  ));
  if ($type == 'text/plain')
    echo REST::urlbase() . $relurl;
  else
    echo Portal::html_start('Proxy created') .
      "<p><a href=\"proxy\">proxy</a></p>" .
      Portal::html_end();
  exit;
}


REST::header(REST::best_xhtml_type() . "; charset=UTF-8");
$default_server = getenv('MYPROXY_SERVER');
echo Portal::html_start("myProxy") . <<<EOS
<form action="./myproxy" method="post">
<table border="0" cellpadding="0" cellspacing="0"><tbody>
  <tr><td>Username</td><td><input type="text" name="username" /></td></tr>
  <tr><td>Password</td><td><input type="password" name="password" /></td></tr>
  <tr><td>MyProxy server</td><td><input type="text" name="server" value="{$default_server}" /></td></tr>
  <tr><td>&nbsp;</td><td><input type="submit" value="Delegate" /></td></tr>
</tbody></table>
</form>
EOS
  . Portal::html_end();
