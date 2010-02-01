DESCRIPTION='Directory listing: JSON'

source 'common.inc'

function unittest() (
  ${CURL} --header 'Accept: application/json' "${BASEURL}" >"${TMPTEST}"
  diff 'expect_1.2' "${TMPTEST}"
)
