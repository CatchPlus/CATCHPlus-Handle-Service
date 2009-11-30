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
 * $Id: fire_pilotjobs.php 2380 2009-07-14 23:44:07Z pieterb $
 **************************************************************************/

/**
 * File documentation.
 * @package Portal
 */

require_once('include/global.php');
require_once('include/topos.php');

REST::require_method('GET');
if ( Portal::user_dn() != @$_SERVER['SSL_SERVER_S_DN'] )
  REST::fatal(
    REST::HTTP_UNAUTHORIZED
  );


function myPilotJob( $poolURL ) {
  $portal_jobs = REST::urlbase() . Portal::portalURL() . 'jobs/';
  return <<<EOS
#!/bin/bash
set -ex

SANDBOX="\${PWD}"
CURL="\${SANDBOX}/curl --silent --fail --insecure --cert \${X509_USER_PROXY} --retry 20"
TOPOS_POOL='{$poolURL}'
PORTAL_JOBS='{$portal_jobs}'
DOWNLOADED_APPLICATIONS=''
DOWNLOADED_DATABASES=''
INDIR="\${TMPDIR}/indir/"
OUTDIR="\${TMPDIR}/outdir/"
APPDIR="\${TMPDIR}/apps/"
DBDIR="\${TMPDIR}/dbs/"
export INDIR OUTDIR APPDIR

chmod 755 ./curl
mkdir \${APPDIR}
mkdir \${DBDIR}


function reportError {
  echo "\$1" |
  \${CURL} --upload-file - --header 'Content-Type: text/plain' "\${PORTAL_JOBS}\${TOKEN_ID}" &&
  \${CURL} -X DELETE "\${TOKEN_URL}" || true
  exit 0      # TODO: REMOVE THIS
}


while true; do
  TOKEN_URL=\$(
    \${CURL} --header 'Accept: text/plain' \${TOPOS_POOL}nextToken
  )
  # [ "\${TOKEN_URL}" ] || exit 0;
  TOKEN_ID=\$( basename \${TOKEN_URL} )
  rm -rf \${INDIR} || true
  mkdir  \${INDIR}
  rm -rf \${OUTDIR} || true
  mkdir  \${OUTDIR}
  
  cd \${INDIR}
  TMPTAR=\$( mktemp -p \${TMPDIR} ).tar
  \${CURL} \${TOKEN_URL} > \${TMPTAR} || continue
  if ! tar xf \${TMPTAR}; then
    rm \${TMPTAR}
    reportError "The token in ToPoS isn't a valid tar file." 
    continue
  fi
  rm \${TMPTAR}
  
  if ! source ./run.sh; then
    reportError "Couldn't source ./run.sh." 
    continue
  fi
  
  if ! echo "\${DOWNLOADED_APPLICATIONS}" | grep -q -F "\${APPLICATION}"; then
    if ! \${CURL} "\${APPLICATION}" | tar -C \${APPDIR} -x -B; then
      reportError "Couldn't download and/or untar application \${APPLICATION}"
      continue
    fi
    DOWNLOADED_APPLICATIONS="\${DOWNLOADED_APPLICATIONS} \${APPLICATION}"
  fi
  
  export DBFILE=\${DBDIR}\$( basename "\${DATABASE}" )
  if ! echo "\${DOWNLOADED_DATABASES}" | grep -q -F "\${DATABASE}"; then
    if ! \${CURL} "\${DATABASE}" > "\${DBFILE}"; then
      reportError "Couldn't download database \${DATABASE}"
      continue
    fi
    DOWNLOADED_DATABASES="\${DOWNLOADED_DATABASES} \${DATABASE}"
  fi
  
  cd \${OUTDIR}
  ( runJob > pilotstdout.txt 2>pilotstderr.txt 2>&1 )
  STATUS=\$?
  cd \${OUTDIR}
  if [ \${STATUS} -eq 0 ]; then
    find -maxdepth 1 -mindepth 1 -print0 | xargs -0 tar cf \$TMPTAR
    if \${CURL} --header 'Content-Type: application/x-tar' --upload-file \$TMPTAR "\${PORTAL_JOBS}\${TOKEN_ID}"; then
      \${CURL} -X DELETE "\$TOKEN_URL" || true
    else
      reportError "Couldn't upload results to \${PORTAL_JOBS}\${TOKEN_ID}"
    fi
    rm \$TMPTAR
  else
    cat pilotstdout.txt >> pilotstderr.txt
    \${CURL} --upload-file pilotstderr.txt --header 'Content-Type: text/plain' "\${PORTAL_JOBS}\${TOKEN_ID}" || true;
    [ \${STATUS} -eq 2 ] && \${CURL} -X DELETE "\$TOKEN_URL" || true
  fi
done


EOS;
} // function myPilotJob
    

