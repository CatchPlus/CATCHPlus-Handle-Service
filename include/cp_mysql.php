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
 **************************************************************************/

/**
 * File documentation.
 * @todo - The call register_shutdown_function at the end of this document
 * doesn't look too good.
 * @package CP
 */

/**
 * A MySQL exception.
 * This exception is thrown by some MySQL related methods in {@link CP
 * class CP }.
 * @package CP
 */
class CP_MySQL_Exception extends Exception {}


/**
 * A temporary Exception.
 * A special case of a MySQL exception: a transient error occured, e.g. because
 * a deadlock was detected. The caller should rollback the transaction and try
 * again.
 * @package CP
 */
class CP_Retry_Exception extends CP_MySQL_Exception {}


/**
 * Contains all MySQL related methods.
 * This class is mostly just a namespace.
 * @package CP
 */
class CP_MySQL {
  
  
  /**
   * The mysql connection.
   * @var resource
   */
  private static $mysql = null;
  /**
   * The mysql connection.
   * @return resource
   */
  public static function mysql() {
    if (is_null(self::$mysql)) {
      global $CP_PASSWD;
      self::$mysql = new mysqli(
        'localhost', 'handle_bg', $CP_PASSWD, 'handle_625_beeldengeluid'
      );
      if ( !self::$mysql )
        throw new CP_MySQL_Exception(mysqli_connect_error());
    }
    return self::$mysql;
  }


  /**
   * @param string $query
   * @return void
   * @throws Exception
   */
  public static function real_query($query) {
    if (! self::mysql()->real_query($query)) {
      if (self::mysql()->errno == 1205 || self::mysql()->errno == 1213)
        throw new CP_Retry_Exception( self::mysql()->error, self::mysql()->errno );
      throw new CP_MySQL_Exception( self::mysql()->error, self::mysql()->errno );
    }
  }


  /**
   * @param string $query
   * @return mysqli_result
   * @throws Exception
   */
  public static function query($query) {
    if ( !( $retval = self::mysql()->query($query) ) ) {
      if (self::mysql()->errno == 1205 || self::mysql()->errno == 1213)
        throw new CP_Retry_Exception(self::mysql()->error);
      throw new CP_MySQL_Exception( self::mysql()->error, self::mysql()->errno );
    }
    return $retval;
  }


  /**
   * Escapes a string for MySQL.
   * @param string $string
   * @return string
   */
  public static function escape_string($string) {
    return is_null($string)
      ? 'NULL'
      : '\'' . self::mysql()->escape_string($string) . '\'';
  }


  ///*
  // * Fabricates a new Unique Database Object ID.
  // * @throws object PeopleException E_MYSQL_ERROR
  // * @return int a new unique ID.
  // */
  //public static function uid() {
  //  self::real_query('INSERT INTO `JobId` () VALUES ()');
  //  $retval = self::mysql()->insert_id;
  //  self::real_query("DELETE FROM `JobId` WHERE `job_id` < $retval;");
  //  return $retval;
  //}


} // class CP_MySQL

