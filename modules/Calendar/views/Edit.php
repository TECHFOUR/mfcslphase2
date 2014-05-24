<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Calendar_Edit_View extends Vtiger_Edit_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('Events');
		$this->exposeMethod('Calendar');
	}

	function process(Vtiger_Request $request) {
		
		
		global $adb;
		global $current_user;
		
		$sub_query = " AND vtiger_users.id = ".$current_user->id." ";						
		$userDetails = getUserDetails($sub_query);
		$currentprofileid = $userDetails['profileid'];
		
		$mode = $request->getMode();

		$recordId = $request->get('record');
		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$mode = $recordModel->getType();
		}
		$viewer = $this->getViewer($request);
		
		if(!empty($recordId)){
			
						/*Start Query for find the activity Status by jitendra singh on 5 March 2014*/
			
						$activity_query = $adb->query("select cf_895
										from vtiger_activitycf
										INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_activitycf.activityid 
										where vtiger_crmentity.deleted = 0 and activityid = ".$recordId."");
										if($adb->num_rows($activity_query) > 0) {
											$activitydata = $adb->fetch_array($activity_query);	
											$activity_status = $activitydata['cf_895'];
										}
			
						/*Start Query for find the activity Status by jitendra singh on 5 March 2014*/
			
						
						// Query for lead details
						
						$lead_query = $adb->query("select  *,vtiger_leaddetails.leadid as 'lead_id',vtiger_outletmastercf.outlet as 'outletname',vtiger_assets.make as 'make1', 						                        vtiger_service.model as 'model1',vtiger_campaignscf.location as 'campaign_location',setype as 'main_module',vendorname as 'campaign_type',closingdate as 'camp_start_date',end_date as 'camp_end_date' 
						from vtiger_seactivityrel
						inner join vtiger_leaddetails on vtiger_leaddetails.leadid = vtiger_seactivityrel.crmid 
						INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
						inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid 
						inner join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leadaddress.leadaddressid
						inner join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_leadaddress.campaignid
						inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_campaignscf.campaignid
						inner join vtiger_vendor on vtiger_vendor.vendorid = vtiger_campaign.campaigntype
						inner join vtiger_outletmastercf on vtiger_outletmastercf.outletmasterid = vtiger_leaddetails.outlet 
						left join vtiger_assets on vtiger_assets.assetsid = vtiger_leadscf.make
						left join vtiger_service on vtiger_service.serviceid = vtiger_leadscf.model
						where vtiger_crmentity.deleted = 0 and activityid = ".$recordId."");
						if($adb->num_rows($lead_query) > 0) {
							$leaddata = $adb->fetch_array($lead_query);	
							$lead_id = $leaddata['lead_id'];
							$firstname = $leaddata['firstname'];
							$lastname = $leaddata['lastname'];
							$mobile = $leaddata['mobile'];
							$outletname = $leaddata['outletname'];
							$campaign_location = $leaddata['campaign_location'];
							$registrationno = $leaddata['registrationno'];
							$make1 = $leaddata['make1'];
							$model1 = $leaddata['model1'];
							$customertype = $leaddata['leadsource'];
							$main_module = $leaddata['main_module'];
							$lead_no = $leaddata['lead_no'];
							
							$camp_type = $leaddata['campaign_type'];
							$camp_start_date = $leaddata['camp_start_date'];
							$camp_end_date = $leaddata['camp_end_date'];
							
							
							
						}
						// End
												
						// Query for related lead details
						$rellead_query = $adb->query("select registrationno, vtiger_outletmastercf.outlet as 'outletname' from vtiger_leadaddress 
						INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_leadaddress.leadaddressid
						inner join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leadaddress.leadaddressid
						inner join vtiger_leaddetails on vtiger_leaddetails.leadid = vtiger_leadaddress.leadaddressid
						inner join vtiger_outletmastercf on vtiger_outletmastercf.outletmasterid = vtiger_leaddetails.outlet
						where vtiger_crmentity.deleted = 0 and mobile = '".$mobile."' and lead_no <> '".$lead_no."'");
						if($adb->num_rows($rellead_query) > 0) {
							$no_of_rellead = $adb->num_rows($rellead_query);
							$i = 0;
							foreach($rellead_query as $querydata){
								if($i == 0){
									$registrationno1 = $querydata['registrationno'];
									$outletname1 = $querydata['outletname'];
									$i++;	
									}
								else{
									$registrationno2 = $querydata['registrationno'];
									$outletname2 = $querydata['outletname'];
									}
							}
						}
						// End
						
						// Query for lead history
						
						
											
						$lead_history_query = $adb->query("select  activitytype,activitycrmentity.modifiedtime as 'activity_modifiedtime',eventstatus
						  	from vtiger_leaddetails 
							inner join vtiger_crmentity as leadcrmentity on leadcrmentity.crmid = vtiger_leaddetails.leadid
							inner join  vtiger_seactivityrel on  vtiger_seactivityrel.crmid =  vtiger_leaddetails.leadid
							inner join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_seactivityrel.activityid
							inner join vtiger_activity on vtiger_activity.activityid = vtiger_activitycf.activityid
							inner join vtiger_crmentity as activitycrmentity on activitycrmentity.crmid = vtiger_activity.activityid
						  	where leadcrmentity.deleted = 0 and activitycrmentity.deleted = 0 and leadid = ".$lead_id."  order by activity_modifiedtime desc");
						
						
						if($adb->num_rows($lead_history_query) > 0) {
							$i = 0;
							foreach($lead_history_query as $historydata){
								if($i == 0){
									$activitytype1 = $historydata['activitytype'];
									$modifiedtime1 = $historydata['activity_modifiedtime'];
									$eventstatus1 = $historydata['eventstatus'];
										
									}
								if($i == 1){
									$activitytype2 = $historydata['activitytype'];
									$modifiedtime2 = $historydata['activity_modifiedtime'];
									$eventstatus2 = $historydata['eventstatus'];
									}
								$i++;
							}
						}
						// End
						
						
						/*Assign the values of related value of Activity*/
							$viewer->assign('firstname', $firstname);
							$viewer->assign('lastname', $lastname);
							$viewer->assign('mobile', $mobile);
							$viewer->assign('campaign_location', $campaign_location);
							$viewer->assign('outletname', $outletname);
							$viewer->assign('registrationno', $registrationno);
							$viewer->assign('make1', $make1);
							$viewer->assign('model1', $model1);
							if($no_of_rellead == '')
							$viewer->assign('no_of_rellead', 'No Related Leads');
							else
							$viewer->assign('no_of_rellead', $no_of_rellead);
							
							$viewer->assign('registrationno1', $registrationno1);
							$viewer->assign('outletname1', $outletname1);
							$viewer->assign('registrationno2', $registrationno2);
							$viewer->assign('outletname2', $outletname2);
							
							
							$viewer->assign('activitytype1', $activitytype1);
							$viewer->assign('modifiedtime1', $modifiedtime1);
							$viewer->assign('eventstatus1', $eventstatus1);
							$viewer->assign('activitytype2', $activitytype2);
							$viewer->assign('modifiedtime2', $modifiedtime2);
							$viewer->assign('eventstatus2', $eventstatus2);
							
							$viewer->assign('customertype', $customertype);
							$viewer->assign('MAIN_MODULE', $main_module);
							
							
							$viewer->assign('camp_type', $camp_type);
							$viewer->assign('camp_start_date', $camp_start_date);
							$viewer->assign('camp_end_date', $camp_end_date);
							$viewer->assign('lead_no', $lead_no);
							$viewer->assign('lead_id', $lead_id);
						/*End*/
						$viewer->assign('activity_status', $activity_status);
						
					
	}

		$viewer->assign('CURRENT_PROFILE', $currentprofileid); // Assign profile_id in activity module by jitendra singh on 14 feb 2014
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request, $mode);
			return;
		}
		$this->Calendar($request, 'Calendar');
	}

	function Events($request, $moduleName) {
		
		
        $currentUser = Users_Record_Model::getCurrentUserModel();
        
		$viewer = $this->getViewer ($request);
		$record = $request->get('record');

		 if(!empty($record) && $request->get('isDuplicate') == true) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', '');
		}else if(!empty($record)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', 'edit');
			$viewer->assign('RECORD_ID', $record);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$viewer->assign('MODE', '');
		}
		$eventModule = Vtiger_Module_Model::getInstance($moduleName);
		$recordModel->setModuleFromInstance($eventModule);

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach($requestFieldList as $fieldName=>$fieldValue){
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if (empty($record) && ($fieldName == 'time_start' || $fieldName == 'time_end') && !empty($fieldValue)) { 
				$specialField = true; 
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i"); 
			} 
            if (empty($record) && ($fieldName == 'date_start' || $fieldName == 'due_date') && !empty($fieldValue)) { 
                if($fieldName == 'date_start'){
                    $startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
                    $startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue." ".$startTime);
                    list($startDate, $startTime) = explode(' ', $startDateTime);
                    $fieldValue = Vtiger_Date_UIType::getDisplayDateValue($startDate);
                }else{
                    $endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_end']);
                    $endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue." ".$endTime);
                    list($endDate, $endTime) = explode(' ', $endDateTime);
                    $fieldValue = Vtiger_Date_UIType::getDisplayDateValue($endDate);
                }
            }
            
			if($fieldModel->isEditable() || $specialField) { 
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel,
									Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);

		$viewMode = $request->get('view_mode');
		if(!empty($viewMode)) {
			$viewer->assign('VIEW_MODE', $viewMode);
		}
		
		
		
		
		$viewer->assign('RECURRING_INFORMATION', $recordModel->getRecurrenceInformation());
		$viewer->assign('TOMORROWDATE', Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d', time()+86400)));
		
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$existingRelatedContacts = $recordModel->getRelatedContactInfo();

		//To add contact ids that is there in the request . Happens in gotoFull form mode of quick create
		$requestContactIdValue = $request->get('contact_id');
		if(!empty($requestContactIdValue)) {
			$existingRelatedContacts[] = array('name' => Vtiger_Util_Helper::getRecordName($requestContactIdValue) ,'id' => $requestContactIdValue);
		}
		
        $viewer->assign('RELATED_CONTACTS', $existingRelatedContacts);

		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $accessibleUsers = $currentUser->getAccessibleUsers();
		
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
        $viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
        $viewer->assign('CURRENT_USER', $currentUser);

		$viewer->view('EditView.tpl', $moduleName);
	}

	function Calendar($request, $moduleName) {
		parent::process($request);
	}
}