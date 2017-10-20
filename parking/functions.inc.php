<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

	include_once(dirname(__FILE__) . '/functions.inc/registries.php');
	include_once(dirname(__FILE__) . '/functions.inc/geters_seters.php');
	include_once(dirname(__FILE__) . '/functions.inc/dialplan.php');
    
    function parking_views($view,$data) {
        if(function_exists('parkpro_view')) {
            $o = parkpro_view($view,$data);
            if($o) {
                return $o;
            }
        }
        return load_view(dirname(__FILE__).'/views/'.$view.'.php',$data);
    }
