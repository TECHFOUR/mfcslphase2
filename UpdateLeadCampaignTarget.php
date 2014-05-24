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
				 					
    
  //if($currentid != "" && $sourceRecord != "") {

		
		$lead_qry = $adb->query("
SELECT count(leadid), campaignid, leadstatus FROM vtiger_leaddetails 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
WHERE vtiger_crmentity.deleted = 0 GROUP BY campaignid" );		
  		if($adb->num_rows($lead_qry) > 0) {
			while($row = $adb->fetch_array($lead_qry)) {
			
			}			
		}
		
	
?>