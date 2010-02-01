DESCRIPTION='Handle URL: PUT a new handle with different modes'

source 'common.inc'

function unittest() (
  set -e
  ${CURL} --request 'DELETE' "${BASEURL}${TESTHANDLE}" || true
  ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}?mode=create"
  if ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}?mode=create";
  then false; else true; fi
  ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}?mode=update"
  ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}"
  ${CURL} --request 'DELETE' "${BASEURL}${TESTHANDLE}"
  if ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}?mode=update";
  then false; else true; fi
  ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}"  
)
