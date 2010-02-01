DESCRIPTION='Handle URL: PUT and GET application/x-www-form-urlencoded'

source 'common.inc'

function unittest() (
  set -e
  ${CURL} --upload-file 'input_2.1_1' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}"
  ${CURL} --header 'Accept: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}?redirect=no" >"${TMPTEST}"
  grep -P '^type%5B1%5D=URL&data%5B1%5D=http%3A%2F%2Fwww\.sara\.nl%2F&timestamp%5B1%5D=\d+&refs%5B1%5D=&type%5B2%5D=EMAIL&data%5B2%5D=pieterb%40djinnit\.com&timestamp%5B2%5D=\d+&refs%5B2%5D=&type%5B3%5D=MY_TYPE&data%5B3%5D=This\+is\+some\+free\+form\+data%0A%0AIt\+contains\+newlines%2C\+%27quotes%27\+%22like%22\+%60this%60\+and\+even\+some\+%3Cb%3Ehtml%3C%2Fb%3E&timestamp%5B3%5D=\d+&refs%5B3%5D=$' "${TMPTEST}"
  ${CURL} --upload-file 'input_2.1_2' --header 'Content-Type: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}"
  ${CURL} --header 'Accept: application/x-www-form-urlencoded' "${BASEURL}${TESTHANDLE}?redirect=no" >"${TMPTEST}"
  grep -P '^type%5B1%5D=URL&data%5B1%5D=http%3A%2F%2Fwww\.sara\.nl%2F&timestamp%5B1%5D=\d+&refs%5B1%5D=&type%5B2%5D=EMAIL&data%5B2%5D=pieterb%40sara\.nl&timestamp%5B2%5D=\d+&refs%5B2%5D=&type%5B3%5D=MY_TYPE&data%5B3%5D=This\+is\+some\+free\+form\+data%0A%0AIt\+contains\+newlines%2C\+%27quotes%27\+%22like%22\+%60this%60\+and\+even\+some\+%3Cb%3Ehtml%3C%2Fb%3E&timestamp%5B3%5D=\d+&refs%5B3%5D=$' "${TMPTEST}"
)
