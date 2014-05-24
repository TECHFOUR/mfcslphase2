<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_RelatedList_View extends Vtiger_Index_View {
	function process(Vtiger_Request $request) {
		global $adb;
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$requestedPage = $request->get('page');
		if(empty($requestedPage)) {
			$requestedPage = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',$requestedPage);

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'icon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'icon-chevron-up';
		}
		if(!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder',$sortOrder);
		}
		
		// Start code to find Customer type (If Individual then Add Activity button will not show in Summary of Leads) by jitendra singh on 3 Feb 2014
		
		$lead_query = $adb->query("select leadsource from vtiger_leaddetails 		
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_leaddetails.leadid 						
			where vtiger_crmentity.deleted = 0 and leadid = ".$_REQUEST['record']." ");							
			if($adb->num_rows($lead_query) > 0) {
				$lead_res = $adb->fetch_array($lead_query);
				$customer_type = $lead_res['leadsource'];
				
			}
		// End code to find Customer type (If Individual then Add Activity button will not show in Summary of Leads) by jitendra singh on 3 Feb 2014
		
		
		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);
		$relationModel = $relationListView->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		
		$viewer->assign('CUSTOMER_TYPE', $customer_type);

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
			$viewer->assign('PERFORMANCE', true);
		}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);

		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
						
// Start added code by Ajay [TECHFOUR]
		if($relatedModuleName == "CampaignApproval" && $_SESSION['authenticated_user_id'] != 1) {
			$viewer->assign('IS_EDITABLE', 0);
			$viewer->assign('IS_DELETABLE',0);
		}
		if($relatedModuleName == "Driver" && $_SESSION['authenticated_user_id'] != 1)			
			$viewer->assign('IS_DELETABLE',1);
			
		if(($relatedModuleName == "Calendar" && $_REQUEST['tab_label'] != "Activities") && $_SESSION['authenticated_user_id'] != 1) {
			$viewer->assign('IS_EDITABLE', 0);
			$viewer->assign('IS_DELETABLE',0);
		}
// End added code by Ajay [TECHFOUR]
		$viewer->assign('VIEW', $request->get('view'));

		return $viewer->view('RelatedList.tpl', $moduleName, 'true');
	}
}