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
 * $Id: portal_app_blast_2_2_19.php 2471 2009-08-17 20:09:55Z pieterb $
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
class Portal_App_blast_2_2_19 extends Portal_App {
  
  /**
   * Array containing the Blast options that can contain INFILEs, with booleans indicating whether or not
   * they're required.
   * @var array[bool]
   */
  private static $INPUT_FILES = array(
    'i' => true,
    'R' => false,
    'l' => false
  );
  
//  private static $FLAGS = array(
//    'F', 'I', 'g', 'J', 'T', 'U', 'n', 'V', 's', 'O'
//  );
  
  private static $DEFAULT_OPTIONS = array(
    'p' => '',
    'd' => '',
    'e' => '10.0',
    'm' => '0',
    'F' => 'T',
    'G' => '-1',
    'E' => '-1',
    'X' => '0',
    'I' => 'F',
    'q' => '-3',
    'r' => '1',
    'v' => '500',
    'b' => '250',
    'f' => '0',
    'g' => 'T',
    'Q' => '1',
    'D' => '1',
    'O' => '',
    'J' => 'F',
    'M' => 'BLOSUM62',
    'W' => '0',
    'z' => '0',
    'K' => '0',
    'P' => '0',
    'Y' => '0',
    'S' => '3',
    'T' => 'F',
    'U' => 'F',
    'y' => '0.0',
    'Z' => '0',
    'n' => 'F',
    'L' => '',
    'A' => '0',
    'w' => '0',
    't' => '0',
    'B' => '0',
    'V' => 'F',
    'C' => '2',
    'Cu' => '',
    's' => 'F',
  );
  
  private static $REQUIRED_OPTIONS = array( 'p', 'd' );
  
  
  public function doGET() {
    $db_options = Portal_DB::availableDatabases('formatdb');
?><style type="text/css">
table#table_options {
  border-collapse: collapse;
}
table#table_options td {
  border: 1px solid lightgrey;
  vertical-align: top;
}
table#table_C td {
  border-style: none;
  font-size: small;
}
pre {
  border: 1px solid grey;
  background-color: #ff9;
  font-weight: bold;
  padding: 2pt;
}
  </style>
<table style="text-align: left;" id="table_options" border="0" cellpadding="2" cellspacing="0">
  <tbody>
    <tr>
      <th>Option</th>
      <th>Value</th>
      <th>Explanation</th>
    </tr>
    <tr>
      <td>-p</td>
      <td>
      <select name="p">
      <option value="blastn">blastn</option>
      <option selected="selected" value="blastp">blastp</option>
      <option value="blastx">blastx</option>
      <option value="tblastn">tblastn</option>
      <option value="tblastx">tblastx</option>
      </select>
      </td>
      <td>Program Name
      </td>
    </tr>
    <tr>
      <td valign="top">-d</td>
      <td valign="top">
      <select name="d"><?php echo $db_options; ?></select>
      </td>
      <td valign="top">Database</td>
    </tr>
    <tr id="alternative_1">
      <td>-i</td>
      <td><input name="i" type="file" /></td>
      <td>Query File [File In]</td>
    </tr>
    <tr>
      <td>-e</td>
      <td><input style="text-align: right;" name="e" value="10.0" /></td>
      <td>Expectation value (E) [Real]</td>
    </tr>
    <tr>
      <td>-m</td>
      <td>
      <select name="m">
      <option selected="selected" value="0">pairwise</option>
      <optgroup label="query-anchored">
      <option value="1">showing identities</option>
      <option value="2">no identities</option>
      <option value="5">no identities and blunt ends</option>
      </optgroup><optgroup label="flat query-anchored">
      <option value="3">show identities</option>
      <option value="4">no identities</option>
      <option value="6">no identities and blunt ends</option>
      </optgroup>
      <option value="7">XML
Blast output</option>
      <optgroup label="tabular">
      <option value="8">without comment lines</option>
      <option value="9">tabular
