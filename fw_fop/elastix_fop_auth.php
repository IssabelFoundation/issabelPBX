<?php
function showview($viewname, $parameters = false) {
    $documentRoot = $_SERVER["DOCUMENT_ROOT"];
    if (is_array($parameters)) {
           extract($parameters);
    }
    $viewname = str_replace('..','.',$viewname); // protect against going to subdirectories
    if (file_exists("$documentRoot/admin/views/".$viewname.'.php')) {
           include("$documentRoot/admin/views/".$viewname.'.php');
    }
}

/*******Empieza validacióara saber si el usuario tiene permisos o no para ingresar al fop********/
$documentRoot = $_SERVER["DOCUMENT_ROOT"];
include_once "$documentRoot/libs/paloSantoDB.class.php";
include_once "$documentRoot/libs/paloSantoACL.class.php";

session_name("elastixSession");
session_start();

$elastix_user = (isset($_SESSION["elastix_user"]))?$_SESSION["elastix_user"]:null;
$pDB          = new paloDB("sqlite3:////var/www/db/acl.db");
$pACL         = new paloACL($pDB);
$isUserAuth   = $pACL->isUserAuthorized($elastix_user,"access","fop");
$fromDirectAccess  = (isset($_SERVER['REQUEST_URI']) && preg_match("/\/admin\/modules\/fw_fop\//",$_SERVER['REQUEST_URI']))?true:false;

if(!$fromDirectAccess || !$isUserAuth){
   include_once "$documentRoot/libs/misc.lib.php";
   include_once "$documentRoot/configs/default.conf.php";
   $lang = get_language("$documentRoot/");
   if(file_exists("$documentRoot/lang/$lang.lang"))
       include_once "$documentRoot/lang/$lang.lang";
   else
       include_once "$documentRoot/lang/en.lang";
   global $arrConf;
   global $arrLang;
   $advice = isset($arrLang["Unauthorized"])?$arrLang["Unauthorized"]:"Unauthorized";
   $msg1 = isset($arrLang['You are not authorized to access this page.'])?$arrLang['You are not authorized to access this page.']:'You are not authorized to access this page.';
   $msg2 = isset($arrLang['You have not permissions to access to Flash Operator Panel. Please contact your administrator.'])?$arrLang['You have not permissions to access to Flash Operator Panel. Please contact your administrator.']:'You have not permissions to access to Flash Operator Panel. Please contact your administrator.';
   $title  = isset($arrLang['Advice'])?$arrLang['Advice']:'Advice';
   $template['content']['msg']   =  "<br /><b style='font-size:1.5em;'>$advice</b> <p>$msg1<br/>$msg2</p>";
   $template['content']['title'] = $title;
   $template['content']['theme'] = $arrConf['mainTheme'];
   showview("elastix_advice",$template);
   exit;
}
/**********Fin de la validació****************************************************************/
$request      = $_SERVER['REQUEST_URI'];
$documentRoot = $_SERVER["DOCUMENT_ROOT"];

if(preg_match("/variables\.txt/",$request)){
     echo file_get_contents("$documentRoot/admin/modules/fw_fop/variables.txt");
     exit;
}

if(preg_match("/operator_panel\.swf/",$request)){
     echo file_get_contents("$documentRoot/admin/modules/fw_fop/flash/operator_panel.swf");
     exit;
}
?>
