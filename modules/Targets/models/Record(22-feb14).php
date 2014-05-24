<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Targets_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&contact_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&contact_id='.$this->getId();
	}

	/**
	 * Function to check duplicate exists or not
	 * @return <boolean>
	 */
	public function checkDuplicate() {							
		global $current_user;
		$userid = $current_user->id;
		$date_format = $current_user->date_format;
		$db = PearDatabase::getInstance();
		$assigned_to = $_REQUEST['assigned_to'];		
		$start_date = $_REQUEST['start_date'];
		$end_date = $_REQUEST['end_date'];		
		$start_date = DateTimeField::__convertToDBFormat($start_date, $date_format);
		$end_date = DateTimeField::__convertToDBFormat($end_date, $date_format);		
		// First day of the month.
		$start_date =  date('Y-m-01', strtotime($start_date));				
		// Last day of the month.
		$end_date = date('Y-m-t', strtotime($start_date));		
		
		$alltarget = $_REQUEST['alltarget'];
		$allrevenue = $_REQUEST['allrevenue'];
		$alltarget = explode("#",$alltarget);
		$allrevenue = explode("#",$allrevenue);
		$total_new_target = 0;
		$total_new_revenue = 0;
		$errorduplicate = 0;
		$usersid = array();
		$new_map_target = 0;
		$new_map_revenue = 0;
		foreach($alltarget as $row) {
			list($target, $uid) = explode("@",$row);
			$total_new_target = $total_new_target + $target;
			$usersid[] = $uid;
			if($userid == $uid) { // currentuser
				$new_map_target = $target;								
			}
		}	
		
		foreach($allrevenue as $row) {
			list($revenue, $uid) = explode("@",$row);
			$total_new_revenue = $total_new_revenue + $revenue;			
			if($userid == $uid) { // currentuser
				$new_map_revenue = $revenue;								
			}
		}	
			
		//return "outlet_level### $totaltarget $allrevenue Target for this sales person has already been assigned for this period.";
		if(count($usersid) != count(array_unique($usersid)))
			return "outlet_level### There is repeated Target for the same users.";
			
		$sub_query = " AND vtiger_users.id = ".$userid." ";		
		$userDetails = getUserDetails($sub_query);
		$profileid = $userDetails['profileid'];
		$zone = $userDetails['zone'];          
		// 10 Head BD Profile, 2 RBDM  Profile, 4 MO Profile 
		if($userid != 1) {
			if($profileid == 10) {				
				$custom_qry = " and vtiger_profile.profileid in(2,10) ";
				/*if(!in_array($userid, $usersid))	
					return "outlet_level### $userid Please assigned the Target for yourself.";*/
			}
			if($profileid == 2)
				$custom_qry = " and vtiger_profile.profileid in(2,4) AND vtiger_outletmastercf.cf_781 like '%".$zone."%' ";		
		}
		
		$query_insert = "SELECT salesperson, target, revenue FROM vtiger_targetscf
			INNER JOIN vtiger_targets ON vtiger_targets.targetsid = vtiger_targetscf.targetsid 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_targetscf.targetsid
			INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			INNER JOIN vtiger_user2role ON vtiger_users.id = vtiger_user2role.userid 
			INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid 
			INNER JOIN vtiger_role2profile ON vtiger_role2profile.roleid = vtiger_role.roleid 
			INNER JOIN vtiger_profile ON vtiger_profile.profileid = vtiger_role2profile.profileid 
			INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775        
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_users.status = 'Active' AND vtiger_crmentity.setype = 'Targets' 
			AND (start_date >= '".$start_date."' AND end_date <= '".$end_date."') ";
		$query_insert .= $custom_qry;
		$record = $this->getId();
		if ($record) {
			$query_insert .= " AND crmid != $record ";
		}
		$query_insert = $db->query($query_insert);
		if($db->num_rows($query_insert) > 0) {
			$all_old_target = 0;
			$all_old_revenue = 0;
			$all_old_users = array();;
			while($row = $db->fetch_array($query_insert)) {
				$all_old_target = $all_old_target + $row['target'];
				$all_old_revenue = $all_old_revenue + $row['revenue'];
				$all_old_users[] = $row['salesperson'];
				if($userid == $row['salesperson']) { // currentuser
					$map_target = $row['target'];
					$map_revenue = $row['revenue'];
					$errorduplicate = 1;
				}
			}
		}
		if(is_array($all_old_users)) {		
			$new_user_array = array_intersect($all_old_users, $usersid);
			if((!in_array($userid, $all_old_users) || !in_array($userid, $usersid)) && $record == "" && $profileid == 10)	
				return "outlet_level### Please assigned the Target for yourself.";								
		}
		
		if(count($new_user_array) > 0)
			return "outlet_level###Target for this sales person has already been assigned for this period.";
			
		$total_current_user_target = $map_target + $new_map_target;
		$total_current_user_revenue = $map_revenue + $new_map_revenue;
		
		$total_old_new_target = ($all_old_target - $map_target) + ($total_new_target - $new_map_target);
		$total_old_new_revenue = ($all_old_revenue - $map_revenue) + ($total_new_revenue - $new_map_revenue);
		if(!in_array($userid, $usersid) && $record == "" && $profileid == 10)	
				return "outlet_level### Please assigned the Target for yourself.";
								
		elseif($total_current_user_target < $total_old_new_target)
			return "outlet_level###UnAllocated No of ROs can not be more than given No of ROs.";
		elseif($total_current_user_revenue < $total_old_new_revenue)
			return "outlet_level###UnAllocated Revenue can not be more than Revenue.";
		
		return false;
	}

	/**
	 * Function to get List of Fields which are related from Contacts to Inventory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(
				array('parentField'=>'account_id', 'inventoryField'=>'account_id', 'defaultValue'=>''),

				//Billing Address Fields
				array('parentField'=>'mailingcity', 'inventoryField'=>'bill_city', 'defaultValue'=>''),
				array('parentField'=>'mailingstreet', 'inventoryField'=>'bill_street', 'defaultValue'=>''),
				array('parentField'=>'mailingstate', 'inventoryField'=>'bill_state', 'defaultValue'=>''),
				array('parentField'=>'mailingzip', 'inventoryField'=>'bill_code', 'defaultValue'=>''),
				array('parentField'=>'mailingcountry', 'inventoryField'=>'bill_country', 'defaultValue'=>''),
				array('parentField'=>'mailingpobox', 'inventoryField'=>'bill_pobox', 'defaultValue'=>''),

				//Shipping Address Fields
				array('parentField'=>'otherstreet', 'inventoryField'=>'ship_street', 'defaultValue'=>''),
				array('parentField'=>'othercity', 'inventoryField'=>'ship_city', 'defaultValue'=>''),
				array('parentField'=>'otherstate', 'inventoryField'=>'ship_state', 'defaultValue'=>''),
				array('parentField'=>'otherzip', 'inventoryField'=>'ship_code', 'defaultValue'=>''),
				array('parentField'=>'othercountry', 'inventoryField'=>'ship_country', 'defaultValue'=>''),
				array('parentField'=>'otherpobox', 'inventoryField'=>'ship_pobox', 'defaultValue'=>'')
		);
	}
	
	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails() {
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_crmentity.setype = 'Contacts Image' and vtiger_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($recordId));

			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageName = $db->query_result($result, 0, 'name');

			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = decode_html($imageName);

			//urlencode - added to handle special characters like #, %, etc.,
			$imageName = urlencode($imageName);

			$imageDetails[] = array(
					'id' => $imageId,
					'orgname' => $imageOriginalName,
					'path' => $imagePath.$imageId,
					'name' => $imageName
			);
		}
		return $imageDetails;
	}
}
