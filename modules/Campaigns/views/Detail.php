<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Detail_View extends Vtiger_Detail_View {

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getHeaderScripts(Vtiger_Request $request) {		
		global $adb;
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
				'modules.Vtiger.resources.List',
				"modules.$moduleName.resources.List",
				'modules.CustomView.resources.CustomView',
				"modules.$moduleName.resources.CustomView",
				"modules.Emails.resources.MassEdit",
		);
		
		// Code Added by jitendra singh on 24 Dec 2013
		$status_qry = $adb->query("SELECT campaignstatus,expectedresponse from vtiger_campaign inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid 
		where vtiger_crmentity.deleted = 0 AND campaignid = ".$_REQUEST['record']."");
		
		if($adb->num_rows($status_qry) > 0) {
			while($row = $adb->fetch_array($status_qry)) {			
				$approvalstatus = $row['campaignstatus'];
				$campaignstatus = $row['expectedresponse'];
			}
		}
		if($approvalstatus == 'Approved' &&  $campaignstatus == 'Held') // Modified on 24 Jan 2014 by jitendra
		$import = 'yes';
		else
		$import = 'no';
		
		$viewer = $this->getViewer($request);
		$viewer->assign('IMPORT_STATUS', $import);
		// End
		
		
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}