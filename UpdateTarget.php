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

		
		$campaign_qry = $adb->query("SELECT * FROM vtiger_campaign
INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid		
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
WHERE vtiger_crmentity.deleted = 0 AND  closingdate >= '2013-12-01' AND end_date <= '2013-12-31' " );		
  		if($adb->num_rows($campaign_qry) > 0) {
			$total_budget_cost = 0;
			$total_actual_budget_cost = 0;			
			$total_tag = 0;
			$total_actual_tag = 0;			
			$total_pot_no_cars = 0;			
			$total_target_v_send_hub = 0;			
			$total_actual_send_to_hub = 0;			
			$total_target_checkup = 0;			
			$total_actual_checkup = 0;			
			$total_leaflet = 0;			
			$total_actual_leaflet = 0;			
			$total_poster = 0;			
			$total_actual_poster = 0;			
			while($row = $adb->fetch_array($campaign_qry)) {	
				$budget_cost = str_replace(",","",$row['targetsize']);
				$actual_budget_cost = str_replace(",","",$row['actualresponsecount']);
				$tag = str_replace(",","",$row['expectedsalescount']);
				$actual_tag = str_replace(",","",$row['actual_tag']);
				
				$pot_no_cars = str_replace(",","",$row['sponsor']);
				$target_v_send_hub = str_replace(",","",$row['actualsalescount']);
				$actual_send_to_hub = str_replace(",","",$row['actual_send_to_hub']);
				$target_checkup = str_replace(",","",$row['expectedresponsecount']);
				$actual_checkup = str_replace(",","",$row['actual_checkup']);
				
				$leaflet = $row['leaflet'];
				$actual_leaflet = $row['actual_leaflet'];
				$poster = $row['poster'];
				$actual_poster = $row['actual_poster'];
				
				$total_budget_cost = $total_budget_cost + $budget_cost;
				$total_actual_budget_cost = $total_actual_budget_cost + $actual_budget_cost;			
				$total_tag = $total_tag + $tag;
				$total_actual_tag = $total_actual_tag + $actual_tag;		
				$total_pot_no_cars = $total_pot_no_cars + $pot_no_cars;			
				$total_target_v_send_hub = $total_target_v_send_hub + $target_v_send_hub;			
				$total_actual_send_to_hub = $total_actual_send_to_hub + $actual_send_to_hub;			
				$total_target_checkup = $total_target_checkup + $target_checkup;			
				$total_actual_checkup = $total_actual_checkup + $actual_checkup;			
				$total_leaflet = $total_leaflet + $leaflet;			
				$total_actual_leaflet = $total_actual_leaflet + $actual_leaflet;			
				$total_poster = $total_poster + $poster;		
				$total_actual_poster = $total_actual_poster + $actual_poster;
				
														
			}
		}
		
		//echo $total_budget_cost.'___'.$total_actual_budget_cost;die;
		$target_qry = $adb->query("SELECT start_date, end_date, smownerid, targetsid FROM vtiger_targetscf
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_targetscf.targetsid
WHERE vtiger_crmentity.deleted = 0 AND  end_date = '$end_date' " );		
  		if($adb->num_rows($target_qry) > 0) {
			while($row = $adb->fetch_array($target_qry)) {	
				$start_date = $row['start_date'];
				$end_date = $row['end_date'];
				$smownerid = $row['smownerid'];
				$targetsid = $row['targetsid'];
				
				$adb->query("UPDATE vtiger_targetscf SET 
				budget_cost = '".$total_budget_cost."', actual_budget = '".$total_actual_budget_cost."', pot_no_cars = '".$total_pot_no_cars."', target_tag = '".$total_tag."', actual_tag = '".$total_actual_tag."', target_v_sale = '".$total_target_v_send_hub."', actual_v_s_hub = '".$total_actual_send_to_hub."', target_checkup = '".$total_target_checkup."', actual_checkup = '".$total_actual_checkup."'
						WHERE vtiger_targetscf.targetsid = ".$targetsid." ");
				
				break;			
			}
		}
		
	
?>