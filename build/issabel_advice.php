<?php

if (!ini_get('date.timezone')) {
    if (@date_default_timezone_get()) {
        @date_default_timezone_set(date_default_timezone_get());
    }
}

$theme         = isset($content['theme'])?$content['theme']:"elastixwave";
$size = "100%";
$styleBody = "";
$styleDiv = "";
switch($theme){
    case "elastixwave":
	  $image = "/themes/elastixwave/images/logo_elastix.gif";
	  break;
    case "default":
	  $image = "/images/logo_elastix.png";
	  break;
    case "elastixneo":
	  $image = "/themes/elastixneo/images/elastix_logo_mini2.png";
	  $size = "1270px";
	  $styleBody = "style='background-image:url(/themes/elastixneo/images/bgbodytest.png);'";
	  $styleDiv = "style='background:#FFFFFF; height:100%; width:1270px;'";
	  break;
    default:
	  $image = "/images/issabel_logo_mini2.png";
	  break;
}
$theme	       = "/themes/$theme";
$currentYear   = date("Y");
$msg           = isset($content['msg'])?$content['msg']:"";
$title         = isset($content['title'])?$content['title']:"";
$islicensed    = isset($content['islicensed'])?$content['islicensed']:"";
?>

<html>
<head>
<title>Issabel - <?php echo $title; ?></title>
<link rel="stylesheet" href="<?php echo $theme; ?>/styles.css">
<script src="/themes/tenant/js/lottie.min.js"></script>
<script src="/libs/js/jquery/jquery-1.11.2.min.js"></script>
<script type="text/javascript">
var baseurl = '';

$(document).ready(function() {
    var anim;
    var animData = {
        container: document.getElementById('denied'),
        renderer: 'svg',
        loop: false,
        autoplay: true,
        rendererSettings: {
            progressiveLoad: false
        },
        path: '/images/denied.json'
    };
    anim = bodymovin.loadAnimation(animData);
    anim.setSpeed(1.5);
});
</script>
</head>

<body bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" <?php echo $styleBody; ?> >
  <table cellspacing="0" cellpadding="0" width="<?php echo $size;?>" border="0" class="menulogo2" height="74">
    <tr>
       <td class="menulogo" valign="top">
           <a target="_blank" href="http://www.issabel.org">
               <!--img border="0" src="<?php echo $image; ?>"/-->
<div id='denied'></div>
           </a>
       </td>
    </tr>
  </table>
  <div align="center" <?php echo $styleDiv; ?> >
    <?php echo $msg; ?>
  <div/>
  <br /><br />
  <div align="center" class="copyright"><a href="http://www.issabel.org" target='_blank'>Issabel</a> <?php echo $islicensed; ?> <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank'>GPL</a>. 2006 - <?php echo $currentYear; ?>.</div>
  <br />
</body>
</html>
