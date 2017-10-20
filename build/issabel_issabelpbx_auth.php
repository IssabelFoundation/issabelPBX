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

$documentRoot = $_SERVER["DOCUMENT_ROOT"];
include_once "$documentRoot/libs/paloSantoDB.class.php";
include_once "$documentRoot/libs/paloSantoACL.class.php";

session_name("issabelSession");
session_start();
$issabel_user = (isset($_SESSION["issabel_user"]))?$_SESSION["issabel_user"]:null;
$pDB = new paloDB("sqlite3:////var/www/db/acl.db");
$pACL = new paloACL($pDB);
$isUserAuth = $pACL->isUserAuthorized($issabel_user,"access","pbxadmin");
unset($_SESSION);
session_commit();

if(file_exists("$documentRoot/modules/sec_advanced_settings/libs/paloSantoChangePassword.class.php")){
    include_once "$documentRoot/modules/sec_advanced_settings/libs/paloSantoChangePassword.class.php";
    include_once("$documentRoot/libs/misc.lib.php");
    require_once "$documentRoot/configs/default.conf.php";
    global $arrConf;

    $pAdvancedSecuritySettings = new paloSantoAdvancedSecuritySettings($arrConf);
    $fromDirectAccess = (isset($_SERVER['REQUEST_URI']) && preg_match("/\/admin\/config.php/",$_SERVER['REQUEST_URI']))?true:false;

    if((!$pAdvancedSecuritySettings->isActivatedIssabelPBXFrontend() && $fromDirectAccess) || !$isUserAuth){
       if(isset($_SESSION['AMP_user'])) unset($_SESSION['AMP_user']);
       $_SESSION['logout'] = true;

       $lang = get_language("$documentRoot/");
       $lang_file="$documentRoot/modules/sec_advanced_settings/lang/$lang.lang";
       if (file_exists("$lang_file")) include_once "$lang_file";
       else include_once "$documentRoot/modules/sec_advanced_settings/lang/en.lang";
       global $arrLangModule;

       $advice = isset($arrLangModule['Unauthorized'])?$arrLangModule['Unauthorized']:'Unauthorized';
       $msg1   = isset($arrLangModule['You are not authorized to access this page.'])?$arrLangModule['You are not authorized to access this page.']:'You are not authorized to access this page.';
       $msg2   = isset($arrLangModule["Enable direct access (Non-embedded) to IssabelBX in \"Security >> Advanced Security Settings\" menu."])?$arrLangModule["Enable direct access (Non-embedded) to IssabelPBX in \"Security >> Advanced Security Settings\" menu."]:"Enable direct access (Non-embedded) to IssabelPBX in \"Security >> Advanced Security Settings\" menu.";
       $title  = isset($arrLangModule['Advice'])?$arrLangModule['Advice']:'Advice';

       $template['content']['msg']   =  "<br /><b style='font-size:1.5em;'>$advice</b> <p>$msg1<br/>$msg2</p>";
       $template['content']['title'] = $title;
       $template['content']['theme'] = $arrConf['mainTheme'];
       showview("issabel_advice",$template);
       exit;
    }
}  
?>
