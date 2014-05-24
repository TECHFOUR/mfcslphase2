<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Targets_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		global $adb, $current_user;
		$userid = $current_user->id;
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
        $recordModel = $this->record;
        if(!$recordModel){
           if (!empty($recordId)) {
               $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
           } else {
               $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
           }
            $this->record = $recordModel;
        }
		
		$viewer = $this->getViewer($request);
// start to find target assign users	
		$sub_query = " AND vtiger_users.id = ".$userid." ";		
		$userDetails = getUserDetails($sub_query);
		$profileid = $userDetails['profileid'];
		$zone = $userDetails['zone'];          
		// 10 Head BD Profile, 2 RBDM  Profile, 4 MO Profile 
		if($userid != 1) {
			if($profileid == 10)				
				$custom_qry = " and vtiger_profile.profileid in(2,10) ";
			if($profileid == 2)
				$custom_qry = " and vtiger_profile.profileid in(4) AND vtiger_outletmastercf.cf_781 like '%".$zone."%' ";
			
			$query_target = "SELECT vtiger_users.first_name, vtiger_users.last_name, vtiger_users.email1, vtiger_users.email2, vtiger_users.id, vtiger_users.id FROM vtiger_users INNER JOIN vtiger_user2role ON vtiger_users.id = vtiger_user2role.userid INNER JOIN vtiger_role on vtiger_role.roleid = vtiger_user2role.roleid INNER JOIN vtiger_role2profile on vtiger_role2profile.roleid = vtiger_role.roleid INNER JOIN vtiger_profile on vtiger_profile.profileid = vtiger_role2profile.profileid INNER JOIN vtiger_outletmastercf on vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775 WHERE vtiger_users.status='Active' AND vtiger_users.id > 0 ";
			$query_target .= $custom_qry;
			$query_target = $adb->query($query_target);																
			$viewer->assign('TARGETLIMITVALUE', $adb->num_rows($query_target));
		}
// end to find target assign users				
		/*$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		$salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $recordModel->getModule());
		$salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype'));
		$viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);*/

		parent::process($request);
	}

}