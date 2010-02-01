DESCRIPTION='Handle URL: DELETE'

source 'common.inc'

function unittest() (
  set -e
  ${CURL} --upload-file 'input_2.1_1' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}"
  ${CURL} --request 'DELETE' "${BASEURL}${TESTHANDLE}"
  if ${CURL} --request 'DELETE' "${BASEURL}${TESTHANDLE}"; then false; else true; fi
)
