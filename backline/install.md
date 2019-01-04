Backline OpenEMR Integration installation instructions
======================================================
1. Copy the backline folder to the root of your openEmr installation.
2. Add a script tag linked to backline.js in openemr/interface/main/main_screen.php right after the script tag for
 jquery.
 For example:
<script type="text/javascript" src="../../backline/backline.js"></script>


3. Add a script tag for the patient portal to openemr/patients/summary_pat_portal.php right after the script tag for jquery.fancybox
For example:
<script type="text/javascript" src="<?php echo $web_root; ?>/backline/backline_patient_portal.js"></script>

4. Edit the backline/backline_defs.inc file and put in your backline partner account information.  Also create a new partner
user in the backline admin application and specify the username / email for $partnerOrgEmail

5. Each provider in OpenEMR MUST have a backline id (email address) assigned to them for the integration to
work.  A new link to a user list that allows the accounts will be linked can be found at Administration | Backline Aministration
    1. Goto Administration | Backline Administration
    2. For every user that you want to have a backline account, enter in an e-mail address, then choose Create Backline account

 To test the integration, go to a patient with an e-mail address.  There should be a backline message button in the top
 left.  Clicking on it should start a new chat with that patient.