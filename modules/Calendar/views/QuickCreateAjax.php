<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

	public function  process(Vtiger_Request $request) {
		
		global $current_user;
		global $adb;
		
		
		$moduleName = $request->getModule();
		
		$sub_query = " AND vtiger_users.id = ".$current_user->id." ";						
		$userDetails = getUserDetails($sub_query);
		$currentprofileid = $userDetails['profileid'];
		
		/*Start code to find the campign type of current lead*/
		$camp_query = $adb->query("select vendorname as campaign_type from vtiger_leadaddress
						inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_leadaddress.leadaddressid
						inner join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_leadaddress.campaignid
						inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_campaignscf.campaignid
						inner join vtiger_vendor on vtiger_vendor.vendorid = vtiger_campaign.campaigntype
						where vtiger_crmentity.deleted = 0 and leadaddressid = ".$_REQUEST['parent_id']."");
						if($adb->num_rows($camp_query) > 0) {
							$campdata = $adb->fetch_array($camp_query);	
							$campaign_type = $campdata['campaign_type']; 
						}
		/*Start code to find the campign type of current lead*/
		 

		$moduleList = array('Calendar','Events');

		$quickCreateContents = array();
		foreach($moduleList as $module) {
			$info = array();

			$recordModel = Vtiger_Record_Model::getCleanInstance($module);
			$moduleModel = $recordModel->getModule();

			$fieldList = $moduleModel->getFields();
			$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

			foreach($requestFieldList as $fieldName => $fieldValue) {
				$fieldModel = $fieldList[$fieldName];
				if($fieldModel->isEditable()) {
					$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
				}
			}

			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);

			$info['recordStructureModel'] = $recordStructureInstance;
			$info['recordStructure'] = $recordStructureInstance->getStructure();
			$info['moduleModel'] = $moduleModel;
			$quickCreateContents[$module] = $info;
		}
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUICK_CREATE_CONTENTS', $quickCreateContents);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('CURRENT_PROFILE', $currentprofileid); // Assign profile_id in activity module by jitendra singh on 15 feb 2014
		$viewer->assign('CAMPAIGN_TYPE', $campaign_type);
		

		$viewer->view('QuickCreate.tpl', $moduleName);
	}
}
