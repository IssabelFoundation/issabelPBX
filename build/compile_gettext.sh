#!/bin/bash
GETTEXT=`which gettext`

if [ "$GETTEXT" == "" ];
then
    echo "Cannot proceed. gettext must be installed."
    exit
fi

for SOURCE in `find . -name \*.po`
do
    COMPILED=$(echo ${SOURCE:0:-2}mo)
    msgfmt -o $COMPILED $SOURCE
done
