<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

$srcdir = '../library';

require_once("../interface/globals.php");
require_once("../library/acl.inc");
//require_once("$srcdir/sha1.js");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once(dirname(__FILE__) . "/../library/classes/WSProvider.class.php");
require_once("$srcdir/erx_javascript.inc.php");
require_once('backlineCURL.php');

$alertmsg = '';

$cc = new backlineCURL();
$userDetails = $cc->getUserDetails($_GET['id']);

?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox.js"></script>

<script src="interface/usergroup/checkpwd_validation.js" type="text/javascript"></script>

<script language="JavaScript">
function trimAll(sString)
{
	while (sString.substring(0,1) == ' ')
	{
		sString = sString.substring(1, sString.length);
	}
	while (sString.substring(sString.length-1, sString.length) == ' ')
	{
		sString = sString.substring(0,sString.length-1);
	}
	return sString;
} 

function submitform() {

       alertMsg='';
       f=document.forms[0];

        if (f.email.value.length <= 0) {
            alertMsg += "<?php xl('Required field missing: Please enter the email.  ','e');?>";
        } else {
            alertMsg += checkLength('email',f.email.value,50);
            //alertMsg += checkUsername(f.email,f[i].value);
        }

       if(alertMsg)
       {
          alert(alertMsg);
          return false;
       }

       f.submit();
       parent.$.fn.fancybox.close();
}


</script>
</head>
<body class="body_top">


<table><tr><td>
<span class="title"><?php xl('Edit User Email','e'); ?></span>&nbsp;</td>
<td>
<a class="css_button" name='form_save' id='form_save' href='#' onclick="return submitform()">
	<span><?php xl('Save','e');?></span></a>
<a class="css_button large_button" id='cancel' href='#'>
	<span class='css_button_span large_button_span'><?php xl('Cancel','e');?></span>
</a>
</td></tr></table>
<br><br>

<table border=0>
    <tr>
        <td valign=top>
            <?php
                echo $userDetails{"fname"};
            ?>
        </td>
        <td valign=top>
            <?php
                echo $userDetails{"lname"};
            ?>
        </td>
    </tr>
</table>


<table border=0>

<tr><td valign=top>
<form name='email' method='post'  target="_parent" action="backline_orgs.php">
    <input type='hidden' name='mode' value='update_email'>
    <input type='hidden' name='secure_pwd' value="<?php echo $GLOBALS['secure_password']; ?>">
    <input type='hidden' name='id' value="<?php echo $_GET['id']; ?>">

    <span class="bold">&nbsp;</span>
    </td><td>

    <span class="text"><?php xl('E-Mail','e'); ?>: </span></td><td  style="width:450px;">
        <input type=entry name=email style="width:420px;"  value="<?php echo $_GET['email']; ?>">
        <span class="mandatory">&nbsp;*</span>

    <br>
    <input type="hidden" name="newauthPass">
</form>
</td>

</tr>


</table>



<script language="JavaScript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
$(document).ready(function(){
    $("#cancel").click(function() {
		  parent.$.fn.fancybox.close();
	 });

});
</script>
<table>

</table>

</body>
</html>
