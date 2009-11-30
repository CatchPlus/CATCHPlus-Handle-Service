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
 * $Id: databases_files.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

$path_info = Portal::path_info();
$dbname = Portal_MySQL::escape_string($path_info[0]);
$dbversion = Portal_MySQL::escape_string($path_info[1]);
if (preg_match("/([^\\w\\-.~])/", $path_info[0] . $path_info[1]))
  REST::fatal(
    REST::HTTP_FORBIDDEN, <<<EOS
Illegal characters found in database name or version.<br/>
Allowed characters are: A-Z a-z 0-9 _ - . ~
EOS
  );

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = Portal_User::current()->user_id();
  if (strpos( @$_SERVER['CONTENT_TYPE'], 'multipart/form-data' ) === 0) {
    if (empty ($_POST['type']))
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "No type specified"
      );
    if ( !( $typeId = (int)( Portal_DB::databaseTypeIDByName($_POST['type']) ) ) )
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "Wrong type specified"
      );
    if (count($_FILES) == 0)
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "No file in multipart/form-data."
      );
    if (count($_FILES) > 1)
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "Multiple files in multipart/form-data."
      );
    $file = array_shift($_FILES);
    if (is_array($file['error']))
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "Multiple files in multipart/form-data."
      );
    if ( $file['error'] !== UPLOAD_ERR_OK )
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        htmlentities("Errno {$file['error']} occured during file upload.")
      );
    $is_shared = ( @$_POST['shared'] == '1' ) ? 1 : 0;
    $checksum = md5_file( $file['tmp_name'] );
    try {
      Portal_MySQL::real_query(<<<EOS
INSERT INTO `Database`
  ( `name`, `version`, `user_id`, `is_shared`, `checksum`, `type` )
VALUES
  ( {$dbname}, {$dbversion}, {$user_id}, {$is_shared}, '{$checksum}', {$typeId} );
EOS
      );
    }
    catch (Portal_MySQL_Exception $e) {
      REST::fatal(
        REST::HTTP_CONFLICT,
        "Can't overwrite existing file: name={$dbname}, version={$dbversion}, type={$_POST['type']}"
      );
    }
    $insert_id = Portal_MySQL::mysql()->insert_id;
    if (! move_uploaded_file(
            $file['tmp_name'],
            Portal_DB::DATABASE_DIR . $insert_id
          ) ) {
      Portal_MySQL::real_query(
        "DELETE FROM `Database` WHERE `database_id` = {$insert_id}"
      );
      REST::fatal(
        REST::HTTP_INTERNAL_SERVER_ERROR,
        "Couldn't store uploaded file."
      );
    }
    chmod( Portal_DB::DATABASE_DIR . $insert_id, 0660 );
  } else {
    // Not multipart/form-data:
    if (empty ($_GET['type']))
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "No type specified"
      );
    if (!($typeId = (int)(Portal_DB::databaseTypeId($_GET['type']))))
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "Wrong type specified"
      );
    $tmpfilename = tempnam('/tmp', 'portal_');
    $tmpfile = fopen($tmpfilename, 'w');
    while (($block = fread(REST::inputhandle(), 8192)) !== "") {
      fwrite($tmpfile, $block);
    }
    fclose(REST::inputhandle());
    fclose($tmpfile);
    $checksum = md5_file($tmpfilename);
    if ( isset($_SERVER['CONTENT_LENGTH']) &&
         $_SERVER['CONTENT_LENGTH'] != filesize($tmpfilename) ) {
      unlink($tmpfilename);
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "Content-Length header doesn't match actual content length."
      );
    }
    $is_shared = ( @$_GET['shared'] == '1' ) ? 1 : 0;
    try {
      Portal_MySQL::real_query(<<<EOS
INSERT INTO `Database`
  ( `name`, `version`, `user_id`, `is_shared`, `checksum`, `type` )
VALUES
  ( {$dbname}, {$dbversion}, {$user_id}, {$is_shared}, '{$checksum}', {$typeId} );
EOS
      );
    }
    catch (Portal_MySQL_Exception $e) {
      unlink($tmpfilename);
      REST::fatal(
        REST::HTTP_CONFLICT,
        "Can't overwrite existing file: name={$dbname}, version={$dbversion}, type={$_POST['type']}"
      );
    }
    $insert_id = Portal_MySQL::mysql()->insert_id;
    if (!rename( $tmpfilename, Portal_DB::DATABASE_DIR . $insert_id ) ) {
      unlink($tmpfilename);
      Portal_MySQL::real_query(
        "DELETE FROM `Database` WHERE `database_id` = {$insert_id}"
      );
      REST::fatal(
        REST::HTTP_INTERNAL_SERVER_ERROR,
        "Couldn't store uploaded file."
      );
    }
    chmod( Portal_DB::DATABASE_DIR . $insert_id, 0660 );
  }
  $extension = Portal_DB::databaseTypeExtension($typeId);
  $htmlurl = "{$insert_id}.{$extension}";
  $fullurl = REST::urlbase() . $_SERVER['REDIRECT_URL'] . $htmlurl;
  $content_type = REST::best_content_type(
    array(
      REST::best_xhtml_type() => 1.0,
      'text/plain' => 0.5,
    ), 'text/plain'
  );
  if ($content_type == 'text/plain') {
    REST::header(array(
      'status' => REST::HTTP_CREATED,
      'Location' => $fullurl,
      'Content-Type' => 'text/plain; charset=US-ASCII',
    ));
    echo $fullurl;
    exit;
  }
  REST::header(array(
    'status' => REST::HTTP_CREATED,
    'Location' => $fullurl,
    'Content-Type' => REST::best_xhtml_type() . '; charset=US-ASCII',
  ));
  echo Portal::html_start('New database created') .
       "<a href=\"$htmlurl\" rel=\"child\" rev=\"index\">$htmlurl</a>" .
       Portal::html_end();
  exit;
}

REST::require_method('GET', 'HEAD');

$options = '';
foreach (Portal_DB::databaseTypeIDs() as $databaseTypeID) {
  $databaseTypeName = Portal_DB::databaseTypeName($databaseTypeID);
  $options .= "\n<option value=\"{$databaseTypeName}\">{$databaseTypeName}</option>";
}

$directory = RESTDir::factory("{$path_info[0]}, version {$path_info[1]}")->setForm(<<<EOS
<h1>Database upload</h1>
<form method="post" action="./" enctype="multipart/form-data">
<input type="file" name="dbfile" /><br />
<input type="checkbox" name="shared" value="1" /> Share this database with others<br />
Database type: <select name="type">
{$options}
</select><br />
<input type="submit" value="Upload" />
</form>
EOS
);

$user_id = Portal_User::current()->user_id();

$result = Portal_MySQL::query(<<<EOS
SELECT `user_name`, `database_id`, `type` FROM `Database` LEFT JOIN `User` USING(`user_id`)
WHERE `name` = {$dbname}
  AND `version` = {$dbversion}
  AND ( `is_shared` > 0 OR `Database`.`user_id` = {$user_id})
ORDER BY 3, 1;
EOS
);

while ($row = $result->fetch_array()) {
  $filesize = filesize(Portal_DB::DATABASE_DIR . $row[1]);
  $directory->line(
    $row[1] . '.' . Portal_DB::databaseTypeExtension($row[2]),
    array(
      'Size' => filesize(Portal_DB::DATABASE_DIR . $row[1]) . ' B',
      'DBType' => Portal_DB::databaseTypeName($row[2]),
      'Creator' => $row[0],
      'Content-Type' => Portal_DB::databaseTypeContentType($row[2]),
    )
  );
}

$directory->end();
