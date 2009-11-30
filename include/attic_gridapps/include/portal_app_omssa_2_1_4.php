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
 * $Id: portal_app_omssa_2_1_4.php 2459 2009-08-10 21:20:41Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once dirname(__FILE__) . '/portal_app.php';
require_once dirname(__FILE__) . '/global.php';


/**
 * Class documentation.
 * @package Portal
 */
class Portal_App_omssa_2_1_4 extends Portal_App {
  
  private $db_url = 'd';
  private $files = array('conffile' => false,
                         'f'        => false,
                         'fb'       => false,
                         'fbz2'     => false,
                         'fm'       => false,
                         'foms'     => false,
                         'fomx'     => false,
                         'fp'       => false,
                         'fx'       => false,
                         'fxml'     => false);
  private $flags = array('dryrun', 'el', 'il', 'ml', 'mnm', 'ni', 'ns', 'os', 'umm', 'w');
  private $params = array('ch' => '0.2',
                          'ci' => '0.0005',
                          'cl' => '0.0',
                          'cp' => '0',
                          'e' => '0',
                          'h1' => '2',
                          'h2' => '2',
                          'he' => '1',
                          'hl' => '30',
                          'hm' => '2',
                          'hs' => '4',
                          'ht' => '6',
                          'i' => '1,4',
                          'ii' => '0.01',
                          'ir' => '0.0',
                          'is' => '0.0',
                          'logfile' => '',
                          'mf' => '',
                          'mm' => '128',
                          'mv' => '',
                          'no' => '4',
                          'nox' => '40',
                          'nt' => '0',
                          'o' => '',
                          'ob' => '',
                          'obz2' => '',
                          'oc' => '',
                          'op' => '',
                          'ox' => '',
                          'p' => '',
                          'pc' => '1',
                          'sb1' => '1',
                          'scorp' => '0.5',
                          'scorr' => '0',
                          'sct' => '0',
                          'sp' => '100',
                          'ta' => '1.0',
                          'te' => '2.0',
                          'tem' => '0',
                          'tex' => '1446.94',
                          'tez' => '0',
                          'to' => '0.8',
                          'tom' => '0',
                          'v' => '1',
                          'w1' => '20',
                          'w2' => '14',
                          'x' => '0',
                          'z1' => '0.95',
                          'zc' => '1',
                          'zcc' => '2',
                          'zh' => '3',
                          'zl' => '1',
                          'zoh' => '2',
                          'zt' => '3');

