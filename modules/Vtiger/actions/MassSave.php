<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_MassSave_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		$moduleName = $request->getModule();		
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordModels = $this->getRecordModelsFromRequest($request);
		foreach($recordModels as $recordId => $recordModel) {
// Start added code by ajay [TECHFOUR] *****************************************			
			if($moduleName == "Leads") {				
					$assigned_user_id = $request->get('assigned_user_id');
					$activity_qry = $adb->query("SELECT activityid, smownerid, leadsource FROM vtiger_leaddetails 
												INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
												LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = vtiger_leaddetails.leadid 
												WHERE vtiger_leaddetails.leadid = $recordId " );		
					if($adb->num_rows($activity_qry) > 0) {
						while($row = $adb->fetch_array($activity_qry)) {
							$old_assigned_to = $row['smownerid'];
							$activityid = $row['activityid'];
							$customer_type = $row['leadsource'];
							//if($customer_type == "Individual")
								$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = $activityid ");
						}
					}
					//echo $old_assigned_to.'___'.$assigned_user_id.'___'.$recordId;die;
					//if($customer_type == "Individual")
						//$this->createNewActivity($old_assigned_to, $assigned_user_id, $recordId);				
			}
// End added code by ajay [TECHFOUR]	**************************************************		
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				$recordModel->save();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	function getRecordModelsFromRequest(Vtiger_Request $request) {			
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordIds = $this->getRecordsListFromRequest($request);
		$recordModels = array();

		$fieldModelList = $moduleModel->getFields();
		foreach($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$fieldValue = $request->get($fieldName, null);
				$fieldDataType = $fieldModel->getFieldDataType();
				if($fieldDataType == 'time'){
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if(isset($fieldValue) && $fieldValue != null) {
					if(!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				} else {
					$uiTypeModel = $fieldModel->getUITypeModel();
					$recordModel->set($fieldName, $uiTypeModel->getUserRequestValue($recordModel->get($fieldName)));
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}		
}
