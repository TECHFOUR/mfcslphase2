<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Activities/Activity.php,v 1.26 2005/03/26 10:42:13 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Calendar/RenderRelatedListUI.php');
require_once('data/CRMEntity.php');
require_once('modules/Calendar/CalendarCommon.php');

// Task is used to store customer information.
class Activity extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "vtiger_activity";
	var $table_index= 'activityid';
	var $reminder_table = 'vtiger_activity_reminder';
	var $tab_name = Array('vtiger_crmentity','vtiger_activity','vtiger_activitycf');

	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_activity'=>'activityid','vtiger_seactivityrel'=>'activityid','vtiger_cntactivityrel'=>'activityid','vtiger_salesmanactivityrel'=>'activityid','vtiger_activity_reminder'=>'activity_id','vtiger_recurringevents'=>'activityid','vtiger_activitycf'=>'activityid');

	var $column_fields = Array();
	var $sortby_fields = Array('subject','due_date','date_start','smownerid','activitytype','lastname');	//Sorting is added for due date and start date	

	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name');
	
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_activitycf', 'activityid');

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
       'Close'=>Array('activity'=>'status'),
       'Type'=>Array('activity'=>'activitytype'),
       'Subject'=>Array('activity'=>'subject'),
       'Related to'=>Array('seactivityrel'=>'parent_id'),
       'Start Date'=>Array('activity'=>'date_start'),
       'Start Time'=>Array('activity','time_start'),
       'End Date'=>Array('activity'=>'due_date'),
       'End Time'=>Array('activity','time_end'),
       //'Recurring Type'=>Array('recurringevents'=>'recurringtype'),
       'Assigned To'=>Array('crmentity'=>'smownerid'),
       'Contact Name'=>Array('contactdetails'=>'lastname')
       );

       var $range_fields = Array(
		'name',
		'date_modified',
		'start_date',
		'id',
		'status',
		'date_due',
		'time_start',
		'description',
		'contact_name',
		'priority',
		'duehours',
		'dueminutes',
		'location'
	   );
       

       var $list_fields_name = Array(
       'Close'=>'status',
       'Type'=>'activitytype',
       'Subject'=>'subject',
       'Contact Name'=>'lastname',
       'Related to'=>'parent_id',
       'Start Date & Time'=>'date_start',
       'End Date & Time'=>'due_date',
	   //'Recurring Type'=>'recurringtype',	
       'Assigned To'=>'assigned_user_id',
       'Start Date'=>'date_start',
       'Start Time'=>'time_start',
       'End Date'=>'due_date',
       'End Time'=>'time_end');

       var $list_link_field= 'subject';
	
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'due_date';
	var $default_sort_order = 'ASC';

	//var $groupTable = Array('vtiger_activitygrouprelation','activityid');

	function Activity() {
		$this->log = LoggerManager::getLogger('Calendar');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Calendar');
	}

	function save_module($module)
	{		
		global $adb;
		//Handling module specific save
		//Insert into seactivity rel			
		if(isset($this->column_fields['parent_id']) && $this->column_fields['parent_id'] != '')
		{
			$this->insertIntoEntityTable("vtiger_seactivityrel", $module);
		}
		elseif($this->column_fields['parent_id']=='' && $insertion_mode=="edit")
		{
			$this->deleteRelation("vtiger_seactivityrel");
		}
		//Insert into cntactivity rel		
		if(isset($this->column_fields['contact_id']) && $this->column_fields['contact_id'] != '')
		{
			$this->insertIntoEntityTable('vtiger_cntactivityrel', $module);
		}
		elseif($this->column_fields['contact_id'] =='' && $insertion_mode=="edit")
		{
			$this->deleteRelation('vtiger_cntactivityrel');
		}
		$recur_type='';	
		if(($recur_type == "--None--" || $recur_type == '') && $this->mode == "edit")
		{
			$sql = 'delete  from vtiger_recurringevents where activityid=?';
			$adb->pquery($sql, array($this->id));		
		}	
		//Handling for recurring type
		//Insert into vtiger_recurring event table
		if(isset($this->column_fields['recurringtype']) && $this->column_fields['recurringtype']!='' && $this->column_fields['recurringtype']!='--None--')
		{
			$recur_type = trim($this->column_fields['recurringtype']);
			$recur_data = getrecurringObjValue();
			if(is_object($recur_data))
	      			$this->insertIntoRecurringTable($recur_data);
		}	
	
		//Insert into vtiger_activity_remainder table

			$this->insertIntoReminderTable('vtiger_activity_reminder',$module,"");

		//Handling for invitees
			$selected_users_string =  $_REQUEST['inviteesid'];
			$invitees_array = explode(';',$selected_users_string);
			$this->insertIntoInviteeTable($module,$invitees_array);

		//Inserting into sales man activity rel
		$this->insertIntoSmActivityRel($module);
		
		$this->insertIntoActivityReminderPopup($module);
	 	
// Start Auto Lead activity [TECHFOUR]  **********************
		$assigned_user_id = $this->column_fields['assigned_user_id'];
		$date_start = $this->column_fields['date_start'];
		$due_date = $this->column_fields['due_date'];
		$app_book_date = $this->column_fields['app_book_date'];
		$activity_status = $this->column_fields['cf_895'];
		$eventstatus = $this->column_fields['eventstatus'];
		$parent_id = $this->column_fields['parent_id'];
		$subject = $this->column_fields['subject'];
		
		$lead_qry = $adb->query("SELECT setype, leadstatus, day_counter_flag, start_date_counter, leadsource  FROM vtiger_crmentity 
								INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
								WHERE vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.leadid = ".$parent_id." ");
		if($adb->num_rows($lead_qry) > 0) {
			$lead_res = $adb->fetch_array($lead_qry);
			$setype = $lead_res['setype'];
			$old_leadstatus = $lead_res['leadstatus'];
			$day_counter_flag = $lead_res['day_counter_flag'];
			$start_date_counter = $lead_res['start_date_counter'];
			$customer_type = $lead_res['leadsource'];			
		}
		
		if($setype == "Leads") { // Update latest Activity date in Lead		
		$latest_activity_date = date('Y-m-d',strtotime($date_start));
			$adb->query("UPDATE vtiger_leadscf SET latest_activity_date = '".$latest_activity_date."' WHERE vtiger_leadscf.leadid = ".$parent_id." ");
		}
		
		if($setype == "Leads" && $customer_type == "Individual") {								
			switch($activity_status) {
				case "Invalid Mobile Number":
					$case = 1;
				break;
				
				case "Wrong Mobile Number":
					$case = 1;
				break;
				
				case "Customer not Intrested":
					$case = 1;
				break;
				
				case "Vehicle Sold":
					$case = 1;
				break;
				
				case "Dissatisfy Customer":
					$case = 1;
				break;
				
				case "Call Back":
					$case = 2;
				break;								
												
				case "Mobile not Reachable OR Switched Off":
					$case = 3;
				break;
				
				case "Appointment Booked":
					$case = 4;
				break;				
			}
			
			if($case == 1) {
				$this->updateLostLeadActivity('Held','Lost',$parent_id, $old_leadstatus,$eventstatus);
			}
			
			if($case == 3) {
				$this->createRescheduledLeadActivity('Open',$old_leadstatus, $day_counter_flag, $start_date_counter);
			}
			
			if($case == 4) {
				$this->createAppointmentLeadActivity('Appointment Booked',$old_leadstatus, $day_counter_flag, $start_date_counter);
			}
			
			if($case != 3)
				$adb->query("UPDATE vtiger_leaddetails SET day_counter_flag = 0 WHERE vtiger_leaddetails.leadid = ".$parent_id." ");
		}

//echo "<pre>"; print_r($this->column_fields);die;
		//$this->createAutoLeadActivity();
// End Auto Lead activity [TECHFOUR]  **********************				
	}	
	
	
	function createAppointmentLeadActivity($status, $old_leadstatus, $day_counter_flag, $start_date_counter) {
		global $adb, $current_user;
		
		$assigned_user_id = $this->column_fields['assigned_user_id'];
		$date_start = $this->column_fields['date_start'];		
		$time_start = $this->column_fields['time_start'];
		$due_date = $this->column_fields['due_date'];
		$time_end = $this->column_fields['time_end'];		
		$eventstatus = $this->column_fields['eventstatus'];
		$parent_id = $this->column_fields['parent_id'];
		$callerName = $this->column_fields['subject'];
		$activity_status = $this->column_fields['cf_895'];
		$app_book_date = $this->column_fields['app_book_date'];
		$app_book_time = $this->column_fields['app_book_time'];
		$driver_pickup = $this->column_fields['driver_pickup'];	
		$sub_disposition = $this->column_fields['cf_903'];		
		$sub_query = " AND vtiger_users.id = ".$assigned_user_id." ";   		
		$userDetails = getUserDetails($sub_query);		
		$depth = $userDetails['depth'];
		$profileid = $userDetails['profileid'];
		$outletmasterid = $userDetails['outletmasterid'];
		
		$app_book_date = date('Y-m-d',strtotime($app_book_date));	
		
		// profileid 5 CRO Profile,  6 Promoter Profile
 		$user_promoter_qry = $adb->query("SELECT vtiger_users.id as userid FROM vtiger_users 
								INNER JOIN vtiger_user2role ON vtiger_user2role.userid = vtiger_users.id 
								INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid 
								INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775
								INNER JOIN vtiger_role2profile ON vtiger_role2profile.roleid = vtiger_user2role.roleid			
								WHERE STATUS = 'Active' AND depth = ".$depth." AND vtiger_outletmastercf.outletmasterid = ".$outletmasterid."  
								AND vtiger_role2profile.profileid = 5 ");	
		if($adb->num_rows($user_promoter_qry) > 0) {
			$user_promoter_res = $adb->fetch_array($user_promoter_qry);
			$cro_id = $user_promoter_res['userid'];						
		}
		
		$driver_pickup = 0;
		if($driver_pickup == "on")
			$driver_pickup = 1;
			
		switch($sub_disposition) {
			case "Appointment Cancelled":
				$disposition = 1;
			break;
			
			case "Confirm for Today":
				$disposition = 2;
			break;
			
			case "Confirm for Tomorrow":
				$disposition = 3;
			break;
			
			case "Rescheduled":
				$disposition = 4;
			break;
			
			default:
			$disposition = 0;
			$new_app_book_date = date('Y-m-d', strtotime('-1 days', strtotime($app_book_date)));
			$date_start = $new_app_book_date;
			$time_start = "17:00:00";	
			$time_end = "17:05:00";			
			$adb->query("UPDATE vtiger_leadscf SET app_book_date = '".$app_book_date."', app_book_time = '".$app_book_time."'
						WHERE vtiger_leadscf.leadid = ".$parent_id." ");		
			break;
		}
		
		if($driver_pickup == 1 && $disposition == 0 && $profileid == 6) { // if Driver						
			 // Re-Assignment
			 $new_assigned_to = $cro_id;
			 $this->createNewActivity($callerName, $date_start, $time_start, $time_end, $app_book_date, $app_book_time, $driver_pickup, $sub_disposition, $old_leadstatus, $status, $new_assigned_to);
			 if($assigned_user_id != $cro_id) {
				$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
				$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $parent_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																															
				
				$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';													
				$adb->pquery($sql,Array($thisid, 'assigned_user_id', $assigned_user_id, $cro_id));
																					
				$adb->query("UPDATE vtiger_leaddetails 
					INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
					INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
					SET  smownerid = ".$cro_id.", modifiedby = ".$current_user->id.", modifiedtime = 
					'".date("Y-m-d H:i:s",time())."' where vtiger_leaddetails.leadid = ".$parent_id." ");
			 }
		}
						
		if($disposition == 1 ) { // Appointment Cancelled
			$status = "Lost";
			if($old_leadstatus != $status) {
				$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
				$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
						VALUES(?,?,?,?,?,?)', Array($thisid, $parent_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																																				
				$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
				$adb->pquery($sql,Array($thisid, 'leadstatus', $old_leadstatus, $status));
				
				$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
							SET leadstatus = '".$status."', modifiedby = ".$current_user->id.", modifiedtime = '".date('Y-m-d H:i:s')."' 
							WHERE vtiger_leaddetails.leadid = ".$parent_id." ");
			}
			// Held old activity
			$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = ".$this->id." ");
		}
		
		if($disposition == 2 ) { // Confirm for Today			
			// Held old activity			
			$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = ".$this->id." ");
		}
		
		if($disposition == 3 ) { // Confirm for Tomorrow			
			// Held old activity
			$new_custom_app_book_date = date('Y-m-d');
			$date_start = date('Y-m-d', strtotime('+1 days', strtotime($new_custom_app_book_date)));			
			$time_start = "9:00:00";	
			$time_end = "9:05:00";
			$new_assigned_to = $assigned_user_id;
			$this->createNewActivity($callerName, $date_start, $time_start, $time_end, $app_book_date, $app_book_time, $driver_pickup, $sub_disposition, $old_leadstatus, $status, $new_assigned_to);
			$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = ".$this->id." ");
		}
				
	}
	
	function createRescheduledLeadActivity($status, $old_leadstatus, $day_counter_flag, $start_date_counter) {
		global $adb, $current_user;
		
		$assigned_user_id = $this->column_fields['assigned_user_id'];
		$date_start = $this->column_fields['date_start'];		
		$time_start = $this->column_fields['time_start'];
		$due_date = $this->column_fields['due_date'];
		$time_end = $this->column_fields['time_end'];		
		$activity_status = $this->column_fields['cf_895'];
		$eventstatus = $this->column_fields['eventstatus'];
		$parent_id = $this->column_fields['parent_id'];
		$callerName = $this->column_fields['subject'];
			
		switch($day_counter_flag) {
			case 0:
				$day_counter_flag = 1;
			break;
			
			case 1:
				$day_counter_flag = 3;
			break;
			
			case 3:
				$day_counter_flag = 7;
			break;
			
			case 7:
				$day_counter_flag = 15;

			break;
			
			case 15:
				$day_counter_flag = 60;
			break;
				
			case 60:
				$day_counter_flag = 1;
			break;
		}
		if($start_date_counter != "")
			$date_start = $start_date_counter;
		
		$new_date_start = date('Y-m-d', strtotime('+'.$day_counter_flag.' days', strtotime($date_start)));
		
		//$due_date = date('Y-m-d', strtotime('+'.$day_counter_flag.' days', strtotime($due_date)));
		$new_assigned_to = $assigned_user_id;
		$this->createNewActivity($callerName, $new_date_start, $time_start, $time_end, $app_book_date, $app_book_time, $driver_pickup, $sub_disposition, $old_leadstatus, $status, $new_assigned_to);
		
		if($day_counter_flag == 60)
			$date_start = $new_date_start;	
		if($day_counter_flag == 1 || $day_counter_flag == 60)
			$adb->query("UPDATE vtiger_leaddetails SET start_date_counter='".$date_start."' WHERE vtiger_leaddetails.leadid = ".$parent_id." ");
		$adb->query("UPDATE vtiger_leaddetails SET day_counter_flag = ".$day_counter_flag." WHERE vtiger_leaddetails.leadid = ".$parent_id." ");
				
				
	}
	
	
	function createNewActivity($callerName, $date_start, $time_start, $time_end, $app_book_date, $app_book_time, $driver_pickup, $sub_disposition, $old_leadstatus, $status, $new_assigned_to) {
		global $adb, $current_user;
		$assigned_user_id = $new_assigned_to;
		$parent_id = $this->column_fields['parent_id'];
		$currentdatetime = date("Y-m-d H:i:s");
		$crmid = $adb->getUniqueID("vtiger_crmentity");	
		$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
					modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $current_user->id, $assigned_user_id, $current_user->id, "Calendar", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $callerName));
					
		$query = "INSERT into vtiger_activity (activityid, subject, activitytype, date_start, due_date, time_start, time_end, eventstatus, visibility) VALUES (?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $callerName, 'Call', $date_start, $date_start, $time_start, $time_end, 'Planned', 'all'));
									
		$adb->query("INSERT into vtiger_activitycf (activityid, app_book_date, app_book_time, driver_pickup, cf_903, cf_895) 
					values(".$crmid.",'".$app_book_date."','".$app_book_time."','".$driver_pickup."','".$sub_disposition."','".$status."')"); 
		
		$adb->query("INSERT into vtiger_activity_reminder_popup (semodule,recordid,date_start,time_start,status) values('Calendar','".$crmid."','".$startdate."','".$start_time."',0)");
					
		$adb->query("INSERT into vtiger_seactivityrel (crmid,activityid) values(".$parent_id.",".$crmid.")");	
		
		// Held old activity
		$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = ".$this->id." ");
		
		
		// Start update Lead	
		if($old_leadstatus != $status) {
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $parent_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																																				
			$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
			$adb->pquery($sql,Array($thisid, 'leadstatus', $old_leadstatus, $status));
			
			$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
						INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
						SET leadstatus = '".$status."', modifiedby = ".$current_user->id.", modifiedtime = '".date('Y-m-d H:i:s')."' , latest_activity_date = '".$date_start."' 
						WHERE vtiger_leaddetails.leadid = ".$parent_id." ");
		}
		
		// Start Save in Modtracker table ******************
					$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
							VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'Events',$current_user->id, date('Y-m-d H:i:s',time()), 2));
																	
					foreach($this->column_fields as $key=>$row) {
						if($row != "")	{
							if($key == "date_start" || $key == "due_date")
								$row = $date_start;
							$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,postvalue) VALUES(?,?,?)',
								Array($thisid, $key, $row));
						}
					}
					
					$thisid_rel = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid_rel, $parent_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 4));
					
					$adb->pquery('INSERT INTO vtiger_modtracker_relations(id,targetmodule, targetid, changedon) VALUES(?,?,?,?)',
								Array($thisid_rel, 'Calendar', $crmid, date('Y-m-d H:i:s',time())));					
		// End Save in Modtracker table ******************	
		
	}
	
	function updateLostLeadActivity($status,$leadstatus,$parent_id, $old_leadstatus,$eventstatus) {
		global $adb, $current_user;
		// Start update Lead
		if($old_leadstatus != $leadstatus) {
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $parent_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																																				
			$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
			$adb->pquery($sql,Array($thisid, 'leadstatus', $old_leadstatus, $leadstatus));
			
			$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
						SET leadstatus = '".$leadstatus."', modifiedby = ".$current_user->id.", modifiedtime = '".date('Y-m-d H:i:s')."' 
						WHERE vtiger_leaddetails.leadid = ".$parent_id." ");
		}
		// Start update Activity
		
		if($eventstatus != "Held" ) {
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $this->id, 'Events',$current_user->id, date('Y-m-d H:i:s',time()), 0));
			$adb->pquery($sql,Array($thisid, 'eventstatus', $eventstatus, $status));																		
			$adb->query("UPDATE vtiger_activity SET eventstatus = '".$status."' WHERE vtiger_activity.activityid = ".$this->id." ");
		}
	}
	
	/** Function to insert values in vtiger_activity_reminder_popup table for the specified module
  	  * @param $cbmodule -- module:: Type varchar
 	 */
	function insertIntoActivityReminderPopup($cbmodule) {
		
		global $adb;
		
		$cbrecord = $this->id;
		unset($_SESSION['next_reminder_time']);
		if(isset($cbmodule) && isset($cbrecord)) {
			$cbdate = getValidDBInsertDateValue($this->column_fields['date_start']);
			$cbtime = $this->column_fields['time_start'];
			
			$reminder_query = "SELECT reminderid FROM vtiger_activity_reminder_popup WHERE semodule = ? and recordid = ?";
			$reminder_params = array($cbmodule, $cbrecord);		
			$reminderidres = $adb->pquery($reminder_query, $reminder_params);
		
			$reminderid = null;
			if($adb->num_rows($reminderidres) > 0) {
				$reminderid = $adb->query_result($reminderidres, 0, "reminderid");
			}
	
			if(isset($reminderid)) {
				$callback_query = "UPDATE vtiger_activity_reminder_popup set status = 0, date_start = ?, time_start = ? WHERE reminderid = ?"; 
				$callback_params = array($cbdate, $cbtime, $reminderid);
			} else {
				$callback_query = "INSERT INTO vtiger_activity_reminder_popup (recordid, semodule, date_start, time_start) VALUES (?,?,?,?)";
				$callback_params = array($cbrecord, $cbmodule, $cbdate, $cbtime);
			}
		
			$adb->pquery($callback_query, $callback_params);
		}		
	}


	/** Function to insert values in vtiger_activity_remainder table for the specified module,
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */
	function insertIntoReminderTable($table_name,$module,$recurid)
	{
	 	global $log;
		$log->info("in insertIntoReminderTable  ".$table_name."    module is  ".$module);
		if($_REQUEST['set_reminder'] == 'Yes')
		{
			unset($_SESSION['next_reminder_time']);
			$log->debug("set reminder is set");
			$rem_days = $_REQUEST['remdays'];
			$log->debug("rem_days is ".$rem_days);
			$rem_hrs = $_REQUEST['remhrs'];
			$log->debug("rem_hrs is ".$rem_hrs);
			$rem_min = $_REQUEST['remmin'];
			$log->debug("rem_minutes is ".$rem_min);
			$reminder_time = $rem_days * 24 * 60 + $rem_hrs * 60 + $rem_min;
			$log->debug("reminder_time is ".$reminder_time);
			if ($recurid == "")
			{
				if($_REQUEST['mode'] == 'edit')
				{
					$this->activity_reminder($this->id,$reminder_time,0,$recurid,'edit');
				}
				else
				{
					$this->activity_reminder($this->id,$reminder_time,0,$recurid,'');
				}
			}
			else
			{
				$this->activity_reminder($this->id,$reminder_time,0,$recurid,'');
			}
		}
		elseif($_REQUEST['set_reminder'] == 'No')
		{
			$this->activity_reminder($this->id,'0',0,$recurid,'delete');
		}
	}
	

	// Code included by Jaguar - starts
	/** Function to insert values in vtiger_recurringevents table for the specified tablename,module
  	  * @param $recurObj -- Recurring Object:: Type varchar
 	 */	
