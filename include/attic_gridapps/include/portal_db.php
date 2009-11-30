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
 * $Id: portal_db.php 2490 2009-08-26 10:44:52Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @todo - The call register_shutdown_function at the end of this document
 * doesn't look too good.
 * @package Portal
 */

/**
 * Just a namespace for common portal tasks.
 * @package Portal
 */
class Portal_DB {
  

  const DATABASE_DIR   = '/space/gridapps/databases/';


  /**
   * Hash of available database types.
   * - 'ext'  => the file extension (without initial dot)
   * - 'name' => the name of the type (only unreserved URI characters!)
   * - 'type' => the mime-type of the database type
   * @var array
   */
  private static $databaseTypes = array(
    1 => array(
           'ext'  => 'faa',
           'name' => 'FASTA',
           'type' => 'text/plain; charset=US-ASCII',
           'desc' => '<p>A FASTA file as <a href="http://www.ncbi.nlm.nih.gov/blast/fasta.shtml">defined by NCBI</a></p>'
         ),
    2 => array(
           'ext'  => 'tgz',
           'name' => 'formatdb',
           'type' => 'application/x-compressed-tar',
           'desc' => '<p>An compressed tar-ball with a formatdb-generated set of files. The tar-ball must contain either</p>
<ul>
<li>no more than one index file (.pin or .nin), or</li>
<li>multiple index files, with a single .pal or .nal file.</li>
</ul>'
         ),
    3 => array(
           'ext'  => 'csbfa',
           'name' => 'csbfa',
           'type' => 'application/dbf',
           'desc' => '<p>A color-space binary FASTA file. This seems to be some kind of binary format. (DBF-like?)</p>'
         ),
  );
  
  
  /**
   * A sorted list of extensions.
   * @return array
   */
  public static function databaseTypeIDs() {
    return array_keys(self::$databaseTypes);
  }
  
  
  /**
   * Cache.
   * @var array
   */
  private static $databaseTypeIDByName = null;
  /**
   * @param string $name
   * @return int
   */
  public static function databaseTypeIDByName($name) {
    if (self::$databaseTypeIDByName === null)
      foreach (self::$databaseTypes as $key => $value)
        self::$databaseTypeIDByName[$value['name']] = $key;
    return @self::$databaseTypeIDByName[$name];
  }
  
  
  /**
   * @param $id int
   * @return string
   */
  public static function databaseTypeName($id) {
    return @self::$databaseTypes[(int)$id]['name'];
  }
  

  /**
   * @param $id int
   * @return string
   */
  public static function databaseTypeDescription($id) {
    return @self::$databaseTypes[(int)$id]['desc'];
  }
  

  /**
   * @param $id int
   * @return string
   */
  public static function databaseTypeExtension($id) {
    return @self::$databaseTypes[(int)$id]['ext'];
  }
  

  /**
   * @param $id int
   * @return string
   */
  public static function databaseTypeContentType($id) {
    return @self::$databaseTypes[(int)$id]['type'];
  }
  
  
  /**
   * Get a list of available databases, given a set of database types.
   * @param $name... string the name(s) of the database types.
   * @return string an x fragment, to be put inside a select element.
   */
  public static function availableDatabases() {
    $dbTypes = func_get_args();
    if (empty($dbTypes)) return array();
    foreach($dbTypes as $key => $value)
      $dbTypes[$key] = self::databaseTypeIDByName($value);
    $dbTypes = implode(',', $dbTypes);
    $user_id = Portal_User::current()->user_id();
    $result = Portal_MySQL::query(<<<EOS
SELECT `d`.`name`, `d`.`version`, `d`.`type`, `u`.`user_name`, `d`.`database_id`
  FROM `Database` AS d LEFT JOIN `User` AS u USING(`user_id`)
 WHERE (`d`.`is_shared` > 0 OR `d`.`user_id` = {$user_id})
   AND `d`.`type` IN({$dbTypes});
EOS
    );
    $sorter = array();
    while ($row = $result->fetch_row()) {
      $extension = self::databaseTypeExtension($row[2]);
      $sorter[$row[3]]["{$row[0]}-{$row[1]}.{$extension}"] =
        REST::urlencode(
          Portal::portalURL() . 'databases/' . $row[0] . '/' . $row[1] . '/' .
          $row[4] . '.' . $extension
        );
    }
    $user_names = array_keys($sorter);
    natsort($user_names);
    $retval = '';
    foreach ($user_names as $user_name) {
      $retval .= "\n<optgroup label=\"" . htmlentities($user_name) . "\">";
      $dbnames = array_keys($sorter[$user_name]);
      natsort($dbnames);
      foreach ($dbnames as $dbname)
        $retval .= "\n<option value=\"" . $sorter[$user_name][$dbname] . "\">{$dbname}</option>";
      $retval .= "\n</optgroup>";
    }
    return $retval;
  }

} // class Portal_DB
