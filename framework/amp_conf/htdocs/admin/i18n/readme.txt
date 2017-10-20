To create a new language create the proper i18n/<lang_code>/LC_MESSAGES directory structure.
Copy the amp.pot file to i18n/<lang_code>/LC_MESSAGES/amp.po
Translate your text strings from this file then create the .mo file:

msgfmt -v amp.po -o amp.mo
