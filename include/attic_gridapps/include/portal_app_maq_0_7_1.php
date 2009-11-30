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
 * $Id: portal_app_maq_0_7_1.php 2490 2009-08-26 10:44:52Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once dirname(__FILE__) . '/portal_app.php';


/**
 * Class documentation.
 * @package Portal
 */
class Portal_App_maq_0_7_1 extends Portal_App {
  
//Usage:   maq map [options] <out.map> <chr.bfa> <reads_1.bfq> [reads_2.bfq]
//
//Options: -1 INT      length of the first read (<=127) [0]
//         -2 INT      length of the second read (<=127) [0]
//         -m FLOAT    rate of difference between reads and references [0.001]
//         -e INT      maximum allowed sum of qualities of mismatches [70]
//         -d FILE     adapter sequence file [null]
//         -a INT      max distance between two paired reads [250]
//         -A INT      max distance between two RF paired reads [0]
//         -n INT      number of mismatches in the first 24bp [2]
//         -M c|g      methylation alignment mode [null]
//         -u FILE     dump unmapped and poorly aligned reads to FILE [null]
//         -H FILE     dump multiple/all 01-mismatch hits to FILE [null]
//         -C INT      max number of hits to output. >512 for all 01 hits. [250]
//         -s INT      seed for random number generator [random]
//         -W          disable Smith-Waterman alignment
//         -t          trim all reads (usually not recommended)
//         -c          match in the colorspace
  
  
  public function doGET() {
    $db_options = Portal_DB::availableDatabases('csbfa');
?><style type="text/css">
table#table_options {
  border-collapse: collapse;
}
table#table_options td {
  border: 1px solid lightgrey;
  vertical-align: top;
}
  </style>
<table style="text-align: left;" id="table_options" border="0" cellpadding="2" cellspacing="0">
  <tbody>
    <tr>
      <th>Option</th>
      <th>Type</th>
      <th>Value</th>
      <th>Explanation</th>
    </tr>
    <tr>
      <td valign="top">&lt;chr&gt;</td>
      <td>INFILE</td>
      <td valign="top">
        <select name="chr"><?php echo $db_options; ?></select>
      </td>
      <td valign="top">Chromosome reference (FASTA) (required)</td>
    </tr>
    <tr>
      <td valign="top">&lt;reads_1.bfq&gt;</td>
      <td>INFILE</td>
      <td valign="top"><input name="reads_1" type="file" /></td>
      <td valign="top">First read (BFQ) (required)</td>
    </tr>
    <tr>
      <td valign="top">&lt;reads_2.bfq&gt;</td>
      <td>INFILE</td>
      <td valign="top"><input name="reads_2" type="file" /></td>
      <td valign="top">Second read (BFQ) (optional)</td>
    </tr>
    <tr>
      <td>-1</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="1" value="0" /></td>
      <td>length of the first read (&lt;=127)</td>
    </tr>
    <tr>
      <td>-2</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="2" value="0" /></td>
      <td>length of the second read (&lt;=127)</td>
    </tr>
    <tr>
      <td>-m</td>
      <td>FLOAT</td>
      <td><input type="text" style="text-align: right;" name="m" value="0.001" /></td>
      <td>rate of difference between reads and references</td>
    </tr>
    <tr>
      <td>-e</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="e" value="70" /></td>
      <td>maximum allowed sum of qualities of mismatches</td>
    </tr>
    <tr>
      <td>-d</td>
      <td>OUTFILE</td>
      <td><input type="checkbox" name="d" value="1" /></td>
      <td>adapter sequence file</td>
    </tr>
    <tr>
      <td>-a</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="a" value="250" /></td>
      <td>max distance between two paired reads</td>
    </tr>
    <tr>
      <td>-A</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="A" value="0" /></td>
      <td>max distance between two RF paired reads</td>
    </tr>
    <tr>
      <td>-n</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="n" value="2" /></td>
      <td>number of mismatches in the first 24bp</td>
    </tr>
    <tr>
      <td>-M</td>
      <td>SELECT</td>
      <td><select name="M">
        <option value="" selected="selected"></option>
        <option value="c">c</option>
        <option value="g">g</option>
      </select></td>
      <td>methylation alignment mode</td>
    </tr>
    <tr>
      <td>-u</td>
      <td>OUTFILE</td>
      <td><input type="checkbox" name="u" value="1" /></td>
      <td>dump unmapped and poorly aligned reads to FILE</td>
    </tr>
    <tr>
      <td>-H</td>
      <td>OUTFILE</td>
      <td><input type="checkbox" name="H" value="1" /></td>
      <td>dump multiple/all 01-mismatch hits to FILE</td>
    </tr>
    <tr>
      <td>-C</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="C" value="250" /></td>
      <td>max number of hits to output. &gt;512 for all 01 hits.</td>
    </tr>
    <tr>
      <td>-s</td>
      <td>INT</td>
      <td><input type="text" style="text-align: right;" name="s" value="" /></td>
      <td>seed for random number generator (Default: random)</td>
    </tr>
    <tr>
      <td>-W</td>
      <td>FLAG</td>
      <td><input type="checkbox" name="W" value="1" /></td>
      <td>disable Smith-Waterman alignment</td>
    </tr>
    <tr>
      <td>-t</td>
      <td>FLAG</td>
      <td><input type="checkbox" name="t" value="1" /></td>
      <td>trim all reads (usually not recommended)</td>
    </tr>
    <tr>
      <td>-c</td>
      <td>FLAG</td>
      <td><input type="checkbox" name="c" value="1" /></td>
      <td>match in the colorspace</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td colspan="2"><input type="submit" /><input type="reset" onclick="return confirm('Are you sure you want to reset the form?');"/></td>
    </tr>
  </tbody>
