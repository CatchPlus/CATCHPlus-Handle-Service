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
 * $Id: application.php 2471 2009-08-17 20:09:55Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';
require_once 'portal_app.php';
require_once 'topos.php';


function myRemoveTempFiles() {
  global $TEMPNAM;
  exec("rm -rf {$TEMPNAM}*");
}


REST::require_method('POST', 'GET', 'HEAD');
$path_info = Portal::path_info();
$appname    = @$path_info[0];
$appversion = @$path_info[1];
$portlet = Portal_App::factory($appname, $appversion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = Portal_User::current()->user_id();
  $applicationURL = "http://{$_SERVER['SERVER_NAME']}/gridapps/{$appname}-{$appversion}.tgz";
  $TEMPNAM = tempnam('/tmp', 'portal_');
  register_shutdown_function('myRemoveTempFiles');
  $sandbox = "{$TEMPNAM}.d/";
  mkdir($sandbox);
  $bashcode = $database = '';
  $portlet->doPOST($sandbox, $bashcode, $database);
  if (substr($database, 0, 1) === '/') {
    $database = REST::urlbase() . $database;
  }
  $database = str_replace(
    REST::urlbase(),
    'https://' . $_SERVER['SERVER_NAME'] . ':' . Portal::PORT_SSL_CSA,
    $database
  );
  if (!empty($database) && !REST::isValidURI($database))
    REST::fatal( REST::HTTP_BAD_REQUEST, "$database is not a valid URL." );
  $escdatabase = escapeshellarg($database);
  file_put_contents(
    $sandbox . 'run.sh',
    <<<EOS
#!/bin/bash

DATABASE={$escdatabase}
APPLICATION='{$applicationURL}'
USER_ID={$user_id}

function runJob() (
{$bashcode}
)

EOS
  );


  exec("cd '{$sandbox}'; find -mindepth 1 -maxdepth 1 -print0 | xargs -0 tar zcf {$TEMPNAM}.tgz", $output, $return_var);
  if ($return_var) {
    $output = implode("\n", $output);
    REST::fatal(
      REST::HTTP_INTERNAL_SERVER_ERROR,
      $output
    );
  }
  
  $tokenhandle = fopen("{$TEMPNAM}.tgz", 'r');
  try {
    $token_url = Topos::putTokenFile(
      $tokenhandle,
      'application/x-compressed-tar'
    );
  } catch (Exception $e) {
    fclose($tokenhandle);
    throw $e;
  }
  
  fclose($tokenhandle);
  
  $token_id = basename($token_url);
  
  Portal_MySQL::real_query(<<<EOS
INSERT INTO `Token`
       ( `token_id`,  `user_id` )
VALUES ( {$token_id}, {$user_id} );
EOS
  );
  
  $resultURL = REST::urlbase() . Portal::portalURL() . "jobstates/{$token_id}";
  REST::created($resultURL);
}

Portal_User::current();
REST::header(array(
  'Content-Type' => REST::best_xhtml_type(),
));
echo
  Portal::html_start("{$appname}-{$appversion}") .
  '<form action="' . $appversion . '" method="post" enctype="multipart/form-data">';
$portlet->doGET();
echo
  '</form>' . Portal::html_end();


