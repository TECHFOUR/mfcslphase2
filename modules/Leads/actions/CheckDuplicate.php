<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_CheckDuplicate_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$registrationno = $request->get('registrationno');
		$record = $request->get('record');

		if ($record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}

		$recordModel->set('registrationno', $registrationno);		
				
		$response_result = $recordModel->checkDuplicate();
		$response_result_split = explode("###",$response_result);
		
		if($response_result_split[0] == "outlet_level") {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DUPLICATES_EXIST', $moduleName),'alert'=>$response_result_split[1],'outlet_flag'=>1);
		}
			
		else if(is_string($response_result) && $response_result_split[0] == "outlet_level_except_InBound") {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DUPLICATES_EXIST', $moduleName),'alert'=>$response_result_split[1],'outlet_flag'=>2);
		}
		else if(is_string($response_result) && $response_result_split[0] != "outlet_level") {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DUPLICATES_EXIST', $moduleName),'alert'=>$recordModel->checkDuplicate(),'outlet_flag'=>0);
		}
		else
			$result = array('success'=>false);
						
		$response = new Vtiger_Response();
		$response->setResult($result);		
		$response->emit();
	}
}