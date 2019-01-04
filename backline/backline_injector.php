<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

/* Include our required headers */
require_once('../interface/globals.php');
require_once('backlineCURL.php');
require_once("backline_defs.inc");

$cc = new backlineCURL();

if (!empty($_SESSION['authUserID'])) {
    $userDetails = $cc->getUserDetails($_SESSION['authUserID']);
    $backlineUserId = $userDetails['email'];
}


//echo "Backline User Id: -> ".$backlineUserId."\n";

//$f = fopen('backlineDataVariables.txt', 'w');
//fwrite($f, $backlineUserId);
//fwrite($f, $backlineUserId);
//fclose($f);

if (!empty($backlineUserId)) {

//$f = fopen('backlineData.txt', 'w');

    $partnerAuthToken = $cc->getPartnerToken();

//echo "Partner Auth Token: -> ". $partnerAuthToken."\n";


//var_dump($partnerAuthToken);


    $org = $cc->getClientOrg($partnerAuthToken);

//var_dump($org);

//echo "Org: -> ". $org."\n";

    //Get the user token from the partner token
    $userToken = $cc->post('/partners/tokens/sudouser', 'auth_token='.$partnerAuthToken.'&email='.htmlspecialchars($userDetails['email'], ENT_QUOTES, 'UTF-8').'&system_id='.$systemId.'&org_id='.$org->id);

//var_dump($userToken);

//echo "user token: -> ". $userToken . "\n";


    if (!empty($userToken['auth_token'])) {
        $userData = new stdClass();

//var_dump($userData);


        //If we have the patient id return the data for that specific user, otherwise just return the global unread count
        if (!empty($_GET["patient_id"]) ){
            //Get the patient id from the query string
            $patientId = $_GET["patient_id"];

//var_dump($patientId);

            //Try and get the patient_details including the patient e-mail
            $patient_details = $cc->getPatientDetails($patientId);

//var_dump($patient_details);

    //        var_dump($patient_details);
            if (!$patient_details) {
                $userData->error_code = 'INVALID_PATIENT_ID';
                $userData->error_message = 'An patient with the id ('.$patientId.') could not be found';
            } else if (empty($patient_details['email']) || strlen($patient_details['email']) === 0) {
                $userData->error_code = 'MISSING_PATIENT_EMAIL';
                $userData->error_message = 'An email could not be found for the patient with the id ('.$patientId.')';
            } else {

                //Get a chat URL with
                $queryData = array(
                    'user_type'=>2,
                    'auth_token'=>$userToken['auth_token'],
                    'fname'=>$patient_details['fname'],
                    'lname'=>$patient_details['lname'],
                    'email'=>$patient_details['email'],
                    'phone'=>$patient_details['phone_home']);

                $query = http_build_query($queryData);

                //fwrite($f, $query."\n");
                //fclose($f);                

                //Now use the user token to get the link to a public chat for a parrticular patient
                $userChat = $cc->post('/partners/users/discussions', $query);



                $location = null;
                $isDeceased = $patient_details['deceased_date'] && $patient_details['deceased_date'] !== '0000-00-00 00:00:00';

                if ($patient_details['city'] || $patient_details['state']) {
                    $location = $patient_details['city'].', '.$patient_details['state'];
                }

                //Get a chat URL with
                $pccQueryData = array(
                    'user_type'=>2,
                    'auth_token'=>$userToken['auth_token'],
                    'firstname'=>$patient_details['fname'],
                    'middlename'=>$patient_details['mname'],
                    'lastname'=>$patient_details['lname'],
                    'gender'=>$patient_details['sex'],
                    'deceased'=>$isDeceased,
                    'mrn'=>$patient_details['pid'],
                    'location'=>$location,
                    'dob'=>$patient_details['DOB']);

                $pccQuery = http_build_query($pccQueryData);

                //Now use the user token to get the link to a public chat for a parrticular patient
                $pccChat = $cc->post('/partners/users/pcc', $pccQuery);

                $userData->patient = $patient_details;
                $userData->user_token = $userToken;
                $userData->user_chat = $userChat;
                $userData->pcc_chat = $pccChat;
            }


        } else {
            //Now use the user token to get the link to a public chat for a parrticular patient
            $unreadCount = $cc->get('/partners/users/messages/unread'.'?auth_token='.$userToken['auth_token']);
            $recentsLink = $cc->get('/partners/users/discussions/recent'.'?auth_token='.$userToken['auth_token']);

            if (isset($unreadCount->error_code)) {
                $userData = $unreadCount;
            } else if (isset($recentsLink->error_code)) {
                $userData = $recentsLink;
            } else {
                $userData->unread_count = $unreadCount->unread_count;
                $userData->recent_link = $recentsLink;
            }


        }
    } else {
        $userData = $userToken;
    }


    header('Content-type: application/json');
    echo json_encode($userData);
} else {
    $errorResponse = new stdClass();
    header('Content-type: application/json');
    $errorResponse->error_code = 'MISSING_USER_EMAIL';
    $errorResponse->error_message = 'An email could not be found for the user ('.$userDetails['username'].')  An e-mail with an associated backline account must be setup under the "Backline" Admin tab for the current user for backline integration to work.';
    echo json_encode($errorResponse);
}


//
//curl 'http://adama.ppg.akariobl.com/api/partners/tokens/sudouser'
//-H 'Pragma: no-cache' -H 'Origin: http://adama.ppg.akariobl.com'
//-H 'Accept-Encoding: gzip, deflate'
//-H 'Accept-Language: en-US,en;q=0.8'
//-H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'
//-H 'Content-Type: application/x-www-form-urlencoded'
//-H 'Accept: application/json'
//-H 'Cache-Control: no-cache'
//-H 'Referer: http://adama.ppg.akariobl.com/apidoc'
//-H 'Connection: keep-alive'
//--data 'auth_token=Qcgy8ngznN9kTMMFBFjs&email=chuck-openemr%40sbx.akariobl.com&system_id=55'
//--compressed

//echo '<!--';
//var_dump($partnerAuthToken);
//var_dump($userToken);
//echo '--!>';


?>