function myRemoveTempFiles() {
  global $TEMPNAM;
  exec("rm -rf {$TEMPNAM}*");
}


REST::header('text/plain; charset=UTF-8');
$TEMPNAM = tempnam('/tmp', 'portal');
register_shutdown_function('myRemoveTempFiles');

foreach (glob(Portal::PROXY_DIR . '*.pem') as $fullproxyfile) {
  #$escfilename = escapeshellarg($filename);
  
  $proxyfile = basename($fullproxyfile);
  if (!preg_match('/^([a-f0-9]{32})\\.pem$/', $proxyfile, $matches)) {
    Portal::debug("Strange proxy filename: $proxyfile");
    @unlink($fullproxyfile);
    continue;
  }
  $userdnmd5 = $matches[1];
  $escuserdnmd5 = Portal_MySQL::escape_string( $userdnmd5 );
  $result = Portal_MySQL::query(<<<EOS
SELECT `user_dn`, `proxy_server`, `proxy_username`, `proxy_password`
FROM `User`
WHERE `user_dn_md5` = {$escuserdnmd5};
EOS
  );
  if (!($row = $result->fetch_row())) {
    Portal::debug("A proxy file {$proxyfile} was found, but there's no corresponding user in the database!");
    @unlink($fullproxyfile);
    continue;
  }
  $userdn = $row[0];
  $escproxyserver = escapeshellarg($proxyserver = $row[1]);
  $proxyusername = escapeshellarg($row[2]);
  $proxypassword = $row[3];
  
  $escfullproxyfile = escapeshellarg($fullproxyfile);
  $output='';
  exec("grid-proxy-info -f {$escfullproxyfile} -exists -valid 0:30", $output, $returnval);

  if ($returnval && $proxypassword === null) {
    @unlink($fullproxyfile);
    continue;
  } elseif ($returnval) {
    $handle = popen(
      "myproxy-logon -v -l {$proxyusername} -s {$escproxyserver} -S -o {$escfullproxyfile} >/dev/null 2>&1",
      'w'
    );
    fwrite($handle, $proxypassword);
    if (pclose($handle)) {
      @unlink($fullproxyfile);
      Portal_MySQL::real_query(<<<EOS
UPDATE `User` SET `proxy_server` = NULL, `proxy_username` = NULL, `proxy_password` = NULL
WHERE `user_dn_md5` = {$escuserdnmd5};
EOS
      );
      continue;
    }
  }
  
  $topos = new Topos();
  $pools = $topos->getPools();
  foreach ($pools as $pool => $ntokens) {
    $poolURL = $topos->realmURL() . "pools/{$pool}";
    if (!preg_match('@^todo_for_vo_([-\\w.]+)/$@', $pool, $matches)) {
      Portal::debug("Strange pool URL $poolURL");
      continue;
    }
    $vo = $matches[1];
    
    putenv("X509_USER_PROXY={$fullproxyfile}");
    $vomsproxy = "{$TEMPNAM}.{$vo}.pem";
    $output = '';
    exec("voms-proxy-init -noregen -out $vomsproxy -voms $vo 2>&1", $output);
    if (!file_exists($vomsproxy)) {
      $output = implode("\n", $output);
      Portal::debug("VO $vo unknown, and/or user $userdn isn't in it!\n$output");
      exec("curl -k -X DELETE '$poolURL'");
      continue;
    }
    //chmod($vomsproxy, 0600); # voms-proxy-init does this already
    
    file_put_contents("{$TEMPNAM}.sh", myPilotJob($poolURL));
    $njobs = floor($ntokens / 5.9) + 1; # This number is to be tweaked.
    $shellscript = basename("{$TEMPNAM}.sh");
    file_put_contents("{$TEMPNAM}.jdl", <<<EOS
JobType = "Parametric";
Executable = "/bin/sh";
Arguments = "{$shellscript}";
InputSandbox = {"{$TEMPNAM}.sh", "/home/portal/opt/curl/bin/curl"};
Parameters = {$njobs};
ParameterStep = 1;
ParameterStart = 0;
StdOutput = "std.out";
StdError = "std.err";
OutputSandbox  = {"std.out", "std.err"};
ShallowRetryCount = 0;
MyProxyServer = "$proxyserver";
EOS
    );
    
    putenv("X509_USER_PROXY=$vomsproxy");
    $idfile = Portal::PROXY_DIR . $userdnmd5 . '.' . $vo . '.ids';
    $output = '';
    exec("glite-wms-job-submit -a -o '$idfile' {$TEMPNAM}.jdl 2>&1", $output, $returnval);
    if ($returnval) {
      $output = implode("\n", $output);
      Portal::debug("$userdn\n\n$output");
      continue;
    }
    echo "Job submission succesful VO $vo, user $userdn\n";
  }
}


