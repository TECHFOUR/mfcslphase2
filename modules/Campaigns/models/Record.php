<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get selected ids list of related module for send email
	 * @param <String> $relatedModuleName
	 * @param <array> $excludedIds
	 * @return <array> List of selected ids
	 */
	public function getSelectedIdsList($relatedModuleName, $excludedIds = false) {
		$db = PearDatabase::getInstance();

		switch($relatedModuleName) {
			case "Leads"		: $tableName = "vtiger_campaignleadrel";		$fieldName = "leadid";		break;
			case "Accounts"		: $tableName = "vtiger_campaignaccountrel";		$fieldName = "accountid";	break;
			case 'Contacts'		: $tableName = "vtiger_campaigncontrel";		$fieldName = "contactid";	break;
		}

		$query = "SELECT $fieldName FROM $tableName
					INNER JOIN vtiger_crmentity ON $tableName.$fieldName = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = ?
					WHERE campaignid = ?";
		if ($excludedIds) {
			$query .= " AND $fieldName NOT IN (". implode(',', $excludedIds) .")";
		}

		$result = $db->pquery($query, array(0, $this->getId()));
		$numOfRows = $db->num_rows($result);

		$selectedIdsList = array();
		for ($i=0; $i<$numOfRows; $i++) {
			$selectedIdsList[] = $db->query_result($result, $i, $fieldName);
		}
		return $selectedIdsList;
	}
	
	public function checkDuplicate() {
		/*global $current_user;
		$date_format = $current_user->date_format;
		$db = PearDatabase::getInstance();*/
		$assigned_to = $_REQUEST['assigned_to'];				
		$actualbudget = $_REQUEST['actualbudget'];		
		$actualtag = $_REQUEST['actualtag'];		
		$campaign_status = $_REQUEST['campaign_status'];		
		$actual_send_hub = $_REQUEST['actual_send_hub'];
		$actual_checkup = $_REQUEST['actual_checkup'];		
		$actual_leaflet = $_REQUEST['actual_leaflet'];
		$actual_poster = $_REQUEST['actual_poster'];		
			
		/*
		$query = "SELECT 1 FROM vtiger_targetscf 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_targetscf.targetsid
					WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentity.setype = ? AND salesperson = ? AND (start_date >= ? OR end_date <= ?) ";
		$params = array($this->getModule()->getName(), $assigned_to, $start_date, $end_date);

		$record = $this->getId();
		if ($record) {
			$query .= " AND crmid != ?";
			array_push($params, $record);
		}
						
		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return "outlet_level###Target for this sales person has already been assigned for this period.";																					
		}*/
		if($campaign_status == "Held" && ($actualbudget == "" || $actualtag == "" || $actual_send_hub == "" || $actual_checkup == "" || $actual_leaflet == "" || $actual_poster == ""))	
			return "outlet_level###All Actual fields must be filled";
				
		return false;
	}
}

