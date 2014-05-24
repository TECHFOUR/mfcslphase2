<?php
session_start();
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Import_FileReader_Reader {

	var $status='success';
	var $numberOfRecordsRead = 0;
	var $numberOfUpdated = 0;
	var $errorMessage='';
	var $user;
	var $request;
    var $moduleModel;
	var $updated_records = 0;
	var $skipped_records = 0;
	var $imported_records = 0;

	public function  __construct($request, $user) {
		$this->request = $request;
		$this->user = $user;
        $this->moduleModel = Vtiger_Module_Model::getInstance($this->request->get('module'));
	}

	public function getStatus() {
		return $this->status;
	}

	public function getErrorMessage() {
		return $this->errorMessage;
	}

	public function getNumberOfRecordsRead() {
		return $this->numberOfRecordsRead;
	}
	
	public function getnumberOfUpdated() {
		return $this->numberOfUpdated;
	}

	public function hasHeader() {
		if($this->request->get('has_header') == 'on'
				|| $this->request->get('has_header') == 1
				|| $this->request->get('has_header') == true) {
			return true;
		}
		return false;
	}

	public function getFirstRowData($hasHeader=true) {
		return null;
	}

	public function getFilePath() {
		return Import_Utils_Helper::getImportFilePath($this->user);
	}

	public function getFileHandler() {
		$filePath = $this->getFilePath();
		if(!file_exists($filePath)) {
			$this->status = 'failed';
			$this->errorMessage = "ERR_FILE_DOESNT_EXIST";
			return false;
		}

		$fileHandler = fopen($filePath, 'r');
		if(!$fileHandler) {
			$this->status = 'failed';
			$this->errorMessage = "ERR_CANT_OPEN_FILE";
			return false;
		}
		return $fileHandler;
	}

	public function convertCharacterEncoding($value, $fromCharset, $toCharset) {
		if (function_exists("mb_convert_encoding")) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($fromCharset, $toCharset, $value);
		}
		return $value;
	}

	public function read() {
		// Sub-class need to implement this
	}

	public function deleteFile() {
		$filePath = $this->getFilePath();
		@unlink($filePath);
	}

	public function createTable() {
		$db = PearDatabase::getInstance();

		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		$fieldMapping = $this->request->get('field_mapping');
        $moduleFields = $this->moduleModel->getFields();
        $columnsListQuery = 'id INT PRIMARY KEY AUTO_INCREMENT, status INT DEFAULT 0, recordid INT';
		$fieldTypes = $this->getModuleFieldDBColumnType();
		foreach($fieldMapping as $fieldName => $index) {
            $fieldObject = $moduleFields[$fieldName];
            $columnsListQuery .= $this->getDBColumnType($fieldObject, $fieldTypes);
		}
		$createTableQuery = 'CREATE TABLE '. $tableName . ' ('.$columnsListQuery.') ENGINE=MyISAM ';
		$db->query($createTableQuery);
		return true;
	}
	public function getDashInDate($string) {
			  $string = strtolower($string);
			  //Make alphanumeric (removes all other characters)
			  $string = preg_replace("/[^a-z0-9_\s-]/", "-", $string);
			  //Clean up multiple dashes or whitespaces
			  $string = preg_replace("/[\s-]+/", " ", $string);
			  //Convert whitespaces and underscore to dash
			  $string = preg_replace("/[\s_]/", "-", $string); 
			  list($mm,$dd,$yyyy) = explode("-",$string);
			  $finaldate = $yyyy.'-'.$mm.'-'.$dd;
			  return $finaldate;
		}
		

	public function addRecordToDB($columnNames, $fieldValues, $i,$row,$filename,$handle) {
		$db = PearDatabase::getInstance();
		global $current_user;
		
		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		
		$lead_create_update_status = 1;
		// 0  skip, 1 create, 2 update
		if($_REQUEST['module'] == 'Leads'){
			$campaignid = $_SESSION['importid'];
			
			$logerror = "";
		if($fieldValues[0] == '' || strlen($fieldValues[0]) != 10 || ($fieldValues[1] == '' && $fieldValues[2] == '')){ // Start code when skip lead
		
				$this->skipped_records++;
				
				if($fieldValues[0] == '')
					$logerror .= "Mobile is Empty";
				if(strlen($fieldValues[0]) != 10 && $fieldValues[0] != '')
					$logerror .= "Mobile number is not Valid";
				if($fieldValues[1] == "" && $fieldValues[2] == "")
					$logerror .= "First Name and Last Name is Empty";
			fputcsv($handle, array($logerror,$fieldValues[0],$fieldValues[1],$fieldValues[2],$fieldValues[3],$fieldValues[4],$fieldValues[5],$fieldValues[6],
					$fieldValues[7],$fieldValues[8],$fieldValues[9],$fieldValues[10],$fieldValues[11],$fieldValues[12],$fieldValues[13],$fieldValues[14],$fieldValues[15],
					$fieldValues[16],$fieldValues[17],$fieldValues[18],$fieldValues[19],$fieldValues[20],$fieldValues[21],$fieldValues[22],$fieldValues[23],$fieldValues[24],
					$fieldValues[25],$fieldValues[26],$fieldValues[27],$fieldValues[28],$fieldValues[29],$fieldValues[30],$fieldValues[31]));		
			}
		else{ // When Insert or Update
			$mobile = str_replace('', '-', $fieldValues[0]); // Replaces all spaces with hyphens.
			$fieldValues[0] = substr(preg_replace('/[^0-9\-]/', '', $mobile),-10);
			
			$registrationno = str_replace('', '-', $fieldValues[24]); // Replaces all spaces with hyphens.
			$fieldValues[24] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9\']/', '', $registrationno),-11)); // Removes special chars.
			$registrationno = trim($fieldValues[24]);
			$mobile = trim($fieldValues[0]);
				
			if($fieldValues[1] == "") { // If firstname is empty then lastname assign to firsname
				$fieldValues[1] = $fieldValues[2];
				$fieldValues[2] = ".";
			}
			if($fieldValues[17] != '') // Date of Birth
				$fieldValues[17] = Import_FileReader_Reader::getDashInDate($fieldValues[17]);
				
				
			
			
			/*Start Query for dind the Make and Model*/
			
			//For Make
			$current_make_qry = $db->query("select assetsid from vtiger_assets 												
													inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_assets.assetsid
													where vtiger_crmentity.deleted = 0 AND make = '".trim($fieldValues[22])."' ");
				$makedata = $db->fetch_array($current_make_qry);
				$make_id = $makedata['assetsid'];
				
			//For Model
			$current_model_qry = $db->query("select serviceid from vtiger_service 												
													inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_service.serviceid
													where vtiger_crmentity.deleted = 0 AND model = '".trim($fieldValues[23])."' ");
				$modeldata = $db->fetch_array($current_model_qry);
				$model_id = $modeldata['serviceid'];	
			
			
			/*End Query for find the Make and Model*/
			
					
			$sub_query = " AND vtiger_users.id = ".$current_user->id." ";						
			$userDetails = getUserDetails($sub_query);
			$outlet_id = $userDetails['outletmasterid'];
			$outletmaster = $userDetails['outletmaster'];
			$depth = $userDetails['depth'];	
			$profileid = $userDetails['profileid'];
			$plant_code = $userDetails['outletmaster'];
			
			
			
			/* Start Code to find out the current user outlet for Central*/
			if($profileid == 13 || $profileid == 7){ // Superviser Profile & BD Corporate Profile			
				$current_outlet_qry = $db->query("select outletmasterid from vtiger_outletmastercf 												
													inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_outletmastercf.outletmasterid
													INNER JOIN vtiger_outletmaster on vtiger_outletmaster.outletmasterid = vtiger_outletmastercf.outletmasterid
													where vtiger_crmentity.deleted = 0 AND outlet = '".trim($fieldValues[26])."' ");
				$outletdata = $db->fetch_array($current_outlet_qry);
				$outlet_id = $outletdata['outletmasterid'];	
				$plant_code = $userDetails['outletmaster'];					
			}else
			{
				$fieldValues[31] = $fieldValues[30];
				$fieldValues[30] = $fieldValues[29];
				$fieldValues[29] = $fieldValues[28];
				$fieldValues[28] = $fieldValues[27];
				$fieldValues[27] = $fieldValues[26];
			}
			$fieldValues[26] = $outlet_id;
			/* End Code to find out the current user outlet*/
			
			
			if($fieldValues[27] != '') // Insurance date
				$fieldValues[27] = Import_FileReader_Reader::getDashInDate($fieldValues[27]);
						
			if($fieldValues[30] != '') // Last Service date
				$fieldValues[30] = Import_FileReader_Reader::getDashInDate($fieldValues[30]);
							
			if($fieldValues[31] != '') // Date of sale
				$fieldValues[31] = Import_FileReader_Reader::getDashInDate($fieldValues[31]);
			
					
			/*Data will not upload when mandatory field is empty so its use here*/
			$camp_qry = $db->pquery("select campaignname, priority, location, vtiger_vendorcf.vendorid as camp_type_id, vendorname
									, vtiger_vendorcf.campaign_category as campcategory, 
									vtiger_campaignscf.campaignid as campid from vtiger_campaign 
									INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
									INNER JOIN vtiger_vendorcf ON vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype
									INNER JOIN vtiger_vendor on vtiger_vendor.vendorid = vtiger_vendorcf.vendorid
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vendorcf.vendorid																		
									where vtiger_crmentity.deleted = 0 AND vtiger_campaign.campaignid = ?",array($campaignid));		
			if($db->num_rows($camp_qry) > 0 ) {
				$camp_type_priority = $db->query_result($camp_qry,0,'priority');
				$campaignlocation = $db->query_result($camp_qry,0,'location');
				$camp_type_id = $db->query_result($camp_qry,0,'camp_type_id');
				$campaign_type = $db->query_result($camp_qry,0,'vendorname');
				$campcategory = $db->query_result($camp_qry,0,'campcategory');
				$campid = $db->query_result($camp_qry,0,'campid');
			}
			$fieldValues[25] = $campaignid;	
			
			/*End Data will not upload when mandatory field is empty so its use here*/
							
			$all_Request = array(						
							'mobile' => $fieldValues[0],
							'firstname' => $fieldValues[1],
							'lastname' => $fieldValues[2],
							'lane' => $fieldValues[3],
							'society_name' => $fieldValues[4],
							'homeaddtwo' => $fieldValues[5],
							'homeaddthree' => $fieldValues[6],
							'home_state' =>$fieldValues[7],
							'city' => $fieldValues[8],
							'code' =>$fieldValues[9],
							'company_name' => $fieldValues[10],
							'officeaddtwo' =>$fieldValues[11],
							'officeaddthree' =>$fieldValues[12],
							'office_state' =>$fieldValues[13],
							'country' =>$fieldValues[14],
							'state' =>$fieldValues[15],
							'email' =>$fieldValues[16],
							'dateofbirth' =>$fieldValues[17],
							'occupation' =>$fieldValues[18],
							'company' =>$fieldValues[19],
							'designation' =>$fieldValues[20],
							'secondaryemail' =>$fieldValues[21],
							'make' =>$fieldValues[22],
							'model' =>$fieldValues[23],
							'registrationno' =>$fieldValues[24],
							'insurancedate' =>$fieldValues[27],
							'insurancecompany' =>$fieldValues[28],
							'odometer' =>$fieldValues[29],
							'lastservicedate' =>$fieldValues[30],
							'dateofsale' =>$fieldValues[31]
													
						);		  			
						
			$lead_details = getDuplicateLeadDetails($registrationno,$mobile);	// step 1
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
					$old_outletmasterid = $userDetails1['outletid'];
				}					
			}
				
	// Start override registerno if old lead have blank register no*********		
			if($registrationno != "") { // Step 2
				$lead_details_new = getDuplicateLeadDetails('', $mobile);			
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
					$lead_outlet_new = $row['lead_outlet'];
					$lead_create_update_status = 2;																													
				}			
		}// End override registerno if old lead have blank register no*********
		
		
		if(count($lead_details) == 0 && $registrationno == "") { // Step 3
				$lead_details_new1 = getDuplicateLeadDetails('', $mobile,'without_regno'); // To check same mobile no exist without regno.
				foreach($lead_details_new1 as $row) {								
					$old_camp_type_priority = $row['old_camp_type_priority'];	
					$old_lead_id = $row['oldleadid'];
					$lead_outlet_new = $row['lead_outlet'];											
					// Case Low
					if($camp_type_priority <= $old_camp_type_priority) {	
						$lead_create_update_status = 0;	
						$logerror .= "Mobile No. Already Exist";
					}
					// Case Heigher
					if($camp_type_priority > $old_camp_type_priority) {					
						$lead_create_update_status = 2;	
					}
				}
			}
			
	// Start New Conditon check by Ajay ***************************	
							
			if(count($lead_details) == 1) { // Case 1	*****************************	
				// Case 12	*****************************																
				if($camp_type_priority > $old_camp_type_priority)
						$lead_create_update_status = 2;																				
				if($camp_type_priority <= $old_camp_type_priority){ // Case 12 ********************					
					$lead_create_update_status = 0;	
					$logerror .= "Mobile No. Already Exist";
					$this->skipped_records++;
				}
			}// END
			
			elseif(count($lead_details) > 1){ // Case 2 *************************
				$lead_create_update_status = 0;	
				$logerror .= "Mobile No. Already Exist";
				$this->skipped_records++;
			}
				
	
			if($lead_create_update_status == 1){  //create	
					
				$this->createNewLead($fieldValues,$campaignlocation,$camp_type_id,$campcategory,$make_id,$model_id, $plant_code);
				$this->imported_records++;	
					
			}
				
			elseif($lead_create_update_status == 2) { // Update	
				$this->updated_records++;		
				$this->addHistoryLead($old_camp_type_id, $old_lead_id, $old_assigned_to, $old_camp_type_priority, $old_campid , $old_outlet, $old_campaignlocation, $current_user->id, $outletmaster,$camp_type_id , $outlet_id, $campaignid, $campaignlocation,$all_Request, $registrationno, $campaign_type,$depth, $camp_type_priority,1,$profileid, $campcategory, $old_campcategory);
						
			}
		fputcsv($handle, array($logerror,$fieldValues[0],$fieldValues[1],$fieldValues[2],$fieldValues[3],$fieldValues[4],$fieldValues[5],$fieldValues[6],
					$fieldValues[7],$fieldValues[8],$fieldValues[9],$fieldValues[10],$fieldValues[11],$fieldValues[12],$fieldValues[13],$fieldValues[14],$fieldValues[15],
					$fieldValues[16],$fieldValues[17],$fieldValues[18],$fieldValues[19],$fieldValues[20],$fieldValues[21],$fieldValues[22],$fieldValues[23],$fieldValues[24],
					$fieldValues[25],$fieldValues[26],$fieldValues[27],$fieldValues[28],$fieldValues[29],$fieldValues[30],$fieldValues[31]));	
			
		}
		}
	else{
		$db->pquery('INSERT INTO '.$tableName.' ('. implode(',', $columnNames).') VALUES ('. generateQuestionMarks($fieldValues) .')', $fieldValues);
		}
		
		$this->numberOfRecordsRead++;
	}
    
	function createNewLead($fieldValues,$campaignlocation,$camp_type_id,$campcategory,$make_id,$model_id, $plant_code) {
		
		//echo $fieldValues."_".$campaignlocation."_".$camp_type_id."_".$campcategory."_".$make_id."_".$model_id; die;
		global $adb, $current_user;
		$crmid = $adb->getUniqueID("vtiger_crmentity");					
		$createrid = $current_user->id;
		$currentdatetime = date("Y-m-d H:i:s");																											
		$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='Leads' and active = 1");
		$resultnum = $adb->fetch_array($querynum);
		$prefix = $resultnum['prefix'];
		$cur_id = $resultnum['cur_id'];
		$LeadNum = $prefix.$cur_id; 
		$next_curr_id = $cur_id + 1;
		$adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='Leads' and active = 1");					  					
					$callerName = $LeadNum;
					$all_Values = array(
						'lead_no'=> $LeadNum,
						'mobile' => $fieldValues[0],
						'firstname' => $fieldValues[1],
						'lastname' => $fieldValues[2],
						'lane' => $fieldValues[3],
						'society_name' => $fieldValues[4],
						'homeaddtwo' => $fieldValues[5],
						'homeaddthree' => $fieldValues[6],
						'home_state' =>$fieldValues[7],
						'city' => $fieldValues[8],
						'code' =>$fieldValues[9],
						'company_name' => $fieldValues[10],
						'officeaddtwo' =>$fieldValues[11],
						'officeaddthree' =>$fieldValues[12],
						'office_state' =>$fieldValues[13],
						'country' =>$fieldValues[14],
						'state' =>$fieldValues[15],
						'email' =>$fieldValues[16],
						'dateofbirth' =>$fieldValues[17],
						'occupation' =>$fieldValues[18],
						'company' =>$fieldValues[19],
						'designation' =>$fieldValues[20],
						'secondaryemail' =>$fieldValues[21],
						'make' =>$fieldValues[22],
						'model' =>$fieldValues[23],
						'registrationno' =>$fieldValues[24],
						'insurancedate' =>$fieldValues[27],
						'insurancecompany' =>$fieldValues[28],
						'odometer' =>$fieldValues[29],
						'lastservicedate' =>$fieldValues[30],
						'dateofsale' =>$fieldValues[31],
						'record_id'=>$crmid,
						'assigned_user_id'=>$createrid,								
						'record_module'=>'Leads'		
					);	
						
					
					
		$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
		modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$adb->pquery($query, array($crmid, $createrid, $createrid, $createrid, "Leads", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $LeadNum));
		
		
				            		
		$adb->query("insert into vtiger_leaddetails (leadid,lead_no,firstname,lastname,email,company,designation,secondaryemail,outlet)
 values(".$crmid.",'".$LeadNum."','".$fieldValues[1]."', '".$fieldValues[2]."', '".$fieldValues[16]."', '".$fieldValues[19]."'
 ,'".$fieldValues[20]."', '".$fieldValues[21]."', ".$fieldValues[26].")"); 
 
 
 $adb->query("insert into vtiger_leadsubdetails(leadsubscriptionid,campaignlocation)values(".$crmid.",'".$campaignlocation."')"); 
 
		
				
		$adb->query("insert into vtiger_leadscf (leadid,society_name,homeaddtwo,homeaddthree,home_state,officeaddtwo,officeaddthree,office_state,dateofbirth,   occupation,registrationno,insurancedate,insurancecompany,odometer,lastservicedate,dateofsale,campaign_category,make,model,plant_code)
		values(".$crmid.",'".$fieldValues[4]."','".$fieldValues[5]."'
,'".$fieldValues[6]."','".$fieldValues[7]."', '".$fieldValues[11]."', '".$fieldValues[12]."', '".$fieldValues[13]."','".$fieldValues[17]."'
,'".$fieldValues[18]."','".$fieldValues[24]."', '".$fieldValues[27]."', '".$fieldValues[28]."', '".$fieldValues[29]."','".$fieldValues[30]."'
,'".$fieldValues[31]."','".$campcategory."','".$make_id."','".$model_id."','".$plant_code."')");



		
			$adb->query("insert into vtiger_leadaddress (leadaddressid,mobile,lane,city,code,company_name,country,state,campaignid,campaigntype)
			values(".$crmid.",'".$fieldValues[0]."','".$fieldValues[3]."','".$fieldValues[8]."' ,'".$fieldValues[9]."',
				'".$fieldValues[10]."','".$fieldValues[14]."','".$fieldValues[15]."',".$fieldValues[25].",'".$camp_type_id."')");
// Start Save in Modtracker table ******************
					$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
					$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
							VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'Leads',$current_user->id, date('Y-m-d H:i:s',time()), 2));
																	
					foreach($all_Values as $key=>$row) {
						if($row != "")	{									
							$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,postvalue) VALUES(?,?,?)',
								Array($thisid, $key, $row));
						}
					}					
