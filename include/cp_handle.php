<?php

/*·************************************************************************
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
 * @package CP
 */

/**
 * @package CP
 */
class CP_Handle {
  

/**
 * @var string
 */
private $handle;
/**
 * @return string
 */
public function handle() { return $this->handle; }


/**
 * Data types.
 * The keys in this array are authoritative. That is: keys in the other arrays
 * (like $this->data) which are not in $this->type must be ignored.
 * @var array of uppercase strings
 */
public $type;


/**
 * @var array of strings
 */
public $data;


/**
 * @var array of ints
 */
public $ttl_type;


/**
 * @var array of ints
 */
public $ttl;


/**
 * @var array of ints
 */
public $timestamp;


/**
 * @var array of strings
 */
public $refs;


/**
 * @var array of ints
 */
public $admin_read;


/**
 * @var array of ints
 */
public $admin_write;


/**
 * @var array of ints
 */
public $pub_read;


/**
 * @var array of ints
 */
public $pub_write;


/**
 * Constructor.
 * Creates a new handle object, with nothing but the default HS_ADMIN entry.
 * @param $handle string
 */
public function __construct($handle) {
  $this->handle = strtoupper($handle);
//  $this->type = array(100 => 'HS_ADMIN');
//  $this->data = array(100 => pack('H*', '0FF20000000A302E4E412F31303537340000012C0000'));
  $this->type = $this->data = $this->ttl_type = $this->ttl = $this->timestamp =
    $this->refs = $this->admin_read = $this->admin_write = $this->pub_read =
    $this->pub_write = array();
}


/**
 * Forces the presence of an HS_ADMIN field.
 * @return void
 */
private function force_hs_admin() {
  $max_idx = 0;
  foreach ($this->type as $idx => $type)
    if ($type == 'HS_ADMIN')
      return;
    elseif ((int)$idx > $max_idx)
      $max_idx = (int)$idx;
  $max_idx++;
  if ($max_idx < 100) $max_idx = 100;
  $this->type[$max_idx] = 'HS_ADMIN';
  $this->data[$max_idx] = pack('H*', '0FF20000000A302E4E412F31303537340000012C0000');
}


/**
 * @var mysqli_stmt prepared statement cache.
 */
private static $create_stmt = null;
/**
 * @param $handle string
 * @return bool
 * @todo Allow large fields by using mysqli_stmt_send_long_data().
 */
public function create() {
  $eschandle = CP_MySQL::escape_string( $this->handle );
  CP_MySQL::real_query("LOCK TABLES `handles` LOW_PRIORITY WRITE;");
  try {
    // Check if the handle already exists:
    $result = CP_MySQL::query("SELECT COUNT(*) FROM `handles` WHERE `handle` = {$eschandle};");
    $row = $result->fetch_row();
    if ($row[0]) return false;
    // Check if a prepared statement already exists:
    if ( ! self::$create_stmt )
      self::$create_stmt = CP_MySQL::mysql()->prepare(<<<EOS
INSERT INTO `handles` (
  handle, idx, type, data, ttl_type, ttl, timestamp, refs,
  admin_read, admin_write, pub_read, pub_write
)
VALUES (
  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
);
EOS
      );
    $p_idx = $p_type = $p_data = $p_ttl_type = $p_ttl = $p_timestamp
           = $p_refs = $p_admin_read = $p_admin_write = $p_pub_read
           = $p_pub_write = null;
    self::$create_stmt->bind_param(
      'sissiiisiiii', $this->handle,
      $p_idx, $p_type, $p_data, $p_ttl_type, $p_ttl, $p_timestamp, $p_refs,
      $p_admin_read, $p_admin_write, $p_pub_read, $p_pub_write
    );
    $this->force_hs_admin();
    foreach ($this->type as $p_idx => $p_type) {
      $p_data = (string)(@$this->data[$p_idx]);
      if (!isset($this->ttl_type[$p_idx])) $this->ttl_type[$p_idx] = 0;
      $p_ttl_type = $this->ttl_type[$p_idx];
      if (!isset($this->ttl[$p_idx])) $this->ttl[$p_idx] = 86400;
      $p_ttl = $this->ttl[$p_idx];
      if (!isset($this->timestamp[$p_idx])) $this->timestamp[$p_idx] = time();
      $p_timestamp = $this->timestamp[$p_idx];
      if (!isset($this->refs[$p_idx])) $this->refs[$p_idx] = '';
      $p_refs = $this->refs[$p_idx];
      if (!isset($this->admin_read[$p_idx])) $this->admin_read[$p_idx] = 1;
      $p_admin_read = $this->admin_read[$p_idx];
      if (!isset($this->admin_write[$p_idx])) $this->admin_write[$p_idx] = 1;
      $p_admin_write = $this->admin_write[$p_idx];
      if (!isset($this->pub_read[$p_idx])) $this->pub_read[$p_idx] = 1;
      $p_pub_read = $this->pub_read[$p_idx];
      if (!isset($this->pub_write[$p_idx])) $this->pub_write[$p_idx] = 0;
      $p_pub_write = $this->pub_write[$p_idx];
      if ( !self::$create_stmt->execute() )
        throw new CP_MySQL_Exception(
          CP_MySQL::mysql()->error,
          CP_MySQL::mysql()->errno
        );
    }
    CP_MySQL::real_query('UNLOCK TABLES;');
  }
  catch (Exception $e) {
    self::delete($this->handle);
    CP_MySQL::real_query('UNLOCK TABLES;');
    throw $e;
  }
  return true;
}


/**
 * @param $handle string
 * @return bool
 * @todo optimization by preparsed statements.
 */
public function read() {
  $eschandle = CP_MySQL::escape_string( $this->handle );
  $result = CP_MySQL::query(<<<EOS
SELECT `idx`, `type`, `data`, `ttl_type`, `ttl`, `timestamp`, `refs`,
       `admin_read`, `admin_write`, `pub_read`, `pub_write`
FROM `handles`
WHERE `handle` = {$eschandle}
ORDER BY `idx`;
EOS
  );
  if (!$result->num_rows) return false;
  $this->type = array();
  while ($row = $result->fetch_row()) {
    $idx = $row[0];
    $this->type[$idx] = $row[1];
    $this->data[$idx] = $row[2];
    $this->ttl_type[$idx] = $row[3];
    $this->ttl[$idx] = $row[4];
    $this->timestamp[$idx] = $row[5];
    $this->refs[$idx] = $row[6];
    $this->admin_read[$idx] = $row[7];
    $this->admin_write[$idx] = $row[8];
    $this->pub_read[$idx] = $row[9];
    $this->pub_write[$idx] = $row[10];
  }
  $result->free();
  return true;
}


/**
 * @param $handle string
 * @return bool
 * @todo optimization by preparsed statements.
 */
public function update() {
  return $this->delete() && $this->create();
}


/**
 * @param $handle string
 * @return bool
 * @todo optimization by preparsed statements.
 */
public function delete() {
  $eschandle = CP_MySQL::escape_string($this->handle);
  CP_MySQL::real_query(<<<EOS
DELETE FROM `handles` WHERE `handle` = $eschandle;
EOS
  );
  return CP_MySQL::mysql()->affected_rows > 0;
}


} // class CP_Handle
