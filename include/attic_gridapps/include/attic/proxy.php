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
 * $Id: proxy.php 2378 2009-07-14 14:00:34Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once('include/global.php');

$userdnmd5 = md5( Portal::user_dn() );
$proxy = Portal::PROXY_DIR . $userdnmd5 . '.pem';
$escproxy = str_replace("'", "\\'", $proxy);


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
  $input = REST::inputhandle();
  $output = fopen($proxy, 'w');
  while (!feof($input)) {
    if (($block = fread($input, 8192)) === false)
      REST::fatal(
        REST::HTTP_INTERNAL_SERVER_ERROR,
        'Error while reading PUT data'
      );
    fwrite($output, $block);
  }
  fclose($output);
  fclose($input);
  REST::header(array('status' => REST::HTTP_NO_CONTENT));
  exit;
}


REST::require_method('GET', 'HEAD');
if (file_exists($proxy)) {
  REST::header(array(
    'Content-Type' => 'text/plain; charset=UTF-8', # 'application/x-x509-cert'
  ));
  system("openssl x509 -text -in '{$escproxy}'");
  exit;
}

REST::fatal(
  REST::HTTP_NOT_FOUND, <<<EOS
<p>Couldn't find a proxy. You could try to delegate credentials here:</p>
<ul>
<li><a href="myproxy">./myproxy</a></li>
</ul>
EOS
);
