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
 * $Id: csaconfirm.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

if (!isset($_GET['email']) || !isset($_GET['password']))
  REST::fatal(
    REST::HTTP_BAD_REQUEST,
    'Missing (one of) required parameters "email" and "password"'
  );  

$dn = Portal_User::csa_dn();
if (empty($dn))
  REST::fatal(REST::HTTP_NOT_FOUND);

$escemail = Portal_MySQL::escape_string($_GET['email']);
$md5password = md5($_GET['password']);
$escdn = Portal_MySQL::escape_string($dn);
Portal_MySQL::real_query(<<<EOS
DELETE FROM `User`
WHERE `user_dn` = {$escdn}
  AND `user_email` <> {$escemail};
EOS
);
Portal_MySQL::real_query(<<<EOS
UPDATE `User`
SET `user_dn` = {$escdn}
WHERE `user_email` = {$escemail}
  AND `user_password` = '{$md5password}';
EOS
);
if ( ! Portal_MySQL::mysql()->affected_rows )
  Portal_User::unauthorized();

$url = REST::htmlspecialchars(Portal::portalURL());
REST::fatal(
  REST::HTTP_OK, <<<EOS
<p>Registration complete.</p>
<p>You can now start <a href="{$url}">using the GridApps web service</a>.</p>
EOS
);
