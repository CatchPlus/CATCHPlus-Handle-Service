#!/bin/bash

cd `dirname $0`
dirname="$PWD"
rm -rf docs/* 2>/dev/null

mkdir docs/devel 2>/dev/null
phpdoc \
  --filename '*.php' \
  --directory . \
  --ignore '*/.svn/*.php,*/attic/*.php' \
  --target "$dirname/docs/devel" \
  --output HTML:frames:default \
  --parseprivate on \
  --sourcecode on \
  --defaultpackagename Portal \
  --title "PHP Portal Documentation"

exit 0

mkdir docs/user 2>/dev/null
phpdoc \
  --filename 'DAV_Server.php','REST/REST.php','Exception.php' \
  --ignore '*/.svn/' \
  --target "$dirname/docs/user" \
  --output HTML:frames:default \
  --parseprivate off \
  --sourcecode on \
  --defaultpackagename DAV_Server \
  --title "DAV_Server Documentation"
