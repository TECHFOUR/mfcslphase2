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
require_once('include/utils/utils.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Leads/Leads.php');
require_once('user_privileges/default_module_view.php');

class Campaigns extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "vtiger_campaign";
	var $table_index= 'campaignid';

	var $tab_name = Array('vtiger_crmentity','vtiger_campaign','vtiger_campaignscf');
	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_campaign'=>'campaignid','vtiger_campaignscf'=>'campaignid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_campaignscf', 'campaignid');
	var $column_fields = Array();

	var $sortby_fields = Array('campaignname','smownerid','campaigntype','productname','expectedrevenue','closingdate','campaignstatus','expectedresponse','targetaudience','expectedcost');

	var $list_fields = Array(
					'Campaign No'=>Array('campaign'=>'campaignname'),
					'Type of Activity'=>Array('campaign'=>'campaigntype'),
					'Campaign Category'=>Array('campaignscf'=>'campaign_category'),
					'Location'=>Array('campaignscf'=>'location'),
					'Start Date'=>Array('campaign'=>'closingdate'),
					'End Date'=>Array('campaign'=>'end_date')
				);

	var $list_fields_name = Array(
					'Campaign No'=>'campaignname',
					'Type of Activity'=>'campaigntype',
					'Campaign Category'=>'campaign_category',
					'Location'=>'location',
					'Start Date'=>'closingdate',
					'End Date'=>'end_date'
				     );

	var $list_link_field= 'campaignname';
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'DESC';

	//var $groupTable = Array('vtiger_campaigngrouprelation','campaignid');

	var $search_fields = Array(
					'Campaign No'=>Array('campaign'=>'campaignname'),
					'Type of Activity'=>Array('campaign'=>'campaigntype'),
					'Campaign Category'=>Array('campaignscf'=>'campaign_category'),
					'Location'=>Array('campaignscf'=>'location'),
					'Start Date'=>Array('campaign'=>'closingdate'),
					'End Date'=>Array('campaign'=>'end_date')
			);

	var $search_fields_name = Array(
					'Campaign No'=>'campaignname',
					'Type of Activity'=>'campaigntype',
					'Campaign Category'=>'campaign_category',
					'Location'=>'location',
					'Start Date'=>'closingdate',
					'End Date'=>'end_date'
			);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('campaignname','createdtime' ,'modifiedtime','assigned_user_id');

	// For Alphabetical search
	var $def_basicsearch_col = 'campaignname';
	
	function Campaigns() 
	{
		$this->log =LoggerManager::getLogger('campaign');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Campaigns');
	}

	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module)   // New Code added by jitendra singh [TECHFOUR]
	{
		//echo "<pre>"; print_r($_REQUEST); die;
		global $adb,$current_user;
		$arr_length = sizeof($_REQUEST);
		$subArray = array_slice($_REQUEST,57,$arr_length);
		$subArray_length = (sizeof($subArray))/12;
		$assignedid = $_REQUEST['assigned_user_id'];
		$date_format = $current_user->date_format;
		$startingdate = $_REQUEST['closingdate'];
		$startingdate = DateTimeField::__convertToDBFormat($startingdate, $date_format);		
		
		$timestamp = strtotime($startingdate);
		$daymain = date('l', $timestamp);	
		
		/*Start code to find the Campaign Category according to Campaign Type and Update it by jitendra singh on 7 feb14*/
		$campaign_qry = $adb->query("select campaign_category from vtiger_vendorcf
						INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_vendorcf.vendorid				
						where vendorid = ".$_REQUEST['campaigntype']."");	
				
		if($adb->num_rows($campaign_qry) > 0) {
			$data = $adb->fetch_array($campaign_qry);
			$campaign_catogory = $data['campaign_category'];
			$adb->query("update vtiger_campaignscf set campaign_category = '".$campaign_catogory."' where campaignid = ".$this->id."");			
		}
		/*Start code to find the Campaign Category according to Campaign Type and Update it by jitendra singh on 7 feb14*/
		
		
				
		$adb->query("update vtiger_campaign set campain_day = '".$daymain."' where campaignid = ".$this->id.""); // Modified by jitendra singh on 25 Jan 2014
		
		if(!empty($_REQUEST['currentid'])){    // Update for the Day of main screen						
				$adb->query("update vtiger_campaignscf set draft_campaign = 1 where campaignid = ".$this->id."");
				/*End Date According to Start date*/
				if($_REQUEST['end_date'] == ''){									
					// Last day of the month.
					$end_date = date('Y-m-t', strtotime($startingdate));					
					$adb->query("update vtiger_campaignscf set end_date = '".$end_date."' where campaignid = ".$this->id."");	
				}
				/*End*/			
			
			}


		for($i = 0; $i <= $subArray_length+3; $i++) {
			
		
				$x1 = $i+1;
				$x2 = $i+1;
				$x3 = $i+1;
				$x4 = $i+1;			
				
				$start_date = $_REQUEST['start_date'.$x1];
				if(!empty($start_date)){
				$timestamp= strtotime($start_date);
				$start_date = date('Y-m-d',$timestamp);
				$day = date('l', $timestamp);
				}
				$end_date = $_REQUEST['end_date'.$x1];
				if($end_date == ''){
				// Last day of the month.
					$end_date = date('Y-m-t', strtotime($start_date));
				}
				$end_date = date('Y-m-d',strtotime($end_date));
				$start_time = $_REQUEST['start_time'.$x1];
				if(!empty($start_time)){
				$start_time = $start_time.":00";
				}
				$end_time = $_REQUEST['end_time'.$x1];
				if(!empty($end_time)){
				$end_time = $end_time.":00";
				}
				$location = $_REQUEST['location'.$x1];
				$targetaudience = $_REQUEST['targetaudience'.$x2];
				$campaigntype = $_REQUEST['campaigntype'.$x2];
				$sponsor = $_REQUEST['sponsor'.$x2];
				$targetsize = $_REQUEST['targetsize'.$x2];
				$actualsalescount = $_REQUEST['actualsalescount'.$x2];
				$expectedsalescount = $_REQUEST['expectedsalescount'.$x3];
				$expectedresponsecount = $_REQUEST['expectedresponsecount'.$x3];
				$leaflet = $_REQUEST['leaflet'.$x3];
				$poster = $_REQUEST['poster'.$x3];
				$tag = $_REQUEST['tag'.$x3];
				$other1 = $_REQUEST['other1'.$x4];
				$other2 = $_REQUEST['other2'.$x4];
				
				
				/*Start code to find the Campaign Category according to Campaign Type and Update it by jitendra singh on 7 feb14*/
				$campaign_qry1 = $adb->query("select campaign_category from vtiger_vendorcf
											INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_vendorcf.vendorid				
											where vendorid = ".$campaigntype."");	
				
				if($adb->num_rows($campaign_qry1) > 0) {
					$data = $adb->fetch_array($campaign_qry1);
					$campaign_catogory1 = $data['campaign_category'];
				}
				/*Start code to find the Campaign Category according to Campaign Type and Update it by jitendra singh on 7 feb14*/
								
				// Query for insert the data
				
				
			if($_REQUEST['start_date'.$x1] != ''){
				$num = $x4;				
				$crmid = $adb->getUniqueID("vtiger_crmentity");
				$createrid = $current_user->id;
				$currentdatetime = date("Y-m-d H:i:s");	
				
				$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='Campaigns' and active = 1");
				$resultnum = $adb->fetch_array($querynum);
				$prefix = $resultnum['prefix'];
				$cur_id = $resultnum['cur_id'];
				$CampaignsNum = $prefix.$cur_id; 
				$next_curr_id = $cur_id + 1;
				$adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='Campaigns' and active = 1");		  
		  
		  		$callerName = $CampaignsNum;
		  		$all_Values = array('closingdate'=>$start_date,
									'campaignname'=> $CampaignsNum,
									'end_date'=>$end_date, 
									'campain_day'=>$day, 
									'start_time'=>$start_time, 
									'end_time'=>$end_time, 
									'location'=>$location, 
									'targetaudience'=>$targetaudience, 
									'campaigntype'=>$campaigntype, 
									'sponsor'=>$sponsor, 
									'targetsize'=>$targetsize, 
									'actualsalescount'=>$actualsalescount, 
									'expectedsalescount'=>$expectedsalescount, 
									'expectedresponsecount'=>$expectedresponsecount, 
									'leaflet'=>$leaflet, 
									'poster'=>$poster, 
									'tag'=>$tag, 
									'other1'=>$other1, 
									'other2'=>$other2,
									'assigned_user_id'=>$assignedid, 
									'createdtime'=>$currentdatetime, 
									'modifiedby'=>$createrid, 
									'draft_campaign'=>1,
									'record_id'=>$crmid,									
									'record_module'=>'Campaigns',
									'campaign_category'=>$campaign_category1
								);

		  
		   $query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
			modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$adb->pquery($query, array($crmid, $createrid, $assignedid, $createrid, "Campaigns", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $callerName));
	
		  $adb->query("INSERT into vtiger_campaign(campaignid,campain_day, closingdate,targetaudience,campaigntype,sponsor,targetsize,actualsalescount,expectedsalescount,expectedresponsecount,campaignname) 
		  values(".$crmid.",'".$day."',  
		  '".$start_date."','".$targetaudience."', '".$campaigntype."', '".$sponsor."', '".$targetsize."', '".$actualsalescount."', '".$expectedsalescount."', '".$expectedresponsecount."', '".$CampaignsNum."')"); 
	
		  $adb->query("INSERT into vtiger_campaignscf (campaignid,end_date,start_time,end_time,location,leaflet,poster,tag,other1,other2,campaign_category) values(".$crmid.",'".$end_date."','".$start_time."','".$end_time."','".$location."','".$leaflet."','".$poster."','".$tag."','".$other1."', '".$other2."', '".$campaign_catogory1."')"); 		  
		  	
// Start Save in Modtracker table ******************
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'Campaigns',$current_user->id, date('Y-m-d H:i:s',time()), 2));
			foreach($all_Values as $key=>$row) {
				if($row != "")	{									
					$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,postvalue) VALUES(?,?,?)',
						Array($thisid, $key, $row));
				}
			}
														
// End Save in Modtracker table ******************
				  		  		  
  		}
		else {
			$num = $x2;	
		}
		}
		
