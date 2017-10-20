// To create the .pot (this is done by the development team now):
$ find ../includes/*.php ../modules/*.module ../misc/*.php ../theme/*.php | xargs xgettext --no-location -L PHP -o ../locale/ari.pot --keyword=_ -

// To create the utf-8 .po  
// This is only needed once as all new localization should be utf-8
$ iconv -f iso-8859-1 -t utf-8 -o ari.utf-8.po ari.po

// To create the .mo:  
$ msgfmt -v ari.utf-8.po -o ari.mo

// To update, this will not do fuzzy (-N)
$ msgmerge -N -U es_ES/LC_MESSAGES/ari.po ari.pot --output-file=es_ES/LC_MESSAGES/ari.po
$ msgfmt -v es_ES/LC_MESSAGES/ari.po -o es_ES/LC_MESSAGES/ari.mo


// script
//   for this to work all translated files need to be converted to utf-8 (use iconv)
//
find ../includes/*.php ../modules/*.module ../misc/*.php ../theme/*.php | xargs xgettext -L PHP -o ../locale/ari.pot --keyword=_ -
msgmerge -N bg_BG/LC_MESSAGES/ari.po ari.pot --output-file=bg_BG/LC_MESSAGES/ari.po
msgfmt -v bg_BG/LC_MESSAGES/ari.po -o bg_BG/LC_MESSAGES/ari.mo
msgmerge -N da_DK/LC_MESSAGES/ari.po ari.pot --output-file=da_DK/LC_MESSAGES/ari.po
msgfmt -v da_DK/LC_MESSAGES/ari.po -o da_DK/LC_MESSAGES/ari.mo
msgmerge -N de_DE/LC_MESSAGES/ari.po ari.pot --output-file=de_DE/LC_MESSAGES/ari.po
msgfmt -v de_DE/LC_MESSAGES/ari.po -o de_DE/LC_MESSAGES/ari.mo
msgmerge -N el_GR/LC_MESSAGES/ari.po ari.pot --output-file=el_GR/LC_MESSAGES/ari.po
msgfmt -v el_GR/LC_MESSAGES/ari.po -o el_GR/LC_MESSAGES/ari.mo
msgmerge -N es_ES/LC_MESSAGES/ari.po ari.pot --output-file=es_ES/LC_MESSAGES/ari.po
msgfmt -v es_ES/LC_MESSAGES/ari.po -o es_ES/LC_MESSAGES/ari.mo
msgmerge -N fr_FR/LC_MESSAGES/ari.po ari.pot --output-file=fr_FR/LC_MESSAGES/ari.po
msgfmt -v fr_FR/LC_MESSAGES/ari.po -o fr_FR/LC_MESSAGES/ari.mo
msgmerge -N he_IL/LC_MESSAGES/ari.po ari.pot --output-file=he_IL/LC_MESSAGES/ari.po
msgfmt -v he_IL/LC_MESSAGES/ari.po -o he_IL/LC_MESSAGES/ari.mo
msgmerge -N hu_HU/LC_MESSAGES/ari.po ari.pot --output-file=hu_HU/LC_MESSAGES/ari.po
msgfmt -v hu_HU/LC_MESSAGES/ari.po -o hu_HU/LC_MESSAGES/ari.mo
msgmerge -N it_IT/LC_MESSAGES/ari.po ari.pot --output-file=it_IT/LC_MESSAGES/ari.po
msgfmt -v it_IT/LC_MESSAGES/ari.po -o it_IT/LC_MESSAGES/ari.mo
msgmerge -N pt_BR/LC_MESSAGES/ari.po ari.pot --output-file=pt_BR/LC_MESSAGES/ari.po
msgfmt -v pt_BR/LC_MESSAGES/ari.po -o pt_BR/LC_MESSAGES/ari.mo
msgmerge -N ru_RU/LC_MESSAGES/ari.po ari.pot --output-file=ru_RU/LC_MESSAGES/ari.po
msgfmt -v ru_RU/LC_MESSAGES/ari.po -o ru_RU/LC_MESSAGES/ari.mo
msgmerge -N sv_SE/LC_MESSAGES/ari.po ari.pot --output-file=sv_SE/LC_MESSAGES/ari.po
msgfmt -v sv_SE/LC_MESSAGES/ari.po -o sv_SE/LC_MESSAGES/ari.mo
msgmerge -N uk_UA/LC_MESSAGES/ari.po ari.pot --output-file=uk_UA/LC_MESSAGES/ari.po
msgfmt -v uk_UA/LC_MESSAGES/ari.po -o uk_UA/LC_MESSAGES/ari.mo
msgmerge -N zh_TW/LC_MESSAGES/ari.po ari.pot --output-file=zh_TW/LC_MESSAGES/ari.po
msgfmt -v zh_TW/LC_MESSAGES/ari.po -o zh_TW/LC_MESSAGES/ari.mo

