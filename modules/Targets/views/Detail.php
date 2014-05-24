<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Targets_Detail_View extends Accounts_Detail_View {

	function __construct() {
		$this->linkTargetToCampaign($_REQUEST['record']);		
		parent::__construct();
	}

	public function showModuleDetailView(Vtiger_Request $request) {		
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}
	
	function linkTargetToCampaign($record) {
		global $adb,$current_user;
		
		$target_qry = $adb->query("SELECT start_date FROM vtiger_targetscf
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_targetscf.targetsid
WHERE vtiger_crmentity.deleted = 0 AND targetsid = $record " );		
  		if($adb->num_rows($target_qry) > 0) {
			$row = $adb->fetch_array($target_qry);
			$startingdate = $row['start_date'];				
		}
		
		// First day of the month.
		$start_date =  date('Y-m-01', strtotime($startingdate));				
		// Last day of the month.
		$end_date = date('Y-m-t', strtotime($startingdate));
		
		//echo $start_date.'___'.$end_date;die;	
			$count = 0;
		$campaign_qry = $adb->query("SELECT vtiger_campaign.campaignid as campid FROM vtiger_campaign
INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid		
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
WHERE vtiger_crmentity.deleted = 0 AND  closingdate >= '$start_date' AND end_date <= '$end_date' " );		
  		if($adb->num_rows($campaign_qry) > 0) {			
			while($row = $adb->fetch_array($campaign_qry)) {
				$campaignid = $row['campid'];
				$relcrm_qry = $adb->query("SELECT * FROM vtiger_crmentityrel WHERE crmid = $record AND  relcrmid = $campaignid " );		
  				if($adb->num_rows($relcrm_qry) == 0) {
					$count++;										
					$sql = "insert into vtiger_crmentityrel values (?,?,?,?)";
					$adb->pquery($sql, array($record,'Targets',$campaignid,'Campaigns'));
				}
			}
		}
	}
}