<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
require_once('backlineCURL.php');
require_once("../patients/verify_session.php");

$pid = $_SESSION['pid'];

$cc = new backlineCURL();



$patientDetails = $cc->getPatientDetails($pid);

//If we don't have a providerID, we can't do backline things cause we need someone to send the message to
if ($patientDetails['providerID']) {
    $userDetails = $cc->getUserDetails($patientDetails['providerID']);
    $partnerData = $cc->getPartnerToken();
    $partnerAuthToken = $partnerData["auth_token"];

    $queryData = array(
        'user_type'=>2,
        'auth_token'=>$partnerAuthToken,
        'fname'=>$patientDetails['fname'],
        'lname'=>$patientDetails['lname'],
        'sender_email'=>$patientDetails['email'],
        'recipient_email'=>$userDetails['email'],
        'content'=>'Hello World');

    $query = http_build_query($queryData);

    //Now use the user token to get the link to a public chat for a parrticular patient
    $userChat = $cc->post('/partners/external/discussions', $query);


    header('Content-type: application/json');
    echo json_encode($userChat);
}

?>
