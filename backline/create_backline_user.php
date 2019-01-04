

<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
require_once('backlineCURL.php');
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

require_once("backline_defs.inc");
require_once("../interface/globals.php");
require_once("../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/auth.inc");


$cc = new backlineCURL();



if (isset($_GET['id'])) {
    $userDetails = $cc->getUserDetails($_GET['id']);
    $partnerAuthToken = $cc->getPartnerToken();

   // $userToken = $cc->getUserToken($partnerAuthToken, $partnerOrgEmail);

    if (isset($_GET['delete']) && $_GET['delete'] == true) {
        $queryData = array(
            'email'=>$userDetails['email'],
            'auth_token'=>$partnerAuthToken
        );

        $query = http_build_query($queryData);

        //Now use the user token to get the link to a public chat for a parrticular patient
        $response = $cc->delete('/partners/clients/users/remove', $query);
    } else {
        $queryData = array('user'=>array(
                'fname'=>$userDetails['fname'],
                'mname'=>$userDetails['mname'],
                'lname'=>$userDetails['lname'],
                'email'=>$userDetails['email'],
                'phone'=>$userDetails['phone'],
                'npi'=>$userDetails['npi'],
                'title'=>$userDetails['title'],
                'fax'=>$userDetails['fax'],
                'reg_source'=>'OpenEMR'
            ),
            'auth_token'=>$partnerAuthToken
        );

        $query = http_build_query($queryData);

        //Now use the user token to get the link to a public chat for a parrticular patient
        $response = $cc->post('/partners/clients/users', $query);
    }

    header('Content-type: application/json');
    echo json_encode($response);
} else {
    echo "{error_code:'invalidUserId', error_text: 'Empty user id specified'}";
}


?>