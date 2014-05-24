<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_Detail_View extends Vtiger_Index_View {

	protected $reportData;
	protected $calculationFields;

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId()) && !$reportModel->isEditable()) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	const REPORT_LIMIT = 1000;

	function preProcess(Vtiger_Request $request) {
		global $current_user, $adb;
		$dateFormat = $current_user->date_format;
		$current_user_id = $current_user->id;
		parent::preProcess($request);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$page = $request->get('page');

		$detailViewModel = Reports_DetailView_Model::getInstance($moduleName, $recordId);
		$reportModel = $detailViewModel->getRecord();
		$reportModel->setModule('Reports');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', self::REPORT_LIMIT);

		$this->reportData = $reportModel->getReportData($pagingModel);
		$this->calculationFields = $reportModel->getReportCalulationData();

		$primaryModule = $reportModel->getPrimaryModule();
		$primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
		$permission = $userPrivilegesModel->hasModulePermission($primaryModuleModel->getId());

		if(!$permission) {
			$viewer->assign('MODULE', $primaryModule);
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', $primaryModule);
			exit;
		}

		$detailViewLinks = $detailViewModel->getDetailViewLinks();
// added by Ajay
		$sub_query = " AND vtiger_users.id = ".$current_user->id." ";		
		$userDetails = getUserDetails($sub_query);
		$profileid = $userDetails['profileid'];
		$zone = $userDetails['zone'];
		
		$filter_permission = 0;
		if($profileid == 3 || $profileid == 5 || $profileid == 6) // agent level
			$filter_permission = 1;
		elseif($profileid == 4 || $profileid == 11 || $profileid == 13) // Mo level
			$filter_permission = 2;
		elseif($profileid == 2 || $profileid == 8 || $profileid == 9) // Manager level
			$filter_permission = 3;
			
		$fieldname1 = "outlet";
		$fieldname2 = "cf_781";					
		$query_outlet = "SELECT $fieldname1, $fieldname2 FROM vtiger_outletmaster 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_outletmaster.outletmasterid
					INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_outletmaster.outletmasterid
					WHERE vtiger_crmentity.deleted = 0 ";
		$sub_query = "AND outlet NOT IN('Regional Manager', 'Tele Caller', 'BD Corporate', 'RBDM', 'Business Manager') ORDER BY $fieldname1";
		if($filter_permission == 3)
			$sub_query1 = " AND $fieldname2 = '$zone' ";
		$query = $query_outlet." $sub_query1 ".$sub_query;		
		$outletvalues = $this->getAllQueryResult($query, $fieldname1);
				
		$sub_query = "GROUP BY $fieldname2 ORDER BY $fieldname2";
		$query = $query_outlet.' '.$sub_query;				
		$regionvalues = $this->getAllQueryResult($query, $fieldname2);
		
// end
//echo "<pre>";print_r($fieldPickListValues);die;
		// Advanced filter conditions
		$viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
		$viewer->assign('PRIMARY_MODULE', $primaryModule);
		$viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $reportModel->getPrimaryModuleRecordStructure());
		$viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $reportModel->getSecondaryModuleRecordStructure());
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Vtiger_Field_Model::getAdvancedFilterOpsByFieldType());
        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
        foreach($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'],$module);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }
        $viewer->assign('DATE_FILTERS', $dateFilters);
		$viewer->assign('LINEITEM_FIELD_IN_CALCULATION', $reportModel->showLineItemFieldsInFilter(false));
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('COUNT',count($this->reportData));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', $currentUser); // added by Ajay
		$viewer->assign('dateFormat', $dateFormat); // added by Ajay
		$viewer->assign('CURRENT_USER_ID', $current_user_id); // added by Ajay
		$viewer->assign('FieldOutLetValues', $outletvalues); // added by Ajay
		$viewer->assign('FieldReqionValues', $regionvalues); // added by Ajay 
		$viewer->assign('PROFILEID', $profileid); // added by Ajay
		$viewer->assign('FILTER_PERMISSION', $filter_permission); // added by Ajay
		$viewer->view('ReportHeader.tpl', $moduleName);
	}

	function getAllQueryResult($sSQL, $field) {
		global $adb;
		$sSQL = $adb->query($sSQL);
		if($adb->num_rows($sSQL) > 0) {
			$outlet_value = array();
			while($row = $adb->fetch_array($sSQL)) {
				$outlet_value[$row[$field]] = $row[$field];
			}											
		}
		return $outlet_value;	
	}
	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		echo $this->getReport($request);
	}

	function getReport(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->get('record');
		$page = $request->get('page');

		$data = $this->reportData;
		$calculation = $this->calculationFields;

		if(empty($data)){
			$reportModel = Reports_Record_Model::getInstanceById($record);
			$reportModel->setModule('Reports');

			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $page);
			$pagingModel->set('limit', self::REPORT_LIMIT+1);

			$data = $reportModel->getReportData($pagingModel);
			$calculation = $reportModel->getReportCalulationData();
		}

		$viewer->assign('CALCULATION_FIELDS',$calculation);
		$viewer->assign('DATA', $data);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE', $moduleName);

		if (count($data) > self::REPORT_LIMIT) {
			$viewer->assign('LIMIT_EXCEEDED', true);
		}

		$viewer->view('ReportContents.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.Detail',
			"modules.$moduleName.resources.Detail"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}
