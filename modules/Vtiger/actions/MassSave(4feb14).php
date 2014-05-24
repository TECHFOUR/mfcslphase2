<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_MassSave_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		$moduleName = $request->getModule();		
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordModels = $this->getRecordModelsFromRequest($request);
		foreach($recordModels as $recordId => $recordModel) {
// Start added code by ajay [TECHFOUR] *****************************************			
			if($moduleName == "Leads") {				
					$assigned_user_id = $request->get('assigned_user_id');
					$activity_qry = $adb->query("SELECT activityid, smownerid, leadsource FROM vtiger_leaddetails 
												INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
												LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = vtiger_leaddetails.leadid 
												WHERE vtiger_leaddetails.leadid = $recordId " );		
					if($adb->num_rows($activity_qry) > 0) {
						while($row = $adb->fetch_array($activity_qry)) {
							$old_assigned_to = $row['smownerid'];
							$activityid = $row['activityid'];
							$customer_type = $row['leadsource'];
							if($customer_type == "Individual")
								$adb->query("UPDATE vtiger_activity SET eventstatus = 'Held' WHERE vtiger_activity.activityid = $activityid ");
						}
					}
					//echo $old_assigned_to.'___'.$assigned_user_id.'___'.$recordId;die;
					if($customer_type == "Individual")
						$this->createNewActivity($old_assigned_to, $assigned_user_id, $recordId);				
			}
// End added code by ajay [TECHFOUR]	**************************************************		
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				$recordModel->save();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	function getRecordModelsFromRequest(Vtiger_Request $request) {			
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordIds = $this->getRecordsListFromRequest($request);
		$recordModels = array();

		$fieldModelList = $moduleModel->getFields();
		foreach($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$fieldValue = $request->get($fieldName, null);
				$fieldDataType = $fieldModel->getFieldDataType();
				if($fieldDataType == 'time'){
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if(isset($fieldValue) && $fieldValue != null) {
					if(!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				} else {
					$uiTypeModel = $fieldModel->getUITypeModel();
					$recordModel->set($fieldName, $uiTypeModel->getUserRequestValue($recordModel->get($fieldName)));
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
	
	function createNewActivity($old_assigned_to, $new_assigned_to, $lead_id) {			
			global $adb;
			$current_user = 1;
			
		$callerName = "Lead Activity";
		
		$assigned_user_id = $new_assigned_to;		
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
		$adb->pquery($query, array($crmid, $current_user, $assigned_user_id, $current_user, "Calendar", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $callerName));
					
		$query = "INSERT into vtiger_activity (activityid, subject, activitytype, date_start, due_date, time_start, time_end, eventstatus, visibility) VALUES (?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $callerName, 'Call', $date_start, $date_start, $time_start, $time_end, 'Planned', 'all'));
									
		$adb->query("INSERT into vtiger_activitycf (activityid) 
					values(".$crmid.")"); 
		
		$adb->query("INSERT into vtiger_activity_reminder_popup (semodule,recordid,date_start,time_start,status) values('Calendar','".$crmid."','".$startdate."','".$start_time."',0)");
					
		$adb->query("INSERT into vtiger_seactivityrel (crmid,activityid) values(".$lead_id.",".$crmid.")");	
		
		
		// Start update Lead			
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $lead_id, 'Leads',$current_user, date('Y-m-d H:i:s',time()), 0));																																				
			$sql = 'INSERT INTO vtiger_modtracker_detail(id,fieldname, prevalue, postvalue) VALUES(?,?,?,?)';										
			$adb->pquery($sql,Array($thisid, 'assigned_user_id', $old_assigned_to, $assigned_user_id));
			
			$adb->query("UPDATE vtiger_leaddetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
						INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
						SET  smownerid = ".$assigned_user_id.", modifiedby = ".$current_user.", modifiedtime = '".date('Y-m-d H:i:s')."' , latest_activity_date = '".$date_start."' 
						WHERE vtiger_leaddetails.leadid = ".$lead_id." ");
				
		
		// Start Save in Modtracker table ******************
					$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
							VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'Events',$current_user, date('Y-m-d H:i:s',time()), 2));
						
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
					VALUES(?,?,?,?,?,?)', Array($thisid_rel, $lead_id, 'Leads',$current_user, date('Y-m-d H:i:s',time()), 4));
					
					$adb->pquery('INSERT INTO vtiger_modtracker_relations(id,targetmodule, targetid, changedon) VALUES(?,?,?,?)',
								Array($thisid_rel, 'Calendar', $crmid, date('Y-m-d H:i:s',time())));					
		// End Save in Modtracker table ******************	
		
	
		}
}
