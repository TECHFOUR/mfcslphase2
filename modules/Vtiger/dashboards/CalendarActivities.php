<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_CalendarActivities_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$page = $request->get('page');
		$linkId = $request->get('linkid');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', 10);

		$user = $request->get('type');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$calendarActivities = $moduleModel->getCalendarActivities('upcoming', $pagingModel, $user);

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		
		$sub_query1 = " AND vtiger_users.id = ".$currentUser->getId()." ";		
		$userDetails1 = getUserDetails($sub_query1);
		$depth = $userDetails1['depth'];
		$profileid = $userDetails1['profileid'];
		$profile_permission = $userDetails1['profile_permission'];
		
		$currentdate = date('m/d');
		$currentDate = date('M/d');
		$dayless1day = date('M/d',strtotime('- 1 days', strtotime($currentdate)));			
		$daygreater1day = date('M/d',strtotime('+ 1 days', strtotime($currentdate)));	
		$daygreater2day = date('M/d',strtotime('+ 2 days', strtotime($currentdate)));		
		
		$viewer->assign('dayless1day', $dayless1day);
		$viewer->assign('currentDate', $currentDate);
		$viewer->assign('daygreater1day', $daygreater1day);
		$viewer->assign('daygreater2day', $daygreater2day);
		
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $calendarActivities);
		$viewer->assign('PAGING', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('CURRENTUSERDEPTH', $depth);
		$viewer->assign('PROFILEID', $profileid);
		$viewer->assign('PROFILEID_PERMISSION', $profile_permission);
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CalendarActivities.tpl', $moduleName);
		}
	}
}