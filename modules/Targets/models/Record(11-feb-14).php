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
		$query = "SELECT 1 FROM vtiger_targetscf 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_targetscf.targetsid
					WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentity.setype = ? AND salesperson = ? AND (start_date >= ? AND end_date <= ?) ";
		$params = array($this->getModule()->getName(), $assigned_to, $start_date, $end_date);

		$record = $this->getId();
		if ($record) {
			$query .= " AND crmid != ?";
			array_push($params, $record);
		}
						
		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return "outlet_level###Target for this sales person has already been assigned for this period.";																					
		}		
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