// End Save in Modtracker table ******************				
					
	}
	
	function addHistoryLead($old_camp_type_id, $old_lead_id, $old_assigned_to, $old_camp_type_priority, $old_campid , $old_outlet, $old_campaignlocation, $assigned_to, $outletmaster,$camp_type_id , $outletid, $campaign_id, $campaignlocation,$all_Request, $registrationno, $campaign_type,$depth, $camp_type_priority,$empty_reg_no,$profileid, $campcategory, $old_campcategory) {
		
					global $adb,$log, $current_user;																									
									
// Start Save in Modtracker table ******************
//echo $old_assigned_to.'___'.$assigned_to.'___'.$outletmaster.'___'.$empty_reg_no;die;
//echo "<pre>"; print_r($_REQUEST); die; 					
							//echo $old_lead_id;die;		
			$this->nonBlankLeadData($all_Request, $old_lead_id);									
								
// End Save in Modtracker table ******************
		//echo $old_lead_id.'___'.$old_camp_type_id.'___'.$camp_type_id.'___'.$old_outlet.'___'.$outletid.'___'.$old_campid.'___'.$campaign_id.'___'.$old_campaignlocation.'___'. $campaignlocation.'___'.$campcategory.'___'.$old_campcategory;die;
	if($registrationno != "" ) {
		$this->setCampaignId($old_lead_id, $old_camp_type_id, $camp_type_id, $old_outlet, $outletid, $old_campid, $campaign_id, $old_campaignlocation, $campaignlocation, $campcategory, $old_campcategory);
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
						SET  smownerid = ".$assigned_to.", modifiedby = ".$current_user->id.", 
						modifiedtime = '".date("Y-m-d H:i:s",time())."', outlet = ".$outletid.", campaignid = ".$campaign_id.", 
						campaigntype = ".$camp_type_id.",	campaignlocation = '".$campaignlocation."', plant_code = '".$outletmaster."',
						campaign_category = '".$campcategory."'
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
			INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
			SET  modifiedby = ".$current_user->id.", 
			modifiedtime = '".date("Y-m-d H:i:s",time())."', outlet = ".$outletid.", campaignid = ".$campaign_id.", 
			campaigntype = ".$camp_type_id.", campaignlocation = '".$campaignlocation."', campaign_category = '".$campcategory."'
			where vtiger_leaddetails.leadid = ".$old_lead_id." ");
	}
	
	function nonBlankLeadData($all_Request, $old_lead_id) {
		global $adb, $current_user;
		$date_format = $current_user->date_format;
		foreach($all_Request as $key=>$row) {																				
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
	
	/** Function returns the database column type of the field
	 * @param $fieldObject <Vtiger_Field_Model>
	 * @param $fieldTypes <Array> - fieldnames with column type
	 * @return <String> - column name with type for sql creation of table
	 */	
    public function getDBColumnType($fieldObject,$fieldTypes){
        $columnsListQuery = '';
        $fieldName = $fieldObject->getName();
        $dataType = $fieldObject->getFieldDataType();
        if($dataType == 'reference' || $dataType == 'owner' || $dataType == 'currencyList'){
            $columnsListQuery .= ','.$fieldName.' varchar(250)';
        } else {
            $columnsListQuery .= ','.$fieldName.' '.$fieldTypes[$fieldObject->get('column')];
        }
        
        return $columnsListQuery;
    }
    
	/** Function returns array of columnnames and their column datatype
	 * @return <Array>
	 */
    public function getModuleFieldDBColumnType() {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT tablename FROM vtiger_field WHERE tabid=? GROUP BY tablename', array($this->moduleModel->getId()));
        $tables = array();
        if ($result && $db->num_rows($result) > 0) {
            while ($row = $db->fetch_array($result)) {
                $tables[] = $row['tablename'];
            }
        }
        $fieldTypes = array();
        foreach ($tables as $table) {
            $result = $db->pquery("DESC $table", array());
            if ($result && $db->num_rows($result) > 0) {
                while ($row = $db->fetch_array($result)) {
                    $fieldTypes[$row['field']] = $row['type'];
                }
            }
        }
        return $fieldTypes;
    }
}
?>