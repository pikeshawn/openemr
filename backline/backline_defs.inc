<?php
$systemId=0;  # Unique ID for the session being created on behalf of the user.  THis should normally be left at 0

# A partner user in the org that must be created in the backline admin app that will be used to create the client users
$partnerOrgEmail='OpenEmrTest@sbx.akariobl.com';

# The system account used to generate a partner token
$backlinePartnerUsername = 'fimp-partner@sbx.akariobl.com';
//$backlinePartnerUsername = 'openemr-master-account@sbx.akariobl.com';
$backlinePartnerPassword = 'Password1';
?>
