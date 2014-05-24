<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('modules/Calendar/Activity.php');
require_once('modules/Campaigns/Campaigns.php');
require_once('modules/Documents/Documents.php');
require_once('modules/Emails/Emails.php');
require_once('include/utils/utils.php');
require_once('user_privileges/default_module_view.php');

class Leads extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "vtiger_leaddetails";
	var $table_index= 'leadid';

	var $tab_name = Array('vtiger_crmentity','vtiger_leaddetails','vtiger_leadsubdetails','vtiger_leadaddress','vtiger_leadscf');
	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_leaddetails'=>'leadid','vtiger_leadsubdetails'=>'leadsubscriptionid','vtiger_leadaddress'=>'leadaddressid','vtiger_leadscf'=>'leadid');

	var $entity_table = "vtiger_crmentity";

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_leadscf', 'leadid');

	//construct this from database;
	var $column_fields = Array();
	var $sortby_fields = Array('lastname','firstname','email','phone','company','smownerid','website');

	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('smcreatorid', 'smownerid', 'contactid','potentialid' ,'crmid');

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'First Name'=>Array('leaddetails'=>'firstname'),
		'Last Name'=>Array('leaddetails'=>'lastname'),
		'Company'=>Array('leaddetails'=>'company'),
		'Phone'=>Array('leadaddress'=>'phone'),
		'Website'=>Array('leadsubdetails'=>'website'),
		'Email'=>Array('leaddetails'=>'email'),
		'Assigned To'=>Array('crmentity'=>'smownerid')
	);
	var $list_fields_name = Array(
		'First Name'=>'firstname',
		'Last Name'=>'lastname',
		'Company'=>'company',
		'Phone'=>'phone',
		'Website'=>'website',
		'Email'=>'email',
		'Assigned To'=>'assigned_user_id'
	);
	var $list_link_field= 'lastname';

	var $search_fields = Array(
		'Name'=>Array('leaddetails'=>'lastname'),
		'Company'=>Array('leaddetails'=>'company')
	);
	var $search_fields_name = Array(
		'Name'=>'lastname',
		'Company'=>'company'
	);

	var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'lastname', 'createdtime' ,'modifiedtime');

	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('firstname','lastname','leadsource','leadstatus','rating','industry','secondaryemail','email','annualrevenue','designation','salutation');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'vtiger_leaddetails.leadid';
	var $default_sort_order = 'DESC';

	// For Alphabetical search
	var $def_basicsearch_col = 'lastname';

	//var $groupTable = Array('vtiger_leadgrouprelation','leadid');

	function Leads()	{
		$this->log = LoggerManager::getLogger('lead');
		$this->log->debug("Entering Leads() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Leads');
		$this->log->debug("Exiting Lead method ...");
	}

	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module)
	{
		global $adb,$log, $current_user;
		
		$log->debug("Entering function save_module ($module");	
		
		//if($_REQUEST['mode'] != "import"){
			if($_REQUEST['mode'] == "import") {
				$assignedid = $current_user->id;
				$campaignid = $_SESSION['importid'];				
			}
			else {
				$assignedid = $_REQUEST['assigned_user_id'];
				$campaignid = $_REQUEST['campaignid'];
			}
				
				$sub_query = " AND vtiger_users.id = ".$assignedid." ";						
				$userDetails = getUserDetails($sub_query);
				$depth = $userDetails['depth'];
				$rolename = $userDetails['rolename'];
				$outletmasterid = $userDetails['outletmasterid'];
				$plantcode = $userDetails['outletmaster'];
				$profileid = $userDetails['profileid'];
				
				$sub_query = " AND vtiger_users.id = ".$current_user->id." ";						
				$userDetailsMo = getUserDetails($sub_query);
				$mo_profileid = $userDetailsMo['profileid'];
				if($mo_profileid == 4 && ($current_user->id != $assignedid)) { // MO profile Reassignment
					$this->createNewActivity($current_user->id, $assignedid, $this->id);
				}
				
				if($profileid == 3) { // Customer care agent Profile
					$outletmasterid = $_REQUEST['outlet'];
					$plant_code_qry = $adb->pquery("select outletmaster from vtiger_outletmaster 
					where outletmasterid = ?", array($outletmasterid));		
					if($adb->num_rows($plant_code_qry) > 0) {
						$plantcode = $adb->query_result($plant_code_qry,0,'outletmaster');
						$adb->query("UPDATE vtiger_leaddetails 
							INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid 
							SET outlet = ".$outletmasterid.", plant_code = '".$plantcode."' where vtiger_leaddetails.leadid = ".$this->id." ");
					}
				}
					
				if(!isset($_REQUEST['record']) && $_REQUEST['record'] == ""){			
					$adb->query("UPDATE vtiger_leaddetails SET import_lead_flag = '0' where leadid = ".$this->id." ");
				}
					
				$lead_qry = $adb->pquery("select leadsource from vtiger_leaddetails where vtiger_leaddetails.leadid = ?", array($this->id));		
				if($adb->num_rows($lead_qry) > 0) {					
					$customer_type = $adb->query_result($lead_qry,0,'leadsource');
					if($customer_type == "")
						$adb->pquery("update vtiger_leaddetails  set leadsource = ? where leadid = ? ",array('Individual',$this->id));									
				}
													
				$campaign_qry = $adb->pquery("select campaigntype, location, vendorname from vtiger_campaign 
				INNER JOIN vtiger_vendor on vtiger_vendor.vendorid = vtiger_campaign.campaigntype
				INNER JOIN vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_campaign.campaignid				
				where vtiger_campaign.campaignid = ?", array($campaignid));		
				if($adb->num_rows($campaign_qry) > 0) {
					$campaigntype = $adb->query_result($campaign_qry,0,'campaigntype');
					$campaignlocation = $adb->query_result($campaign_qry,0,'location');
					$campaign_type = $adb->query_result($campaign_qry,0,'vendorname');					
					$adb->pquery("update vtiger_leadaddress inner join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid = vtiger_leadaddress.leadaddressid set campaigntype = ?, campaignlocation = ? where leadaddressid=? ",array($campaigntype,$campaignlocation,$this->id));			
				}
				$log->debug("Exiting function save_module ($module");
				
				if($profileid != 3) {					
					$adb->query("UPDATE vtiger_leaddetails 
					INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid 
					SET outlet = ".$outletmasterid.", plant_code = '".$plantcode."' where vtiger_leaddetails.leadid = ".$this->id." ");
				// start to add except inbound lead will transfer to MO if created by agent or cro ***************
					if($campaign_type != "InBound" && ($profileid == 5 || $profileid == 6) && isset($_REQUEST['currentid'])) {
						$sub_query1 = " AND depth = 8 AND vtiger_outletmastercf.outletmasterid = ".$outletmasterid." ";						
						$userDetails1 = getUserDetails($sub_query1);
						$mo_id = $userDetails1['id'];
						$this->setReassignedLead($mo_id, $assignedid);
					}
					
				// end to add except inbound lead will transfer to MO if created by agent or cro ***************	
				}
		//}
							
		// Start to add in History Module **************************************
		
		if($this->mode == "edit" && $_REQUEST['record'] != "") {
			$history_qry = $adb->query("SELECT lead_no, campaignid, vtiger_modtracker_basic.id as basicid, vtiger_modtracker_basic.module as basicmodule, vtiger_modtracker_basic.crmid as entityid, whodid, createdtime, changedon, prevalue, postvalue, outletmaster FROM vtiger_modtracker_basic 
		INNER JOIN vtiger_modtracker_detail ON vtiger_modtracker_detail.id = vtiger_modtracker_basic.id
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modtracker_basic.crmid
INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
INNER JOIN vtiger_users on vtiger_users.id = vtiger_modtracker_detail.postvalue
INNER JOIN vtiger_outletmaster on vtiger_outletmaster.outletmasterid = vtiger_users.cf_775
WHERE vtiger_crmentity.deleted = 0 AND vtiger_modtracker_detail.fieldname = 'assigned_user_id' AND history_status = '0' AND vtiger_modtracker_basic.module = 'Leads' ORDER BY basicid");		
  		if($adb->num_rows($history_qry) > 0) {
			while($row = $adb->fetch_array($history_qry)) {			
				$basicmodule = $row['basicmodule'];
				$entityid = $row['entityid'];
				$whodid = $row['whodid'];
				$createdtime = $row['createdtime'];
				$changedon = $row['changedon'];
				$prevalue = $row['prevalue'];
				$postvalue = $row['postvalue'];
				$outletmaster = $row['outletmaster'];					
				$basicid = $row['basicid'];
				$entity_no = $row['lead_no'];
				$campaignid = $row['campaignid'];				
				$crmid = $adb->getUniqueID("vtiger_crmentity");					
				$createrid = $userid;
				$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='Faq' and active = 1");
				$resultnum = $adb->fetch_array($querynum);
				$prefix = $resultnum['prefix'];
				$cur_id = $resultnum['cur_id'];
				$HISNo = $prefix.$cur_id; 
				$next_curr_id = $cur_id + 1;
				$adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='Faq' and active = 1");					  				
				$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
				modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$adb->pquery($query, array($crmid, $whodid, $whodid, 0, "Faq", "", $createdtime, $changedon, NULL, NULL, 0, 1, 0, $HISNo));
				
				
				$adb->query("INSERT INTO vtiger_faq (id, faq_no, changed_by, entityid, outlet, post_assigned_to, pre_assigned_to, module, entity_no, campid) VALUES(".$crmid.",'".$HISNo."', ".$whodid.", ".$entityid.", '".$outletmaster."', ".$postvalue.", '".$prevalue."', '".$basicmodule."', '".$entity_no."', '".$campaignid."')"); 
				
				$adb->query("UPDATE vtiger_modtracker_basic SET history_status = '1' WHERE id = ".$basicid."");
				
				$sql = "insert into vtiger_crmentityrel values (?,?,?,?)";
				$adb->pquery($sql, array($entityid,'Leads',$crmid,'Faq'));
			}
		}
	}
		// End to add in History Module **************************************
		
	// Start to add activity ************************************************************
		if($this->mode != "edit" && $customer_type != "Corporate" && $campaign_type == "InBound") {
			$this->createNewActivity($current_user->id, $assignedid, $this->id);	
		}
		// End to add activity 
		
		// Start update Target module ****************************	

		$lead_qry = $adb->query("
SELECT COUNT(DISTINCT CASE WHEN leadstatus = 'Appointment Booked' THEN vtiger_leaddetails.leadid END ) AS leadappointment,COUNT(leadid) AS uploadleadcount, vtiger_leadaddress.campaignid as campid, leadstatus FROM vtiger_leaddetails 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
LEFT JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
WHERE vtiger_crmentity.deleted = 0 GROUP BY vtiger_leadaddress.campaignid" );		
  		if($adb->num_rows($lead_qry) > 0) {
			while($row = $adb->fetch_array($lead_qry)) {
				$campaignid = $row['campid'];
				$uploadleadcount = $row['uploadleadcount'];
				$leadappointment = $row['leadappointment'];				
			$adb->query("UPDATE vtiger_campaignscf SET 
				total_uploded_lead = ".$uploadleadcount.", total_appointment_booked = ".$leadappointment."
						WHERE vtiger_campaignscf.campaignid = ".$campaignid." ");	
			}			
		}
		
		$lead_camp_qry = $adb->query("SELECT closingdate  FROM vtiger_leaddetails 
LEFT JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_leadaddress.campaignid 
INNER JOIN vtiger_campaign ON vtiger_campaign.campaignid = vtiger_campaignscf.campaignid
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
INNER JOIN vtiger_crmentity as leadcrmentity ON leadcrmentity.crmid = vtiger_leaddetails.leadid
WHERE vtiger_crmentity.deleted = 0 AND leadcrmentity.deleted = 0  AND vtiger_leaddetails.leadid = $this->id " );		
  		if($adb->num_rows($lead_camp_qry) > 0) {
			$row = $adb->fetch_array($lead_camp_qry);			
			$timestamp = strtotime($row['closingdate']);
			$startdate = date('Y-m-d',$timestamp);
			// First day of the month.
			$start_date =  date('Y-m-01', strtotime($startdate));				
			// Last day of the month.
			$end_date = date('Y-m-t', strtotime($startdate));
		}		
		$this->updateTargetModuleFields($start_date, $end_date);
			
// End update Target module ****************************
		
		
	}

	function setReassignedLead($mo_id, $assignedid) {
		global $adb, $current_user;
		
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $this->id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																																				
			$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
			$adb->pquery($sql,Array($thisid, 'assigned_user_id', $assignedid, $mo_id));
			
			$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
						INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
						SET  smownerid = ".$mo_id.", modifiedby = ".$current_user->id.", modifiedtime = '".date('Y-m-d H:i:s')."' 
						WHERE vtiger_leaddetails.leadid = ".$this->id." ");
	}

	function createNewActivity($current_user, $new_assigned_to, $lead_id) {					
		global $adb;
		$current_user = 1;
		$current_user;	
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
	function updateTargetModuleFields($start_date, $end_date) {
		
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
				$usersid = "";
				require('user_privileges/user_privileges_'.$smownerid.'.php');
				$usersid = $smownerid;
				if(is_array($subordinate_roles_users)) {
					foreach($subordinate_roles_users as $row) {
						foreach($row as $row1) {
							$usersid .= ','.$row1;
						}
					}
					
// Start Campaign 
					
						$campaign_qry = $adb->query("SELECT * FROM vtiger_campaign
INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid		
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
WHERE vtiger_crmentity.deleted = 0 AND  closingdate >= '$start_date' AND end_date <= '$end_date' AND smownerid IN($usersid) " );		
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
			$total_uploded_lead = 0;			
			$total_appointment_booked = 0;			
			$total_lead_conversion = 0;		
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
				
				$uploded_lead = str_replace(",","",$row['total_uploded_lead']);
				$appointment_booked = str_replace(",","",$row['total_appointment_booked']);
				$lead_conversion = str_replace(",","",$row['total_lead_conversion']);
				
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
				$total_uploded_lead = $total_uploded_lead + $uploded_lead;			
				$total_appointment_booked = $total_appointment_booked + $appointment_booked;		
				$total_lead_conversion = $total_lead_conversion + $lead_conversion;																		
			}
		}
			
					
// End Campaign
					$adb->query("UPDATE vtiger_targetscf SET 
				budget_cost = '".$total_budget_cost."', actual_budget = '".$total_actual_budget_cost."', pot_no_cars = '".$total_pot_no_cars."', target_tag = '".$total_tag."', actual_tag = '".$total_actual_tag."', target_v_sale = '".$total_target_v_send_hub."', actual_v_s_hub = '".$total_actual_send_to_hub."', target_checkup = '".$total_target_checkup."', actual_checkup = '".$total_actual_checkup."', total_up_lead = '".$total_uploded_lead."', total_lead_apoint = '".$total_appointment_booked."', total_lead_conversion = '".$total_lead_conversion."'
						WHERE vtiger_targetscf.targetsid = ".$targetsid." ");					
				}
			}
		}
	
	}


	// Mike Crowe Mod --------------------------------------------------------Default ordering for us

	/** Function to export the lead records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Leads Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Leads", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
	      			FROM ".$this->entity_table."
				INNER JOIN vtiger_leaddetails
					ON vtiger_crmentity.crmid=vtiger_leaddetails.leadid
				LEFT JOIN vtiger_leadsubdetails
					ON vtiger_leaddetails.leadid = vtiger_leadsubdetails.leadsubscriptionid
				LEFT JOIN vtiger_leadaddress
					ON vtiger_leaddetails.leadid=vtiger_leadaddress.leadaddressid
				LEFT JOIN vtiger_leadscf
					ON vtiger_leadscf.leadid=vtiger_leaddetails.leadid
	                        LEFT JOIN vtiger_groups
                        	        ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users
					ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'
				";

		$query .= $this->getNonAdminAccessControlQuery('Leads',$current_user);
		$where_auto = " vtiger_crmentity.deleted=0 AND vtiger_leaddetails.converted =0";

		if($where != "")
			$query .= " where ($where) AND ".$where_auto;
		else
			$query .= " where ".$where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}



	/** Returns a list of the associated tasks
 	 * @param  integer   $id      - leadid
 	 * returns related Task or Event record in array format
	*/
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_activities(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/Activity.php");
		$other = new Activity();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')

			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="activity_mode">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
				if(getFieldVisibilityPermission('Events',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_activity.*,vtiger_seactivityrel.crmid as parent_id, vtiger_contactdetails.lastname,
			vtiger_contactdetails.contactid, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
			vtiger_crmentity.modifiedtime,case when (vtiger_users.user_name not like '') then
		$userNameSql else vtiger_groups.groupname end as user_name,
		vtiger_recurringevents.recurringtype
		from vtiger_activity inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=
		vtiger_activity.activityid inner join vtiger_crmentity on vtiger_crmentity.crmid=
		vtiger_activity.activityid left join vtiger_cntactivityrel on
		vtiger_cntactivityrel.activityid = vtiger_activity.activityid left join
		vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid
		left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
		left outer join vtiger_recurringevents on vtiger_recurringevents.activityid=
		vtiger_activity.activityid left join vtiger_groups on vtiger_groups.groupid=
		vtiger_crmentity.smownerid where vtiger_seactivityrel.crmid=".$id." and
			vtiger_crmentity.deleted = 0 and ((vtiger_activity.activitytype='Task' and
			vtiger_activity.status not in ('Completed','Deferred')) or
			(vtiger_activity.activitytype NOT in ('Emails','Task') and
			vtiger_activity.eventstatus not in ('','Held'))) ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/** Returns a list of the associated Campaigns
	  * @param $id -- campaign id :: Type Integer
	  * @returns list of campaigns in array format
	  */
	function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_campaigns(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name ,
				vtiger_campaign.campaignid, vtiger_campaign.campaignname, vtiger_campaign.campaigntype, vtiger_campaign.campaignstatus,
				vtiger_campaign.expectedrevenue, vtiger_campaign.closingdate, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
				vtiger_crmentity.modifiedtime from vtiger_campaign
				inner join vtiger_campaignleadrel on vtiger_campaignleadrel.campaignid=vtiger_campaign.campaignid
				inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid
				left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				where vtiger_campaignleadrel.leadid=".$id." and vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
	}


		/** Returns a list of the associated emails
	 	 * @param  integer   $id      - leadid
	 	 * returns related emails record in array format
		*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_emails(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query ="select case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name," .
				" vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.semodule, vtiger_activity.activitytype," .
				" vtiger_activity.date_start, vtiger_activity.status, vtiger_activity.priority, vtiger_crmentity.crmid," .
				" vtiger_crmentity.smownerid,vtiger_crmentity.modifiedtime, vtiger_users.user_name, vtiger_seactivityrel.crmid as parent_id " .
				" from vtiger_activity" .
				" inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=vtiger_activity.activityid" .
				" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid" .
				" left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid" .
				" left join vtiger_users on  vtiger_users.id=vtiger_crmentity.smownerid" .
				" where vtiger_activity.activitytype='Emails' and vtiger_crmentity.deleted=0 and vtiger_seactivityrel.crmid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}

	/**
	 * Function to get Lead related Task & Event which have activity type Held, Completed or Deferred.
	 * @param  integer   $id      - leadid
	 * returns related Task or Event record in array format
	 */
	function get_history($id)
	{
		
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.status,
			vtiger_activity.eventstatus, vtiger_activity.activitytype,vtiger_activity.date_start,
			vtiger_activity.due_date,vtiger_activity.time_start,vtiger_activity.time_end,
			vtiger_crmentity.modifiedtime,vtiger_crmentity.createdtime,
			vtiger_crmentity.description, $userNameSql as user_name,vtiger_groups.groupname
				from vtiger_activity
				inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=vtiger_activity.activityid
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
				left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_crmentity.smownerid= vtiger_users.id
				where (vtiger_activity.activitytype != 'Emails')
				and (vtiger_activity.status = 'Completed' or vtiger_activity.status = 'Deferred' or (vtiger_activity.eventstatus = 'Held' and vtiger_activity.eventstatus != ''))
				and vtiger_seactivityrel.crmid=".$id."
	                        and vtiger_crmentity.deleted = 0";
							//and vtiger_seactivityrel.crmid=".$id."
						
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Leads',$query,$id);
	}
	
	/**
	* Function to get lead related Products
	* @param  integer   $id      - leadid
	* returns related Products record in array format
	*/
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode,
				vtiger_products.commissionrate, vtiger_products.qty_per_unit, vtiger_products.unit_price,
				vtiger_crmentity.crmid, vtiger_crmentity.smownerid
				FROM vtiger_products
				INNER JOIN vtiger_seproductsrel ON vtiger_products.productid = vtiger_seproductsrel.productid and vtiger_seproductsrel.setype = 'Leads'
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_seproductsrel.crmid
				LEFT JOIN vtiger_users
					ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			   WHERE vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.leadid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** Function to get the Columnnames of the Leads Record
	* Used By vtigerCRM Word Plugin
	* Returns the Merge Fields for Word Plugin
	*/
	function getColumnNames_Lead()
	{
		global $log,$current_user;
		$log->debug("Entering getColumnNames_Lead() method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
		{
			$sql1 = "select fieldlabel from vtiger_field where tabid=7 and vtiger_field.presence in (0,2)";
			$params1 = array();
		}else
		{
			$profileList = getCurrentUserProfileList();
			$sql1 = "select vtiger_field.fieldid,fieldlabel from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=7 and vtiger_field.displaytype in (1,2,3,4) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (". generateQuestionMarks($profileList) .")  group by fieldid";
				array_push($params1, $profileList);
			}
		}
		$result = $this->db->pquery($sql1, $params1);
		$numRows = $this->db->num_rows($result);
		for($i=0; $i < $numRows;$i++)
		{
	   	$custom_fields[$i] = $this->db->query_result($result,$i,"fieldlabel");
	   	$custom_fields[$i] = preg_replace("/\s+/","",$custom_fields[$i]);
	   	$custom_fields[$i] = strtoupper($custom_fields[$i]);
		}
		$mergeflds = $custom_fields;
		$log->debug("Exiting getColumnNames_Lead method ...");
		return $mergeflds;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("Activities"=>"vtiger_seactivityrel","Documents"=>"vtiger_senotesrel","Attachments"=>"vtiger_seattachmentsrel",
					"Products"=>"vtiger_seproductsrel","Campaigns"=>"vtiger_campaignleadrel");

		$tbl_field_arr = Array("vtiger_seactivityrel"=>"activityid","vtiger_senotesrel"=>"notesid","vtiger_seattachmentsrel"=>"attachmentsid",
					"vtiger_seproductsrel"=>"productid","vtiger_campaignleadrel"=>"campaignid");

		$entity_tbl_field_arr = Array("vtiger_seactivityrel"=>"crmid","vtiger_senotesrel"=>"crmid","vtiger_seattachmentsrel"=>"crmid",
					"vtiger_seproductsrel"=>"crmid","vtiger_campaignleadrel"=>"leadid");

		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
						" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
						array($transferId,$entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if($res_cnt > 0) {
					for($i=0;$i<$res_cnt;$i++) {
						$id_field_value = $adb->query_result($sel_result,$i,$id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
							array($entityId,$transferId,$id_field_value));
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		$log->debug("Exiting transferRelatedRecords...");
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule, $queryPlanner) {
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_leaddetails',array('vtiger_leadaddress','vtiger_leadsubdetails','vtiger_leadscf','vtiger_email_trackLeads'));
		$matrix->setDependency('vtiger_crmentityLeads',array('vtiger_groupsLeads','vtiger_usersLeads','vtiger_lastModifiedByLeads'));
		
		// TODO Support query planner
		if (!$queryPlanner->requireTable("vtiger_leaddetails",$matrix)){
			return '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"vtiger_leaddetails","leadid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityLeads",$matrix)){
		    $query .= " left join vtiger_crmentity as vtiger_crmentityLeads on vtiger_crmentityLeads.crmid = vtiger_leaddetails.leadid and vtiger_crmentityLeads.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_leadaddress")){
		    $query .= " left join vtiger_leadaddress on vtiger_leaddetails.leadid = vtiger_leadaddress.leadaddressid";
		}
		if ($queryPlanner->requireTable("vtiger_leadsubdetails")){
		    $query .= " left join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("vtiger_leadscf")){
		    $query .= " left join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("vtiger_email_trackLeads")){
		    $query .= " LEFT JOIN vtiger_email_track AS vtiger_email_trackLeads ON vtiger_email_trackLeads.crmid = vtiger_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsLeads")){
		    $query .= " left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentityLeads.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersLeads")){
		    $query .= " left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentityLeads.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByLeads")){
		    $query .= " left join vtiger_users as vtiger_lastModifiedByLeads on vtiger_lastModifiedByLeads.id = vtiger_crmentityLeads.modifiedby ";
		}
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Calendar" => array("vtiger_seactivityrel"=>array("crmid","activityid"),"vtiger_leaddetails"=>"leadid"),
			"Products" => array("vtiger_seproductsrel"=>array("crmid","productid"),"vtiger_leaddetails"=>"leadid"),
			"Campaigns" => array("vtiger_campaignleadrel"=>array("leadid","campaignid"),"vtiger_leaddetails"=>"leadid"),
			"Documents" => array("vtiger_senotesrel"=>array("crmid","notesid"),"vtiger_leaddetails"=>"leadid"),
			"Services" => array("vtiger_crmentityrel"=>array("crmid","relcrmid"),"vtiger_leaddetails"=>"leadid"),
			"Emails" => array("vtiger_seactivityrel"=>array("crmid","activityid"),"vtiger_leaddetails"=>"leadid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Campaigns') {
			$sql = 'DELETE FROM vtiger_campaignleadrel WHERE leadid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		}
		elseif($return_module == 'Products') {
			$sql = 'DELETE FROM vtiger_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}
	
	function getListButtons($app_strings) {
		$list_buttons = Array();

		if(isPermitted('Leads','Delete','') == 'yes') {
			$list_buttons['del'] =	$app_strings[LBL_MASS_DELETE];
		}
		if(isPermitted('Leads','EditView','') == 'yes') {
			$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
			$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
		}
		if(isPermitted('Emails','EditView','') == 'yes')
			$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
		
		// end of mailer export
		return $list_buttons;
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Products')
				$adb->pquery("insert into vtiger_seproductsrel values (?,?,?)", array($crmid, $with_crmid, $module));
			elseif($with_module == 'Campaigns')
				$adb->pquery("insert into  vtiger_campaignleadrel values(?,?,1)", array($with_crmid, $crmid));
			else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}
}

?>