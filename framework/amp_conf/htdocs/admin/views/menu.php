<?php
global $amp_conf;
global $_item_sort;

$out = '';
$out .= '<div id="header">';
$out .= '<nav class="navbar has-shadow is-dark is-fixed-top" aria-label="main navigation">';
//left hand logo
$out .= '<div class="navbar-brand">'
	. "<a class='navbar-item' href='".$amp_conf['BRAND_IMAGE_ISSABELPBX_LINK_LEFT']."'>";

$out .= '<img src="' . $amp_conf['BRAND_IMAGE_TANGO_LEFT']
    . '" alt="' . $amp_conf['BRAND_ISSABELPBX_ALT_LEFT']
    . '" title="' . $amp_conf['BRAND_ISSABELPBX_ALT_LEFT']
    . '" id="MENU_BRAND_IMAGE_TANGO_LEFT" /></a>';

$out .= '<a id="button_reload" href="#" data-button-icon-primary="ui-icon-gear" class="mt-2 button is-danger animate__animated animate__tada">' . _("Apply Config") .'</a>';
$out .= '<a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="mainnavbar">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>';
$out .= '  <div id="mainnavbar" class="navbar-menu">
    <div class="navbar-start">';

// If issabelpbx_menu.conf exists then use it to define/redefine categories
//
if ($amp_conf['USE_ISSABELPBX_MENU_CONF']) {
    $fd = $amp_conf['ASTETCDIR'].'/issabelpbx_menu.conf';
    if (file_exists($fd)) {
        $favorites = @parse_ini_file($fd,true);
        if ($favorites !== false) foreach ($favorites as $menuitem => $setting) {
            if (isset($ipbx_menu[$menuitem])) {
                foreach($setting as $key => $value) {
                    switch ($key) {
                    case 'category':
                    case 'name':
                        $ipbx_menu[$menuitem][$key] = htmlspecialchars($value);
                        break;
                    case 'type':
                        // this is really deprecated but ???
                        if (strtolower($value)=='setup' || strtolower($value)=='tool') {
                            $ipbx_menu[$menuitem][$key] = strtolower($value);
                        }
                        break;
                    case 'sort':
                        if (is_numeric($value) && $value > -10 && $value < 10) {
                            $ipbx_menu[$menuitem][$key] = $value;
                        }
                        break;
                    case 'remove':
                        // parse_ini_file sets all forms of yes/true to 1 and no/false to nothing
                        if ($value == '1') {
                            unset($ipbx_menu[$menuitem]);
                        }
                        break;
                    }
                }
            }
        } else {
            issabelpbx_log('IPBX_LOG_ERROR', _("Syntax error in your issabelpbx_menu.conf file"));
        }
    }
}


// TODO: these categories are not localizable
//
if (isset($ipbx_menu) && is_array($ipbx_menu)) {    // && issabelpbx_menu.conf not defined
    if (empty($favorites)) foreach ($ipbx_menu as $mod => $deets) {
        switch(strtolower($deets['category'])) {
        case 'admin':
        case 'applications':
        case 'connectivity':
        case 'reports':
        case 'settings':
        case 'user panel':
            $menu[strtolower($deets['category'])][] = $deets;
            break;
        default:
            $menu['other'][] = $deets;
            break;
        }
    } else {
        foreach ($ipbx_menu as $mod => $deets) {
            $menu[$deets['category']][] = $deets;
        }
    }

    $count = 0;
    foreach($menu as $t => $cat) { //catagories
        if (count($cat) == 1) {
            if (isset($cat[0]['hidden']) && $cat[0]['hidden'] == 'true') {
                continue;
            }
            $href = isset($cat[0]['href']) ? $cat[0]['href'] : 'config.php?display=' . $cat[0]['display'];
            $target = isset($cat[0]['target']) ? ' target="' . $cat[0]['target'] . '"'  : '';
            $hclass = $cat[0]['display'] == $display ? 'ui-state-highlight' : '';
	        $mods[$t] = '<a class="navbar-item" href="' . $href . '" ' . $target .'>'.modgettext::_(ucwords($cat[0]['name']),$cat[0]['module']['rawname']) . '</a>';
            continue;
        }
        // $t is a heading so can't be isolated to a module, translation must come from amp
	    //$mods[$t] = '<div class="navbar-item has-dropdown is-hoverable"><a class="navbar-link">'. _(ucwords($t)) .'</a>';
	    $mods[$t] = '<button class="navbar-item has-dropdown is-hoverable"><a class="navbar-link">'. _(ucwords($t)) .'</a>';

        if(count($cat)>7) {
            $multico=' multicolumn ';
            $scroll_div_open  = '<div class="scroll-container">';
            $scroll_div_close = '</div>';
            $scroll_p_open    = '<p class="scroll">';
            $scroll_p_close   = '</p>';
        } else {
            $multico='';
            $scroll_div_open  = '';
            $scroll_div_close = '';
            $scroll_p_open    = '';
            $scroll_p_close   = '';
        }
        $mods[$t] .= '<div class="navbar-dropdown '.$multico.'">'."\n";

        foreach ($cat as $c => $mod) { //modules
            if (isset($mod['hidden']) && $mod['hidden'] == 'true') {
                continue;
            }
            $classes = array();

            //build defualt module url
            $href = isset($mod['href'])
                ? $mod['href']
                : "config.php?display=" . $mod['display'];

            $target = isset($mod['target'])
                ? ' target="' . $mod['target'] . '" '  : '';

            //highlight currently in-use module
            if ($display == $mod['display']) {
                $classes[] = 'is-active';
            }

            //highlight disabled modules
            if (isset($mod['disabled']) && $mod['disabled']) {
                $classes[] = 'is-disabled';
            }

	        // try the module's translation domain first
	        $trans_name = modgettext::_(ucwords($mod['name']), $mod['module']['rawname']);
            $items[$trans_name] = $scroll_div_open.'<a href="' . $href . '"'
                . $target
                . ' class="navbar-item ' . implode(' ', $classes) .'">' 
                . $scroll_p_open
		        . $trans_name
                . $scroll_p_close
                . '</a>'.$scroll_div_close. "\n";

	        $_item_sort[$mod['name']] = $mod['sort'];
        }
        uksort($items,'_item_sort');
	    $mods[$t] .= implode($items);

	    $mods[$t].="</div></button>";
//	    $mods[$t].="</div></div>";
        unset($items);
        unset($_item_sort);
    }
    uksort($mods,'_menu_sort');
    $out .= implode($mods);
}

