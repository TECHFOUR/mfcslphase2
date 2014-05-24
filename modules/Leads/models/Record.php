<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function returns the url for converting lead
	 */
	function getConvertLeadUrl() {
		return 'index.php?module='.$this->getModuleName().'&view=ConvertLead&record='.$this->getId();
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module=false) {
		$db = PearDatabase::getInstance();

		$deletedCondition = $this->getModule()->getDeletedRecordCondition();
		$query = 'SELECT * FROM vtiger_crmentity
                    INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
                    WHERE label LIKE ? AND '.$deletedCondition;
		$params = array("%$searchKey%");
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$moduleModels = array();
		$matchingRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$moduleName = $row['setype'];
			if(!array_key_exists($moduleName, $moduleModels)) {
				$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
			}
			$moduleModel = $moduleModels[$moduleName];
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
			$recordInstance = new $modelClassName();
			$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
		}
		return $matchingRecords;
	}
	
	/**
	 * Function to check duplicate exists or not
	 * @return <boolean>
	 */
	public function checkDuplicate() {		
		global $current_user;
		$db = PearDatabase::getInstance();
		$sub_query = " AND vtiger_users.id = ".$current_user->id." ";						
		$userDetails = getUserDetails($sub_query);
		$currentprofileid = $userDetails['profileid'];
		
		$assigned_to = $_REQUEST['assigned_to'];						
		$campaign_id = $_REQUEST['campaignid'];				
		$home_pin = $_REQUEST['code'];
		$office_pin = $_REQUEST['state'];
		
		if($currentprofileid == 3 || $currentprofileid == 6 || $currentprofileid == 5)
			$assigned_to = $current_user->id;
		
		$mobile = str_replace('', '-', $_REQUEST['mobile']); // Replaces all spaces with hyphens.
		$mobile = substr(preg_replace('/[^0-9\-]/', '', $mobile),-10);
		
		if(!preg_match('/^\d{10}$/', $mobile))
			return "outlet_level###Mobile No length must be 10 digit only.";
		if(!preg_match('/^\d{6}$/', $home_pin) && $home_pin != "")
			return "outlet_level###Home Pin length must be 6 digit only.";
		if(!preg_match('/^\d{6}$/', $office_pin) && $office_pin != "")
			return "outlet_level###Office Pin length must be 6 digit only.";																		
		
		$registrationno = str_replace('', '-', $_REQUEST['registrationno']); // Replaces all spaces with hyphens.
		$registrationno = strtoupper(substr(preg_replace('/[^a-zA-Z0-9\']/', '', $registrationno),-11)); // Removes special chars.
		
		$camp_qry = $db->pquery("select priority, location, vtiger_vendorcf.vendorid as camp_type_id, vendorname
								, vtiger_vendorcf.campaign_category as campcategory from vtiger_campaign 
								INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
								INNER JOIN vtiger_vendorcf ON vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype
								INNER JOIN vtiger_vendor on vtiger_vendor.vendorid = vtiger_vendorcf.vendorid
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vendorcf.vendorid																		
								where vtiger_crmentity.deleted = 0 AND vtiger_campaign.campaignid = ?",array($campaign_id));		
		if($db->num_rows($camp_qry) > 0 ) {
			$camp_type_priority = $db->query_result($camp_qry,0,'priority');
			$campaignlocation = $db->query_result($camp_qry,0,'location');
			$camp_type_id = $db->query_result($camp_qry,0,'camp_type_id');
			$campaign_type = $db->query_result($camp_qry,0,'vendorname');
			$campcategory = $db->query_result($camp_qry,0,'campcategory');
		}		
				
		$sub_query = " AND vtiger_users.id = ".$assigned_to." ";  
		$userDetails = getUserDetails($sub_query);				
		$outletid = $userDetails['outletmasterid'];
		$outletmaster = $userDetails['outletmaster'];
		$depth = $userDetails['depth'];	
		$profileid = $userDetails['profileid'];
		
		if($profileid == 3 ) { // For Customer Care Agent  Profile
			$outletid = $_REQUEST['outlet'];						
			$plant_code_qry = $db->pquery("select outletmaster from vtiger_outletmaster where outletmasterid =?", array($outletid));		
			if($db->num_rows($plant_code_qry) > 0) {
				$outletmaster = $db->query_result($plant_code_qry,0,'outletmaster');							
			}
		}				
		
		$lead_details = $this->getDuplicateLeadDetails($registrationno, $mobile); // Step 1 *******************		
		$first_lead_no = $lead_details['lead_no'];
		if(count($lead_details) > 0) {
			foreach($lead_details as $row) {							
				$lead_no = $row['lead_no'];
				$old_outletmaster = $row['outletmaster'];
				$old_camp_type_priority = $row['old_camp_type_priority'];	
				$old_lead_id = $row['oldleadid'];
				$old_assigned_to = $row['smownerid'];
				$old_outlet = $row['outlet'];
				$old_campid = $row['campid'];
				$old_campaignlocation = $row['campaignlocation'];
				$old_camp_type_id = $row['old_camp_type_id'];
				$old_campcategory = $row['old_campcategory'];
				$lead_outlet = $row['lead_outlet'];
			}
			$sub_query1 = " AND vtiger_users.id = ".$old_assigned_to." ";  
			$userDetails1 = getUserDetails($sub_query1);				
			$old_outletmasterid = $userDetails1['outletmasterid'];			
		}
											
		if($registrationno != "" && $this->getId() == "") { // Start override registerno if old lead have blank register no*********
			$lead_details_new = $this->getDuplicateLeadDetails('', $mobile);			
			foreach($lead_details_new as $row) {			
				$lead_no = $row['lead_no'];
				$old_outletmaster = $row['outletmaster'];
				$old_camp_type_priority = $row['old_camp_type_priority'];	
				$old_lead_id = $row['oldleadid'];
				$old_assigned_to = $row['smownerid'];
				$old_outlet = $row['outlet'];
				$old_campid = $row['campid'];
				$old_campaignlocation = $row['campaignlocation'];
				$old_camp_type_id = $row['old_camp_type_id'];
				$old_campcategory = $row['old_campcategory'];
				$lead_outlet_new = $row['lead_outlet'];			
				$this->addHistoryLead($old_camp_type_id, $old_lead_id, $old_assigned_to, $old_camp_type_priority, $old_campid , $old_outlet, $old_campaignlocation, $assigned_to, $outletmaster,$camp_type_id , $outletid, $campaign_id, $campaignlocation,$_REQUEST, $registrationno, $campaign_type,$depth, $camp_type_priority,1,$profileid, $campcategory, $old_campcategory);																					
			return "outlet_level###The lead already exists Since the registration no is non blank, therefore existing Lead is being updated with details, campaign id and outlet. The existing Lead no being updated is : $lead_outlet_new";															
						die;
			}			
	}// End override registerno if old lead have blank register no*********
		
		if(count($lead_details) == 0 && $registrationno == "" && $this->getId() == "") {
			$lead_details_new1 = $this->getDuplicateLeadDetails('', $mobile,'without_regno');
			foreach($lead_details_new1 as $row) {								
				$old_camp_type_priority = $row['old_camp_type_priority'];	
				$old_lead_id = $row['oldleadid'];
				$lead_outlet_new = $row['lead_outlet'];											
				// Case Low
				if($camp_type_priority <= $old_camp_type_priority) {	
					return "outlet_level###The lead already exists with registration no and since campaign type is lower therefore no updation on existing Lead and no new Lead is created. The existing Lead no is : $lead_outlet_new";															
							die;
				}
				// Case Heigher
				if($camp_type_priority > $old_camp_type_priority) {
					$this->nonBlankLeadData($_REQUEST, $old_lead_id);
					return "outlet_level###The lead you are creating has blank registration no and a lead already exists with same mobile no and non-blank registration no. Therefore existing Lead is being updated with details only. The existing Lead no being updated is : $lead_outlet_new";															
							die;
				}
			}
		}
		
				
		if(count($lead_details) == 0 && $campcategory != "InBound" && ($profileid == 5 || $profileid == 6) && $this->getId() == "") { // for Except InBound
			return "outlet_level_except_InBound### This lead will be transferred to MO of the outlet because 'Campaign Category' is not InBound";															
			die;
		}
	
	
	
	
		if(count($lead_details) == 1 ) { // Case 1	*****************************
			/*if($old_outletmasterid != $outletid) {// Case 11	*****************************
				return "outlet_level### Customer already exists based on mobile no in some other outlet then the user will be prompted that does he want to change the outlet to his own outlet, If he select 'Yes' then the existing lead will be transferred to new outlet $lead_outlet";															
					die;
			}*/
			//else {// Case 12	*****************************																
				if($camp_type_priority > $old_camp_type_priority) {// Case 121 *******************
					if($_SESSION['ygsdf8743895784'] == "fgdfg345354534545") {
					unset($_SESSION['ygsdf8743895784']);
					if($registrationno == "")	{
							return "The lead already exists and Current campaign type priority is higher than existing campaign type priority. Since the registration no is blank in existing and no new Lead is created, therefore existing Lead is being updated with details. The existing Lead no being updated is : $lead_outlet";																				
							die;	
						}
						else {
							return "The lead already exists and Current campaign type priority is higher than existing campaign type priority. Since the registration no is non blank, therefore existing Lead is being updated with details, campaign id and outlet. The existing Lead no being updated is : $lead_outlet";															
						die;
						}
				}														
						$response_data = $this->addHistoryLead($old_camp_type_id, $old_lead_id, $old_assigned_to, $old_camp_type_priority, $old_campid , $old_outlet, $old_campaignlocation, $assigned_to, $outletmaster,$camp_type_id , $outletid, $campaign_id, $campaignlocation,$_REQUEST, $registrationno, $campaign_type,$depth, $camp_type_priority,0,$profileid, $campcategory, $old_campcategory);
						if($registrationno == "")	{
							return "The lead already exists and Current campaign type priority is higher than existing campaign type priority. Since the registration no is blank in existing and no new Lead is created, therefore existing Lead is being updated with details. The existing Lead no being updated is : $lead_outlet";																				
							die;	
						}
						else {
							return "The lead already exists and Current campaign type priority is higher than existing campaign type priority. Since the registration no is non blank, therefore existing Lead is being updated with details, campaign id and outlet. The existing Lead no being updated is : $lead_outlet";															
						die;
						}
				//} // end else
			}			
			if($camp_type_priority <= $old_camp_type_priority) {// Case 122 ********************	
				if($_SESSION['ygsdf8743895784'] == "fgdfg345354534545") {
					unset($_SESSION['ygsdf8743895784']);
					return "The lead already exists and Current campaign type priority is higher than existing campaign type priority. Since the registration no is non blank, therefore existing Lead is being updated with details, campaign id and outlet. The existing Lead no being updated is : $lead_outlet";															
						die;																				
					die;
				}
				return "The lead already exists with registration no and since campaign type is lower therefore no updation on existing Lead and no new Lead is created. The existing Lead no is : $lead_outlet";	
				die;
			}
		}// END
		
		elseif(count($lead_details) > 1) {// Case 2 *************************
			return "There are more than one existing Leads for the entered Mobile No or Registration No. Therefore the Lead cannot be created or updated. The existing lead and outlet details are : $lead_outlet";
			die;
		}
		return 0;// Case 3 **************************
	}
	
	// Start to check mobile and register no exist or not  added by Ajay [TECHFOUR] ***************************

function getDuplicateLeadDetails($registrationno, $mobile, $ststus = "") {
			
		global $adb, $log;
		$log->debug("Entering getDuplicateLeadDetails() method ...");		
		$query = "SELECT outletmaster, vtiger_leadaddress.campaignid as campid, campaignlocation,lead_no, vtiger_vendorcf.priority 
				as old_camp_type_priority, vtiger_leaddetails.leadid as oldleadid, smownerid, outlet, vtiger_vendorcf.vendorid as 
				old_camp_type_id, vtiger_leadscf.campaign_category  as campcategory FROM vtiger_leadscf 
				INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid 
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leadscf.leadid
				INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
				INNER JOIN vtiger_campaign ON vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
				INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_vendorcf ON vtiger_vendorcf.vendorid = vtiger_leadaddress.campaigntype
				INNER JOIN vtiger_outletmaster ON vtiger_outletmaster.outletmasterid = vtiger_leaddetails.outlet
				WHERE vtiger_crmentity.setype = ? AND vtiger_crmentity.deleted = 0 ";
				
				if($ststus == 'without_regno') {
					$query .= "AND mobile = ? ";
					$params = array($this->getModule()->getName(), $mobile);
				}else {
					$query .= "AND (registrationno = ?  AND mobile = ?)";
					$params = array($this->getModule()->getName(), $registrationno, $mobile);	
				}		
		$record = $this->getId();
		if ($record) { 
			$query .= " AND crmid != ?";
			array_push($params, $record);
		}
		$result = $adb->pquery($query, $params);
		$num_rows = $adb->num_rows($result);
		$lead_details = Array();						
		for($i=0; $i<$num_rows; $i++) {	
			$lead_details[$i]['lead_no'] = $adb->query_result($result,$i,'lead_no');		
			$lead_details[$i]['outletmaster'] = $adb->query_result($result,$i,'outletmaster');
			$lead_details[$i]['old_camp_type_priority'] = $adb->query_result($result,$i,'old_camp_type_priority');
			$lead_details[$i]['oldleadid'] = $adb->query_result($result,$i,'oldleadid');
			$lead_details[$i]['smownerid'] = $adb->query_result($result,$i,'smownerid');
			$lead_details[$i]['outlet'] = $adb->query_result($result,$i,'outlet');
			$lead_details[$i]['campid'] = $adb->query_result($result,$i,'campid');
			$lead_details[$i]['campaignlocation'] = $adb->query_result($result,$i,'campaignlocation');
			$lead_details[$i]['old_camp_type_id'] = $adb->query_result($result,$i,'old_camp_type_id');
			$lead_details[$i]['old_campcategory'] = $adb->query_result($result,$i,'campcategory');
			$lead_details[$i]['lead_outlet'] = $adb->query_result($result,$i,'lead_no')." -- ".$adb->query_result($result,$i,'outletmaster');					
		}
		$log->debug("Exiting getDuplicateLeadDetails method ...");		
		return $lead_details;
	
}
// End added by Ajay [TECHFOUR] ***************************

	
	function addHistoryLead($old_camp_type_id, $old_lead_id, $old_assigned_to, $old_camp_type_priority, $old_campid , $old_outlet, $old_campaignlocation, $assigned_to, $outletmaster,$camp_type_id , $outletid, $campaign_id, $campaignlocation,$_REQUEST, $registrationno, $campaign_type,$depth, $camp_type_priority,$empty_reg_no,$profileid, $campcategory, $old_campcategory) {
					global $adb,$log, $current_user;																									
					if($empty_reg_no == 0 || $profileid == 3) {
						$_SESSION['ygsdf8743895784'] = "fgdfg345354534545";
						$this->createNewActivityAfterHeld($assigned_to, $old_lead_id, $old_assigned_to, $registrationno);
					}
										
// Start Save in Modtracker table ******************					
									
			$this->nonBlankLeadData($_REQUEST, $old_lead_id);
									
								
// End Save in Modtracker table ******************

$reassigned_permission = 1;
//$profileid
	if($depth == 9 && $campcategory != "InBound") {
		$reassigned_permission = 0;
		$sub_query1 = " AND depth = 8 AND vtiger_outletmastercf.outletmasterid = ".$outletid." ";						
		$userDetails1 = getUserDetails($sub_query1);
		$mo_id = $userDetails1['id'];
		if($mo_id != $old_assigned_to)
			$this->setReassignedLead($mo_id, $assigned_to);
		if($empty_reg_no == 1) {
			$this->setCampaignId($old_lead_id, $old_camp_type_id, $camp_type_id, $old_outlet, $outletid, $old_campid, $campaign_id, $old_campaignlocation, $campaignlocation, $campcategory, $old_campcategory);
		}
	}
	
	if($registrationno != "" && $reassigned_permission == 1) {
		$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
		$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)', Array($thisid, $old_lead_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																															
		
		$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';
							
		$adb->pquery($sql,Array($thisid, 'assigned_user_id', $old_assigned_to, $assigned_to));										
		$adb->pquery($sql,Array($thisid, 'campaigntype', $old_camp_type_id, $camp_type_id));										
		$adb->pquery($sql,Array($thisid, 'outlet', $old_outlet, $outletid));					
		$adb->pquery($sql,Array($thisid, 'campaignid', $old_campid, $campaign_id));
		$adb->pquery($sql,Array($thisid, 'campaignid', $old_campid, $campaign_id));														
		$adb->pquery($sql,Array($thisid, 'campaign_category', $old_campcategory, $campcategory));
					
	$adb->query("UPDATE vtiger_leaddetails 
						INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
						INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
						INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
						SET re_campaign_status = '1', smownerid = ".$assigned_to.", modifiedby = ".$current_user->id.", 
						modifiedtime = '".date("Y-m-d H:i:s",time())."', outlet = ".$outletid.", campaignid = ".$campaign_id.", 
						campaigntype = ".$camp_type_id.",	campaignlocation = '".$campaignlocation."', plant_code = '".$outletmaster."',
						campaign_category = ".$campcategory."
						where vtiger_leaddetails.leadid = ".$old_lead_id." ");
	  	$history_qry = $adb->query("SELECT lead_no, campaignid, vtiger_modtracker_basic.id as basicid, vtiger_modtracker_basic.module as basicmodule, vtiger_modtracker_basic.crmid as entityid, whodid, createdtime, changedon, prevalue, postvalue, outletmaster FROM vtiger_modtracker_basic 
		INNER JOIN vtiger_modtracker_detail ON vtiger_modtracker_detail.id = vtiger_modtracker_basic.id
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modtracker_basic.crmid
INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
INNER JOIN vtiger_users on vtiger_users.id = vtiger_modtracker_detail.postvalue
INNER JOIN vtiger_outletmaster on vtiger_outletmaster.outletmasterid = vtiger_users.cf_775
WHERE vtiger_crmentity.deleted = 0 AND vtiger_modtracker_detail.fieldname = 'assigned_user_id' AND history_status = '0' AND vtiger_modtracker_basic.module = 'Leads' AND vtiger_leaddetails.leadid = ".$old_lead_id." ORDER BY basicid");		
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
				$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='Faq' and active = 1");
				$resultnum = $adb->fetch_array($querynum);
				$prefix = $resultnum['prefix'];
				$cur_id = $resultnum['cur_id'];
				$HISNo = $prefix.$cur_id; 
				$next_curr_id = $cur_id + 1;
				$adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='Faq' and active = 1");					  				
				$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
				modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$adb->pquery($query, array($crmid, $whodid, $whodid, 0, "Faq", "", $createdtime, $changedon, NULL, NULL, 0, 1, 0, $HISNo));
								
				$adb->query("INSERT INTO vtiger_faq (id, faq_no, changed_by, entityid, outlet, post_assigned_to, pre_assigned_to, module, entity_no, campid) VALUES(".$crmid.",'".$HISNo."', ".$whodid.", ".$entityid.", '".$outletmaster."', ".$postvalue.", '".$prevalue."', '".$basicmodule."', '".$entity_no."', '".$campaignid."')"); 
				
				$adb->query("UPDATE vtiger_modtracker_basic SET history_status = '1' WHERE id = ".$basicid."");
				
				$sql = "insert into vtiger_crmentityrel values (?,?,?,?)";
				$adb->pquery($sql, array($entityid,'Leads',$crmid,'Faq'));
				$adb->pquery($sql, array($old_campid,'Campaigns',$crmid,'Faq'));								
				$adb->query("UPDATE vtiger_campaignscf SET reallocated_lead = '1' where campaignid = ".$old_campid." ");
				
				}		
			}
		}
	}
	
	function setCampaignId($old_lead_id, $old_camp_type_id, $camp_type_id, $old_outlet, $outletid, $old_campid, $campaign_id, $old_campaignlocation, $campaignlocation, $campcategory, $old_campcategory) {
		global $adb, $current_user;
		$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
		$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)', Array($thisid, $old_lead_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																															
		
		$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';
																			
		$adb->pquery($sql,Array($thisid, 'campaigntype', $old_camp_type_id, $camp_type_id));										
		$adb->pquery($sql,Array($thisid, 'outlet', $old_outlet, $outletid));					
		$adb->pquery($sql,Array($thisid, 'campaignid', $old_campid, $campaign_id));														
		$adb->pquery($sql,Array($thisid, 'campaignlocation', $old_campaignlocation, $campaignlocation));
		$adb->pquery($sql,Array($thisid, 'campaign_category', $old_campcategory, $campcategory));
		
		$adb->query("UPDATE vtiger_leaddetails 
			INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
			SET re_campaign_status = '1', modifiedby = ".$current_user->id.", 
			modifiedtime = '".date("Y-m-d H:i:s",time())."', outlet = ".$outletid.", campaignid = ".$campaign_id.", 
			campaigntype = ".$camp_type_id.", campaignlocation = '".$campaignlocation."', campaign_category = '".$campcategory."'
			where vtiger_leaddetails.leadid = ".$old_lead_id." ");
	}
	
	function setReassignedLead($mo_id, $assignedid) {
		global $adb, $current_user;			
		$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
		$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)', Array($thisid, $this->id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 0));																																				
		$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
		$adb->pquery($sql,Array($thisid, 'assigned_user_id', $assignedid, $mo_id));
		
		$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid 
					INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
					SET  smownerid = ".$mo_id.", modifiedby = ".$current_user->id.", modifiedtime = '".date('Y-m-d H:i:s')."' 
					WHERE vtiger_leaddetails.leadid = ".$this->id." ");
	}
	
	private function createNewActivityAfterHeld($assigned_user_id, $lead_id, $old_assigned_to, $registrationno) {			
			global $adb, $current_user;			
		$callerName = "Lead Activity";
		
		if($registrationno == "")
			$assigned_user_id = $old_assigned_to;
// start to held old activity			
		
			$lead_activity_qry = $adb->query("SELECT vtiger_activity.activityid as activity_id  FROM vtiger_seactivityrel 
								INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_seactivityrel.crmid 
								INNER JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_seactivityrel.activityid 											
								WHERE  vtiger_leaddetails.leadid = ".$lead_id."  ");	
		if($adb->num_rows($lead_activity_qry) > 0) {
			while($row = $adb->fetch_array($lead_activity_qry)) {
				$activity_id = $row['activity_id'];
				$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = ".$activity_id." ");
			}
		}
// End to held old activity			
		$currentdatetime = date("Y-m-d H:i:s");
		list($date,$time) = explode(" ",$currentdatetime);
		/*list($date,$time) = explode(" ",$currentdatetime);			
			list($h,$m,$s) = explode(":",$time);			
			$m1 = $m + 5;
			if($m > 59) {
				$minute = $m1 - 60;
				$h1 = $h + 1;
				$hour = $h1;				
			if($h1 > 23) {
				$hour = 23;		
				}
			}
			else {
				$minute = $m1;
				$hour = $h;
			}
				
			$time_start = $time;
			$time_end = $hour.':'.$minute.':'.$s;*/
			
			$time_start = $time;
			$time_end = $time;
			$date_start = $date;
		
		
		$crmid = $adb->getUniqueID("vtiger_crmentity");	
		$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
					modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$adb->pquery($query, array($crmid, $current_user->id, $assigned_user_id, $assigned_user_id, "Calendar", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $callerName));
					
		$query = "INSERT into vtiger_activity (activityid, subject, activitytype, date_start, due_date, time_start, time_end, eventstatus, visibility) VALUES (?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $callerName, 'Call', $date_start, $date_start, $time_start, $time_end, 'Planned', 'all'));
									
		$adb->query("INSERT into vtiger_activitycf (activityid) 
					values(".$crmid.")"); 
		
		$adb->query("INSERT into vtiger_activity_reminder_popup (semodule,recordid,date_start,time_start,status) values('Calendar','".$crmid."','".$startdate."','".$start_time."',0)");
					
		$adb->query("INSERT into vtiger_seactivityrel (crmid,activityid) values(".$lead_id.",".$crmid.")");	
		
		
		// Start update Lead									
			$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
						INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
						SET  latest_activity_date = '".$date_start."' WHERE vtiger_leaddetails.leadid = ".$lead_id." ");
						
		// Start Save in Modtracker table ******************
					$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
							VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'Events',$current_user->id, date('Y-m-d H:i:s',time()), 2));
						
					$all_Values = array('subject'=>$callerName,
										'assigned_user_id'=> $assigned_user_id,
										'date_start'=>$date_start, 
										'time_start'=>$time_start, 
										'time_end'=>$time_end, 
										'due_date'=>$date_start, 								 								
										'parent_id'=>$lead_id, 
										'activitytype'=>'Call',
										'eventstatus'=>'Planned'									
									);
																
					foreach($all_Values as $key=>$row) {
						if($row != "")	{
							if($key == "date_start" || $key == "due_date")
								$row = $date_start;
							$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,postvalue) VALUES(?,?,?)',
								Array($thisid, $key, $row));
						}
					}
					
					$thisid_rel = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)

					VALUES(?,?,?,?,?,?)', Array($thisid_rel, $lead_id, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 4));
					
					$adb->pquery('INSERT INTO vtiger_modtracker_relations(id,targetmodule, targetid, changedon) VALUES(?,?,?,?)',
								Array($thisid_rel, 'Calendar', $crmid, date('Y-m-d H:i:s',time())));					
		// End Save in Modtracker table ******************	
		
	
		}
		

	
	function nonBlankLeadData($_REQUEST, $old_lead_id) {
		global $adb, $current_user;
		$date_format = $current_user->date_format;
		foreach($_REQUEST as $key=>$row) {																				
			if($row != "" && ($key != "module" || $key != "action" || $key != "mobile" || $key != "assigned_to" || $key != "registrationno" || $key != "outlet"))	{
				
				if($key == "dateofbirth" || $key == "dateofsale"|| $key == "lastservicedate"|| $key == "insurancedate"|| $key == "leadactivitydate") {
					if($date_format != "dd-mm-yyyy")
						$row = str_replace("-","/",$row);				
				$row = date("Y-m-d",strtotime($row));															
				}
				
				$adb->query("UPDATE vtiger_leaddetails 
			INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
			INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
			SET  modifiedby = ".$current_user->id.", modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
			where vtiger_leaddetails.leadid = ".$old_lead_id." ");
			}
		}
	}
	/**
	 * Function returns Account fields for Lead Convert
	 * @return Array
	 */
	function getAccountFieldsForLeadConvert() {
		$accountsFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Accounts';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
            //Fields that need to be shown
            $complusoryFields = array('industry');
			foreach ($fieldModels as $fieldName => $fieldModel) {
				if($fieldModel->isMandatory() && $fieldName != 'assigned_user_id') {
                    $keyIndex = array_search($fieldName,$complusoryFields);
                    if($keyIndex !== false) {
                        unset($complusoryFields[$keyIndex]);
                    }
					$leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
					$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					$accountsFields[] = $fieldModel;
				}
			}
            foreach($complusoryFields as $complusoryField) {
                $fieldModel = Vtiger_Field_Model::getInstance($complusoryField, $moduleModel);
				if($fieldModel->getPermissions('readwrite')) {
                    $industryFieldModel = $moduleModel->getField($complusoryField);
                    $industryLeadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
                    $industryFieldModel->set('fieldvalue', $this->get($industryLeadMappedField));
                    $accountsFields[] = $industryFieldModel;
                }
            }
		}
		return $accountsFields;
	}

	/**
	 * Function returns Contact fields for Lead Convert
	 * @return Array
	 */
	function getContactFieldsForLeadConvert() {
		$contactsFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Contacts';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
            $complusoryFields = array('firstname', 'email');
            foreach($fieldModels as $fieldName => $fieldModel) {
                if($fieldModel->isMandatory() &&  $fieldName != 'assigned_user_id') {
                    $keyIndex = array_search($fieldName,$complusoryFields);
                    if($keyIndex !== false) {
                        unset($complusoryFields[$keyIndex]);
                    }

                    $leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
                    $fieldValue = $this->get($leadMappedField);
                    if ($fieldName === 'account_id') {
                        $fieldValue = $this->get('company');
                    }
                    $fieldModel->set('fieldvalue', $fieldValue);
                    $contactsFields[] = $fieldModel;
                }
            }

			foreach($complusoryFields as $complusoryField) {
                $fieldModel = Vtiger_Field_Model::getInstance($complusoryField, $moduleModel);
				if($fieldModel->getPermissions('readwrite')) {
					$leadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
					$fieldModel = $moduleModel->getField($complusoryField);
					$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					$contactsFields[] = $fieldModel;
				}
			}
		}
		return $contactsFields;
	}

	/**
	 * Function returns Potential fields for Lead Convert
	 * @return Array
	 */
	function getPotentialsFieldsForLeadConvert() {
		$potentialFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Potentials';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();

            $complusoryFields = array('amount');
			foreach($fieldModels as $fieldName => $fieldModel) {
				if($fieldModel->isMandatory() &&  $fieldName != 'assigned_user_id' && $fieldName != 'related_to') {
                    $keyIndex = array_search($fieldName,$complusoryFields);
                    if($keyIndex !== false) {
                        unset($complusoryFields[$keyIndex]);
                    }
					$leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
					$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					$potentialFields[] = $fieldModel;
				}
			}
            foreach($complusoryFields as $complusoryField) {
                $fieldModel = Vtiger_Field_Model::getInstance($complusoryField, $moduleModel);
                if($fieldModel->getPermissions('readwrite')) {
                    $fieldModel = $moduleModel->getField($complusoryField);
                    $amountLeadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
                    $fieldModel->set('fieldvalue', $this->get($amountLeadMappedField));
                    $potentialFields[] = $fieldModel;
                }
            }
		}
		return $potentialFields;
	}

	/**
	 * Function returns field mapped to Leads field, used in Lead Convert for settings the field values
	 * @param <String> $fieldName
	 * @return <String>
	 */
	function getConvertLeadMappedField($fieldName, $moduleName) {
		$mappingFields = $this->get('mappingFields');

		if (!$mappingFields) {
			$db = PearDatabase::getInstance();
			$mappingFields = array();

			$result = $db->pquery('SELECT * FROM vtiger_convertleadmapping', array());
			$numOfRows = $db->num_rows($result);
			
			$accountInstance = Vtiger_Module_Model::getInstance('Accounts');
			$accountFieldInstances = $accountInstance->getFieldsById();
			
			$contactInstance = Vtiger_Module_Model::getInstance('Contacts');
			$contactFieldInstances = $contactInstance->getFieldsById();
			
			$potentialInstance = Vtiger_Module_Model::getInstance('Potentials');
			$potentialFieldInstances = $potentialInstance->getFieldsById();
			
			$leadInstance = Vtiger_Module_Model::getInstance('Leads');
			$leadFieldInstances = $leadInstance->getFieldsById();
			
			for($i=0; $i<$numOfRows; $i++) {
				$row = $db->query_result_rowdata($result,$i);
				if(empty($row['leadfid'])) continue;

				$leadFieldInstance = $leadFieldInstances[$row['leadfid']];
				if(!$leadFieldInstance) continue;

				$leadFieldName = $leadFieldInstance->getName();
				$accountFieldInstance = $accountFieldInstances[$row['accountfid']];
				if ($row['accountfid'] && $accountFieldInstance) {
					$mappingFields['Accounts'][$accountFieldInstance->getName()] = $leadFieldName;
				}
				$contactFieldInstance = $contactFieldInstances[$row['contactfid']];
				if ($row['contactfid'] && $contactFieldInstance) {
					$mappingFields['Contacts'][$contactFieldInstance->getName()] = $leadFieldName;
				}
				$potentialFieldInstance = $potentialFieldInstances[$row['potentialfid']];
				if ($row['potentialfid'] && $potentialFieldInstance) {
					$mappingFields['Potentials'][$potentialFieldInstance->getName()] = $leadFieldName;
				}
			}
			$this->set('mappingFields', $mappingFields);
		}
		return $mappingFields[$moduleName][$fieldName];
	}

	/**
	 * Function returns the fields required for Lead Convert
	 * @return <Array of Vtiger_Field_Model>
	 */
	function getConvertLeadFields() {
		$convertFields = array();
		$accountFields = $this->getAccountFieldsForLeadConvert();
		if(!empty($accountFields)) {
			$convertFields['Accounts'] = $accountFields;
		}

		$contactFields = $this->getContactFieldsForLeadConvert();
		if(!empty($contactFields)) {
			$convertFields['Contacts'] = $contactFields;
		}

		$potentialsFields = $this->getPotentialsFieldsForLeadConvert();
		if(!empty($potentialsFields)) {
			$convertFields['Potentials'] = $potentialsFields;
		}
		return $convertFields;
	}

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&parent_id='.$this->getId();
	}

}