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
$startingdate = "12-12-2013";
		if($date_format != "dd-mm-yyyy")
			$startingdate = str_replace("-","/",$startingdate);
			$timestamp = strtotime($startingdate);
			$startdate = date('Y-m-d',$timestamp);
			list($year, $month, $day2) = split('[/.-]', $startdate);
			$start_date1 = $year."-".$month."-"."1";
			$end_date = date('Y-m-d',strtotime($start_date1 . "+1 month -1 second"));
			$start_date = date('Y-m-d',strtotime($end_date . "-1 month +1 second"));
			$this->linkTargetToCampaign($start_date, $end_date);
$sql = "insert into vtiger_crmentityrel values (?,?,?,?)";
				$adb->pquery($sql, array($entityid,'Leads',$crmid,'Faq'));	
function linkTargetToCampaign($start_date, $end_date) {
	
			global $adb, $current_user;
			
			$target_qry = $adb->query("SELECT start_date, end_date, smownerid, targetsid FROM vtiger_targetscf
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_targetscf.targetsid
WHERE vtiger_crmentity.deleted = 0 AND start_date >= '$start_date' AND end_date <= '$end_date'" );		
  		if($adb->num_rows($target_qry) > 0) {
			while($row = $adb->fetch_array($target_qry)) {	
				$start_date = $row['start_date'];
				$end_date = $row['end_date'];
				$smownerid = $row['smownerid'];
				$targetsid = $row['targetsid'];
				
				
					
// Start Campaign 					
						$campaign_qry = $adb->query("SELECT * FROM vtiger_campaign
INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid		
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
WHERE vtiger_crmentity.deleted = 0 AND  closingdate >= '$start_date' AND end_date <= '$end_date' AND smownerid IN($usersid) " );		
  		if($adb->num_rows($campaign_qry) > 0) {
			
			while($row = $adb->fetch_array($campaign_qry)) {
			
			}
		}
			
					
// End Campaign
					
			
		
	
				}}}
			
?>