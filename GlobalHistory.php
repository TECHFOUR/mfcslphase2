<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Emails/mail.php');
include('config.php');

global $adb,$log, $current_user;
				 					
    
  //if($currentid != "" && $sourceRecord != "") {				  
  $faq_values = array();
  $crmentity_values = array();
  $entityrel_values = array();  
  $modtracker_values = array();
  
	 $counter = 0;
	  	$history_qry = $adb->query("SELECT lead_no, campaignid, vtiger_modtracker_basic.id as basicid, vtiger_modtracker_basic.module as basicmodule, vtiger_modtracker_basic.crmid as entityid, whodid, createdtime, changedon, prevalue, postvalue, outletmaster FROM vtiger_modtracker_basic 
		INNER JOIN vtiger_modtracker_detail ON vtiger_modtracker_detail.id = vtiger_modtracker_basic.id
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modtracker_basic.crmid
INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
INNER JOIN vtiger_users on vtiger_users.id = vtiger_modtracker_detail.postvalue
INNER JOIN vtiger_outletmaster on vtiger_outletmaster.outletmasterid = vtiger_users.cf_775
WHERE vtiger_crmentity.deleted = 0 AND vtiger_modtracker_detail.fieldname = 'assigned_user_id' AND history_status = '0' AND vtiger_modtracker_basic.module = 'Leads' ORDER BY vtiger_crmentity.crmid ASC");		
  		if($adb->num_rows($history_qry) > 0) {
			while($row = $adb->fetch_array($history_qry)) {			
				$basicmodule = $row['basicmodule'];
				$entityid = $row['entityid'];
				$whodid = $row['whodid'];
				$createdtime = $row['createdtime'];
				$changedon = $row['changedon'];
				$prevalue = $row['prevalue'];
				$postvalue = $row['postvalue'];
				$outletmaster = $row['outletmaster'];					
				$basicid = $row['basicid'];
				$entity_no = $row['lead_no'];
				$campaignid = $row['campaignid'];
		
				$crmid = $adb->getUniqueID("vtiger_crmentity");					
				$createrid = $userid;
				if($counter == 0) {
					$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='Faq' and active = 1");
					$resultnum = $adb->fetch_array($querynum);
					$prefix = $resultnum['prefix'];
					$cur_id = $resultnum['cur_id'];
					$HISNo = $prefix.$cur_id; 
					$next_curr_id = $cur_id + 1;
				}else {
					$HISNo = $prefix.$next_curr_id;
					$next_curr_id++;
				}
				
														
			$crmentity_values[] =  "(".$crmid.", ".$whodid.", ".$whodid.", 'Faq',  '".$createdtime."', '".$changedon."', '".$HISNo."')";				
			$faq_values[] =  "(".$crmid.",'".$HISNo."', ".$whodid.", ".$entityid.", '".$outletmaster."', ".$postvalue.", '".$prevalue."', '".$basicmodule."', '".$entity_no."', '".$campaignid."')";						
			$entityrel_values[] =  "(".$entityid.", 'Leads', ".$crmid.", 'Faq')";											
			$modtracker_values[] = $basicid;
						
				$counter++;								
			}// While End
			
				$adb->query("UPDATE vtiger_modentity_num SET cur_id = ".$next_curr_id." where semodule='Faq' and active = 1");
				
				if( !empty($modtracker_values) ) {
					$query_mod_tracker = "UPDATE vtiger_modtracker_basic SET history_status = '1' WHERE id IN(".implode(',',$modtracker_values).") " ;
					$adb->query($query_mod_tracker);
				}
							
				if( !empty($crmentity_values) ) {
						$query_crmentity = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,createdtime,
					modifiedtime,label) VALUES " 
						. implode(',',$crmentity_values);
						$adb->query($query_crmentity);
					}
					
				if( !empty($faq_values) ) {
					$query_faq = "INSERT INTO vtiger_faq (id, faq_no, changed_by, entityid, outlet, post_assigned_to, pre_assigned_to, module, entity_no, campid) VALUES " 
					. implode(',',$faq_values);
					$adb->query($query_faq);
				}
				
				
				if( !empty($entityrel_values) ) {
					$query_crmentityrel = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES " 
					. implode(',',$entityrel_values);
					$adb->query($query_crmentityrel);
				}
		}
?>