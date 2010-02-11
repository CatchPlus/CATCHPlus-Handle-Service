DESCRIPTION='Handle URL: POST a new templated handle'

source 'common.inc'

# TODO: This unit test tests only some basics.
function unittest() (
  set -e
  # %2A is the asterisk '*'
  TEMPLATE='10574/TESTHANDLE_~**'
  ${CURL} --data-binary '@input_2.1_2' --dump-header "${TMPTEST}.hdr" "${BASEURL}${TEMPLATE}"
  grep -P '^HTTP/1\.1 201 Created' "${TMPTEST}.hdr"
  HANDLE=$( grep -P '^Location:\s' <"${TMPTEST}.hdr" | grep -o -P 'http.*/10574/TESTHANDLE_*\S+' )
  [ $? -eq 0 ]
  ${CURL} --header 'Accept: application/x-www-form-urlencoded' "${HANDLE}?redirect=no" >"${TMPTEST}"
  ${CURL} --request 'DELETE' "${HANDLE}" || true
  grep -P '^type%5B1%5D=URL&data%5B1%5D=http%3A%2F%2Fwww\.sara\.nl%2F&timestamp%5B1%5D=\d+&refs%5B1%5D=&type%5B2%5D=EMAIL&data%5B2%5D=pieterb%40sara\.nl&timestamp%5B2%5D=\d+&refs%5B2%5D=&type%5B3%5D=MY_TYPE&data%5B3%5D=This\+is\+some\+free\+form\+data%0A%0AIt\+contains\+newlines%2C\+%27quotes%27\+%22like%22\+%60this%60\+and\+even\+some\+%3Cb%3Ehtml%3C%2Fb%3E&timestamp%5B3%5D=\d+&refs%5B3%5D=$' "${TMPTEST}"
)
