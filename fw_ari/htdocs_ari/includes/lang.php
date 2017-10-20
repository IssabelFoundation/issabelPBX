<?php

/**
 * @file
 * i18n language functions
 */

/**
  * Class for login
  */
class Language {

  var $error;

  /**
   * Sets i18n locale language
   *
   * sets the language for i18n php gettext module
   * (gettext has to be enabled in the php.ini)
   *
   */
  function set() {

    if (extension_loaded('gettext')) {

      // try and find the default locale
      $default_lang = preg_replace('/-/','_',$_SERVER['HTTP_ACCEPT_LANGUAGE']);

      $locale = 'en_US';
      $locale_dir = "./locale";
      $directories = getdirectories($locale_dir,"");
      foreach($directories as $directory) {
        $buf = substr($directory,strlen($locale_dir)+1,strlen($directory) - strlen($locale_dir));
        if (preg_match("/" . $buf . "/i",$default_lang)) {
          $locale = $buf;
          break;
        }
      }

      // set locale
      if (empty($_COOKIE['ari_lang']) || !preg_match('/^[\w\._@-]+$/', $_COOKIE['ari_lang'])) {
        $language = $locale;
      } else {
        $language = $_COOKIE['ari_lang'];
      }
      putenv("LANG=$language");
      putenv("LANGUAGE=$language");
      setlocale(LC_MESSAGES,$language);
      bindtextdomain('ari','./locale');
      bind_textdomain_codeset('ari', 'UTF-8');
      textdomain('ari');

    } else {
      function _($str) {
        return $str;
      }
    }
  }

  /**
   * Sets the i18n language in a cookie
   *
   * @param $lang_code
   *   length of random number
   */
  function setCookie($lang_code) {

    if (extension_loaded('gettext')) {
      setcookie("ari_lang", $lang_code, time()+365*24*60*60);
    }
  }

  /**
   * Sets the i18n language in a cookie
   *
   * @param $lang_code
   *   length of random number
   */
  function getForm() {

    // lang setting options
    if (extension_loaded('gettext')) {

      $langOptions = "
        <script>
          function setCookie(name,value) {
            var t = new Date();
            var e = new Date();
            e.setTime(t.getTime() + 365*24*60*60);
            document.cookie = name+\"=\"+escape(value) + \";expires=\"+e.toGMTString();
          }
        </script>
        <form class='lang' name='lang' action=" . $_SESSION['ARI_ROOT'] . " method='POST'>
          <select class='lang_code' name='lang_code'  onChange=\"setCookie('ari_lang',document.lang.lang_code.value); window.location.reload();\">
            <option value='en_US' " . ($_COOKIE['ari_lang']=='en_US' ? 'selected' : '') .  ">English</option>
            <option value='bg_BG' " . ($_COOKIE['ari_lang']=='bg_BG' ? 'selected' : '') .  ">Bulgarian</option>
            <option value='da_DK' " . ($_COOKIE['ari_lang']=='da_DK' ? 'selected' : '') .  ">Danish</option>
            <option value='nl_NL' " . ($_COOKIE['ari_lang']=='nl_NL' ? 'selected' : '') .  ">Dutch</option>
            <option value='es_ES' " . ($_COOKIE['ari_lang']=='es_ES' ? 'selected' : '') .  ">Espa&ntilde;ol</option>
            <option value='fr_FR' " . ($_COOKIE['ari_lang']=='fr_FR' ? 'selected' : '') .  ">French</option>
            <option value='de_DE' " . ($_COOKIE['ari_lang']=='de_DE' ? 'selected' : '') .  ">German</option>
            <option value='el_GR' " . ($_COOKIE['ari_lang']=='el_GR' ? 'selected' : '') .  ">Greek</option>
            <option value='he_IL' " . ($_COOKIE['ari_lang']=='he_IL' ? 'selected' : '') .  ">Hebrew</option>
            <option value='hu_HU' " . ($_COOKIE['ari_lang']=='hu_HU' ? 'selected' : '') .  ">Hungarian</option>
            <option value='it_IT' " . ($_COOKIE['ari_lang']=='it_IT' ? 'selected' : '') .  ">Italian</option>
            <option value='pt_BR' " . ($_COOKIE['ari_lang']=='pt_BR' ? 'selected' : '') .  ">Portuguese</option>
            <option value='ru_RU' " . ($_COOKIE['ari_lang']=='ru_RU' ? 'selected' : '') .  ">Russki</option>
            <option value='sv_SE' " . ($_COOKIE['ari_lang']=='sv_SE' ? 'selected' : '') .  ">Swedish</option>
            <option value='zh_TW' " . ($_COOKIE['ari_lang']=='zh_TW' ? 'selected' : '') .  ">Traditional Chinese</option>
            <option value='uk_UA' " . ($_COOKIE['ari_lang']=='uk_UA' ? 'selected' : '') .  ">Ukrainian</option>
          </select>
        </form>";
    }

    return $langOptions;
  }


}


?>