// Start update Target module ****************************	
	$startdate = date('Y-m-d',$timestamp);
	// First day of the month.
	$start_date =  date('Y-m-01', strtotime($startdate));				
	// Last day of the month.
	$end_date = date('Y-m-t', strtotime($startdate));	
	
		$lead_qry = $adb->query("
SELECT COUNT(DISTINCT CASE WHEN leadstatus = 'Appointment Booked' THEN vtiger_leaddetails.leadid END ) AS leadappointment,COUNT(leadid) AS uploadleadcount, campaignid, leadstatus FROM vtiger_leaddetails 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
LEFT JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
WHERE vtiger_crmentity.deleted = 0 GROUP BY campaignid" );		
  		if($adb->num_rows($lead_qry) > 0) {
			while($row = $adb->fetch_array($lead_qry)) {
				$campaignid = $row['campaignid'];
				$uploadleadcount = $row['uploadleadcount'];
				$leadappointment = $row['leadappointment'];
			$adb->query("UPDATE vtiger_campaignscf SET 
				total_uploded_lead = ".$uploadleadcount.", total_appointment_booked = ".$leadappointment."
						WHERE vtiger_campaignscf.campaignid = ".$campaignid." ");	
			}			
		}
		
		$this->updateTargetModuleFields($start_date, $end_date);
			
// End update Target module ****************************		
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
	/**
	 * Function to get Campaign related Accouts
	 * @param  integer   $id      - campaignid
	 * returns related Accounts record in array format
	 */
	function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		global $log, $singlepane_view,$currentModule;
		$log->debug("Entering get_accounts(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		global $current_user;
		if(getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus'=>'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus','readwrite') == '0')? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		// Send mail button for selected Accounts
		$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';
		/* To get Accounts CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$ahtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$ahtml .= $customviewcombo_html;
		$ahtml .= "</select>";
		/* To get Accounts CustomView -END */

		$button .= $ahtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if($actions)
		{
			if(is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes')
			{
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes')
			{
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_account.*,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.*, vtiger_crmentity.modifiedtime, vtiger_campaignrelstatus.*, vtiger_accountbillads.*
				FROM vtiger_account
				INNER JOIN vtiger_campaignaccountrel ON vtiger_campaignaccountrel.accountid = vtiger_account.accountid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
				LEFT JOIN vtiger_accountbillads ON vtiger_accountbillads.accountaddressid = vtiger_account.accountid
				LEFT JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignaccountrel.campaignrelstatusid
				WHERE vtiger_campaignaccountrel.campaignid = ".$id." AND vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null)
			$return_value = Array();
		else if($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Accounts');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Contacts
	 * @param  integer   $id      - campaignid
	 * returns related Contacts record in array format
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		global $current_user;
		if(getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus'=>'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus','readwrite') == '0')? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_contactdetails.accountid, vtiger_account.accountname,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name ,
				vtiger_contactdetails.contactid, vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.title,
				vtiger_contactdetails.department, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid,
				vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime, vtiger_campaignrelstatus.*
				FROM vtiger_contactdetails
				INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
				INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
				INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
				INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaigncontrel.campaignrelstatusid
				WHERE vtiger_campaigncontrel.campaignid = ".$id." AND vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null)
			$return_value = Array();
		else if($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Contacts');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Leads
	 * @param  integer   $id      - campaignid
	 * returns related Leads record in array format
	 */
	function get_leads($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view, $currentModule;
        $log->debug("Entering get_leads(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		global $current_user;
		if(getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus'=>'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed  = (getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus','readwrite') == '0')? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_leaddetails.*, vtiger_crmentity.crmid,vtiger_leadaddress.phone,vtiger_leadsubdetails.website,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.smownerid, vtiger_campaignrelstatus.*
				FROM vtiger_leaddetails
				INNER JOIN vtiger_campaignleadrel ON vtiger_campaignleadrel.leadid=vtiger_leaddetails.leadid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_leadsubdetails  ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leadsubdetails.leadsubscriptionid
				INNER JOIN vtiger_leadscf ON vtiger_leaddetails.leadid = vtiger_leadscf.leadid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignleadrel.campaignrelstatusid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_campaignleadrel.campaignid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null)
			$return_value = Array();
		else if($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Leads');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Potentials
	 * @param  integer   $id      - campaignid
	 * returns related potentials record in array format
	 */
	function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_opportunities(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module,$current_user->id,'campaignid', 'readwrite') == '0') {
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
					vtiger_potential.related_to, vtiger_account.accountname, vtiger_potential.potentialid, vtiger_potential.potentialname,
					vtiger_potential.potentialtype, vtiger_potential.sales_stage, vtiger_potential.amount, vtiger_potential.closingdate,
					vtiger_crmentity.crmid, vtiger_crmentity.smownerid FROM vtiger_campaign
					INNER JOIN vtiger_potential ON vtiger_campaign.campaignid = vtiger_potential.campaignid
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid
					INNER JOIN vtiger_potentialscf ON vtiger_potential.potentialid = vtiger_potentialscf.potentialid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_potential.related_to
					WHERE vtiger_campaign.campaignid = ".$id." AND vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Activities
	 * @param  integer   $id      - campaignid
	 * returns related activities record in array format
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
		$query = "SELECT vtiger_contactdetails.lastname,
			vtiger_contactdetails.firstname,
			vtiger_contactdetails.contactid,
			vtiger_activity.*,
			vtiger_seactivityrel.crmid as parent_id,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
			vtiger_crmentity.modifiedtime,
			CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
			vtiger_recurringevents.recurringtype
			FROM vtiger_activity
			INNER JOIN vtiger_seactivityrel
				ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid=vtiger_activity.activityid
			LEFT JOIN vtiger_cntactivityrel
				ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
			LEFT JOIN vtiger_contactdetails
				ON vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT OUTER JOIN vtiger_recurringevents
				ON vtiger_recurringevents.activityid = vtiger_activity.activityid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_seactivityrel.crmid=".$id."
			AND vtiger_crmentity.deleted = 0
			AND (activitytype = 'Task'
				OR activitytype !='Emails')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;

	}
	
		
	/*
	 * Function populate the status columns' HTML
	 * @param - $related_list return value from GetRelatedList
	 * @param - $status_column index of the status column in the list.
	 * returns true on success
	 */
	function add_status_popup($related_list, $status_column = 7, $related_module)
	{
		global $adb;

		if(!$this->campaignrelstatus)
		{
			$result = $adb->query('SELECT * FROM vtiger_campaignrelstatus;');
			while($row = $adb->fetchByAssoc($result))
			{
				$this->campaignrelstatus[$row['campaignrelstatus']] = $row;
			}
		}
		foreach($related_list['entries'] as $key => &$entry)
		{
			$popupitemshtml = '';
			foreach($this->campaignrelstatus as $campaingrelstatus)
			{
				$camprelstatus = getTranslatedString($campaingrelstatus[campaignrelstatus],'Campaigns');
				$popupitemshtml .= "<a onmouseover=\"javascript: showBlock('campaignstatus_popup_$key')\" href=\"javascript:updateCampaignRelationStatus('$related_module', '".$this->id."', '$key', '$campaingrelstatus[campaignrelstatusid]', '".addslashes($camprelstatus)."');\">$camprelstatus</a><br />";
			}
			$popuphtml = '<div onmouseover="javascript:clearTimeout(statusPopupTimer);" onmouseout="javascript:closeStatusPopup(\'campaignstatus_popup_'.$key.'\');" style="margin-top: -14px; width: 200px;" id="campaignstatus_popup_'.$key.'" class="calAction"><div style="background-color: #FFFFFF; padding: 8px;">'.$popupitemshtml.'</div></div>';

			$entry[$status_column] = "<a href=\"javascript: showBlock('campaignstatus_popup_$key');\">[+]</a> <span id='campaignstatus_$key'>".$entry[$status_column]."</span>".$popuphtml;
		}


		return $related_list;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner){
		$matrix = $queryplanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityCampaigns',array('vtiger_groupsCampaigns','vtiger_usersCampaignss','vtiger_lastModifiedByCampaigns','vtiger_campaignscf'));
        $matrix->setDependency('vtiger_Campaigns', array('vtiger_crmentityCampaigns','vtiger_productsCampaigns'));
		
		if (!$queryplanner->requireTable("vtiger_campaign",$matrix)){
			return '';
		}
		
		$query = $this->getRelationQuery($module,$secmodule,"vtiger_campaign","campaignid", $queryplanner);
		
		if ($queryplanner->requireTable("vtiger_crmentityCampaigns",$matrix)){
			$query .=" left join vtiger_crmentity as vtiger_crmentityCampaigns on vtiger_crmentityCampaigns.crmid=vtiger_campaign.campaignid and vtiger_crmentityCampaigns.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_productsCampaigns")){
			$query .=" 	left join vtiger_products as vtiger_productsCampaigns on vtiger_campaign.product_id = vtiger_productsCampaigns.productid";
		}
		if ($queryplanner->requireTable("vtiger_campaignscf")){
			$query .=" 	left join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_crmentityCampaigns.crmid";
		}
		if ($queryplanner->requireTable("vtiger_groupsCampaigns")){
			$query .=" left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersCampaigns")){
			$query .=" left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByCampaigns")){
			$query .=" left join vtiger_users as vtiger_lastModifiedByCampaigns on vtiger_lastModifiedByCampaigns.id = vtiger_crmentityCampaigns.modifiedby ";
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
			"Contacts" => array("vtiger_campaigncontrel"=>array("campaignid","contactid"),"vtiger_campaign"=>"campaignid"),
			"Leads" => array("vtiger_campaignleadrel"=>array("campaignid","leadid"),"vtiger_campaign"=>"campaignid"),
			"Accounts" => array("vtiger_campaignaccountrel"=>array("campaignid","accountid"),"vtiger_campaign"=>"campaignid"),
			"Potentials" => array("vtiger_potential"=>array("campaignid","potentialid"),"vtiger_campaign"=>"campaignid"),
			"Calendar" => array("vtiger_seactivityrel"=>array("crmid","activityid"),"vtiger_campaign"=>"campaignid"),
			"Documents" => array("vtiger_senotesrel"=>array("crmid","notesid"),"vtiger_campaign"=>"campaignid"),
			"Products" => array("vtiger_campaign"=>array("campaignid","product_id")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Leads') {
			$sql = 'DELETE FROM vtiger_campaignleadrel WHERE campaignid=? AND leadid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Contacts') {
			$sql = 'DELETE FROM vtiger_campaigncontrel WHERE campaignid=? AND contactid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM vtiger_campaignaccountrel WHERE campaignid=? AND accountid=?';
			$this->db->pquery($sql, array($id, $return_id));
			$sql = 'DELETE FROM vtiger_campaigncontrel WHERE campaignid=? AND contactid IN (SELECT contactid FROM vtiger_contactdetails WHERE accountid=?)';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if ($with_module == 'Leads') {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaignleadrel WHERE campaignid = ? AND leadid = ?',
												array($crmid, $with_crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO vtiger_campaignleadrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));

			} elseif($with_module == 'Contacts') {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaigncontrel WHERE campaignid = ? AND contactid = ?',
												array($crmid, $with_crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO vtiger_campaigncontrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));

			} elseif($with_module == 'Accounts') {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaignaccountrel WHERE campaignid = ? AND accountid = ?',
												array($crmid, $with_crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO vtiger_campaignaccountrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));

			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

}
?>