<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Vtiger_Edit_View extends Vtiger_Index_View {
    protected $record = false;
	function __construct() {
		parent::__construct();
	}
	
	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record);

		if(!$recordPermission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		
		global $adb,$current_user;
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		
		if($record != '' && $moduleName == 'Campaigns'){
			/* Query for fetching the Draft Campaigns Status by Jitendra Singh[TECHFOUR] */			
			$campapproval_query = $adb->query("select campaignstatus,expectedresponse from vtiger_campaign where campaignid = ".$record." ");
			if($adb->num_rows($campapproval_query) > 0) {
				$resultnum = $adb->fetch_array($campapproval_query);
				$campaignstatus = $resultnum['expectedresponse'];
				$approvalstatus = $resultnum['campaignstatus'];
			}
			/*End*/
			
			/* Query for fetching the User Details by Jitendra Singh[TECHFOUR] */
			$sub_query = " AND vtiger_users.id = ".$current_user->id." ";
			$userDetails = getUserDetails($sub_query);
			$depth = $userDetails['depth'];
			
			/*End*/
		}
								
        if(!empty($record) && $request->get('isDuplicate') == true) {
            $recordModel = $this->record?$this->record:Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('MODE', '');
        }else if(!empty($record)) {
            $recordModel = $this->record?$this->record:Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $viewer->assign('MODE', '');
        }
        if(!$this->record){
            $this->record = $recordModel;
        }
        
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);
		
		$viewer->assign('CURRENT_USER',  $current_user->id);

		foreach($requestFieldList as $fieldName=>$fieldValue){
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'time_start' && !empty($fieldValue)) { 
				$specialField = true; 
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i"); 
                
			}
            
            if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'date_start' && !empty($fieldValue)) { 
                $startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
                $startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue." ".$startTime);
                list($startDate, $startTime) = explode(' ', $startDateTime);
                $fieldValue = Vtiger_Date_UIType::getDisplayDateValue($startDate);
            }
			if($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		
		/*Start code to Assign Target Start Date and End Date of Target By default*/
			if($moduleName == 'Targets'){
			$curdate = date('Y-m-d');
			$start_date =  date('Y-m-01', strtotime($curdate));
			$end_date = date('Y-m-t', strtotime($curdate));
			list($year1,$month1,$day1) = split('-',$start_date);
			list($year2,$month2,$day2) = split('-',$end_date);
	
			if($current_user->date_format == 'yyyy-mm-dd'){
				$start_date = "".$year1."-".$month1."-".$day1."";
				$end_date = "".$year2."-".$month2."-".$day2."";
				}
			if($current_user->date_format == 'mm-dd-yyyy'){
				$start_date = "".$month1."-".$day1."-".$year1."";
				$end_date = "".$month2."-".$day2."-".$year2."";
				}
			if($current_user->date_format == 'dd-mm-yyyy'){
				$start_date = "".$day1."-".$month1."-".$year1."";
				$end_date = "".$day2."-".$month2."-".$year2."";
				}
			$viewer->assign('START_DATE', $start_date);
			$viewer->assign('END_DATE', $end_date);
			
			
			}
		/*End code to Select Target Start Date and End Date of Target By default*/
		
		//Code modified by jitendra singh[TECHFOUR]
		if($_REQUEST['record'] != '' && $moduleName == 'Campaigns'){
			$viewer->assign('CAMPAIGN_STATUS', $campaignstatus);
			$viewer->assign('APPROVAL_STATUS', $approvalstatus);
			$viewer->assign('DEPTH', $depth);
		}
		
		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}
		
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', vglobal('upload_maxsize')/1000000);
		$viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
		$viewer->view('EditView.tpl', $moduleName);
	}
}