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
 * $Id: portal_app.php 2490 2009-08-26 10:44:52Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

/**
 * Class documentation.
 * @package Portal
 */
class Portal_App {
  
  
  /**
   * An array of arrays of available applcations and versions.
   * @var array
   */
  private static $apps = array(
    'blast' => array('2.2.19'),
    'greeter' => array('1.0'),
    'maq' => array('0.7.1'),
    'omssa' => array('2.1.4'),
  );
  
  
  /**
   * Returns an array of available application names.
   * @return array
   */
  public static function apps() {
    return array_keys(self::$apps);
  }
  
  
  /**
   * Returns an array of available application version, by name.
   * @param $app string
   * @return array
   */
  public static function versions($app) {
    if (!isset(self::$apps[$app])) return array();
    return self::$apps[$app];
  }
  
  
  /**
   * @param $app string
   * @param $version string
   * @return Portal_App
   */
  public static function factory($app, $version) {
    if (!isset(self::$apps[$app]))
      REST::fatal(REST::HTTP_NOT_FOUND);
    if (!in_array($version, self::$apps[$app]))
      REST::fatal(REST::HTTP_NOT_FOUND);
    list( $app, $version ) = preg_replace(
      '/[^\\w]/', '_',
      array($app, $version)
    );
    $classname = "Portal_App_{$app}_{$version}";
    $filename = dirname(__FILE__) . strtolower("/{$classname}.php");
    if (!file_exists($filename))
      REST::fatal(REST::HTTP_NOT_FOUND);
    require_once $filename;
    return eval("return new {$classname}();");
  }
  
  
  /**
   * Abstract method echoing an HTML form or whatever is necessary to document
   * the POST method on this application resource.
   * @return void
   */
  public function doGET() {
    REST::fatal(
      REST::HTTP_NOT_IMPLEMENTED,
      'Portal_App::doGET()'
    );
  }
  
  
  /**
   * Handles a POST request.
   * The returned bash-code can use the following environment variables:
   * - INDIR
   * - OUTDIR
   * - DBFILE
   * The bashcode must return one of the following exit statuses:
   * - 0: OK
   * - 1: Transient error, try again.
   * - 2: Fatal error, this job will always fail.
   * @param string $sandbox name of the input sandbox directory,
   *        including trailing slash
   * @param string $bashcode (by reference) some bash code to execute.
   * @param string $database (by reference) URL of the database to use
   * @return void
   */
  public function doPOST($sandbox, &$bashcode, &$database) {
    REST::fatal(
      REST::HTTP_NOT_IMPLEMENTED,
      'Portal_App::doPOST()'
    );
  }
  
  
  private function createTarBall($dir) {
    $filename = tempnam('/tmp', 'token_tarball');
    exec(<<<EOS
cd '{$dir}'
find -mindepth 1 -maxdepth 1 -print0 | xargs -0 tar -cf {$filename}
EOS
    );
    return $filename;
  }
  

//  protected function standardFormElements() {
//    return <<<EOS
//<!--
//<table cellspacing="0" cellpadding="2" border="0"><tbody>
//<tr>
//  <th align="right">VO:</th>
//  <td><input type="text" name="portal_vo"/></td>
//  <td style="border-bottom: 1px solid lightgrey;">The name of your Virtual Organization</td>
//</tr>
//<tr>
//  <th align="right">LFC-host:</th>
//  <td><input type="text" name="portal_lfc_host"/></td>
//  <td style="border-bottom: 1px solid lightgrey;">The name of your LCG File Catalog (LFC)</td>
//</tr>
//<tr>
//  <th align="right">Output LFN:</th>
//  <td><input type="text" name="portal_output_lfn"/></td>
//  <td>The Logical File Name (lfn) of the output file. This will be a gzipped tarball, so the <tt>.tgz</tt> extension is recommended.<br/>Also, make sure the containing directory exists.</td>
//</tr>
//</tbody></table>
////-->
//EOS;
//  }
  
//  // Niet meer nodig omdat de check nu runtime gebeurt in de pilot-job:
//  protected function canUseDatabase($dburl) {
//    if (strstr($dburl, $_SERVER['SERVER_NAME']) !== false) {
//      $db_ext = @array_pop(explode('.', $dburl));
//      $database_id = basename($dburl, $db_ext);
//      $user_id = Portal_User::current()->user_id();
//      $result = Portal_MySQL::query(<<<EOS
//SELECT `d`.`name`
//  FROM `Database` AS d LEFT JOIN `User` AS u USING(`user_id`)
// WHERE `d`.`database_id` = {$database_id}
//   AND (`d`.`user_id` = {$user_id} OR `d`.`is_shared` = 1);
//EOS
//      );
//      if (!($row = $result->fetch_row()))
//        return false;
//    }
//    return true;
//  }
  
} // class Portal_App