  public function doGET() {
    $options = Portal_DB::availableDatabases('formatdb');

    ?>
<script type="text/javascript">
  function addFileInput(sibling, name) {
    var fi = document.createElement('input'), br = document.createElement('br');
    fi.setAttribute('type', 'file');
    fi.setAttribute('name', name);
    sibling.parentNode.insertBefore(br, sibling);
    sibling.parentNode.insertBefore(fi, sibling)
  }
</script>
<table class="applicationparams" border="0" cellpadding="0"
  cellspacing="0">
  <tr>
    <th>Param</th>
    <th>Value</th>
    <th>Type</th>
    <th>Description</th>
  </tr>

  <tr>
    <td>-ch</td>
    <td><input type="text" name="ch" style="text-align: right"
      value="0.2" /></td>
    <td>[Real]</td>
    <td>high intensity cutoff as a fraction of max peak</td>
  </tr>
  <tr>
    <td>-ci</td>
    <td><input type="text" name="ci" style="text-align: right"
      value="0.0005" /></td>
    <td>[Real]</td>
    <td>intensity cutoff increment as a fraction of max peak</td>
  </tr>
  <tr>
    <td>-cl</td>
    <td><input type="text" name="cl" style="text-align: right"
      value="0.0" /></td>
    <td>[Real]</td>
    <td>low intensity cutoff as a fraction of max peak</td>
  </tr>
  <tr>
    <td>-conffile</td>
    <td><input type="file" name="conffile" /></td>
    <td>[File_In]</td>
    <td>Program's configuration (registry) data file</td>
  </tr>
  <tr>
    <td>-cp</td>
    <td><input type="text" name="cp" style="text-align: right" value="0" /></td>
    <td>[Integer]</td>
    <td>eliminate charge reduced precursors in spectra (0=no, 1=yes)</td>
  </tr>
  <tr>
    <td>-d</td>
    <td><select name="d">
    <?php echo $options; ?>
    </select></td>
    <td>[String]</td>
    <td>Blast sequence library to search. Do not include .p* filename
    suffixes.</td>
  </tr>
  <tr>
    <td>-e</td>
    <td><input type="text" name="e" style="text-align: right" value="0" /></td>
    <td>[Integer]</td>
    <td>id number of enzyme to use</td>
  </tr>
  <tr>
    <td>-f</td>
    <td><input type="file" name="f" /></td>
    <td>[File_In]</td>
    <td>single dta file to search</td>
  </tr>
  <tr>
    <td>-fb</td>
    <td><input type="file" name="fb" /></td>
    <td>[File_In]</td>
    <td>multiple dta files separated by blank lines to search</td>
  </tr>
  <tr>
    <td>-fbz2</td>
    <td><input type="file" name="fbz2" /></td>
    <td>[File_In]</td>
    <td>omssa omx file compressed by bzip2</td>
  </tr>
  <tr>
    <td>-fm</td>
    <td><input type="file" name="fm" /></td>
    <td>[File_In]</td>
    <td>mgf formatted file</td>
  </tr>
  <tr>
    <td>-foms</td>
    <td><input type="file" name="foms" /></td>
    <td>[File_In]</td>
    <td>omssa oms file</td>
  </tr>
  <tr>
    <td>-fomx</td>
    <td><input type="file" name="fomx" /></td>
    <td>[File_In]</td>
    <td>omssa omx file</td>
  </tr>
  <tr>
    <td>-fp</td>
    <td><input type="file" name="fp" /></td>
    <td>[File_In]</td>
    <td>pkl formatted file</td>
  </tr>
  <tr>
    <td>-fx</td>
    <td><input type="file" name="fx" /></td>
    <td>[File_In]</td>
    <td>multiple xml-encapsulated dta files to search</td>
  </tr>
  <tr>
    <td>-fxml</td>
    <td><input type="file" name="fxml" /></td>
    <td>[File_In]</td>
    <td>omssa xml search request file</td>
  </tr>
  <tr>
    <td>-h1</td>
    <td><input type="text" name="h1" style="text-align: right" value="2" /></td>
    <td>[Integer]</td>
    <td>number of peaks allowed in single charge window</td>
  </tr>
  <tr>
    <td>-h2</td>
    <td><input type="text" name="h2" style="text-align: right" value="2" /></td>
    <td>[Integer]</td>
    <td>number of peaks allowed in double charge window</td>
  </tr>
  <tr>
    <td>-he</td>
    <td><input type="text" name="he" style="text-align: right" value="1" /></td>
    <td>[Real]</td>
    <td>the maximum evalue allowed in the hit list</td>
  </tr>
  <tr>
    <td>-hl</td>
    <td><input type="text" name="hl" style="text-align: right"
      value="30" /></td>
    <td>[Integer]</td>
    <td>maximum number of hits retained per precursor charge state per
    spectrum</td>
  </tr>
  <tr>
    <td>-hm</td>
    <td><input type="text" name="hm" style="text-align: right" value="2" /></td>
    <td>[Integer]</td>
    <td>the minimum number of m/z matches a sequence library peptide
    must have for the hit to the peptide to be recorded</td>
  </tr>
  <tr>
    <td>-hs</td>
    <td><input type="text" name="hs" style="text-align: right" value="4" /></td>
    <td>[Integer]</td>
    <td>the minimum number of m/z values a spectrum must have to be
    searched</td>
  </tr>
  <tr>
    <td>-ht</td>
    <td><input type="text" name="ht" style="text-align: right" value="6" /></td>
    <td>[Integer]</td>
    <td>number of m/z values corresponding to the most intense peaks
    that must include one match to the theoretical peptide</td>
  </tr>
  <tr>
    <td>-i</td>
    <td><input type="text" name="i" value="1,4" /></td>
    <td>[String]</td>
    <td>id numbers of ions to search (comma delimited, no spaces)</td>
  </tr>
  <tr>
    <td>-ii</td>
    <td><input type="text" name="ii" style="text-align: right"
      value="0.01" /></td>
    <td>[Real]</td>
    <td>evalue threshold to iteratively search a spectrum again, 0 =
    always</td>
  </tr>
  <tr>
    <td>-ir</td>
    <td><input type="text" name="ir" style="text-align: right"
      value="0.0" /></td>
    <td>[Real]</td>
    <td>evalue threshold to replace a hit, 0 = only if better</td>
  </tr>
  <tr>
    <td>-is</td>
    <td><input type="text" name="is" style="text-align: right"
      value="0.0" /></td>
    <td>[Real]</td>
    <td>evalue threshold to include a sequence in the iterative search,
    0 = all</td>
  </tr>
  <tr>
    <td>-logfile</td>
    <td><input type="text" name="logfile" value="" /></td>
    <td>[File_Out]</td>
    <td>File to which the program log should be redirected</td>
  </tr>
  <tr>
    <td>-mf</td>
    <td><input type="text" name="mf" value="" /></td>
    <td>[String]</td>
    <td>comma delimited (no spaces) list of id numbers for fixed
    modifications</td>
  </tr>
  <tr>
    <td>-mm</td>
    <td><input type="text" name="mm" style="text-align: right"
      value="128" /></td>
    <td>[Integer]</td>
    <td>the maximum number of mass ladders to generate per database
    peptide</td>
  </tr>
  <tr>
    <td>-mux</td>
    <td><input type="file" name="mux" /></td>
    <td>[File_In]</td>
    <td>file containing user modification data (defaults to usermods.xml
    as packaged with the application)</td>
  </tr>
  <tr>
    <td>-mv</td>
    <td><input type="text" name="mv" value="" /></td>
    <td>[String]</td>
    <td>comma delimited (no spaces) list of id numbers for variable
    modifications</td>
  </tr>
  <tr>
    <td>-mx</td>
    <td><input type="file" name="mx" /></td>
    <td>[String]</td>
    <td>file containing modification data (defaults to mods.xml as
    packaged with the application)</td>
  </tr>
  <tr>
    <td>-no</td>
    <td><input type="text" name="no" style="text-align: right" value="4" /></td>
    <td>[Integer]</td>
    <td>minimum size of peptides for no-enzyme and semi-tryptic searches</td>
  </tr>
  <tr>
    <td>-nox</td>
    <td><input type="text" name="nox" style="text-align: right"
      value="40" /></td>
    <td>[Integer]</td>
    <td>maximum size of peptides for no-enzyme and semi-tryptic searches
    (0=none)</td>
  </tr>
  <tr>
    <td>-nt</td>
    <td><input type="text" name="nt" style="text-align: right" value="0" /></td>
    <td>[Integer]</td>
    <td>number of search threads to use, 0=autodetect</td>
  </tr>
  <tr>
    <td>-o</td>
    <td><input type="text" name="o" value="" /></td>
    <td>[String]</td>
    <td>filename for text asn.1 formatted search results</td>
  </tr>
  <tr>
    <td>-ob</td>
    <td><input type="text" name="ob" value="" /></td>
    <td>[String]</td>
    <td>filename for binary asn.1 formatted search results</td>
  </tr>
  <tr>
    <td>-obz2</td>
    <td><input type="text" name="obz2" value="" /></td>
    <td>[String]</td>
    <td>filename for bzip2 compressed xml formatted search results</td>
  </tr>
  <tr>
    <td>-oc</td>
    <td><input type="text" name="oc" value="" /></td>
    <td>[String]</td>
    <td>filename for csv formatted search summary</td>
  </tr>
  <tr>
    <td>-op</td>
    <td><input type="text" name="op" value="" /></td>
    <td>[String]</td>
    <td>filename for pepXML formatted search results</td>
  </tr>
  <tr>
    <td>-ox</td>
    <td><input type="text" name="ox" value="" /></td>
    <td>[String]</td>
    <td>filename for xml formatted search results</td>
  </tr>
  <tr>
    <td>-p</td>
    <td><input type="text" name="p" value="" /></td>
    <td>[String]</td>
    <td>id numbers of ion series to apply no product ions at proline
    rule at (comma delimited, no spaces)</td>
  </tr>
  <tr>
    <td>-pc</td>
    <td><input type="text" name="pc" style="text-align: right" value="1" /></td>
    <td>[Integer]</td>
    <td>minimum number of precursors that match a spectrum</td>
  </tr>
  <tr>
    <td>-sb1</td>
    <td><input type="text" name="sb1" style="text-align: right"
      value="1" /></td>
    <td>[Integer]</td>
    <td>should first forward (b1) product ions be in search (1=no)</td>
  </tr>
  <tr>
    <td>-scorp</td>
    <td><input type="text" name="scorp" style="text-align: right"
      value="0.5" /></td>
    <td>[Real]</td>
    <td>probability of consecutive ion (used in correlation correction)</td>
  </tr>
  <tr>
    <td>-scorr</td>
    <td><input type="text" name="scorr" style="text-align: right"
      value="0" /></td>
    <td>[Integer]</td>
    <td>turn off correlation correction to score (1=off, 0=use
    correlation)</td>
  </tr>
  <tr>
    <td>-sct</td>
    <td><input type="text" name="sct" style="text-align: right"
      value="0" /></td>
    <td>[Integer]</td>
    <td>should c terminus ions be searched (1=no)</td>
  </tr>
  <tr>
    <td>-sp</td>
    <td><input type="text" name="sp" style="text-align: right"
      value="100" /></td>
    <td>[Integer]</td>
    <td>max number of ions in each series being searched (0=all)</td>
  </tr>
  <tr>
    <td>-ta</td>
    <td><input type="text" name="ta" style="text-align: right"
      value="1.0" /></td>
    <td>[Real]</td>
    <td>automatic mass tolerance adjustment fraction</td>
  </tr>
  <tr>
    <td>-te</td>
    <td><input type="text" name="te" style="text-align: right"
      value="2.0" /></td>
    <td>[Real]</td>
    <td>precursor ion m/z tolerance in Da</td>
  </tr>
  <tr>
    <td>-tem</td>
    <td><input type="text" name="tem" style="text-align: right"
      value="0" /></td>
    <td>[Integer]</td>
    <td>precursor ion search type (0 = mono, 1 = avg, 2 = N15, 3 =
    exact)</td>
  </tr>
  <tr>
    <td>-tex</td>
    <td><input type="text" name="tex" style="text-align: right"
      value="1446.94" /></td>
    <td>[Real]</td>
    <td>threshold in Da above which the mass of neutron should be added
    in exact mass search</td>
  </tr>
  <tr>
    <td>-tez</td>
    <td><input type="text" name="tez" style="text-align: right"
      value="0" /></td>
    <td>[Integer]</td>
    <td>charge dependency of precursor mass tolerance (0 = none, 1 =
    linear)</td>
  </tr>
  <tr>
    <td>-to</td>
    <td><input type="text" name="to" style="text-align: right"
      value="0.8" /></td>
    <td>[Real]</td>
    <td>product ion m/z tolerance in Da</td>
  </tr>
  <tr>
    <td>-tom</td>
    <td><input type="text" name="tom" style="text-align: right"
      value="0" /></td>
    <td>[Integer]</td>
    <td>product ion search type (0 = mono, 1 = avg, 2 = N15, 3 = exact)</td>
  </tr>
  <tr>
    <td>-v</td>
    <td><input type="text" name="v" style="text-align: right" value="1" /></td>
    <td>[Integer]</td>
    <td>number of missed cleavages allowed</td>
  </tr>
  <tr>
    <td>-w1</td>
    <td><input type="text" name="w1" style="text-align: right"
      value="20" /></td>
    <td>[Integer]</td>
    <td>single charge window in Da</td>
  </tr>
  <tr>
    <td>-w2</td>
    <td><input type="text" name="w2" style="text-align: right"
      value="14" /></td>
    <td>[Integer]</td>
    <td>double charge window in Da</td>
  </tr>
  <tr>
    <td>-x</td>
    <td><input type="text" name="x" value="0" /></td>
    <td>[String]</td>
    <td>comma delimited list of taxids to search (0 = all)</td>
  </tr>
  <tr>
    <td>-z1</td>
    <td><input type="text" name="z1" style="text-align: right"
      value="0.95" /></td>
    <td>[Real]</td>
    <td>fraction of peaks below precursor used to determine if spectrum
    is charge 1</td>
  </tr>
  <tr>
    <td>-zc</td>
    <td><input type="text" name="zc" style="text-align: right" value="1" /></td>
    <td>[Integer]</td>
    <td>should charge plus one be determined algorithmically? (1=yes)</td>
  </tr>
  <tr>
    <td>-zcc</td>
    <td><input type="text" name="zcc" style="text-align: right"
      value="2" /></td>
    <td>[Integer]</td>
    <td>how should precursor charges be determined? (1=believe the input
    file, 2=use a range)</td>
  </tr>
  <tr>
    <td>-zh</td>
    <td><input type="text" name="zh" style="text-align: right" value="3" /></td>
    <td>[Integer]</td>
    <td>maximum precursor charge to search when not 1+</td>
  </tr>
  <tr>
    <td>-zl</td>
    <td><input type="text" name="zl" style="text-align: right" value="1" /></td>
    <td>[Integer]</td>
    <td>minimum precursor charge to search when not 1+</td>
  </tr>
  <tr>
    <td>-zoh</td>
    <td><input type="text" name="zoh" style="text-align: right"
      value="2" /></td>
    <td>[Integer]</td>
    <td>maximum product charge to search</td>
  </tr>
  <tr>
    <td>-zt</td>
    <td><input type="text" name="zt" style="text-align: right" value="3" /></td>
    <td>[Integer]</td>
    <td>minimum precursor charge to start considering multiply charged
    products</td>
  </tr>
  <tr>
    <td>-dryrun</td>
    <td><input type="checkbox" name="dryrun" /></td>
    <td>[FLAG]</td>
    <td>Dry run the application: do nothing, only test all preconditions</td>
  </tr>
  <tr>
    <td>-el</td>
    <td><input type="checkbox" name="el" /></td>
    <td>[FLAG]</td>
    <td>print a list of enzymes and their corresponding id number</td>
  </tr>
  <tr>
    <td>-il</td>
    <td><input type="checkbox" name="il" /></td>
    <td>[FLAG]</td>
    <td>print a list of ions and their corresponding id number</td>
  </tr>
  <tr>
    <td>-ml</td>
    <td><input type="checkbox" name="ml" /></td>
    <td>[FLAG]</td>
    <td>print a list of modifications and their corresponding id number</td>
  </tr>
  <tr>
    <td>-mnm</td>
    <td><input type="checkbox" name="mnm" /></td>
    <td>[FLAG]</td>
    <td>n-term methionine should not be cleaved</td>
  </tr>
  <tr>
    <td>-ni</td>
    <td><input type="checkbox" name="ni" /></td>
    <td>[FLAG]</td>
    <td>don't print informational messages</td>
  </tr>
  <tr>
    <td>-ns</td>
    <td><input type="checkbox" name="ns" /></td>
    <td>[FLAG]</td>
    <td>depreciated flag</td>
  </tr>
  <tr>
    <td>-os</td>
    <td><input type="checkbox" name="os" /></td>
    <td>[FLAG]</td>
    <td>use omssa 1.0 scoring</td>
  </tr>
  <tr>
    <td>-umm</td>
    <td><input type="checkbox" name="umm" /></td>
    <td>[FLAG]</td>
    <td>use memory mapped sequence libraries</td>
  </tr>
  <tr>
    <td>-w</td>
    <td><input type="checkbox" name="w" /></td>
    <td>[FLAG]</td>
    <td>include spectra and search params in search results</td>
  </tr>

  <tr>
    <td colspan="4"><input type="submit" value="Run Application" /></td>
  </tr>
</table>
<?php

  }

