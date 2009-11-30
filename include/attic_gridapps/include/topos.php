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
 * $Id: topos.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once dirname(__FILE__) . '/global.php';


/**
 * Class documentation.
 * @package Portal
 */
class Topos {


  /**
   * The base of every ToPoS URL.
   */
  const TOPOS_POOL = 'https://topos.grid.sara.nl/4/pools/7d6408a2b635abeb1cf9ea5d/';
  

  /**
   * Puts a new token in the token server.
   * @return the URL of the created token.
   */
  public static function putTokenFile($filehandle, $filetype = 'text/plain; charset="UTF-8"') {
    $fstat = fstat($filehandle);
    $ch = curl_init(
      self::TOPOS_POOL . 'nextToken'
    );
    $options = array(
      CURLOPT_FAILONERROR    => true,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_PUT            => true,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_INFILE         => $filehandle,
      CURLOPT_INFILESIZE     => $fstat['size'],
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER     => array(
        'Content-Type: ' . $filetype,
        'Accept: text/plain',
        'Content-Disposition: attachment; filename="token.tgz"',
      ),
      CURLOPT_CAPATH         => '/etc/grid-security/certificates',
    );
    foreach ($options as $option => $value)
      if (!curl_setopt($ch, $option, $value))
        throw new Exception( 
          "Couldn't set curl option $option"
        );
    $result = curl_exec($ch);
    curl_close($ch);
    if ($result === false)
      throw new Exception(
        "Couldn't PUT new token on ToPoS server."
      );
    return $result;
  }
  
  
  /**
   * Deletes a token from the token server.
   * @return bool TRUE on success, FALSE on failure.
   */
  public static function deleteTokenFile($token_id) {
    $token_id = (int)$token_id;
    $ch = curl_init(
      self::TOPOS_POOL . 'tokens/' . $token_id
    );
    $options = array(
      CURLOPT_CUSTOMREQUEST  => 'DELETE',
      CURLOPT_FAILONERROR    => true,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_DELETE         => true,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CAPATH         => '/etc/grid-security/certificates',
      CURLOPT_HTTP200ALIASES => array( 'HTTP/1.1 204 No Content' ),
    );
    foreach ($options as $option => $value)
      if (!curl_setopt($ch, $option, $value))
        throw new Exception( 
          "Couldn't set curl option $option"
        );
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }
  
  
  /*public function getPools() {
    $ch = curl_init( $this->poolURL . 'pools/' );
    $options = array(
      CURLOPT_FAILONERROR    => true,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER     => array('Accept: application/json' ),
    );
    foreach ($options as $option => $value)
      if (!curl_setopt($ch, $option, $value))
        REST::fatal(
          REST::HTTP_INTERNAL_SERVER_ERROR,
          "Couldn't set option $option"
        );
    $json = curl_exec($ch);
    curl_close($ch);
    if ($json === false)
      REST::fatal(
        REST::HTTP_BAD_GATEWAY,
        "Couldn't GET a list of token pool names."
      );
    $pools = json_decode($json, true);
    $retval = array();
    foreach ($pools['lines'] as $line)
      $retval[$line[0]] = $line[1];
    return $retval;
  }*/


} // class Topos

