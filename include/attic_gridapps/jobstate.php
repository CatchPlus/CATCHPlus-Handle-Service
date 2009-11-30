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
 * $Id: jobstate.php 2471 2009-08-17 20:09:55Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';
require_once 'topos.php';

REST::require_method('GET', 'HEAD', 'PUT', 'DELETE');

$user_id = Portal_User::current()->user_id();
$path_info = Portal::path_info();
$jobid = $path_info[0];

$escjobid = Portal_MySQL::escape_string($jobid);
$escuserid = Portal_MySQL::escape_string($user_id);

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
  if (strpos( @$_SERVER['CONTENT_TYPE'], 'text/plain' ) !== 0)
    REST::fatal(REST::HTTP_UNSUPPORTED_MEDIA_TYPE);
  // The job finished with an error and tries to inform us about it
  $errorstring = '';
  while (($line = fread(REST::inputhandle(), 8192)) !== '')
    $errorstring .= $line;
  if (!strlen($errorstring))
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      'No error string specified'
    );
  $errorstring = Portal_MySQL::escape_string($errorstring);
  Portal_MySQL::real_query(<<<EOS
UPDATE `Token` 
   SET `token_error` = CONCAT(`token_error`, {$errorstring})
 WHERE `token_id`={$escjobid}
   AND `user_id`={$escuserid};
EOS
  );
  REST::header(
    array( 'status' => REST::HTTP_NO_CONTENT ) 
  );
  exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
  if (file_exists($fullfilename = Portal::JOBRESULTS_DIR . $jobid))
    unlink($fullfilename);
  Topos::deleteTokenFile($jobid);
  Portal_MySQL::real_query(<<<EOS
DELETE FROM `Token`
 WHERE `token_id`={$escjobid}
   AND `user_id`={$escuserid};
EOS
  );
  if (!Portal_MySQL::mysql()->affected_rows)
    REST::fatal(REST::HTTP_NOT_FOUND);
  REST::header(array(
    'status' => REST::HTTP_NO_CONTENT
  ));
  exit;
}


// The user tries to get information about eir jobs
if (file_exists($fullfilename = Portal::JOBRESULTS_DIR . $jobid))
  REST::redirect(
    REST::HTTP_SEE_OTHER,
    Portal::portalURL() . "jobresults/{$jobid}"
  );


$result = Portal_MySQL::query(<<<EOS
SELECT `token_error` 
  FROM `Token`
 WHERE `token_id`={$escjobid}
   AND `user_id`={$escuserid};
EOS
);
  
if (!($row = $result->fetch_row()))
  // Can't find what the user is looking for
  REST::fatal(REST::HTTP_GONE);

if (empty($row[0]))
  REST::fatal(
    REST::HTTP_NOT_FOUND,
    "<p>Your job hasn't been executed yet. Try again later.</p>"
  );

REST::fatal(
  REST::HTTP_OK,
  '<p>Your job finished with the following error:</p><pre>' .
    REST::htmlspecialchars($row[0]) . '</pre>'
);