  public function doPOST($sandbox, &$bashcode, &$database) {
    $bashstring = <<<EOS
DBDIR=\${DBFILE}.d/
rm -rf \${DBDIR} || true
mkdir \${DBDIR}
cd \${DBDIR}
tar xf \${DBFILE}
if   [ \$( ls -1d *.pal 2>/dev/null | wc -l ) -eq 1 ]; then
  DB=\$( basename *.pal .pal )
elif [ \$( ls -1d *.pin 2>/dev/null | wc -l ) -eq 1 ]; then
  DB=\$( basename *.pin .pin )
else
  echo "No valid database found"
  exit 2
fi
cd \${OUTDIR}
\${APPDIR}omssa-2.1.4/omssacl -d \${DBDIR}\${DB}
EOS;
    
    // Check -d parameter
    if (empty($_POST[$this->db_url]))
      REST::fatal(
        REST::HTTP_BAD_REQUEST,
        "You need to specify a database URL"
      );
    
    $database = $_POST[$this->db_url];
    
    // File uploads
    $filecounter = 0;
    foreach ($this->files as $input => $required) {
      if (Portal::isUploaded($input)) {
        if (!move_uploaded_file($_FILES[$input]['tmp_name'], $sandbox . $filecounter))
          REST::fatal(
            REST::HTTP_INTERNAL_SERVER_ERROR,
            "Couldn't store uploaded file."
          );
        $bashstring .= " -{$input} \"\${INDIR}{$filecounter}\"";
        $filecounter++;
      } else {
        if ($required)
          REST::fatal( REST::HTTP_BAD_REQUEST, "Missing required file $input" );
      }
    }
    
    // Check flags
    foreach ($this->flags as $flag)
      if (!empty($_POST[$flag]))
        $bashstring .=  " -{$flag}";
    
    // Check params
    foreach ($this->params as $paramname => $defaultvalue)
      if ( isset($_POST[$paramname]) &&
           strlen($_POST[$paramname]) &&
           $_POST[$paramname] != $defaultvalue )
        $bashstring .=  " -{$paramname} " . escapeshellarg($_POST[$paramname]);
        
    $bashcode = $bashstring . " || exit 2";
    
  } // function doPOST()


} // class Portal_App