</table><?php
  }
  
  
  /**
   * Array containing the Blast options that can contain INFILEs, with booleans indicating whether or not
   * they're required.
   * @var array[bool]
   */
  private static $INPUT_FILES = array(
    'reads_1' => true,
    'reads_2' => false,
  );
  
  private static $FLAGS = array(
    'W', 't', 'c'
  );
  
  private static $OUTPUT_FILES = array(
    'd' => 'adapter_sequence',
    'u' => 'unmapped_and_poorly_aligned',
    'H' => '01_mismatch_hits',
  );
  
  private static $FLOATS = array(
    'm' => '0.001',
  );
  
  private static $INTEGERS = array(
    '1' => '0',
    '2' => '0',
    'e' => '70',
    'a' => '250',
    'A' => '0',
    'n' => '2',
    'C' => '250',
  );
  
  public function doPOST($sandbox, &$bashcode, &$database) {
    
    $options = array();
    
    foreach (self::$INTEGERS as $option => $default) {
      if (!isset($_POST[$option])) continue;
      if (!preg_match('/^\\s*\\d+\\s*$/', $_POST[$option]))
        REST::fatal(
          REST::HTTP_BAD_REQUEST,
          "<p>Bad value for option '-{$option}'</p>"
        );
      if ( $_POST[$option] !== $default )
        $options[$option] = $_POST[$option];
    }
    
    foreach (self::$FLOATS as $option => $default) {
      if (!isset($_POST[$option])) continue;
      if (!preg_match('/^\\s*(?:\\d+(?:\\.\\d+)?|\\.\\d+)(?:[eE][+\\-]?\\d+)?\\s*$/', $_POST[$option]))
        REST::fatal(
          REST::HTTP_BAD_REQUEST,
          "<p>Bad value for option '-{$option}'</p>"
        );
      if ( $_POST[$option] !== $default )
        $options[$option] = $_POST[$option];
    }
    
    foreach (self::$OUTPUT_FILES as $option => $filename)
      if (!empty($_POST[$option]))
        $options[$option] = $filename;
    
    foreach (self::$FLAGS as $option)
      if (!empty($_POST[$option]))
        $options[$option] = null;
    
    if (!empty($_POST['M'])) {
      if (!in_array(strtolower($_POST['M']), array('c', 'g')))
        REST::fatal(
          REST::HTTP_BAD_REQUEST,
          "<p>Bad value for option '-M'</p>"
        );
      $options['M'] = strtolower($_POST['M']);
    }
    
    if ( !isset($_POST['chr']) )
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "<p>Missing required option 'chr'</p>"
      );
    $database = $_POST['chr'];
    
    // Handle incoming files:
    $input_files = '"${DBFILE}"';
    foreach (self::$INPUT_FILES as $infile => $required) {
      if ( isset( $_FILES[$infile] ) &&
           $_FILES[$infile]['error'] != UPLOAD_ERR_NO_FILE ) {
        if ( $_FILES[$infile]['error'] != UPLOAD_ERR_OK )
          REST::fatal(
            REST::HTTP_BAD_REQUEST,
            "File upload failed: {$_FILES[$infile]['error']}"
          );
        move_uploaded_file( $_FILES[$infile]['tmp_name'], $sandbox . "in_{$infile}" );
        $input_files .= " \"\${INDIR}in_{$infile}\"";
      } elseif ($required) {
        REST::fatal( REST::HTTP_BAD_REQUEST, "Missing required option input file '{$infile}'" );
      }
    }
    
    
    $bashcode = <<<EOS
#if [ ! -e "\${DBFILE}.bfa" ]; then
#  "\${APPDIR}maq" fasta2bfa "\${DBFILE}" "\${DBFILE}.bfa" || exit 1
#fi
{ "\${APPDIR}maq" map
EOS;
    foreach ($options as $key => $value)
      $bashcode .= " -{$key} {$value}";
    $bashcode .= " out.map {$input_files} || exit 2; } || exit 1";
  
  } // function doPOST()
  
  
} // class Portal_App

