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
	  			
		/*$counter = 0;
		$history_qry = $adb->query("SELECT id, lead_no, smownerid, vtiger_crmentity.crmid as currcrmid FROM vtiger_modtracker_basic 
		INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_modtracker_basic.crmid
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid

WHERE vtiger_crmentity.deleted = 0 AND vtiger_modtracker_basic.status = 2 AND history_status = '0' AND vtiger_modtracker_basic.module = 'Leads' ORDER BY vtiger_crmentity.crmid");		
  		if($adb->num_rows($history_qry) > 0) {
			while($row = $adb->fetch_array($history_qry)) {		
				$lead_no = $row['lead_no'];
				$smownerid = $row['smownerid'];
				$currcrmid = $row['currcrmid'];
				$basicid = $row['id'];
				
				$adb->query("INSERT INTO vtiger_modtracker_detail (id,fieldname,postvalue) 
				VALUES(".$basicid.", 'assigned_user_id', ".$smownerid.")");					
				$adb->query("UPDATE vtiger_crmentity SET label = '".$lead_no."'  where crmid = $currcrmid ");
				$counter++;								
			}
			
		}
		echo $counter;*/
?>