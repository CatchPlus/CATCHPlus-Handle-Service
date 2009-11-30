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
 * 
 * $Id$
 **************************************************************************/

require_once('include/global.php');

$escPool = Topos::escape_string($TOPOS_POOL);

REST::require_method('HEAD', 'GET');
  
$width = 300;
if (!empty($_GET['width']))
  $width = (int)($_GET['width']);

$result = Topos::query(<<<EOS
SELECT COUNT(*)
FROM `Tokens` NATURAL JOIN `Pools`
WHERE `poolName` = $escPool;
EOS
);
$tokens = $result->fetch_row();
$tokens = (int)($tokens[0]);
if (empty($_GET['total']))
  REST::fatal(
    REST::HTTP_BAD_REQUEST, <<<EOS
<p>Missing required parameter <tt>total</tt>.</p>
<form action="progress" method="get">
<input type="text" name="total"/> Total number of tokens<br/>
<input type="submit" value="Show progress bar"/>
</form>
EOS
  );
  $total = (int)($_GET['total']);
if ($total === 0) $total = 1;
$percentage = 100 * $tokens / $total;
if ($percentage > 100)
  REST::fatal(
    REST::HTTP_BAD_REQUEST, <<<EOS
<p>The total number of tokens cannot be smaller than the number of tokens in this pool.</p>
<form action="progress" method="get">
<input type="text" name="total"/> Total number of tokens<br/>
<input type="submit" value="Show progress bar"/>
</form>
EOS
  );

$bct = REST::best_content_type(
  array('text/html' => 1,
        'application/xhtml+xml' => 1,
        'text/plain' => 1), 'text/html'
);
if ($bct === 'text/plain') {
  REST::header(array(
    'Content-Type' => 'text/plain; charset=US-ASCII',
    'Refresh' => '60; ' . $_SERVER['REQUEST_URI'],
    'Cache-Control' => 'no-cache',
  ));
  if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
  echo $tokens / $total;
  exit;
}

REST::header(array(
  'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8',
  'Refresh' => '60; ' . $_SERVER['REQUEST_URI'],
  'Cache-Control' => 'no-cache',
));
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
echo REST::html_start('Progress');
?><table class="progress"><tbody>
<tr>
  <td style="width: <?php echo $width * $tokens / $total; ?>pt;" class="done">
  <?php if ($percentage >= 50) echo sprintf('%.1f%%', $percentage); ?>
  </td>
  <td style="width: <?php echo $width - $width * $tokens / $total; ?>pt;" class="todo">
  <?php if ($percentage < 50) echo sprintf('%.1f%%', $percentage); ?>
  </td>
</tr>
</tbody></table><?php
echo REST::html_end();