function insertIntoRecurringTable(& $recurObj)
{
	global $log,$adb;
	$log->info("in insertIntoRecurringTable  ");
	$st_date = $recurObj->startdate->get_DB_formatted_date();
	$log->debug("st_date ".$st_date);
	$end_date = $recurObj->enddate->get_DB_formatted_date();
	$log->debug("end_date is set ".$end_date);
	$type = $recurObj->getRecurringType();
	$log->debug("type is ".$type);
    $flag="true";

	if($_REQUEST['mode'] == 'edit')
	{
		$activity_id=$this->id;

		$sql='select min(recurringdate) AS min_date,max(recurringdate) AS max_date, recurringtype, activityid from vtiger_recurringevents where activityid=? group by activityid, recurringtype';
		$result = $adb->pquery($sql, array($activity_id));
		$noofrows = $adb->num_rows($result);
		for($i=0; $i<$noofrows; $i++)
		{
			$recur_type_b4_edit = $adb->query_result($result,$i,"recurringtype");
			$date_start_b4edit = $adb->query_result($result,$i,"min_date");
			$end_date_b4edit = $adb->query_result($result,$i,"max_date");
		}
		if(($st_date == $date_start_b4edit) && ($end_date==$end_date_b4edit) && ($type == $recur_type_b4_edit))
		{
			if($_REQUEST['set_reminder'] == 'Yes')
			{
				$sql = 'delete from vtiger_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$sql = 'delete  from vtiger_recurringevents where activityid=?';
				$adb->pquery($sql, array($activity_id));
				$flag="true";
			}
			elseif($_REQUEST['set_reminder'] == 'No')
			{
				$sql = 'delete  from vtiger_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$flag="false";
			}
			else
				$flag="false";
		}
		else
		{
			$sql = 'delete from vtiger_activity_reminder where activity_id=?';
			$adb->pquery($sql, array($activity_id));
			$sql = 'delete  from vtiger_recurringevents where activityid=?';
			$adb->pquery($sql, array($activity_id));
		}
	}

	$recur_freq = $recurObj->getRecurringFrequency();
	$recurringinfo = $recurObj->getDBRecurringInfoString();

	if($flag=="true") {
		$max_recurid_qry = 'select max(recurringid) AS recurid from vtiger_recurringevents;';
		$result = $adb->pquery($max_recurid_qry, array());
		$noofrows = $adb->num_rows($result);
		$recur_id = 0;
		if($noofrows > 0) {
			$recur_id = $adb->query_result($result,0,"recurid");
		}
		$current_id =$recur_id+1;
		$recurring_insert = "insert into vtiger_recurringevents values (?,?,?,?,?,?)";
		$rec_params = array($current_id, $this->id, $st_date, $type, $recur_freq, $recurringinfo);
		$adb->pquery($recurring_insert, $rec_params);
		unset($_SESSION['next_reminder_time']);
		if($_REQUEST['set_reminder'] == 'Yes') {
			$this->insertIntoReminderTable("vtiger_activity_reminder",$module,$current_id,'');
		}
	}
}


	/** Function to insert values in vtiger_invitees table for the specified module,tablename ,invitees_array
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
	  * @param $invitees_array Array
 	 */
	function insertIntoInviteeTable($module,$invitees_array)
	{
		global $log,$adb;
		$log->debug("Entering insertIntoInviteeTable(".$module.",".$invitees_array.") method ...");
		if($this->mode == 'edit'){
			$sql = "delete from vtiger_invitees where activityid=?";
			$adb->pquery($sql, array($this->id));
		}	
		foreach($invitees_array as $inviteeid)
		{
			if($inviteeid != '')
			{
				$query="insert into vtiger_invitees values(?,?)";
				$adb->pquery($query, array($this->id, $inviteeid));
			}
		}
		$log->debug("Exiting insertIntoInviteeTable method ...");

	}


	/** Function to insert values in vtiger_salesmanactivityrel table for the specified module
  	  * @param $module -- module:: Type varchar
 	 */

  	function insertIntoSmActivityRel($module)
  	{
    		global $adb;
    		global $current_user;
    		if($this->mode == 'edit'){
      			$sql = "delete from vtiger_salesmanactivityrel where activityid=?";
      			$adb->pquery($sql, array($this->id));
    		}

		$user_sql = $adb->pquery("select count(*) as count from vtiger_users where id=?", array($this->column_fields['assigned_user_id']));
    	if($adb->query_result($user_sql, 0, 'count') != 0) {
		$sql_qry = "insert into vtiger_salesmanactivityrel (smid,activityid) values(?,?)";
    		$adb->pquery($sql_qry, array($this->column_fields['assigned_user_id'], $this->id));
		
		if(isset($_REQUEST['inviteesid']) && $_REQUEST['inviteesid']!='')
		{
			$selected_users_string =  $_REQUEST['inviteesid'];
			$invitees_array = explode(';',$selected_users_string);
			foreach($invitees_array as $inviteeid)
			{
				if($inviteeid != '')
				{
					$resultcheck = $adb->pquery("select * from vtiger_salesmanactivityrel where activityid=? and smid=?",array($this->id,$inviteeid));
					if($adb->num_rows($resultcheck) != 1){
						$query="insert into vtiger_salesmanactivityrel values(?,?)";
						$adb->pquery($query, array($inviteeid, $this->id));
					}	
				}	
			}
		}
	}	
}

	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName) {
        if($tableName == "vtiger_activity_reminder")
            return 'LEFT JOIN';
		return parent::getJoinClause($tableName);
	}
	
	
	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	function getSortOrder()
	{	
		global $log;                                                                                                  
		$log->debug("Entering getSortOrder() method ...");
		if(isset($_REQUEST['sorder'])) 
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else
			$sorder = (($_SESSION['ACTIVITIES_SORT_ORDER'] != '')?($_SESSION['ACTIVITIES_SORT_ORDER']):($this->default_sort_order));
		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}
	
	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'subject')
	 */
	function getOrderBy()
	{
		global $log;
		$log->debug("Entering getOrderBy() method ...");
	
		$use_default_order_by = '';		
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (isset($_REQUEST['order_by'])) 
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		else
			$order_by = (($_SESSION['ACTIVITIES_ORDER_BY'] != '')?($_SESSION['ACTIVITIES_ORDER_BY']):($use_default_order_by));
		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}	
	// Mike Crowe Mod --------------------------------------------------------



