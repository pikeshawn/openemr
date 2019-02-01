<?php
require_once("backline_defs.inc");

class backlineCURL
{
    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;
    var $baseUrl;

    function __construct($cookies = TRUE, $cookie = 'cookies.txt', $compression = 'gzip', $proxy = '')
    {
        $this->baseUrl = 'https://adama.demo.akariobl.com/api';

        $this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $this->headers[] = 'Connection: Keep-Alive';
        $this->headers[] = 'Accept: application/json';
        $this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';

        $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
        $this->compression = $compression;
        $this->proxy = $proxy;
        $this->cookies = $cookies;
        if ($this->cookies == TRUE) $this->cookie($cookie);
    }

    /**
     * Get the partner token.
     * NOTE: The partner token should be kept private and never sent to the client side.
     * @return mixed
     */
    function getPartnerToken(){
        global $systemId;
        global $backlinePartnerUsername;
        global $backlinePartnerPassword;

        //Get the partner token
        $partnerData = $this->post('/partners/tokens/partner','email='.htmlspecialchars($backlinePartnerUsername, ENT_QUOTES, 'UTF-8').'&pw='.$backlinePartnerPassword.'&system_id='.$systemId);

        return $partnerData;
        //return $partnerData['auth_token'];
    }

    function getUserToken($partnerToken, $clientEmail){
        global $systemId;

        if (!isset($clientEmail)) {
            $userDetails = $this->getUserDetails($_SESSION['authUserID']);
            $clientEmail = $userDetails['email'];
        }

        $org = $this->getClientOrg($partnerToken);


        //Get the user token from the partner token
        $userToken = $this->post('/partners/tokens/sudouser', 'auth_token='.$partnerToken.'&email='.htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8').'&system_id='.$systemId);
        //$userToken = $this->post('/partners/tokens/sudouser', 'auth_token='.$partnerToken.'&email='.htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8').'&system_id='.$systemId.'&org_id='.$org->id);

        if (isset($userToken['auth_token'])) {
            return $userToken['auth_token'];
        } else {
            return $userToken;
        }

    }

    /**
     * Get the details for the specified user
     * @param $patientId
     * @return array|null
     */
    function getUserDetails($userId){
        $user_details = sqlQuery("SELECT * FROM users WHERE id = ?", array($userId));
        return $user_details;
    }

    /**
     * Extract a backline user id from a user object's info property
     * @param $userObject
     * @return mixed
     */
    function getBacklineUserId($userObject) {
        // Extract the user id from info section
        //look for anything with "backline user id" in it followed by the first non-whitespace characters
        //backline\s?user\s?id:?\s*(\S*)
        if (preg_match('@backline\s?user\s?id:?\s*(\S*)@i', $userObject['info'], $matches)) {
            return $matches[1];
        }
    }


    /**
     * Get the details for the specified patient
     * @param $patientId
     * @return array|null
     */
    function getPatientDetails($patientId){
        $patient_details = sqlQuery("SELECT * FROM patient_data WHERE pid = ?", array($patientId));
        return $patient_details;
    }

    /**
     * FOR DEBUGGING PURPOSES ONLY.  e-mail and password are hard coded
     * Check to see if there are no client orgs, if there is not, create one using the current users credentials.
     * to log into the account.
     * @param $patientId
     * @return array|null
     */
    function createClientOrg($authToken){
        global $partnerOrgEmail;
        if (!empty($_SESSION['authUserID'])) {
            $orgs = $this->get('/partners/clients'.'?auth_token='.$authToken);

            if (!$orgs || count($orgs->client_orgs) == 0) {
                $clientResponse = $this->post('/partners/clients', 'auth_token='.$authToken.'&email='.htmlspecialchars($partnerOrgEmail, ENT_QUOTES, 'UTF-8').'&pw=Password1&org_name=First Internal Medicine Physicians Org');
                return $clientResponse;
            }
        }

        return false;
    }

    function getClientOrg($authToken){
        $orgs = $this->get('/partners/clients'.'?auth_token='.$authToken);
	    
//echo "<br>";    
//	echo gettype($orgs);
//echo "<br>";
//	var_dump($orgs);

	if (count($orgs->client_orgs) > 0) {
            return $orgs->client_orgs[0];
        }
        return false;
    }



    /**
     * Given a user object, format a name as a human readable string using all available name information in the object
     * @param $userObject
     * @return string
     */
    function formatName($userObject) {
        $fullName = 'test2';
        if (!empty($userObject['title'])) {
            $fullName .= $userObject['title'].' ';
        }
        if (!empty($userObject['fname'])) {
            $fullName .= $userObject['fname'].' ';
        }
        if (!empty($userObject['mname'])) {
            $fullName .= $userObject['mname'].'. ';
        }
        if (!empty($userObject['lname'])) {
            $fullName .= $userObject['lname'].' ';
        }
        return $fullName;
    }

    function cookie($cookie_file)
    {
        if (file_exists($cookie_file)) {
            $this->cookie_file = $cookie_file;
        } else {
            $cookieHandle = fopen($cookie_file, 'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
            $this->cookie_file = $cookie_file;
            fclose($cookieHandle);
        }
    }

    function get($url)
    {
        $url = $this->baseUrl.$url;
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process, CURLOPT_ENCODING, $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        $jsonData = json_decode($return);

        curl_close($process);

            //$jsonData->url = $url;


        return $jsonData;
    }

    function delete($url, $data='')
    {
        $url = $this->baseUrl.$url;
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process, CURLOPT_ENCODING, $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        curl_setopt($process, CURLOPT_POSTFIELDS, $data);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        $return = curl_exec($process);
        $jsonData = json_decode($return, true);

        curl_close($process);

            $jsonData['url'] = $url;
            $jsonData['data'] = $data;

        return $jsonData;
    }

    function post($url, $data)
    {
        $url = $this->baseUrl.$url;
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process, CURLOPT_ENCODING, $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        curl_setopt($process, CURLOPT_POSTFIELDS, $data);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($process, CURLOPT_POST, 1);
        $return = curl_exec($process);
        $jsonData = json_decode($return, true);

        curl_close($process);

   
            $jsonData['_sourceUrl'] = $url;
            $jsonData['data'] = $data;
            $jsonData['response'] = $return;
    
	//var_dump($jsonData);

    return $jsonData;
    }

    function error($error)
    {
        echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
        die;
    }
}



?>
