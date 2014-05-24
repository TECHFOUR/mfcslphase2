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

global $adb;
$msg = '';

$leadmassedit_auth = "leadmasseditbymo_superviser";
$selected_ids = $_REQUEST['selected_ids'];
$activity_start_date = $_REQUEST['activity_start_date'];
$assigned_user_id = $_REQUEST['assigned_user_id'];
$leadmassedit_req = $_REQUEST['leadmassedit'];
$selected_ids = preg_replace('/[^0-9,\']/', '', $selected_ids);
$start_date = date('Y-m-d',strtotime($activity_start_date));
$start_date_day = date('D',strtotime($activity_start_date));

		// Set All current and extended capacity zero
if($leadmassedit_auth == $leadmassedit_req && $selected_ids != "") {
	$adb->query("UPDATE vtiger_callingmastercf
				 INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_callingmastercf.callingmasterid
				 SET current_capacity = '0' , total_capacity = '0', ext_current_capacity = '0' 
				 WHERE vtiger_crmentity.deleted = 0 AND name = $assigned_user_id ");	

	// Again Set All Real current and extended capacity
	$active_activity_qry = $adb->query("SELECT count(*) as active_count, smownerid FROM vtiger_activity 
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
	INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
	INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775
	WHERE vtiger_crmentity.deleted = 0 AND vtiger_users.status = 'Active' AND date_start = '".$start_date."' 
	AND  eventstatus <> 'Held' AND smownerid = $assigned_user_id  GROUP BY smownerid " );		
	if($adb->num_rows($active_activity_qry) > 0) {
		while($row = $adb->fetch_array($active_activity_qry)) {	
			$active_count = $row['active_count'];
			$smownerid = $row['smownerid'];				
			$adb->query("UPDATE vtiger_callingmastercf 
						SET current_capacity = ".$active_count." , total_capacity = ".$active_count.", ext_current_capacity = '0'
						WHERE vtiger_callingmastercf.name = ".$smownerid." AND auto_calling = '1' ");				
		}
	}
	
	$calling_qry = $adb->query("SELECT cf_935, auto_calling  FROM vtiger_callingmastercf 
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_callingmastercf.callingmasterid	
								WHERE vtiger_crmentity.deleted = 0 AND name = $assigned_user_id ");		
	if($adb->num_rows($calling_qry) > 0) {
		$row = $adb->fetch_array($calling_qry);	
		$weeklyoff = $row['cf_935'];
		$auto_calling = $row['auto_calling'];
		
		if($auto_calling == '0')
			$msg = "Auto Calling is not enabled for this Assigned To user.";
		if(substr($weeklyoff, 0, 3) == $start_date_day)
			$msg .= "\n Weekly off for this Assigned To user.";
																	
	}else {
			$msg = "Please define the Calling Capacity for this user.";
		}
	
	if($msg == "") {				
		$lead_qry = $adb->query("SELECT count(*) as lead_count  FROM vtiger_leaddetails 
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid	
								WHERE vtiger_crmentity.deleted = 0 AND smownerid = $assigned_user_id AND leadid IN($selected_ids)");
		if($adb->num_rows($lead_qry) > 1) {
			$msg = "One or more leads selected by you are already assigned to the same agent. please reselect the data";
		}
		
		$total_count = $active_count + count(explode(",",$selected_ids));
		if($total_count >= 400)
			$msg = "Assigned lead limit is exceeded for this user.";
	}
	echo $msg;
		
}
?>