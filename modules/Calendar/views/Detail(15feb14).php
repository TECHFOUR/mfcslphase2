<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_Detail_View extends Vtiger_Detail_View {
	

	function preProcess(Vtiger_Request $request, $display=true) {
		
		global $adb;
		parent::preProcess($request, false);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
        if(!empty($recordId)){
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $activityType = $recordModel->getType();
            if($activityType == 'Events')
                $moduleName = 'Events';
				
			if(!empty($recordId)){
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
						where vtiger_crmentity.deleted = 0 and mobile = '".$mobile."'and lead_no <> '".$lead_no."'");
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
									$i++;	
									}
								else{
									$activitytype2 = $historydata['activitytype'];
									$modifiedtime2 = $historydata['activity_modifiedtime'];
									$eventstatus2 = $historydata['eventstatus'];
									}
							}
						}
						// End
						
					
			}
				
				
        }
		$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		$recordModel = $detailViewModel->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$summaryInfo = array();
		// Take first block information as summary information
		$stucturedValues = $recordStrucure->getStructure();
		foreach($stucturedValues as $blockLabel=>$fieldList) {
			$summaryInfo[$blockLabel] = $fieldList;
			break;
		}

		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
		$detailViewLinks = $detailViewModel->getDetailViewLinks($detailViewLinkParams);
		$navigationInfo = ListViewSession::getListViewNavigation($recordId);

		$viewer = $this->getViewer($request);
		
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
		
		$viewer->assign('camp_type', $camp_type);
		$viewer->assign('camp_start_date', $camp_start_date);
		$viewer->assign('camp_end_date', $camp_end_date);
		
		$viewer->assign('MAIN_MODULE', $main_module);
		
		$viewer->assign('lead_no', $lead_no);
		$viewer->assign('lead_id', $lead_id);
									
									
		
		/*End*/
		
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('NAVIGATION', $navigationInfo);

		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach($navigationInfo as $page=>$pageInfo) {
				foreach($pageInfo as $index=>$record) {
					//If record found then next record in the interation
					//will be next record
					if($found) {
						$nextRecordId = $record;
						break;
					}
					if($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if(!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if($found && !empty($nextRecordId)) {
					break;
				}
			}
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if(!empty($prevRecordId)) {
			$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		}
		if(!empty($nextRecordId)) {
			$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		}

		$viewer->assign('MODULE_MODEL', $detailViewModel->getModule());
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		$viewer->assign('IS_EDITABLE', $detailViewModel->getRecord()->isEditable($moduleName));
		$viewer->assign('IS_DELETABLE', $detailViewModel->getRecord()->isDeletable($moduleName));

        $linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
		$linkModels = $detailViewModel->getSideBarLinks($linkParams);
		if($_SESSION['authenticated_user_id'] == 1)
       	 $viewer->assign('QUICK_LINKS', $linkModels);
		$viewer->assign('NO_SUMMARY', true);

		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * Function shows the entire detail for the record
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

        if(!empty($recordId)){
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $activityType = $recordModel->getType();
            if($activityType == 'Events')
                $moduleName = 'Events';
        }

		$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		$recordModel = $detailViewModel->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();
		$moduleModel = $recordModel->getModule();

        if ($moduleName == 'Events'){
            $relatedContacts = $recordModel->getRelatedContactInfo();
            foreach($relatedContacts as $index=>$contactInfo) {
                $contactRecordModel = Vtiger_Record_Model::getCleanInstance('Contacts');
                $contactRecordModel->setId($contactInfo['id']);
                $contactInfo['_model'] = $contactRecordModel;
                $relatedContacts[$index] = $contactInfo;
            }
        }else{
            $relatedContacts = array();
        }

		
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStrucure);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('RELATED_CONTACTS', $relatedContacts);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('RECURRING_INFORMATION', $recordModel->getRecurringDetails());

        if($moduleName=='Events') {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $accessibleUsers = $currentUser->getAccessibleUsers();
            $viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
            $viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
        }

		return $viewer->view('DetailViewFullContents.tpl',$moduleName,true);
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	function showModuleBasicView($request) {
		return $this->showModuleDetailView($request);
	}

	/**
	 * Function to get Ajax is enabled or not
	 * @param Vtiger_Record_Model record model
	 * @return <boolean> true/false
	 */
	function isAjaxEnabled($recordModel) {
		return false;
	}

}
