<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {			
        $user = Users_Record_Model::getCurrentUserModel();

		$recordModel = $this->saveRecord($request);

		$fieldModelList = $recordModel->getModule()->getFields();
		$result = array();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue =  Vtiger_Util_Helper::toSafeHTML($recordModel->get($fieldName));
            $result[$fieldName] = array();
			if($fieldName == 'date_start') {
				$timeStart = $recordModel->get('time_start');
                $dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeStart);

				$fieldValue = $fieldValue.' '.$timeStart;
                
                $userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
                $dateTimeComponents = explode(' ',$userDateTimeString);
                $dateComponent = $dateTimeComponents[0];
                //Conveting the date format in to Y-m-d . since full calendar expects in the same format
                $dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
                $result[$fieldName]['calendar_display_value'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			} else if($fieldName == 'due_date') {
				$timeEnd = $recordModel->get('time_end');
                $dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeEnd);

				$fieldValue = $fieldValue.' '.$timeEnd;
                
                $userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
                $dateTimeComponents = explode(' ',$userDateTimeString);
                $dateComponent = $dateTimeComponents[0];
                //Conveting the date format in to Y-m-d . since full calendar expects in the same format
                $dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
                $result[$fieldName]['calendar_display_value']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			}
			$result[$fieldName]['value'] = $fieldValue;
            $result[$fieldName]['display_value'] = $fieldModel->getDisplayValue($fieldValue);
		}

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();
		
		// Handled to save follow up event
		$followupMode = $request->get('followup');
		$appointmentbook = $request->get('cf_895');
		$subdisposition = $request->get('cf_903');
		$driver_pickup = $request->get('driver_pickup');															
		$subject = $request->get('subject');
		
		if($appointmentbook == "Call Back")
			$followupMode = 'on';
		elseif($appointmentbook == "Appointment Booked" && $subdisposition == "Rescheduled")
			$followupMode = 'on';
		
        if($followupMode == 'on') {
            //Start Date and Time values
            $startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
            $startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start') . " " . $startTime);
            list($startDate, $startTime) = explode(' ', $startDateTime);
			
			$startOldTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
			$startOldDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('date_start') . " " . $startOldTime);
			list($startOldDate, $startOldTime) = explode(' ', $startOldDateTime);
										
            if($startTime != '' && $startDate != ''){
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
        }
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);

		$startDate = $request->get('date_start');
		if(!empty($startDate)) {
			//Start Date and Time values
			$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
			$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('date_start')." ".$startTime);
			list($startDate, $startTime) = explode(' ', $startDateTime);
			
			$recordModel->set('date_start', $startDate);
			$recordModel->set('time_start', $startTime);
		}

		$endDate = $request->get('due_date');
		if(!empty($endDate)) {
			//End Date and Time values
			$endTime = $request->get('time_end');
			$endDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('due_date'));

			if ($endTime) {
				$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
				$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('due_date')." ".$endTime);
				list($endDate, $endTime) = explode(' ', $endDateTime);
			}

			$recordModel->set('time_end', $endTime);
			$recordModel->set('due_date', $endDate);
		}

		$activityType = $request->get('activitytype');
		$visibility = $request->get('visibility');
		if(empty($activityType)) {
			$recordModel->set('activitytype', 'Task');
			$visibility = 'Private';
			$recordModel->set('visibility', $visibility);
		}
		
		if(empty($visibility)) {
			$assignedUserId = $recordModel->get('assigned_user_id');
			$sharedType = Calendar_Module_Model::getSharedType($assignedUserId);
			if($sharedType == 'selectedusers') {
				$sharedType = 'private';
			}
			$recordModel->set('visibility', ucfirst($sharedType));
		}

		return $recordModel;
	}
}