//Function Call for Related List -- Start
	/**
	 * Function to get Activity related Contacts
	 * @param  integer   $id      - activityid
	 * returns related Contacts record in array format
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		$returnset = '&return_module='.$this_module.'&return_action=DetailView&activity_mode=Events&return_id='.$id;
		
		$search_string = '';
		$button = '';
				
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab$search_string','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}
		
		$query = 'select vtiger_users.user_name,vtiger_contactdetails.accountid,vtiger_contactdetails.contactid, vtiger_contactdetails.firstname,vtiger_contactdetails.lastname, vtiger_contactdetails.department, vtiger_contactdetails.title, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime from vtiger_contactdetails inner join vtiger_cntactivityrel on vtiger_cntactivityrel.contactid=vtiger_contactdetails.contactid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid where vtiger_cntactivityrel.activityid='.$id.' and vtiger_crmentity.deleted=0';
				
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_contacts method ...");		
		return $return_value;
	}
	
	/**
	 * Function to get Activity related Users
	 * @param  integer   $id      - activityid
	 * returns related Users record in array format
	 */

	function get_users($id) {	
		global $log;
                $log->debug("Entering get_contacts(".$id.") method ...");
		global $app_strings;

		$focus = new Users();

		$button = '<input title="Change" accessKey="" tabindex="2" type="button" class="crmbutton small edit" 
					value="'.getTranslatedString('LBL_SELECT_USER_BUTTON_LABEL').'" name="button" LANGUAGE=javascript 
					onclick=\'return window.open("index.php?module=Users&return_module=Calendar&return_action={$return_modname}&activity_mode=Events&action=Popup&popuptype=detailview&form=EditView&form_submit=true&select=enable&return_id='.$id.'&recordid='.$id.'","test","width=640,height=525,resizable=0,scrollbars=0")\';>';                  

		$returnset = '&return_module=Calendar&return_action=CallRelatedList&return_id='.$id;

		$query = 'SELECT vtiger_users.id, vtiger_users.first_name,vtiger_users.last_name, vtiger_users.user_name, vtiger_users.email1, vtiger_users.email2, vtiger_users.status, vtiger_users.is_admin, vtiger_user2role.roleid, vtiger_users.secondaryemail, vtiger_users.phone_home, vtiger_users.phone_work, vtiger_users.phone_mobile, vtiger_users.phone_other, vtiger_users.phone_fax,vtiger_activity.date_start,vtiger_activity.due_date,vtiger_activity.time_start,vtiger_activity.duration_hours,vtiger_activity.duration_minutes from vtiger_users inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.smid=vtiger_users.id  inner join vtiger_activity on vtiger_activity.activityid=vtiger_salesmanactivityrel.activityid inner join vtiger_user2role on vtiger_user2role.userid=vtiger_users.id where vtiger_activity.activityid='.$id;
		
		$return_data = GetRelatedList('Calendar','Users',$focus,$query,$button,$returnset);
		
		if($return_data == null) $return_data = Array();
		$return_data['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_users method ..."); 
		return $return_data;
	}

	/**
         * Function to get activities for given criteria
	 * @param   string   $criteria     - query string
	 * returns  activity records in array format($list) or null value
         */	 
  	function get_full_list($criteria) {
	 	global $log;
		$log->debug("Entering get_full_list(".$criteria.") method ...");
	    $query = "select vtiger_crmentity.crmid,vtiger_crmentity.smownerid,vtiger_crmentity.setype, vtiger_activity.*, 
	    		vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.contactid 
	    		from vtiger_activity 
	    		inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid 
	    		left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid 
	    		left join vtiger_contactdetails on vtiger_contactdetails.contactid= vtiger_cntactivityrel.contactid 
	    		left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid 
	    		WHERE vtiger_crmentity.deleted=0 ".$criteria;
    	$result =& $this->db->query($query);
        
    if($this->db->getRowCount($result) > 0){
		
      // We have some data.
      while ($row = $this->db->fetchByAssoc($result)) {
        foreach($this->list_fields_name as $field)
        {
          if (isset($row[$field])) {
            $this->$field = $row[$field];
          }
          else {
            $this->$field = '';   
          }
        }
        $list[] = $this;
      }
    }
    if (isset($list))
    	{
		$log->debug("Exiting get_full_list method ...");
	    return $list;
	}
	else
	{
		$log->debug("Exiting get_full_list method ...");
	    return null;
	}

  }

	
//calendarsync
    /**
     * Function to get meeting count
     * @param  string   $user_name        - User Name
     * return  integer  $row["count(*)"]  - count
     */
    function getCount_Meeting($user_name) 
	{
		global $log;
	        $log->debug("Entering getCount_Meeting(".$user_name.") method ...");
      $query = "select count(*) from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid where user_name=? and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Meeting'";
      $result = $this->db->pquery($query, array($user_name),true,"Error retrieving contacts count");
      $rows_found =  $this->db->getRowCount($result);
      $row = $this->db->fetchByAssoc($result, 0);
	$log->debug("Exiting getCount_Meeting method ...");
      return $row["count(*)"];
    }
   
    function get_calendars($user_name,$from_index,$offset)
    {   
	    global $log;
            $log->debug("Entering get_calendars(".$user_name.",".$from_index.",".$offset.") method ...");
		$query = "select vtiger_activity.location as location,vtiger_activity.duration_hours as duehours, vtiger_activity.duration_minutes as dueminutes,vtiger_activity.time_start as time_start, vtiger_activity.subject as name,vtiger_crmentity.modifiedtime as date_modified, vtiger_activity.date_start start_date,vtiger_activity.activityid as id,vtiger_activity.status as status, vtiger_crmentity.description as description, vtiger_activity.priority as vtiger_priority, vtiger_activity.due_date as date_due ,vtiger_contactdetails.firstname cfn, vtiger_contactdetails.lastname cln from vtiger_activity inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid=vtiger_activity.activityid left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_cntactivityrel.contactid inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid where user_name='" .$user_name ."' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Meeting' limit " .$from_index ."," .$offset;
	$log->debug("Exiting get_calendars method ...");
	    return $this->process_list_query1($query);   
    }       
//calendarsync
	/**
	 * Function to get task count
	 * @param  string   $user_name        - User Name
	 * return  integer  $row["count(*)"]  - count
	 */
    function getCount($user_name) 
    {
	    global $log;
            $log->debug("Entering getCount(".$user_name.") method ...");
        $query = "select count(*) from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid where user_name=? and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task'";
        $result = $this->db->pquery($query,array($user_name), true,"Error retrieving contacts count");
        $rows_found =  $this->db->getRowCount($result);
        $row = $this->db->fetchByAssoc($result, 0);

	$log->debug("Exiting getCount method ...");    
        return $row["count(*)"];
    }       

    /**
     * Function to get list of task for user with given limit
     * @param  string   $user_name        - User Name
     * @param  string   $from_index       - query string
     * @param  string   $offset           - query string 
     * returns tasks in array format
     */
    function get_tasks($user_name,$from_index,$offset)
    {   
	global $log;
        $log->debug("Entering get_tasks(".$user_name.",".$from_index.",".$offset.") method ...");
	 $query = "select vtiger_activity.subject as name,vtiger_crmentity.modifiedtime as date_modified, vtiger_activity.date_start start_date,vtiger_activity.activityid as id,vtiger_activity.status as status, vtiger_crmentity.description as description, vtiger_activity.priority as priority, vtiger_activity.due_date as date_due ,vtiger_contactdetails.firstname cfn, vtiger_contactdetails.lastname cln from vtiger_activity inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid=vtiger_activity.activityid left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_cntactivityrel.contactid inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid where user_name='" .$user_name ."' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task' limit " .$from_index ."," .$offset;
	 $log->debug("Exiting get_tasks method ...");
    return $this->process_list_query1($query);
    
    }
	
    /**
     * Function to process the activity list query
     * @param  string   $query     - query string
     * return  array    $response  - activity lists
     */
    function process_list_query1($query)
    {
	    global $log;
            $log->debug("Entering process_list_query1(".$query.") method ...");
        $result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
        $list = Array();
        $rows_found =  $this->db->getRowCount($result);
        if($rows_found != 0)
        {
            $task = Array();
              for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))
            
             {
                foreach($this->range_fields as $columnName)
                {
					if (isset($row[$columnName])) {
						if($columnName == 'time_start'){
							$startDate = new DateTimeField($row['date_start'].' '.
									$row[$columnName]);
							$task[$columnName] = $startDate->getDBInsertTimeValue();
						}else{
							$task[$columnName] = $row[$columnName];
						}
                    }   
                    else     
                    {   
                            $task[$columnName] = "";
                    }   
	            }	
    
                $task[contact_name] = return_name($row, 'cfn', 'cln');    

                    $list[] = $task;
                }
         }

        $response = Array();
        $response['list'] = $list;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;


	$log->debug("Exiting process_list_query1 method ...");
        return $response;
    }

    	/**
	 * Function to get reminder for activity
	 * @param  integer   $activity_id     - activity id
	 * @param  string    $reminder_time   - reminder time
	 * @param  integer   $reminder_sent   - 0 or 1
	 * @param  integer   $recurid         - recuring eventid
	 * @param  string    $remindermode    - string like 'edit'	 
	 */	
	function activity_reminder($activity_id,$reminder_time,$reminder_sent=0,$recurid,$remindermode='')
	{
		global $log;
		$log->debug("Entering vtiger_activity_reminder(".$activity_id.",".$reminder_time.",".$reminder_sent.",".$recurid.",".$remindermode.") method ...");
		//Check for vtiger_activityid already present in the reminder_table
		$query_exist = "SELECT activity_id FROM ".$this->reminder_table." WHERE activity_id = ?";
		$result_exist = $this->db->pquery($query_exist, array($activity_id));

		if($remindermode == 'edit')
		{
			if($this->db->num_rows($result_exist) > 0)
			{
				$query = "UPDATE ".$this->reminder_table." SET";
				$query .=" reminder_sent = ?, reminder_time = ? WHERE activity_id =?"; 
				$params = array($reminder_sent, $reminder_time, $activity_id);
			}
			else
			{
				$query = "INSERT INTO ".$this->reminder_table." VALUES (?,?,?,?)";
				$params = array($activity_id, $reminder_time, 0, $recurid);
			}
		}
		elseif(($remindermode == 'delete') && ($this->db->num_rows($result_exist) > 0))
		{
			$query = "DELETE FROM ".$this->reminder_table." WHERE activity_id = ?";
			$params = array($activity_id);
		}
		else
		{
			$query = "INSERT INTO ".$this->reminder_table." VALUES (?,?,?,?)";
			$params = array($activity_id, $reminder_time, 0, $recurid);
		}
      	$this->db->pquery($query,$params,true,"Error in processing vtiger_table $this->reminder_table");
		$log->debug("Exiting vtiger_activity_reminder method ...");
	}

	//Used for vtigerCRM Outlook Add-In
	/**
 	* Function to get tasks to display in outlookplugin
 	* @param   string    $username     -  User name
 	* return   string    $query        -  sql query 
 	*/
	function get_tasksforol($username)
	{
		global $log,$adb;
		$log->debug("Entering get_tasksforol(".$username.") method ...");
		global $current_user;
		require_once("modules/Users/Users.php");
		$seed_user=new Users();
		$user_id=$seed_user->retrieve_user_id($username);
		$current_user=$seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
  		{
    		$sql1 = "select tablename,columnname from vtiger_field where tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.presence in (0,2)";
			$params1 = array();
  		}else
  	{
    	$profileList = getCurrentUserProfileList();
    	$sql1 = "select tablename,columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
		$params1 = array();
		if (count($profileList) > 0) {
  			$sql1 .= " and vtiger_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
			array_push($params1, $profileList);
		} 
  	}
  	$result1 = $adb->pquery($sql1,$params1);
  	for($i=0;$i < $adb->num_rows($result1);$i++)
  	{
		$permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      	$permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      	/*if($adb->query_result($result1,$i,'columnname') == "parentid")
      	{
        	$permitted_lists[] = 'vtiger_account';
        	$permitted_lists[] = 'accountname';
      	}*/
  		}
		$permitted_lists = array_chunk($permitted_lists,2);
		$column_table_lists = array();
		for($i=0;$i < count($permitted_lists);$i++)
		{
	   		$column_table_lists[] = implode(".",$permitted_lists[$i]);
  		}
   
		$query = "select vtiger_activity.activityid as taskid, ".implode(',',$column_table_lists)." from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid 
			 inner join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid 
			 left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid=vtiger_activity.activityid 
			 left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_cntactivityrel.contactid 
			 left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid 
			 where vtiger_users.user_name='".$username."' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task'";
		$log->debug("Exiting get_tasksforol method ...");		 
		return $query;
	}

	/**
 	* Function to get calendar query for outlookplugin
 	* @param   string    $username     -  User name                                                                            * return   string    $query        -  sql query                                                                            */ 
	function get_calendarsforol($user_name)
	{
		global $log,$adb;
		$log->debug("Entering get_calendarsforol(".$user_name.") method ...");
		global $current_user;
		require_once("modules/Users/Users.php");
		$seed_user=new Users();
		$user_id=$seed_user->retrieve_user_id($user_name);
		$current_user=$seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
  		{
    		$sql1 = "select tablename,columnname from vtiger_field where tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.presence in (0,2)";
  			$params1 = array();
  		}else
  		{
    		$profileList = getCurrentUserProfileList();
    		$sql1 = "select tablename,columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
				array_push($params1,$profileList);		
			}
  		}
  		$result1 = $adb->pquery($sql1, $params1);
  		for($i=0;$i < $adb->num_rows($result1);$i++)
  		{
			$permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      		$permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      		if($adb->query_result($result1,$i,'columnname') == "date_start")
      		{
        		$permitted_lists[] = 'vtiger_activity';
        		$permitted_lists[] = 'time_start';
      		}
      		if($adb->query_result($result1,$i,'columnname') == "due_date")
      		{
				$permitted_lists[] = 'vtiger_activity';
        		$permitted_lists[] = 'time_end';
      		}
  		}
		$permitted_lists = array_chunk($permitted_lists,2);
		$column_table_lists = array();
		for($i=0;$i < count($permitted_lists);$i++)
		{
	   		$column_table_lists[] = implode(".",$permitted_lists[$i]);
  		}
   
	  	$query = "select vtiger_activity.activityid as clndrid, ".implode(',',$column_table_lists)." from vtiger_activity 
				inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid 
				inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid 
				left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid=vtiger_activity.activityid 
				left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_cntactivityrel.contactid 
				left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid 
				where vtiger_users.user_name='".$user_name."' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Meeting'";
		$log->debug("Exiting get_calendarsforol method ...");
		return $query;
	}
	
	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;

		$sql = 'DELETE FROM vtiger_activity_reminder WHERE activity_id=?';
		$this->db->pquery($sql, array($id));
		
		$sql = 'DELETE FROM vtiger_recurringevents WHERE activityid=?';
		$this->db->pquery($sql, array($id));

		$sql = 'DELETE FROM vtiger_cntactivityrel WHERE activityid = ?';
		$this->db->pquery($sql, array($id));
		
		parent::unlinkDependencies($module, $id);
	}
	
	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Contacts') {
			$sql = 'DELETE FROM vtiger_cntactivityrel WHERE contactid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
		} elseif($return_module == 'HelpDesk') {
			$sql = 'DELETE FROM vtiger_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM vtiger_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
			$sql = 'DELETE FROM vtiger_cntactivityrel WHERE activityid = ? AND contactid IN	(SELECT contactid from vtiger_contactdetails where accountid=?)';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql='DELETE FROM vtiger_seactivityrel WHERE activityid=?';
			$this->db->pquery($sql, array($id));
		
			$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}
	
	/**
	 * this function sets the status flag of activity to true or false depending on the status passed to it
	 * @param string $status - the status of the activity flag to set
	 * @return:: true if successful; false otherwise
	 */
	function setActivityReminder($status){
		global $adb;
		if($status == "on"){
			$flag = 0;
		}elseif($status == "off"){
			$flag = 1;
		}else{
			return false;
		}
		$sql = "update vtiger_activity_reminder_popup set status=1 where recordid=?";
		$adb->pquery($sql, array($this->id));
		return true;
	}

	/*
	 * Function to get the relation tables for related modules 
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Contacts" => array("vtiger_cntactivityrel"=>array("activityid","contactid"),"vtiger_activity"=>"activityid"),
			"Leads" => array("vtiger_seactivityrel"=>array("activityid","crmid"),"vtiger_activity"=>"activityid"),
			"Accounts" => array("vtiger_seactivityrel"=>array("activityid","crmid"),"vtiger_activity"=>"activityid"),
			"Potentials" => array("vtiger_seactivityrel"=>array("activityid","crmid"),"vtiger_activity"=>"activityid"),
		);
		return $rel_tables[$secmodule];
	}
	
	/*
	 * Function to get the secondary query part of a report 
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityCalendar',array('vtiger_groupsCalendar','vtiger_usersCalendar','vtiger_lastModifiedByCalendar'));
		$matrix->setDependency('vtiger_cntactivityrel',array('vtiger_contactdetailsCalendar'));
		$matrix->setDependency('vtiger_seactivityrel',array('vtiger_crmentityRelCalendar'));
		$matrix->setDependency('vtiger_crmentityRelCalendar',array('vtiger_accountRelCalendar','vtiger_leaddetailsRelCalendar','vtiger_potentialRelCalendar',
								'vtiger_quotesRelCalendar','vtiger_purchaseorderRelCalendar','vtiger_invoiceRelCalendar',
								'vtiger_salesorderRelCalendar','vtiger_troubleticketsRelCalendar','vtiger_campaignRelCalendar'));
		$matrix->setDependency('vtiger_activity',array('vtiger_crmentityCalendar','vtiger_cntactivityrel','vtiger_activitycf',
								'vtiger_seactivityrel','vtiger_activity_reminder','vtiger_recurringevents'));
		
		if (!$queryPlanner->requireTable('vtiger_activity', $matrix)) {
			return '';
		}
		
		$query = $this->getRelationQuery($module,$secmodule,"vtiger_activity","activityid", $queryPlanner);
	
		if ($queryPlanner->requireTable("vtiger_crmentityCalendar",$matrix)){
			$query .=" left join vtiger_crmentity as vtiger_crmentityCalendar on vtiger_crmentityCalendar.crmid=vtiger_activity.activityid and vtiger_crmentityCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_cntactivityrel",$matrix)){
			$query .=" 	left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid";	
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsCalendar")){
			$query .=" 	left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_cntactivityrel.contactid";
		}
		if ($queryPlanner->requireTable("vtiger_activitycf")){
			$query .=" 	left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_seactivityrel",$matrix)){
			$query .=" 	left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_activity_reminder")){
			$query .=" 	left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_recurringevents")){
			$query .=" 	left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_crmentityRelCalendar",$matrix)){
			$query .=" 	left join vtiger_crmentity as vtiger_crmentityRelCalendar on vtiger_crmentityRelCalendar.crmid = vtiger_seactivityrel.crmid and vtiger_crmentityRelCalendar.deleted=0";

		}
		if ($queryPlanner->requireTable("vtiger_accountRelCalendar")){
			$query .=" 	left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_leaddetailsRelCalendar")){
			$query .=" 	left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_potentialRelCalendar")){
			$query .=" 	left join vtiger_potential as vtiger_potentialRelCalendar on vtiger_potentialRelCalendar.potentialid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_quotesRelCalendar")){
			$query .=" 	left join vtiger_quotes as vtiger_quotesRelCalendar on vtiger_quotesRelCalendar.quoteid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_purchaseorderRelCalendar")){
			$query .=" 	left join vtiger_purchaseorder as vtiger_purchaseorderRelCalendar on vtiger_purchaseorderRelCalendar.purchaseorderid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_invoiceRelCalendar")){
			$query .=" 	left join vtiger_invoice as vtiger_invoiceRelCalendar on vtiger_invoiceRelCalendar.invoiceid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_salesorderRelCalendar")){
			$query .=" 	left join vtiger_salesorder as vtiger_salesorderRelCalendar on vtiger_salesorderRelCalendar.salesorderid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_troubleticketsRelCalendar")){
			$query .=" left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_campaignRelCalendar")){
			$query .=" 	left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsCalendar")){
			$query .=" left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersCalendar")){
			$query .=" 	left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByCalendar")){
			$query .="  left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentityCalendar.modifiedby ";
		}
		return $query;
	}
	
	public function getNonAdminAccessControlQuery($module, $user,$scope='') {
		require('user_privileges/user_privileges_'.$user->id.'.php');
		require('user_privileges/sharing_privileges_'.$user->id.'.php');
		$query = ' ';
		$tabId = getTabid($module);
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]
				== 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u'.$user->id.'_t'.$tabId;			
			$sharingRuleInfoVariable = $module.'_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			// Start added code by Ajay [TECHFOUR]
			if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
					count($sharingRuleInfo['GROUP']) > 0)) {
				$tableName = $tableName . '_t' . $tabId;
				$sharedTabId = $tabId;
			}/*elseif($module == 'Calendar' || !empty($scope)) {
				$tableName .= '_t' . $tabId;
			} */
			
			// End added code by Ajay [TECHFOUR]
			$this->setupTemporaryTable($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);
			
			$query .= " INNER JOIN $tableName $tableName$scope ON ($tableName$scope.id = ".
					"vtiger_crmentity$scope.smownerid and $tableName$scope.shared=0) ";
			$sharedIds = getSharedCalendarId($user->id);
			if(!empty($sharedIds)){
				$query .= "or ($tableName$scope.id = vtiger_crmentity$scope.smownerid AND ".
					"$tableName$scope.shared=1 and vtiger_activity.visibility = 'Public') ";
			}
		}		
		return $query;
	}
	
	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups) {
		$module = null;
		if (!empty($tabId)) {
			$module = getTabname($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared ".
			"int(1) default 0) ignore ".$query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array());
		if(is_object($result)) {
			$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared ".
			"int(1) default 0) replace select 1, userid as id from vtiger_sharedcalendar where ".
			"sharedid = $user->id";
			$result = $db->pquery($query, array());
			if(is_object($result)) {
				return true;
			}
		}
		return false;
	}
}
?>