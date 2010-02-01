DESCRIPTION='Directory listing: XHTML'

source 'common.inc'

function unittest() (
  ${CURL} --header 'Accept: application/xhtml+xml' "${BASEURL}" >"${TMPTEST}"
  diff 'expect_1.1' "${TMPTEST}"
)
