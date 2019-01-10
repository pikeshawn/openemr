<?php
/**
 * This script Assign acl 'Emergency login'.
 *
 * Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

require_once("backline_defs.inc");
require_once("../interface/globals.php");
require_once("../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/auth.inc");
require_once("$srcdir/formdata.inc.php");
//require_once(dirname(__FILE__) . "../library/classes/WSProvider.class.php");
//require_once ($GLOBALS['srcdir'] . "/classes/postmaster.php");


/* Include our required headers */
require_once('../interface/globals.php');
require_once('backlineCURL.php');

$cc = new backlineCURL();




    $partnerData = $cc->getPartnerToken();
    $partnerAuthToken = $partnerData["auth_token"];

//echo $partnerAuthToken["auth_token"];
//echo "<br>";
//echo json_encode($partnerAuthToken);

    $userToken = $cc->getUserToken($partnerAuthToken, $partnerOrgEmail);
    $clientOrgs = $cc->get('/partners/clients'.'?auth_token='.$partnerAuthToken);
    $parentOrgs = $partnerData["user"]["orgs"];

//echo json_encode($orgs);
    //echo "<pre>".json_encode($cc->createClientOrg($partnerAuthToken))."</pre>";




/*if (isset($_GET["mode"])) {
  if ($_GET["mode"] == "update_email") {
    //set the facility name from the selected facility_id
    sqlStatement("UPDATE users SET users.email = '".trim(formData('email'))."' WHERE users.id = '" . $_GET["id"] ."'");
  }
}*/






$alertmsg = '';
$bg_msg = '';
$set_active_msg=0;
$show_message=0;
$form_inactive = empty($_REQUEST['form_inactive']) ? false : true;

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
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.easydrag.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/backline/backline_user_management.js"></script>

<script type="text/javascript">

<?php

if (isset($_POST["mode"])) {
  if ($_POST["mode"] == "update_email") {
    //Update the email address of the specified user
    sqlStatement("UPDATE users SET users.email = '".trim(formData('email'))."' WHERE users.id = '" . trim(formData('id')) ."'");
  }
}
?>
</script>

<script type="text/javascript">

$(document).ready(function(){

    // fancy box
    enable_modals();

    tabbify();

    // special size for
	//$(".iframe_medium").fancybox( {
	//	'overlayOpacity' : 0.0,
	//	'showCloseButton' : true,
	//	'frameHeight' : 450,
	//	'frameWidth' : 660
	//});

	$(".iframe_medium").fancybox();

	$(function(){
		// add drag and drop functionality to fancybox
		$("#fancy_outer").easydrag();
	});
});

</script>
<script language="JavaScript">

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

</script>

</head>
<body class="body_top">

<div>
    <div>
       <table>
	  <tr >
		<td><b><?php xl('Backline Users','e'); ?></b></td>

		</td>

	  </tr>
	</table>
    </div>
    <div style="width:650px;">
        <div>

<div>
    Specify which OpenEMR users should have access to backline by creating a linked backline account.   A user MUST have
    an e-mail address to be linked to backline.  User's will be sent a welcome e-mail with thier backline only password
    and details to access the mobile app upon account creation.  Linked backline users can access the desktop client
    within OpenEMR without a password after logging into OpenEMR.
</div>
<br/>



