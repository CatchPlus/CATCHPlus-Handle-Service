DESCRIPTION='Handle URL: PUT and GET application/json'

source 'common.inc'

function unittest() (
  set -e
  ${CURL} --upload-file 'input_2.2_1' --header 'Content-Type: application/json' "${BASEURL}${TESTHANDLE}"
  ${CURL} --header 'Accept: application/json' "${BASEURL}${TESTHANDLE}?redirect=no" >"${TMPTEST}"
  grep -P '^\{"1":\{"type":"URL","data":"http:\\/\\/www\.sara\.nl\\/","timestamp":\d+,"refs":""\},"2":\{"type":"EMAIL","data":"pieterb@djinnit\.com","timestamp":\d+,"refs":""\},"3":\{"type":"MY_TYPE","data":"This is some free form data\\n\\nIt contains newlines, '\''quotes'\'' \\"like\\" `this` and even some <b>html<\\/b>","timestamp":\d+,"refs":""\}\}$' "${TMPTEST}"
  ${CURL} --upload-file 'input_2.2_2' --header 'Content-Type: application/json' "${BASEURL}${TESTHANDLE}"
  ${CURL} --header 'Accept: application/json' "${BASEURL}${TESTHANDLE}?redirect=no" >"${TMPTEST}"
  grep -P '^\{"1":\{"type":"URL","data":"http:\\/\\/www\.sara\.nl\\/","timestamp":\d+,"refs":""\},"2":\{"type":"EMAIL","data":"pieterb@sara\.nl","timestamp":\d+,"refs":""\},"3":\{"type":"MY_TYPE","data":"This is some free form data\\n\\nIt contains newlines, '\''quotes'\'' \\"like\\" `this` and even some <b>html<\\/b>","timestamp":\d+,"refs":""\}\}$' "${TMPTEST}"
)
