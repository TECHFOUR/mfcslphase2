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
	  
$dt = new DateTime();
$weeklyoff = $dt->format('l');

$today_date = date("Y-m-d");
	//AND date_start = '".$today_date."'	
		$active_activity_qry = $adb->query("SELECT count(*) as active_count, smownerid FROM vtiger_activity 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775
WHERE vtiger_crmentity.deleted = 0  AND  eventstatus <> 'Held' GROUP BY smownerid " );		
  		if($adb->num_rows($active_activity_qry) > 0) {
			while($row = $adb->fetch_array($active_activity_qry)) {	
				$active_count = $row['active_count'];
				$smownerid = $row['smownerid'];				
				$adb->query("UPDATE vtiger_callingmastercf SET current_capacity = ".$active_count." , total_capacity = ".$active_count.", ext_current_capacity = '0'
							WHERE vtiger_callingmastercf.name = ".$smownerid." AND auto_calling = '1' ");				
			}
		}
		
	//re_alco_status of Campaign Type
	  	$mo_lead_qry = $adb->query("SELECT outletmasterid, smownerid, vtiger_leaddetails.leadid AS lead_id, re_alco_status FROM vtiger_leaddetails 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
INNER JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_leadaddress.campaigntype
INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775
WHERE vtiger_crmentity.deleted = 0 AND import_lead_flag = '2' ORDER BY lead_id DESC " );		
  		if($adb->num_rows($mo_lead_qry) > 0) {
			while($row = $adb->fetch_array($mo_lead_qry)) {
				$outletmasterid = $row['outletmasterid'];
				$lead_id = $row['lead_id'];
				$old_assigned_to = $row['smownerid'];
				$re_alco_status = $row['re_alco_status'];
				
				$calling_master_qry = $adb->query("SELECT outletmasterid,  name, total_capacity, ext_current_capacity FROM vtiger_callingmastercf 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_callingmastercf.callingmasterid
INNER JOIN vtiger_users ON vtiger_users.id = vtiger_callingmastercf.name
INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775 
WHERE vtiger_crmentity.deleted = 0 AND outletmasterid = ".$outletmasterid." AND total_capacity < calling_capacity AND auto_calling = '1' AND cf_935 <> '".$weeklyoff."' " );		
				if($adb->num_rows($calling_master_qry) > 0) {
					while($row = $adb->fetch_array($calling_master_qry)) {	
						$total_capacity = $row['total_capacity'];
						$ext_current_capacity = $row['ext_current_capacity'];
						$ext_current_capacity++;
						$total_capacity++;
						$new_assigned_to = $row['name'];
						
						if($re_alco_status == '1') {//die("111");
							createNewActivity($old_assigned_to, $new_assigned_to, $lead_id);
						//echo $old_assigned_to.'___'.$new_assigned_to.'___'.$lead_id.'___'.$outletmasterid;die;
							$adb->query("UPDATE vtiger_callingmastercf SET ext_current_capacity = ".$ext_current_capacity." , 
							total_capacity = ".$total_capacity." WHERE vtiger_callingmastercf.name = ".$new_assigned_to." ");
							$adb->query("UPDATE vtiger_leaddetails SET import_lead_flag = '1' WHERE vtiger_leaddetails.leadid = ".$lead_id." ");
						}
						break;		
					}
				}												
			}
		}
		
		
		function createNewActivity($old_assigned_to, $new_assigned_to, $lead_id) {			
			global $adb;
			$current_user = 1;
			
		$callerName = "Lead Activity";
		
		$assigned_user_id = $new_assigned_to;		
		$currentdatetime = date("Y-m-d H:i:s");
		list($date,$time) = explode(" ",$currentdatetime);
		/*list($date,$time) = explode(" ",$currentdatetime);			
			list($h,$m,$s) = explode(":",$time);			
			$m1 = $m + 5;
			if($m > 59) {
				$minute = $m1 - 60;
				$h1 = $h + 1;
				$hour = $h1;				
			if($h1 > 23) {
				$hour = 23;		
				}
			}
			else {
				$minute = $m1;
				$hour = $h;
			}
				
			$time_start = $time;
			$time_end = $hour.':'.$minute.':'.$s;*/
			
			$time_start = $time;
			$time_end = $time;
			$date_start = $date;
		
		
		$crmid = $adb->getUniqueID("vtiger_crmentity");	
		$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
					modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $current_user, $assigned_user_id, $current_user, "Calendar", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $callerName));
					
		$query = "INSERT into vtiger_activity (activityid, subject, activitytype, date_start, due_date, time_start, time_end, eventstatus, visibility) VALUES (?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $callerName, 'Call', $date_start, $date_start, $time_start, $time_end, 'Planned', 'all'));
									
		$adb->query("INSERT into vtiger_activitycf (activityid) 
					values(".$crmid.")"); 
		
		$adb->query("INSERT into vtiger_activity_reminder_popup (semodule,recordid,date_start,time_start,status) values('Calendar','".$crmid."','".$startdate."','".$start_time."',0)");
					
		$adb->query("INSERT into vtiger_seactivityrel (crmid,activityid) values(".$lead_id.",".$crmid.")");	
		
		
		// Start update Lead			
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $lead_id, 'Leads',$current_user, date('Y-m-d H:i:s',time()), 0));																																				
			$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
			$adb->pquery($sql,Array($thisid, 'assigned_user_id', $old_assigned_to, $assigned_user_id));
			
			$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
						INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
						SET  smownerid = ".$assigned_user_id.", modifiedby = ".$current_user.", modifiedtime = '".date('Y-m-d H:i:s')."' , latest_activity_date = '".$date_start."'
						WHERE vtiger_leaddetails.leadid = ".$lead_id." ");
				
		
		// Start Save in Modtracker table ******************
					$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
							VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'Events',$current_user, date('Y-m-d H:i:s',time()), 2));
						
					$all_Values = array('subject'=>$callerName,
										'assigned_user_id'=> $assigned_user_id,
										'date_start'=>$date_start, 
										'time_start'=>$time_start, 
										'time_end'=>$time_end, 
										'due_date'=>$date_start, 								 								
										'parent_id'=>$lead_id, 
										'activitytype'=>'Call',
										'eventstatus'=>'Planned'									
									);
																
					foreach($all_Values as $key=>$row) {
						if($row != "")	{
							if($key == "date_start" || $key == "due_date")
								$row = $date_start;
							$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,postvalue) VALUES(?,?,?)',
								Array($thisid, $key, $row));
						}
					}
					
					$thisid_rel = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid_rel, $lead_id, 'Leads',$current_user, date('Y-m-d H:i:s',time()), 4));
					
					$adb->pquery('INSERT INTO vtiger_modtracker_relations(id,targetmodule, targetid, changedon) VALUES(?,?,?,?)',
								Array($thisid_rel, 'Calendar', $crmid, date('Y-m-d H:i:s',time())));					
		// End Save in Modtracker table ******************	
		
	
		}
?>