with comment lines</option>
      </optgroup><optgroup label="ASN">
      <option value="10">text</option>
      <option value="11">binary [Integer]</option>
      </optgroup>
      </select>
      </td>
      <td>alignment view options<br />
      </td>
    </tr>
    <!--<tr>
  <td>-o</td>
  <td><input name="o" />
  <br />
  </td>
  <td>BLAST report Output File [File Out]</td>
    </tr>-->
    <tr>
      <td>-F</td>
      <td><select name="F"><option value="T" selected="selected">TRUE</option><option value="F">FALSE</option></select></td>
      <td>Filter query sequence (DUST with blastn, SEGwith others)</td>
    </tr>
    <tr>
      <td>-G</td>
      <td><input style="text-align: right;" name="G" value="-1" /></td>
      <td> Cost to open a gap (-1 invokes defaultbehavior) [Integer]</td>
    </tr>
    <tr>
      <td>-E</td>
      <td><input style="text-align: right;" name="E" value="-1" /></td>
      <td>Cost to extend a gap (-1 invokes defaultbehavior) [Integer]</td>
    </tr>
    <tr>
      <td>-X</td>
      <td><input style="text-align: right;" name="X" value="0" /></td>
      <td>X dropoff value for gapped alignment (in bits) (zero invokes default behavior)<br />blastn 30, megablast 20, tblastx 0, all others 15 [Integer]</td>
    </tr>
    <tr>
      <td>-I</td>
      <td><select name="I"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>Show GI's in deflines</td>
    </tr>
    <tr>
      <td>-q</td>
      <td><input style="text-align: right;" name="q" value="-3" /></td>
      <td>Penalty for a nucleotide mismatch (blastn only) [Integer]</td>
    </tr>
    <tr>
      <td>-r</td>
      <td><input style="text-align: right;" name="r" value="1" /></td>
      <td>Reward for a nucleotide match (blastn only) [Integer]</td>
    </tr>
    <tr>
      <td>-v</td>
      <td><input style="text-align: right;" name="v" value="500" /></td>
      <td>Number of database sequences to show one-line descriptions for (V) [Integer]</td>
    </tr>
    <tr>
      <td>-b</td>
      <td><input style="text-align: right;" name="b" value="250" /></td>
      <td>Number of database sequences to show alignments for (B) [Integer]</td>
    </tr>
    <tr>
      <td>-f</td>
      <td><input style="text-align: right;" name="f" value="0" /></td>
      <td>Threshold for extending hits, default if zero<br /> blastp 11, blastn 0, blastx 12, tblastn 13, tblastx 13, megablast 0 [Real]</td>
    </tr>
    <tr>
      <td>-g</td>
      <td><select name="g"><option value="T" selected="selected">TRUE</option><option value="F">FALSE</option></select></td>
      <td>Perform gapped alignment (not available with tblastx)</td>
    </tr>
    <tr>
      <td>-Q</td>
      <td><input style="text-align: right;" name="Q" value="1" /></td>
      <td>Query Genetic code to use [Integer]</td>
    </tr>
    <tr>
      <td>-D</td>
      <td><input style="text-align: right;" name="D" value="1" /></td>
      <td>DB Genetic code (for tblast[nx] only) [Integer]</td>
    </tr>
    <tr>
      <td>-O</td>
      <td><select name="O"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>SeqAlign file [File Out]&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-J</td>
      <td><select name="J"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>Believe the query defline</td>
    </tr>
    <tr>
      <td>-M</td>
      <td>
      <select name="M">
      <option selected="selected" value="BLOSUM62">BLOSUM62</option>
      </select>
      </td>
      <td>Matrix [String]</td>
    </tr>
    <tr>
      <td>-W</td>
      <td><input style="text-align: right;" name="W" value="0" /></td>
      <td>Word size, default if zero (blastn 11, megablast 28, all others 3) [Integer]</td>
    </tr>
    <tr>
      <td>-z</td>
      <td><input style="text-align: right;" name="z" value="0" /></td>
      <td>Effective length of the database (use zero for the real size) [Real]</td>
    </tr>
    <tr>
      <td>-K</td>
      <td><input style="text-align: right;" name="K" value="0" /></td>
      <td>Number of best hits from a region to keep. Off by default. If used a value of 100 is recommended.<br/>Very high values of -v or -b is also suggested [Integer]</td>
    </tr>
    <tr>
      <td>-P</td>
      <td><input style="text-align: right;" name="P" value="0" /></td>
      <td>0 for multiple hit, 1 for single hit (does not apply to blastn) [Integer]</td>
    </tr>
    <tr>
      <td>-Y</td>
      <td><input style="text-align: right;" name="Y" value="0" /></td>
      <td>Effective length of the search space (use zero for the real size) [Real]</td>
    </tr>
    <tr>
      <td>-S</td>
      <td>
      <select name="S">
      <option value="1">top</option>
      <option value="2">bottom</option>
      <option selected="selected" value="3">both</option>
      </select>
      </td>
      <td>Query strands to search against database (for blast[nx], and tblastx)</td>
    </tr>
    <tr>
      <td>-T</td>
      <td><select name="T"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>Produce HTML output</td>
    </tr>
    <tr>
      <td>-l</td>
      <td><input name="l" type="file" /></td>
      <td>Restrict search of database to list of GI's [File In]&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-U</td>
      <td><select name="U"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>Use lower case filtering of FASTA sequence&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-y</td>
      <td><input style="text-align: right;" name="y" value="0.0" /></td>
      <td>X dropoff value for ungapped extensions in bits (0.0 invokes default behavior)<br />blastn 20, megablast 10, all others 7 [Real]</td>
    </tr>
    <tr>
      <td>-Z</td>
      <td><input style="text-align: right;" name="Z" value="0" /></td>
      <td>X dropoff value for final gapped alignment in bits (0.0 invokes default behavior)<br/>blastn/megablast 100, tblastx 0, all others 25 [Integer]</td>
    </tr>
    <tr>
      <td>-R</td>
      <td><input name="R" type="file" /></td>
      <td>PSI-TBLASTN checkpoint file [File In]&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-n</td>
      <td><select name="n"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>MegaBlast search</td>
    </tr>
    <tr>
      <td>-L</td>
      <td><input name="L" /></td>
      <td>Location on query sequence [String]&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-A</td>
      <td><input style="text-align: right;" name="A" value="0" /></td>
      <td>Multiple Hits window size, default if zero (blastn/megablast 0, all others 40 [Integer]</td>
    </tr>
    <tr>
      <td>-w</td>
      <td><input style="text-align: right;" name="w" value="0" /></td>
      <td>Frame shift penalty (OOF algorithm for blastx) [Integer]</td>
    </tr>
    <tr>
      <td>-t</td>
      <td><input style="text-align: right;" name="t" value="0" /></td>
      <td>Length of the largest intron allowed in a translated nucleotide sequence when linking multiple distinct alignments. (0 invokes default behavior; a negative value disables linking.) [Integer]</td>
    </tr>
    <tr>
      <td>-B</td>
      <td><input style="text-align: right;" name="B" value="0" /></td>
      <td>Number of concatenated queries, for blastn and tblastn [Integer]&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-V</td>
      <td><select name="V"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>Force use of the legacy BLAST engine&nbsp; Optional</td>
    </tr>
    <tr>
      <td>-C</td>
      <td>
      <table id="table_C" border="0" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td><input name="C" value="0" type="radio" /></td>
            <td style="white-space: nowrap;">No composition-based statistics</td>
          </tr>
          <tr>
            <td><input name="C" value="1" type="radio" /></td>
            <td style="white-space: nowrap;">Composition-based statistics<br />as in NAR 29:2994-3005, 2001</td>
          </tr>
          <tr>
            <td><input name="C" value="2" type="radio" checked="checked" /></td>
            <td style="white-space: nowrap;">Composition-based score adjustment<br />as in Bioinformatics 21:902-911, 2005,<br />conditioned on sequence properties</td>
          </tr>
          <tr>
            <td><input name="C" value="3" type="radio" /></td>
            <td style="white-space: nowrap;">Composition-based score adjustment<br />as in Bioinformatics 21:902-911, 2005,<br />unconditionally</td>
          </tr>
          <tr>
            <td><select name="Cu"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
            <td style="white-space: nowrap;">Unified p-value combining alignment<br />p-value and compositional p-value<br />in round 1 only</td>
          </tr>
        </tbody>
      </table>
      </td>
      <td>Use composition-based score adjustments for blastp or tblastn</td>
    </tr>
    <tr>
      <td>-s</td>
      <td><select name="s"><option value="T">TRUE</option><option value="F" selected="selected">FALSE</option></select></td>
      <td>Compute locally optimal Smith-Waterman alignments (This option is only available for gapped tblastn.)</td>
    </tr>
    <tr>
      <td></td>
      <td colspan="2"><input type="submit" /><input type="reset" onclick="return confirm('Are you sure you want to reset the form?');"/></td>
    </tr>
  </tbody>
</table><?php
  }
  
  
  public function doPOST($sandbox, &$bashcode, &$database) {
    
		// Normaliseren van boolean options:
//    foreach (self::$FLAGS as $value)
//      if (!isset($_POST[$value]))
//        $_POST[$value] = 'F';

    $options = array();
    foreach (self::$DEFAULT_OPTIONS as $option => $default)
      if (array_key_exists($option, $_POST) and
          $_POST[$option] !== $default)
        $options[$option] = $_POST[$option];

    foreach (self::$REQUIRED_OPTIONS as $req_opt)
    	if (empty($options[$req_opt]))
        REST::fatal( REST::HTTP_BAD_REQUEST, "Missing required option {$req_opt}" );
    // Optie 'd' wordt apart afgehandeld:
    $database = $options['d'];
    unset($options['d']);
    
    // Parse the C option
    // In the blastall command, the value of option '-C' may optionally be
    // appended by a 'U' character. See the blastall manual for more info.
    if (isset($options['Cu'])) {
      if ( $options['Cu'] and
           isset($options['C']) and
           (int)($options['C']) > 0 )
        $options['C'] .= 'U';
      unset($options['Cu']);
    }
    
    // The O options is interfaced as a flag,
    // but its value is not a boolean (either null or 'SeqAlign')
    if (!empty($options['O']))
      $options['O'] = 'SeqAlign';
    
    // Check flags
//    foreach (self::$FLAGS as $flag) {
//        $bashstring .=   " -{$flag} " . (!empty($_POST[$flag]) ? 'T' : 'F');
//    }
        
    // Handle incoming files:
    foreach (self::$INPUT_FILES as $infile => $required) {
      if ( isset( $_FILES[$infile] ) &&
           $_FILES[$infile]['error'] != UPLOAD_ERR_NO_FILE ) {
        if ( $_FILES[$infile]['error'] != UPLOAD_ERR_OK )
          REST::fatal(
            REST::HTTP_BAD_REQUEST,
            "File upload failed: {$_FILES[$infile]['error']}"
          );
        move_uploaded_file( $_FILES[$infile]['tmp_name'], $sandbox . "in_{$infile}" );
        $options[$infile] = "\"\${INDIR}in_{$infile}\"";
      } elseif ($required) {
        REST::fatal( REST::HTTP_BAD_REQUEST, "Missing required option -{$infile}" );
      }
    }
    
    // Escape shell arguments to prevent code insertion:
    foreach (self::$DEFAULT_OPTIONS as $key => $value)
    	if (isset($options[$key]))
    		$options[$key] = escapeshellarg($options[$key]);
    
    $bashcode = <<<EOS
if [ ! -e "\${DBFILE}.d" ]; then
  mkdir -p "\${DBFILE}.d"
  tar -z -x -C "\${DBFILE}.d" -f "\${DBFILE}" || exit 1
fi
pushd "\${DBFILE}.d"
if   [ \( \$( echo *.nal | wc -w ) -eq 1 \) -a \( -f *.nal \) ]; then
  DBNAME=\$( basename *.nal .nal )
elif [ \( \$( echo *.pal | wc -w ) -eq 1 \) -a \( -f *.pal \) ]; then
  DBNAME=\$( basename *.pal .pal )
elif [ \( \$( echo *.nin | wc -w ) -eq 1 \) -a \( -f *.nin \) ]; then
  DBNAME=\$( basename *.nin .nin )
elif [ \( \$( echo *.pin | wc -w ) -eq 1 \) -a \( -f *.pin \) ]; then
  DBNAME=\$( basename *.pin .pin )
else
  exit 2
fi
popd
\${APPDIR}blastall -d \${DBFILE}.d/\${DBNAME}
EOS;
    foreach ($options as $key => $value)
    	$bashcode .= " -{$key} {$value}";
    $bashcode .= ' >"blast.out" || exit 2';
  
  } // function doPOST()
  
  
} // class Portal_App