$out.='</div><div class="navbar-end">';

$current_lang = $_COOKIE['lang'];

$aval_lang = array();
$aval_lang["en_US"]=_('English');
$aval_lang["es_ES"]= _('Español');
$aval_lang["pt_BR"]= _('Português');
$aval_lang["bg_BG"]= _('Български');
$aval_lang["zh_CN"]= _('中文');
$aval_lang["de_DE"]= _('Deutsch');
$aval_lang["fr_FR"]= _('Français');
$aval_lang["he_IL"]= _('עִברִית');
$aval_lang["hu_HU"]= _('Magyar');
$aval_lang["it_IT"]= _('Italiano');
$aval_lang["pt_PT"]= _('Português');
$aval_lang["ru_RU"]= _('Русский');
$aval_lang["sv_SE"]= _('Svenska');
$aval_lang["ja_JP"]= _('日本');

if($amp_conf['SHOWLANGUAGE']) {
    $out .= '<button class="navbar-item has-dropdown is-hoverable"><a class="navbar-link" id="language-menu-button">';
    $out .= '<i class="fa fa-language mr-2"></i>';
    $out .= _('Language') . '</a>';
    $out .= '<div class="navbar-dropdown is-right">';
    foreach($aval_lang as $iso=>$desc) {
        $parts = preg_split("/_/",strtolower($iso));
        $flag='';
        if(file_exists("images/".$parts[1].".svg")) {
            $flag = "images/".$parts[1].".svg";
        } else {
            if(file_exists("images/".$parts[0].".svg")) {
                $flag = "images/".$parts[0].".svg";
            }
        }
        $current = ($current_lang == $iso)?" current":"";
        $out .= '<a href="javascript:void(0)" class="navbar-item onelang'.$current.'" data-lang="'.$iso.'">';
        $out .= '<img alt="flag icon" style="width:1em;height:1em;" src="'.$flag.'" class="mr-2"/>';
        $out.= $desc . '</a>';
    }
    $out .= '</div>';
    $out .= '</button>';
}

$out .= '<div class="navbar-item"><div class="buttons">';
if ( isset($_SESSION['AMP_user']) && ($authtype != 'none')) {
    $out .= '<a id="user_logout" href="#"'
        . ' class="button is-primary" title="logout">'
        . _('Logout') . ': ' . (isset($_SESSION['AMP_user']->username) ? $_SESSION['AMP_user']->username : 'ERROR')
	. '</a>';
}
 
$out .= '</div></div></div></div>';

$out .= '</nav>';

$out .= '</div>';//header

$out .= '<div id="page_body">';

echo $out;

// key sort but keep Favorites on the far left, Other on the far right
//
function _menu_sort($a, $b) {
    $a = strtolower($a);
    $b = strtolower($b);
    if ($a == 'favorites')
        return false;
    else if ($b == 'favorites')
        return true;
    else if ($a == 'other')
        return true;
    else if ($b == 'other')
        return false;
    else
        return $a > $b;
}

function _item_sort($a, $b) {
    global $_item_sort;

    if (!empty($_item_sort[$a]) && !empty($_item_sort[$a]) && $_item_sort[$a] != $_item_sort[$b])
        return $_item_sort[$a] > $_item_sort[$b];
    else
        return $a > $b;
}

?>
