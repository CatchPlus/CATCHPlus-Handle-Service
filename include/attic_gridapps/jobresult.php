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
 * $Id: jobresult.php 2471 2009-08-17 20:09:55Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

REST::require_method('GET', 'HEAD', 'PUT');

$user_id = Portal_User::current()->user_id();
$path_info = Portal::path_info();
$jobid = $path_info[0];

$escjobid = Portal_MySQL::escape_string($jobid);
$escuserid = Portal_MySQL::escape_string($user_id);

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
  if (strpos( @$_SERVER['CONTENT_TYPE'], 'application/x-compressed-tar' ) !== 0)
    REST::fatal(REST::HTTP_UNSUPPORTED_MEDIA_TYPE);
  // The job wants to put its results on the portal server
  $tmpfilename = tempnam('/tmp', 'portal_');
  $tmpfile = fopen($tmpfilename, 'w');
  while (($block = fread(REST::inputhandle(), 8192)) !== "") {
    fwrite($tmpfile, $block);
  }
  fclose(REST::inputhandle());
  fclose($tmpfile);

  if ( isset($_SERVER['CONTENT_LENGTH']) &&
  $_SERVER['CONTENT_LENGTH'] != filesize($tmpfilename) ) {
    unlink($tmpfilename);
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
        "Content-Length header doesn't match actual content length."
      );
  }
  
  if (!rename( $tmpfilename, Portal::JOBRESULTS_DIR . $jobid ) ) {
    unlink($tmpfilename);
    REST::fatal(
      REST::HTTP_INTERNAL_SERVER_ERROR,
      "Couldn't store uploaded file."
    );
  }
  chmod( Portal::JOBRESULTS_DIR . $jobid, 0660 );
  REST::header(
    array( 'status' => REST::HTTP_NO_CONTENT ) 
  );
  exit;
}


// The user tries to get information about his jobs
if (file_exists($fullfilename = Portal::JOBRESULTS_DIR . $jobid)) {
  // The job has finished and we have a result
  $filename = basename($fullfilename);
  $fileinfo = @stat($fullfilename);
  REST::header(
    array(
      'Content-Type' => 'application/x-compressed-tar',
      'Content-Disposition' => "attachment; filename=\"{$filename}.tgz\"",
      'Last-Modified' => REST::http_date( $fileinfo['mtime'] ),
      'Content-Length' => $fileinfo['size']
    )
  );
  if ($_SERVER['REQUEST_METHOD'] == 'GET') 
    readfile( $fullfilename );
  exit;
}

REST::fatal(REST::HTTP_NOT_FOUND);
