<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');
require_once('modules/Emails/mail.php');

class CampaignApproval extends CRMEntity {
    var $db, $log; // Used in class functions of CRMEntity

    var $table_name = 'vtiger_campaignapproval';
    var $table_index= 'campaignapprovalid';
    var $column_fields = Array();

    /** Indicator if this is a custom module or standard module */
    var $IsCustomModule = true;

    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = Array('vtiger_campaignapprovalcf', 'campaignapprovalid');

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    var $tab_name = Array('vtiger_crmentity', 'vtiger_campaignapproval', 'vtiger_campaignapprovalcf');

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_campaignapproval'   => 'campaignapprovalid',
	    'vtiger_campaignapprovalcf' => 'campaignapprovalid');

    /**
     * Mandatory for Listing (Related listview)
     */
    var $list_fields = Array (
    /* Format: Field Label => Array(tablename, columnname) */
    // tablename should not have prefix 'vtiger_'									
		'Approval Status'=>Array('campaignapproval','cf_839'),	
		'Remark'=> Array('campaignapproval', 'remark'),
		'Campaign Approval No'=> Array('campaignapproval', 'campaignapproval'),
		'Assigned To' => Array('crmentity','smownerid')
    );
    var $list_fields_name = Array(
    /* Format: Field Label => fieldname */		
		'Approval Status'=> 'cf_839',
		'Remark'=>'remark',		
		'Campaign Approval No'=> 'campaignapproval',
		'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'campaignapproval';

	// For Popup listview and UI type support
	var $search_fields = Array(
	/* Format: Field Label => Array(tablename, columnname) */
	// tablename should not have prefix 'vtiger_'
		'Approval Status'=>Array('campaignapproval','cf_839'),	
		'Remark'=> Array('campaignapproval', 'remark'),
		'Campaign Approval No'=> Array('campaignapproval', 'campaignapproval'),
		'Assigned To' => Array('crmentity','smownerid')	
	);
	var $search_fields_name = Array(
	/* Format: Field Label => fieldname */
		'Approval Status'=> 'cf_839',
		'Remark'=>'remark',		
		'Campaign Approval No'=> 'campaignapproval',
		'Assigned To' => 'assigned_user_id'
	);

	// For Popup window record selection
	var $popup_fields = Array('campaignapproval');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'campaignapproval';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'campaignapproval';

	// Required Information for enabling Import feature
	var $required_fields = Array('campaignapproval'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'campaignapproval';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'campaignapproval');

	function __construct() {
	    global $log, $currentModule;
	    $this->column_fields = getColumnFields(get_class($this));
	    $this->db = PearDatabase::getInstance();
	    $this->log = $log;
	}

	function save_module($module) {
		global $adb,$log, $current_user;
		//echo "<pre>";print_r($_REQUEST);die;
		// 
		$log->debug("Entering function save_module ($module");
		$adb->pquery("update vtiger_crmentity  set smownerid = ? where crmid=? ",array($current_user->id,$this->id));
		$log->debug("Existing function save_module ($module");
		
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
  $currentid = $_REQUEST['currentid'];
  $sourceRecord = $_REQUEST['sourceRecord'];
  $approvalstatus = $_REQUEST['cf_839'];
  if($currentid != "" && $sourceRecord != "") {
	  
	  	$campaign_qry = $adb->pquery("select smownerid, location, end_date, closingdate,  vtiger_campaign.campaignid as campid, campaignname, targetsize, email3, 
		email5, email8 from vtiger_campaign 
		INNER JOIN vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
		INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid 
		where vtiger_campaign.campaignid = ?", array($sourceRecord));		
  		if($adb->num_rows($campaign_qry) > 0) {
			$campaignid = $adb->query_result($campaign_qry,0,'campid');
			$campaign_no = $adb->query_result($campaign_qry,0,'campaignname');
			$budgetcost = $adb->query_result($campaign_qry,0,'targetsize');
			$location = $adb->query_result($campaign_qry,0,'location');
			$start_date = $adb->query_result($campaign_qry,0,'closingdate');
			$end_date = $adb->query_result($campaign_qry,0,'end_date');
			$report_email3 = $adb->query_result($campaign_qry,0,'email3');
			$report_email5 = $adb->query_result($campaign_qry,0,'email5');
			$report_email8 = $adb->query_result($campaign_qry,0,'email8');
			$smownerid = $adb->query_result($campaign_qry,0,'smownerid');
			
		$cmatrix_qry = $adb->query("select app_authority from vtiger_camatrixcf inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_camatrixcf.camatrixid where min_budget <= ".$budgetcost." and max_budget >= ".$budgetcost." and vtiger_crmentity.deleted = 0 ");		
  		if($adb->num_rows($cmatrix_qry) > 0) {
			$cmatrix_res = $adb->fetch_array($cmatrix_qry);
			$app_authority = $cmatrix_res['app_authority'];
		}
				
			//$adb->pquery("update vtiger_leadaddress inner join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid = vtiger_leadaddress.leadaddressid set campaigntype = ?, campaignlocation = ? where leadaddressid=? ",array($campaigntype,$campaignlocation,$this->id));			
		}		
	
	$remarksdata = '';
	$remarks_flag = 0;
	$remarks_qry = $adb->query("select concat(vtiger_users.first_name,' ',vtiger_users.last_name) as 'approvefullname', remark, cf_839 from vtiger_campaignapproval 
inner join vtiger_crmentityrel on vtiger_crmentityrel.relcrmid = vtiger_campaignapproval.campaignapprovalid 
inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_crmentityrel.crmid 
inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaignapproval.campaignapprovalid 
inner join vtiger_users  on vtiger_users.id = vtiger_crmentity.smownerid	
where vtiger_crmentity.deleted = 0 and vtiger_campaign.campaignid = ".$sourceRecord." ");	
	if($adb->num_rows($remarks_qry) > 0) {			
		while($row = $adb->fetch_array($remarks_qry)) {
			$remarks_flag = 1;
			$fullname = $row['approvefullname'];
			$remark = $row['remark'];
			$approvestatus = $row['cf_839'];			
			$remarksdata .= '<tr><td>'.$fullname.'</td><td>'.$approvestatus.'</td><td>'.$remark.'</td></tr>';
		}		
	}	
	
	//echo 	$currentid.'___'.$sourceRecord.'___'.$approvalstatus.'___'.$app_authority;die;	
	$sub_query1 = " AND vtiger_users.id = ".$current_user->id." ";		
	$userDetails1 = getUserDetails($sub_query1);
	$email1 = $userDetails1['email'];
	$user_name1 = $userDetails1['user_name'];
	$depth1 = $userDetails1['depth'];
	$zone1 = $userDetails1['zone'];
	$rolename1 = $userDetails1['rolename'];
	$fullname1 = $userDetails1['fullname'];
	if($remarks_flag == 0)
		$remarksdata = '<tr><td>'.$fullname1.'</td><td>'.$approvalstatus.'</td><td>'.$_REQUEST['remark'].'</td></tr>';
	
	$role_Manager = "";	
		switch($app_authority) {
			
			case "Head BD":
				$depth_start = 2;						
			break;
												
			case "BD Corporate Office":
				$depth_start = 3;							
			break;
			
			case "RBDM":
				$depth_start = 5;							
			break;			
		}
			
		$allemails = "";			
		switch($depth1) {			
			case 2:
			$depth_next = 0;			
			$allemails =  $report_email8.', '.$report_email5.', '.$report_email3;					
			break;
												
			case 3:
			$depth_next = 2;
			$role_Manager = "Head Business Development";									
			$allemails =  $report_email8.', '.$report_email5;
			break;
			
			case 5:
			$depth_next = 3;			
			$allemails =  $report_email8;
			$role_Manager = "BD Corporate";
			break;
			
			default:			
			$depth_next = 5;			
			$role_Manager = "RBDM ".$zone1;
		}
						
		$sub_query2 = " AND vtiger_role.rolename = '".$role_Manager."' AND vtiger_role.depth = ".$depth_next." ";
		
		$permission_up_report = 0;
		if($depth1 == $depth_start)
			$permission_up_report = 1;			
			
		$userDetails2 = getUserDetails($sub_query2);
		$id2 = $userDetails2['id'];
		$email2 = $userDetails2['email'];
		$user_name2 = $userDetails2['user_name'];
		$depth2 = $userDetails2['depth'];
		$zone2 = $userDetails2['zone'];
		$rolename2 = $userDetails2['rolename'];
		$fullname2 = $userDetails2['fullname'];
		
		$emailstatus1 = "emailstatus".$depth1;
		$email_field1 = "email".$depth1;
		
		$emailstatus2 = "emailstatus".$depth_next;									
		$email_field2 = "email".$depth_next;
		
				
$urldataapprove = 'campaignid__'.$sourceRecord.'%*@userid__'.$id2.'%*@approvalstatus__Approve';
$urldatareject = 'campaignid__'.$sourceRecord.'%*@userid__'.$id2.'%*@approvalstatus__Reject';
$urldataapprove = base64_encode($urldataapprove);
$urldatareject = base64_encode($urldatareject);

$descriptionsalesapproval = '<table width="80%" cellpadding="5" cellspacing="1" border="0" align="left">
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left" width="50%">Dear Manager , </td></tr>
						<tr><td align="left" colspan="2">I Here by request you to verify and approve/Reject the same. </td></tr>
						<tr><td align="left" colspan="2">The Campaign Details are as follows: </td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><th align="left">Campaign number : </th><td>'.$campaign_no.'</td></tr>
						<tr><th align="left">Budget Cost : </th><td>'.$budgetcost.'</td></tr>
						<tr><th align="left">Location : </th><td>'.$location.'</td></tr>
						<tr><th align="left">Start Date : </th><td>'.$start_date.'</td></tr>
						<tr><th align="left">End Date : </th><td>'.$end_date.'</td></tr>
						
						<tr><th align="left">Approved By</th><th align="left">Approval Status</th>
						
						<th width="36%" align="left">Remarks</th></tr>
						[RemarksData]
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left" colspan="2">Please approve this campaign by clicking the below link <br><a href="[SITEURL]index.php?module=CampaignApproval&view=Edit&sourceModule=Campaigns&sourceRecord='.$sourceRecord.'&relationOperation=true&picklistDependency=[]&cf_839=&remark=&sourceModule=Campaigns&sourceRecord='.$sourceRecord.'&relationOperation=true">Click Here To Login</a><br></td></tr>
						
						<tr><th align="left">Approval URL : </th><td>Approve this campaign by clicking the below link <br><a href="[SITEURL]ApprovalCampaign.php?urldataapprove='.$urldataapprove.'">Click Here To Approve</a><br></td></tr>
						
						<tr><th align="left">Reject URL : </th><td>Reject this campaign by clicking the below link <br><a href="[SITEURL]ApprovalCampaign.php?urldatareject='.$urldatareject.'">Click Here To Reject</a><br></td></tr>
						<tr><td align="left">Thanks and Regards </td></tr>
						<tr><td align="left">'.$fullname1.'</td></tr>
						</table>';
						
$descriptionsalesapproved = '<table width="80%" cellpadding="5" cellspacing="1" border="0" align="left">
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left" width="50%">Dear Manager ('.$fullname1.')</td></tr>
						<tr><td align="left" colspan="2">Your below Campaign is approved. </td></tr>						
						<tr><td>&nbsp;</td></tr>
						<tr><th align="left">Campaign number : </th><td>'.$campaign_no.'</td></tr>
						<tr><th align="left">Budget Cost : </th><td>'.$budgetcost.'</td></tr>
						<tr><th align="left">Location : </th><td>'.$location.'</td></tr>
						<tr><th align="left">Start Date : </th><td>'.$start_date.'</td></tr>
						<tr><th align="left">End Date : </th><td>'.$end_date.'</td></tr>
						<tr><th align="left">Approved By</th><th align="left">Approval Status</th>
						
						<th width="36%" align="left">Remarks</th></tr>

						[RemarksData]
						<tr><td>&nbsp;</td></tr>						
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left">Thanks and Regards </td></tr>
						<tr><td align="left">'.$fullname1.'</td></tr>
						</table>';
						
			
	
		
//$site_URL = "http://mfcscrm.innov.co.in/";
								
$site_URL = "http://mfcslcrm.com/";									
$descriptionsalesapproval = str_replace("[SITEURL]",$site_URL,$descriptionsalesapproval);
$descriptionsalesapproval = str_replace("[RemarksData]",$remarksdata,$descriptionsalesapproval);

$descriptionsalesapproved = str_replace("[RemarksData]",$remarksdata,$descriptionsalesapproved);
		
		
		
		
		$cc = "";		
		if($allemails != "") {
			$allemails_alert = str_replace(',','\n',$allemails);											
		}				
		if($allemails != "")
			$cc  = $allemails;
			
		if($approvalstatus == "Approve") {
			$descriptions = $descriptionsalesapproval;
			$subject = "Campaign Approval Request for ".$fullname2;								
			$adb->query("UPDATE vtiger_campaign 
			INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid  
			SET approval_stage = ".$id2.", ".$emailstatus1." = '1', ".$emailstatus2." = '0', ".$email_field1." = '".$email1."' where vtiger_campaign.campaignid = ".$sourceRecord." ");
			$approvalmailalert = "Approval Campaign mail has been sent to this email id  $email2";
			if($permission_up_report == 1) {
				$descriptions = $descriptionsalesapproved;
				$subject = "Campaign Approved Request for ".$fullname1;
				$email2 = $email1;				
				$approveddate = date('Y-m-d');				
				$adb->query("UPDATE vtiger_campaign 
				INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid 
				SET approveddate = '".$approveddate."', campaignstatus = 'Approved', approval_stage = '' , emailstatus8 = '1', emailstatus5 = '1', emailstatus3 = '1', emailstatus2 = '1' where vtiger_campaign.campaignid = ".$sourceRecord." ");
				$approvalmailalert = "Approved Campaign mail has been sent to this email id  $allemails_alert";
			}
		}
		else {
			$approvalmailalert = "Approval Campaign Canceled mail has been sent to this email id  $allemails_alert";
			$adb->query("UPDATE vtiger_campaign 
			INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid 
			SET campaignstatus = 'Rejected', approval_stage = ".$smownerid." , emailstatus8 = '0', emailstatus5 = '1', emailstatus3 = '1', emailstatus2 = '1',  ".$email_field1." = '".$email1."' where vtiger_campaign.campaignid = ".$sourceRecord." ");
			$descriptions = $descriptionsalesapproved;
			if($permission_up_report == 1) {
				$email2 = $email1;
			}			
			$subject = "Approval Campaign Canceled By ".$fullname1;
		}
		

		//$reportemail = 'ajayk@techfoursolutions.com';
		//echo $email2.'___'.$user_name1.'___'.$subject.'___'.$cc;	die;
		$_SESSION['approvalmailalert_GLOBAL$#@&'] = $approvalmailalert;					
		send_mail('',$email2,$user_name1,'',$subject,$descriptions,$cc);
 }
		
// End for Approval Campaign 		

	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord) {
		// $srcrecord could be empty
	}

	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	function getListQuery($module, $usewhere='') {
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

		// Keep track of tables joined to avoid duplicates
		$joinedTables = array();

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		$joinedTables[] = $this->table_name;
		$joinedTables[] = 'vtiger_crmentity';

		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
			$joinedTables[] = $this->customFieldTable[0];
		}
		$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$joinedTables[] = 'vtiger_users';
		$joinedTables[] = 'vtiger_groups';

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other =  CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			if(!in_array($other->table_name, $joinedTables)) {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
				$joinedTables[] = $other->table_name;
			}
		}

		global $current_user;
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE vtiger_crmentity.deleted = 0 ".$usewhere;
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	function getListViewSecurityParameter($module) {
		global $current_user;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		$sec_query = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					)
					OR
						(";

					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$sec_query .= " vtiger_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		global $current_user;

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('Project', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, vtiger_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
					" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") AS temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";

		return $query;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			global $adb;

			include_once('vtlib/Vtiger/Module.php');
			$moduleInstance = Vtiger_Module::getInstance($modulename);
			$projectsResult = $adb->pquery('SELECT tabid FROM vtiger_tab WHERE name=?', array('Project'));
			$projectTabid = $adb->query_result($projectsResult, 0, 'tabid');

			// Mark the module as Standard module
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($modulename));

			// Add module to Customer portal
			if(getTabid('CustomerPortal') && $projectTabid) {
				$checkAlreadyExists = $adb->pquery('SELECT 1 FROM vtiger_customerportal_tabs WHERE tabid=?', array($projectTabid));
				if($checkAlreadyExists && $adb->num_rows($checkAlreadyExists) < 1) {
					$maxSequenceQuery = $adb->query("SELECT max(sequence) as maxsequence FROM vtiger_customerportal_tabs");
					$maxSequence = $adb->query_result($maxSequenceQuery, 0, 'maxsequence');
					$nextSequence = $maxSequence+1;
					$adb->query("INSERT INTO vtiger_customerportal_tabs(tabid,visible,sequence) VALUES ($projectTabid,1,$nextSequence)");
					$adb->query("INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES ($projectTabid,'showrelatedinfo',1)");
				}
			}

			// Add Gnatt chart to the related list of the module
			$relation_id = $adb->getUniqueID('vtiger_relatedlists');
			$max_sequence = 0;
			$result = $adb->query("SELECT max(sequence) as maxsequence FROM vtiger_relatedlists WHERE tabid=$projectTabid");
			if($adb->num_rows($result)) $max_sequence = $adb->query_result($result, 0, 'maxsequence');
			$sequence = $max_sequence+1;
			$adb->pquery("INSERT INTO vtiger_relatedlists(relation_id,tabid,related_tabid,name,sequence,label,presence) VALUES(?,?,?,?,?,?,?)",
						array($relation_id,$projectTabid,0,'get_gantt_chart',$sequence,'Charts',0));

			// Add Project module to the related list of Accounts module
			$accountsModuleInstance = Vtiger_Module::getInstance('Accounts');
			$accountsModuleInstance->setRelatedList($moduleInstance, 'Projects', Array('ADD','SELECT'), 'get_dependents_list');

			// Add Project module to the related list of Accounts module
			$contactsModuleInstance = Vtiger_Module::getInstance('Contacts');
			$contactsModuleInstance->setRelatedList($moduleInstance, 'Projects', Array('ADD','SELECT'), 'get_dependents_list');

			// Add Project module to the related list of HelpDesk module
			$helpDeskModuleInstance = Vtiger_Module::getInstance('HelpDesk');
			$helpDeskModuleInstance->setRelatedList($moduleInstance, 'Projects', Array('SELECT'), 'get_related_list');

			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if(class_exists('ModComments')) ModComments::addWidgetTo(array('Project'));
			}

			$result = $adb->pquery("SELECT 1 FROM vtiger_modentity_num WHERE semodule = ? AND active = 1", array($modulename));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO vtiger_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("vtiger_modentity_num"), $modulename, 'PROJ', 1, 1, 1));
			}

		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			global $adb;

			$projectsResult = $adb->pquery('SELECT tabid FROM vtiger_tab WHERE name=?', array('Project'));
			$projectTabid = $adb->query_result($projectsResult, 0, 'tabid');

			// Add Gnatt chart to the related list of the module
			$relation_id = $adb->getUniqueID('vtiger_relatedlists');
			$max_sequence = 0;
			$result = $adb->query("SELECT max(sequence) as maxsequence FROM vtiger_relatedlists WHERE tabid=$projectTabid");
			if($adb->num_rows($result)) $max_sequence = $adb->query_result($result, 0, 'maxsequence');
			$sequence = $max_sequence+1;
			$adb->pquery("INSERT INTO vtiger_relatedlists(relation_id,tabid,related_tabid,name,sequence,label,presence) VALUES(?,?,?,?,?,?,?)",
						array($relation_id,$projectTabid,0,'get_gantt_chart',$sequence,'Charts',0));

			// Add Comments widget to Project module
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if(class_exists('ModComments')) ModComments::addWidgetTo(array('Project'));
			}

			$result = $adb->pquery("SELECT 1 FROM vtiger_modentity_num WHERE semodule = ? AND active = 1", array($modulename));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO vtiger_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("vtiger_modentity_num"), $modulename, 'PROJ', 1, 1, 1));
			}
		}
	}

	static function registerLinks() {

	}

    /**
     * Here we override the parent's method,
     * This is done because the related lists for this module use a custom query
     * that queries the child module's table (column of the uitype10 field)
     *
     * @see data/CRMEntity#save_related_module($module, $crmid, $with_module, $with_crmid)
     */
    //function save_related_module($module, $crmid, $with_module, $with_crmid) {    }

    /**
     * Here we override the parent's method
     * This is done because the related lists for this module use a custom query
     * that queries the child module's table (column of the uitype10 field)
     *
     * @see data/CRMEntity#delete_related_module($module, $crmid, $with_module, $with_crmid)
     */
    function delete_related_module($module, $crmid, $with_module, $with_crmid) {
         if (!in_array($with_module, array('ProjectMilestone', 'ProjectTask'))) {
             parent::delete_related_module($module, $crmid, $with_module, $with_crmid);
             return;
         }
        $destinationModule = vtlib_purify($_REQUEST['destination_module']);
		if(empty($destinationModule)) $destinationModule = $with_module;
        if (!is_array($with_crmid)) $with_crmid = Array($with_crmid);
        foreach($with_crmid as $relcrmid) {
            $child = CRMEntity::getInstance($destinationModule);
            $child->retrieve_entity_info($relcrmid, $destinationModule);
            $child->mode='edit';
            $child->column_fields['projectid']='';
            $child->save($destinationModule,$relcrmid);
        }
    }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }


	function get_gantt_chart($id, $cur_tab_id, $rel_tab_id, $actions=false){
		require_once("BURAK_Gantt.class.php");

		$headers = array();
		$headers[0] = getTranslatedString('LBL_PROGRESS_CHART');

		$entries = array();

        global $adb,$tmp_dir,$default_charset;
        $record = $id;
		$g = new BURAK_Gantt();
		// set grid type
		$g->setGrid(1);
		// set Gantt colors
		$g->setColor("group","000000");
		$g->setColor("progress","660000");

		$related_projecttasks = $adb->pquery("SELECT pt.* FROM vtiger_projecttask AS pt
												INNER JOIN vtiger_crmentity AS crment ON pt.projecttaskid=crment.crmid
												WHERE projectid=? AND crment.deleted=0 AND pt.startdate IS NOT NULL AND pt.enddate IS NOT NULL",
										array($record)) or die("Please install the ProjectMilestone and ProjectTasks modules first.");

		while($rec_related_projecttasks = $adb->fetchByAssoc($related_projecttasks)){

			if($rec_related_projecttasks['projecttaskprogress']=="--none--"){
				$percentage = 0;
			} else {
				$percentage = str_replace("%","",$rec_related_projecttasks['projecttaskprogress']);
			}

            $rec_related_projecttasks['projecttaskname'] = iconv($default_charset, "ISO-8859-2//TRANSLIT",$rec_related_projecttasks['projecttaskname']);
			$g->addTask($rec_related_projecttasks['projecttaskid'],$rec_related_projecttasks['startdate'],$rec_related_projecttasks['enddate'],$percentage,$rec_related_projecttasks['projecttaskname']);
		}


		$related_projectmilestones = $adb->pquery("SELECT pm.* FROM vtiger_projectmilestone AS pm
													INNER JOIN vtiger_crmentity AS crment on pm.projectmilestoneid=crment.crmid
													WHERE projectid=? and crment.deleted=0",
											array($record)) or die("Please install the ProjectMilestone and ProjectTasks modules first.");

		while($rec_related_projectmilestones = $adb->fetchByAssoc($related_projectmilestones)){
            $rec_related_projectmilestones['projectmilestonename'] = iconv($default_charset, "ISO-8859-2//TRANSLIT",$rec_related_projectmilestones['projectmilestonename']);
            $g->addMilestone($rec_related_projectmilestones['projectmilestoneid'],$rec_related_projectmilestones['projectmilestonedate'],$rec_related_projectmilestones['projectmilestonename']);
		}

		$g->outputGantt($tmp_dir."diagram_".$record.".jpg","100");

		$origin = $tmp_dir."diagram_".$record.".jpg";
		$destination = $tmp_dir."pic_diagram_".$record.".jpg";

		$imagesize = getimagesize($origin);
		$actualWidth = $imagesize[0];
		$actualHeight = $imagesize[1];

		$size = 1000;
		if($actualWidth > $size){
			$width = $size;
			$height = ($actualHeight * $size) / $actualWidth;
			copy($origin,$destination);
			$id_origin = imagecreatefromjpeg($destination);
			$id_destination = imagecreate($width, $height);
			imagecopyresized($id_destination, $id_origin, 0, 0, 0, 0, $width, $height, $actualWidth, $actualHeight);
			imagejpeg($id_destination,$destination);
			imagedestroy($id_origin);
			imagedestroy($id_destination);

			$image = $destination;
		} else {
			$image = $origin;
		}

		$fullGanttChartImageUrl = $tmp_dir."diagram_".$record.".jpg";
		$thumbGanttChartImageUrl = $image;
		$entries[0] = array("<a href='$fullGanttChartImageUrl' border='0' target='_blank'><img src='$thumbGanttChartImageUrl' border='0'></a>");

		return array('header'=> $headers, 'entries'=> $entries);
	}

	/** Function to unlink an entity with given Id from another entity */
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log, $currentModule;

		if($return_module == 'Accounts') {
			$focus = new $return_module;
			$entityIds = $focus->getRelatedContactsIds($return_id);
			array_push($entityIds, $return_id);
			$entityIds = implode(',', $entityIds);
			$return_modules = "'Accounts','Contacts'";
		} else {
			$entityIds = $return_id;
			$return_modules = "'".$return_module."'";
		}

		$query = 'DELETE FROM vtiger_crmentityrel WHERE (relcrmid='.$id.' AND module IN ('.$return_modules.') AND crmid IN ('.$entityIds.')) OR (crmid='.$id.' AND relmodule IN ('.$return_modules.') AND relcrmid IN ('.$entityIds.'))';
		$this->db->pquery($query, array());

		$sql = 'SELECT tabid, tablename, columnname FROM vtiger_field WHERE fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module=? AND relmodule IN ('.$return_modules.'))';
		$fieldRes = $this->db->pquery($sql, array($currentModule));
		$numOfFields = $this->db->num_rows($fieldRes);

		for ($i = 0; $i < $numOfFields; $i++) {
			$tabId = $this->db->query_result($fieldRes, $i, 'tabid');
			$tableName = $this->db->query_result($fieldRes, $i, 'tablename');
			$columnName = $this->db->query_result($fieldRes, $i, 'columnname');
			$relatedModule = vtlib_getModuleNameById($tabId);
			$focusObj = CRMEntity::getInstance($relatedModule);

			$updateQuery = "UPDATE $tableName SET $columnName=? WHERE $columnName IN ($entityIds) AND $focusObj->table_index=?";
			$updateParams = array(null, $id);
			$this->db->pquery($updateQuery, $updateParams);
		}
	}

}
?>