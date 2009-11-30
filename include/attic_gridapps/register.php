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
 * $Id: register.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once 'include/global.php';

$referrer = empty($_GET['referrer']) ? null : REST::htmlspecialchars($_GET['referrer']);

if (isset($_GET['email']) && isset($_GET['name'])) {
  // Check the email address for syntax:
  $_GET['email'] = strtolower($_GET['email']);
  if (!preg_match('/^[\\w\\d\\-.]+@[\\w\\d\\-]+(?:\\.[\\w\\d\\-]+)*\\.\\w+$/', $_GET['email']))
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      '<p>"' . REST::htmlspecialchars($_GET['email']) .
      '" is not a well-formed e-mail address.</p>'
    );
  // Check the name:
  $_GET['name'] = preg_replace('/\\s+/', ' ', trim($_GET['name']));
  if ($_GET['name'] === '')
    REST::fatal(
      REST::HTTP_BAD_REQUEST,
      '<p>Please provide a display name.</p>'
    );
  $escemail = Portal_MySQL::escape_string($_GET['email']);
  $escname = Portal_MySQL::escape_string($_GET['name']);
//  $dn = ($_SERVER['SERVER_PORT'] == Portal::PORT_SSL_CSA)
//    ? Portal_User::csa_dn() : null;
//  $escdn = Portal_MySQL::escape_string($dn);
  $password = Portal_User::createPassword();
  $md5password = md5($password);
  Portal_MySQL::real_query( <<<EOS
INSERT INTO `User` (`user_email`, `user_name`, `user_password`)
VALUES ({$escemail}, {$escname}, '{$md5password}')
ON DUPLICATE KEY UPDATE
  `user_name` = {$escname},
  `user_password` = '{$md5password}';
EOS
  );
  $csa_confirm = 'https://' . $_SERVER['SERVER_NAME'] . ':' .
    Portal::PORT_SSL_CSA . Portal::portalURL() . 'csaconfirm?email=' .
    urlencode($_GET['email']) . '&password=' . urlencode($password);
  $mailresult = mail(
    $_GET['email'], 'Access to ' . $_SERVER['SERVER_NAME'], <<<EOS
Hi {$_GET['name']},

These are the credentials you may use for the Grid Application Portal:

Login:    {$_GET['email']}
Password: $password

If you want to authenticate using a client certificate, please open a 
browser with your client certificate in it, and follow this link:
<{$csa_confirm}>

Best regards,

EOS
    , "Content-Type: text/plain; charset=\"UTF-8\"\r\n\From: " .
      $_SERVER['SERVER_ADMIN']
  );
  if (!$mailresult)
    REST::fatal(
      REST::HTTP_INTERNAL_SERVER_ERROR, <<<EOS
Your registration was successful, but the email containing your password could not be sent.
The site administrator has been informed and will contact you as soon as possible.
EOS
    );
  $message = <<<EOS
<p>Registration successful.</p>
<p>An e-mail with password has been sent to
<a href="mailto:{$_GET['email']}">{$_GET['email']}</a>.</p>
EOS;
  if ($referrer) $message .= <<<EOS
<p>Click here to continue:<br/>
<a href="{$referrer}">{$referrer}</a></p>
EOS;
  REST::fatal( REST::HTTP_ACCEPTED, $message );
}

REST::header( REST::best_xhtml_type() . '; charset="UTF-8"' );
echo REST::html_start('Register') . <<<EOS
<p>Fill in your e-mail address and display name below, and you'll recieve a password.</p>
<form action="register.php" method="get">
<input type="hidden" name="referrer" value="{$referrer}"/>
<input type="text" name="email" value=""/> E-mail address (invisible to other users)<br/>
<input type="text" name="name" value=""/> Display name (visible to other users if you share your databases)<br/>
<input type="submit" value="Request password"/>
</form>
EOS
  . REST::html_end();
