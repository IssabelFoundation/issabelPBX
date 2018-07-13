#!/bin/sh
find *.php | xargs xgettext --no-location -L PHP -o i18n/dynroute.pot --keyword=_ -
/bin/sed -i 's/PACKAGE VERSION/IssabelPBX dynroute module/g' i18n/dynroute.pot
/bin/sed -i 's/charset=CHARSET/charset=utf-8/g' i18n/dynroute.pot
