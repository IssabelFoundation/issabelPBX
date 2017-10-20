<?php /* $Id */
//Copyright (C) 2009 Astrogen LLC (Philippe Lindheimer) (p_lindheimer at yahoo dot com)
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//version 2.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

function accountcodepreserve_hookGet_config($engine) {
	global $ext;
	global $astman;
  global $active_modules;
	switch($engine) {
		case "asterisk":

      /* set the inheritable channel variable to the callee's accountcode if there is one. Then it will be available in any outbound
         trunk calls replacing the code of the user who is making the call. This way a CF situation results in the CF user's account code.
         With typical calls, the callee will be ARG2 of macro-exten-vm so if coming from there, use that. Otherwise it will be the
         EXTEN that called this macro (such as a followme) so we use MACRO_EXTEN.
       */
			$priority = 'report2';
      $ext->splice('macro-user-callerid', 's', $priority,new ext_execif('$["${CALLEE_ACCOUNCODE}" = ""]', 'Set', '__CALLEE_ACCOUNCODE=${DB(AMPUSER/${IF($["${MACRO_CONTEXT}"="macro-exten-vm"]?${ARG2}:${MACRO_EXTEN})}/accountcode)}'));

      /* check and set the account code in every route (so we don't have to do it in every trunk in case there are fail-over trunks
       */
      if (function_exists('core_routing_getroutepatternsbyid')) {

        // 2.8+ schema version
        //
        $routes = core_routing_list();
        foreach ($routes as $route) {
          $patterns = core_routing_getroutepatternsbyid($route['route_id']);
          $context = 'outrt-'.$route['route_id'];
          foreach ($patterns as $pattern) {
            $fpattern = core_routing_formatpattern($pattern);
            $extension = $fpattern['dial_pattern'];
            $ext->splice($context, $extension, 2, new ext_execif('$[ "${CALLEE_ACCOUNCODE}" != "" ] ','Set','CDR(accountcode)=${CALLEE_ACCOUNCODE}'));
          }
        }
      } else {

        // 2.7- schema version
        //
        $route_list = core_routing_getroutenames();
        foreach($route_list as $route) {
          $context = 'outrt-'.$route[0];
          $patterns = core_routing_getroutepatterns($route[0]);
          foreach($patterns as $rt) {
            //strip the pipe out as that's what we use for the dialplan extension
            $extension = str_replace('|','',$rt);
            // If there are any wildcards in there, add a _ to the start
            if (preg_match("/\.|z|x|\[|\]/i", $extension)) { 
              $extension = "_".$extension;
            }
            $ext->splice($context, $extension, 0, new ext_execif('$[ "${CALLEE_ACCOUNCODE}" != "" ] ','Set','CDR(accountcode)=${CALLEE_ACCOUNCODE}'));
          }
        }
      }

      /* Now lookup each device that has an account code set and create the AMPUSER/user/accountcode key for that user based on the
         first device that we see associated with them. If multiple devices point to the same user, the code used will only be one of them.

         TODO: note this is fine for extension mode assuming there is always a 1-to-1 mapping of device/user. For deviceanduser mode
               it would be necessary to have accountcodes stored with the user and not with the device. And then macro-user-callerid
               would need to set the account code for each user on all calls, just like it does with the language module. (Meaning
               a need to splice a new field into the user. This would not be hard to do, basically an almost exact cut-and-paste of
               the language module code that handles the user gui hook for the language in extensions/users.
       */
      $account_codes = array();
      $devices = core_devices_list('all','full',true);
      foreach ($devices as $device) {
        if ($device['user'] != 'none' && $device['tech'] != 'custom') {
          $dev_props = core_devices_get($device['user']);
          if (isset($dev_props['accountcode']) && $dev_props['accountcode'] != '') {
            $account_codes[$device['user']] = $dev_props['accountcode'];
          }
        }
      }
      foreach ($account_codes as $user => $accountcode) {
		  $astman->database_put("AMPUSER",$user."/accountcode",$accountcode);
      }
      unset($account_codes);
      unset($devices);
		break;
	}
}
?>