<?php  if (empty($parentOrgs)) { ?>
    <div>A backline parent organization does not currently exist for your partner organization.  Please contact your OpenEMR vendor to create an organization for use with backline.</div>
    <pre>
    <?php echo json_encode($parentOrgs); ?>
    </pre>
<?php } else { ?>

    <h1 class="text-center">Parent Organizations</h1>

    <?php foreach($parentOrgs as $org){ ?>

        <h3><?php echo $org['name'] . " : " . $org['id']; ?></h3>

        <?php $partnerusers = $cc->get('/partners/clients/users'.'?auth_token='.$partnerAuthToken.'&org_id='.$org["id"]); ?>

             <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                         foreach ($partnerusers->users as $user) {
                             //echo "<tr><td>$user['id']</td><td>$user['fname'] $user['lname']</td><td>$user['email']</td></tr>";
                             echo "<tr><td>".$user->id."</td><td>".$user->fname." ".$user->lname."</td><td>".$user->email."</td></tr>";
//var_dump($user);
                         }
                        ?>
                    </tr>
                </tbody>
            </table>

    <?php } ?>


    <?php  if (empty($clientOrgs)) { ?>

        <div>A backline client organization does not currently exist for your partner organization.  Please contact your OpenEMR vendor to create an organization for use with backline.</div>
        <pre>

    <?php echo json_encode($clientOrgs); ?>
    </pre>
    <?php } else { ?>


        <h1 class="text-center">Client Organizations</h1>

        <?php foreach($clientOrgs->client_orgs as $org){ ?>

            <h3><?php echo $org->name. " : " . $org->id; ?></h3>

            <?php $clientusers = $cc->get('/partners/clients/users'.'?auth_token='.$partnerAuthToken.'&org_id='.$org->id); ?>

	<?php if(empty($clientusers->users)) {
		echo "There are no Users for this organization";

	} else {?>


            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php
                    foreach ($clientusers->users as $user) {
                        //echo "<tr><td>$user['id']</td><td>$user['fname'] $user['lname']</td><td>$user['email']</td></tr>";
                             //echo "<tr><td>a</td><td>b</td><td>c</td></tr>";
                             echo "<tr><td>".$user->id."</td><td>".$user->fname." ".$user->lname."</td><td>".$user->email."</td></tr>";
                     }
                    ?>
                </tr>
                </tbody>
            </table>
                
              <?php } ?>

	<?php } ?>




<!--        foreach() {-->
<!---->
<!--        }-->
<!---->
<!--        $clientOrg = $orgs->client_orgs[0];-->
<!---->
<!--        $allPartnerOrgs = [];-->
<!---->
<!--        $allUserOrgs = [];-->
<!---->
<!--        foreach () {-->
<!---->
<!--        }-->
<!---->
<!--        $users = $cc->get('/partners/clients/users'.'?auth_token='.$partnerAuthToken.'&org_id='.$clientOrg->id);-->
<!--        $users = $cc->get('/partners/clients/users'.'?auth_token='.$partnerAuthToken.'&org_id='.$clientOrg->id);-->
<!--//    $users = $cc->get('/partners/clients/users'.'?auth_token='.$partnerAuthToken.'&org_id=996);-->
<!---->
<!--// var_dump($users);-->
<!--        ?>-->


<?php } ?>
<hr>

<table cellpadding="1" cellspacing="0" class="showborder">
	<tbody><tr height="22" class="showborder_head">
		<th width="180px"><b><?php xl('Username','e'); ?></b></th>
		<th width="270px"><b><?php xl('Real Name','e'); ?></b></th>
		<th width="320px"><b><span class="bold"><?php xl('E-Mail','e'); ?></span></b></th>
		<th width="320px"><b><?php xl('Backline Account','e'); ?>?</b></th>

		<?php
            $query = "SELECT * FROM users WHERE username != '' ";
                if (!$form_inactive) $query .= "AND active = '1' ";
            $query .= "ORDER BY username";
            $res = sqlStatement($query);
            for ($iter = 0;$row = sqlFetchArray($res);$iter++) {
                $result4[$iter] = $row;
            }
            foreach ($result4 as $iter) {
                if ($iter{"authorized"}) {
                    $iter{"authorized"} = xl('yes');
                } else {
                    $iter{"authorized"} = "";
                }

            print "<tr height=20  class='text' style='border-bottom: 1px dashed;'>
                  <td class='text'><b><span>" . $iter{"username"} . "</span></b>" ."&nbsp;</td>
              <td><span class='text'>" . attr($iter{"fname"}) . ' ' . attr($iter{"lname"}) ."</span>&nbsp;</td>";

              if (isset($iter{"email"}) && strlen($iter{"email"}) > 1) {
                  $foundBacklineUser = false;
                  foreach ($partnerusers->users as $backlineUser) {
                      if($backlineUser->email == $iter{"email"}) {
                          $foundBacklineUser = true;
                      }
                  }
              }

   print "<td><span class='text'>" . $iter{"email"} . "</span>&nbsp;<a href='backline_email_add.php?mode=update_email&id=" . $iter{"id"} . "&email=" . $iter{"email"} . "' class='iframe_medium'>[Edit]</a></td>";

  // print "<td><a href='test.html' class='iframe_medium'>[Edit]</a></td>";

 // echo "<div id='new'>My new Content</div>";

  if (isset($iter{"email"}) && strlen($iter{"email"}) > 1) {
      print "<td width=\"300px\" id=\"createBacklineUserLink".$iter{"id"}."\">";
      if ($foundBacklineUser) {
        print "Yes <a onclick=\"deleteBacklineUser(". $iter{"id"} . ", 'createBacklineUserLink".$iter{"id"}."')\" >[Delete Backline Act.]</a>";
      } else {
        print "No <a onclick=\"createBacklineUser(". $iter{"id"} . ", 'createBacklineUserLink".$iter{"id"}."')\" >[Create Backline Act.]</a>";
      }
      print "</td>";

  } else {
    print "<td width=\"300px\">[Email Required]</td>";
  }

  print "</tr>\n";
}
?>
	</tbody></table>



<?php } ?>


        </div>
    </div>
</div>


<script language="JavaScript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
</script>

</body>
</html>
