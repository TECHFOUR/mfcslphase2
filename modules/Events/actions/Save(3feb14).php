<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Events_Save_Action extends Calendar_Save_Action {
	
	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {			
		 $adb = PearDatabase::getInstance();
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			if($relatedModule->getName() == 'Events'){
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		
		
		
		// Handled to save follow up event
		$followupMode = $request->get('followup');
		
		//Start Date and Time values
		$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
		$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start') . " " . $startTime);
		list($startDate, $startTime) = explode(' ', $startDateTime);
		
		$startOldTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
        $startOldDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('date_start') . " " . $startOldTime);
        list($startOldDate, $startOldTime) = explode(' ', $startOldDateTime);
		
		$appointmentbook = $request->get('cf_895');
		$subdisposition = $request->get('cf_903');
		$driver_pickup = $request->get('driver_pickup');															
		$subject = $request->get('subject');
		if($appointmentbook == "Call Back")
			$followupMode = 'on';
		elseif($appointmentbook == "Appointment Booked" && $subdisposition == "Rescheduled")
			$followupMode = 'on';
				
		if($startTime != '' && $startDate != '' && ($followupMode == 'on') ){
                $recordModel->set('eventstatus', 'Planned');
                $recordModel->set('subject','[Followup] '.$subject);                
				if($appointmentbook == "Appointment Booked") {					
					$driver_flag = 0;		
					if($driver_pickup != "on") {
						$new_date_start_old = strtotime('+1 days', strtotime($startOldDate));
						$new_date_start = strtotime($startDate);	
						if($new_date_start_old == $new_date_start) {
							$driver_flag = 1;
							$new_startTime = "9:00:00";	
							$new_endTime = "9:05:00";
							$new_date_start = $startDate;				
						}
					}
					if($driver_flag == 0) {
						$new_date_start = date('Y-m-d', strtotime('-1 days', strtotime($startDate)));
						$new_startTime = "17:00:00";	
						$new_endTime = "17:05:00";
					}																
					$recordModel->set('date_start',$new_date_start);
					$recordModel->set('due_date',$new_date_start);
					$recordModel->set('time_start',$new_startTime);
					$recordModel->set('time_end',$new_endTime);
					$recordModel->set('app_book_date',$startDate);
                	$recordModel->set('app_book_time',$startTime);
				}else {
					$recordModel->set('date_start',$startDate);
					$recordModel->set('due_date',$startDate);
					$recordModel->set('time_start',$startTime);
					$recordModel->set('time_end',$startTime);
					$recordModel->set('cf_903','');
				}
                $recordModel->set('mode', 'create');
                $recordModel->save();
            }
		
		//TODO: remove the dependency on $_REQUEST
		if($_REQUEST['recurringtype'] != '' && $_REQUEST['recurringtype'] != '--None--') {
			vimport('~~/modules/Calendar/RepeatEvents.php');
			$focus =  new Activity();
			
			//get all the stored data to this object
			$focus->column_fields = $recordModel->getData();
			
			Calendar_RepeatEvents::repeatFromRequest($focus);
		}
		$contactIdList = $request->get('contactidlist');
        $recordId = $recordModel->getId();
        if(isset($contactIdList))
        {
            //split the string and store in an array
            $storearray = explode (";",$contactIdList);
            $del_sql = "delete from vtiger_cntactivityrel where activityid=?";
            $adb->pquery($del_sql, array($recordId));
            //print_r($adb->convert2Sql($del_sql, array($recordId)));
            $record = $recordId;
            foreach($storearray as $id)
            {
                if($id != '')
                {

                    $sql = "insert into vtiger_cntactivityrel values (?,?)";
                    $adb->pquery($sql, array($id, $record));
                    if(!empty($heldevent_id)) {
                        $sql = "insert into vtiger_cntactivityrel values (?,?)";
                        $adb->pquery($sql, array($id, $heldevent_id));
                    }
                }
            }
        }
        return $recordModel;
    }


    /**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request) {
        $recordModel = parent::getRecordModelFromRequest($request);

        $recordModel->set('selectedusers', $request->get('selectedusers'));
        return $recordModel;
    }
}
