<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Emails/mail.php');
include('config.php');


	
		global $adb,$log, $current_user;
				
// Start for Approval Campaign
 
/**   Function used to send email
  *   $module 		-- current module
  *   $to_email 	-- to email address
  *   $from_name	-- currently loggedin user name
  *   $from_email	-- currently loggedin vtiger_users's email id. you can give as '' if you are not in HelpDesk module
  *   $subject		-- subject of the email you want to send
  *   $contents		-- body of the email you want to send
  *   $cc		-- add email ids with comma seperated. - optional
  *   $bcc		-- add email ids with comma seperated. - optional.
  *   $attachment	-- whether we want to attach the currently selected file or all vtiger_files.[values = current,all] - optional
  *   $emailid		-- id of the email object which will be used to get the vtiger_attachments
  */
   					
    
  //if($currentid != "" && $sourceRecord != "") {
	  		$history_qry = $adb->query("SELECT * FROM vtiger_seactivityrel ");		
  		if($adb->num_rows($history_qry) > 0) {
			while($row = $adb->fetch_array($history_qry)) {
				$crmid = $row['crmid'];			
				$adb->query("UPDATE vtiger_leaddetails SET import_lead_flag = '1' where leadid  = $crmid ");
			}
		}	
		
		/*
		$email2 = 'ajayk@techfoursolutions.com';
		//echo $email2.'___'.$user_name1.'___'.$subject.'___'.$cc; echo "<br>";
		//echo $approvalmailalert;die;	
		$subject = "Testing Cron Job";
		$descriptions = "Testing cron ...............";
		$user_name1 = 'admin';
		$cc = 'ajayk@techfoursolutions.com, nadeemk@techfoursolutions.com';
						
		send_mail('',$email2,$user_name1,'',$subject,$descriptions,$cc);*/
			
?>