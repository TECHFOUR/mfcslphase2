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
	var $errorMessage='';
	var $user;
	var $request;
    var $moduleModel;
	var $updated_records = 0;

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
	if($_REQUEST['module'] == 'Leads'){
		$campaignid = $_SESSION['importid'];
			//unset($_SESSION['importid']); 
		//echo $importid; die;
		
		$mobile = str_replace('', '-', $fieldValues[0]); // Replaces all spaces with hyphens.
		$fieldValues[0] = substr(preg_replace('/[^0-9\-]/', '', $mobile),-10);
		
		$registrationno = str_replace('', '-', $fieldValues[24]); // Replaces all spaces with hyphens.
		$fieldValues[24] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9\']/', '', $registrationno),-11)); // Removes special chars.
			
			
		if($fieldValues[1] == "") {
			$fieldValues[1] = $fieldValues[2];
			$fieldValues[2] = ".";
		}
		if($fieldValues[17] != ''){
			$fieldValues[17] = Import_FileReader_Reader::getDashInDate($fieldValues[17]);
			}
		if($fieldValues[26] != ''){
			$fieldValues[26] = Import_FileReader_Reader::getDashInDate($fieldValues[26]);
			}
		if($fieldValues[29] != ''){
			$fieldValues[29] = Import_FileReader_Reader::getDashInDate($fieldValues[29]);
			}
		if($fieldValues[30] != ''){
			$fieldValues[30] = Import_FileReader_Reader::getDashInDate($fieldValues[30]);
			}
	
		 
		/* Start Code to find out the current user outlet*/
		/*if($current_user->id != 5 ){
		$current_outlet_qry = $db->query("select outlet from vtiger_users inner join vtiger_outletmastercf on  vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775 
		where vtiger_users.id = ".$current_user->id." ");
		
		$outletdata = $db->fetch_array($current_outlet_qry);
       	$currentoutlet_name = $outletdata['outlet'];
		$fieldValues[31] = $currentoutlet_name;
		}*/
		/* End Code to find out the current user outlet*/
		
		
		/*Data will not upload when mandatory field is empty so its use here*/
		$campaignqry = $db->query("select campaignname from vtiger_campaign inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_campaign.campaignid 
		where vtiger_crmentity.deleted = 0 and campaignid = ".$campaignid." ");
		
		$rowdata = $db->fetch_array($campaignqry);
        $campaignname = $rowdata['campaignname'];
		$fieldValues[25] = $campaignname;	
		/*End Data will not upload when mandatory field is empty so its use here*/
		
		/*Start Code to find the mobile numnber and registration no in temp table*/
		$temp_duplicateqry = $db->query("select * from $tableName where mobile = '".trim($fieldValues[0])."' and registrationno = '".trim($fieldValues[24])."'");
		if($db->num_rows($temp_duplicateqry)>0)
			$temp_mobile_exist = 1;
		else
			$temp_mobile_exist = 0;
		/*End Code to find the mobile numnber and registration no in temp table*/				
		
		
		
		$duplicateqry = $db->query("select mobile,priority,vtiger_leadscf.leadid as 'oldlead_id' from vtiger_leadscf 
						inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_leadscf.leadid 
						inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid
						inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
						inner join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype 
						where vtiger_crmentity.deleted = 0 and (mobile = '".trim($fieldValues[0])."' and registrationno = '".trim($fieldValues[24])."')");
		if($db->num_rows($duplicateqry) > 0){
				$data =  $db->fetch_array($duplicateqry);
				$old_campaign_priority = $data['priority'];
				$oldlead_id = $data['oldlead_id'];
				$mobileexist = 1;
			}
		else{
				$mobileexist = 0;
			}
			
			
		/*Start code to check lead with this mobile is fitst time or not */
		$duplicatemobileqry = mysql_query("select mobile,priority,vtiger_leadscf.leadid as 'oldlead_id' from vtiger_leadscf 
						inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_leadscf.leadid 
						inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid
						inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
						inner join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype 
						where vtiger_crmentity.deleted = 0 and (mobile = '".trim($fieldValues[0])."' and registrationno = '')");
						
		$firsttime_mobile = mysql_num_rows($duplicatemobileqry);
		
		if($firsttime_mobile == 1){
				$data =  mysql_fetch_array($duplicatemobileqry);
				$old_campaign_priority = $data['priority'];
				$oldlead_id = $data['oldlead_id'];
		}
		/*End*/	
		
		
		/*Start code when lead already exist and new data is coming with blank registration no */
		$duplicatemobileqry1 = mysql_query("select mobile,priority,vtiger_leadscf.leadid as 'oldlead_id' from vtiger_leadscf 
						inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_leadscf.leadid 
						inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid
						inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
						inner join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype 
						where vtiger_crmentity.deleted = 0 and (mobile = '".trim($fieldValues[0])."' and registrationno != '')");
						
		if(mysql_num_rows($duplicatemobileqry1)>0 && $fieldValues[24]==''){
			$check_lead_exist = 0;
		}else{
			$check_lead_exist = 1;
			}
		/*End*/	
		
					
		if($fieldValues[0] != '' && $db->num_rows($duplicateqry) == 0 && strlen($fieldValues[0]) == 10 && ($fieldValues[1] != "" &&  $fieldValues[2] != "") && $firsttime_mobile != 1 && $check_lead_exist == 1){
			
			$db->pquery('INSERT INTO '.$tableName.' ('. implode(',', $columnNames).') VALUES ('. generateQuestionMarks($fieldValues) .')', $fieldValues);
				
		}else{
				$capmaign_qry = $db->query("select priority from vtiger_campaign 
				inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_campaign.campaignid 
				inner join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype 
				where vtiger_crmentity.deleted = 0 and vtiger_campaign.campaignid = ".$campaignid."");
				
				if($db->num_rows($capmaign_qry)>0){
					$campdata = $db->fetch_array($capmaign_qry);
					$new_campaign_priority = $campdata['priority'];
					}
				
							
				$logerror = "";
				if($fieldValues[0] == '')
				$logerror .= "Mobile is Empty/";
				if($db->num_rows($duplicateqry) > 0)
				$logerror .= "Mobile already Exist/";
				if(strlen($fieldValues[0]) != 10 && $fieldValues[0] != '')
				$logerror .= "Mobile number is not Valid/";
				if($fieldValues[1] == "" && $fieldValues[2] == "."){
				$logerror .= "First Name and Last Name is Empty/";
				$fieldValues[2] = "";
				}
				
				
				if($fieldValues[0] != '' && strlen($fieldValues[0]) == 10 && $fieldValues[1] != '' && $fieldValues[2] != '' && $temp_mobile_exist = 0){
					$logerror = "";
					$myarray = array(
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
						'registrationno' =>$fieldValues[24],
						'insurancedate' =>$fieldValues[26],
						'insurancecompany' =>$fieldValues[27],
						'odometer' =>$fieldValues[28],
						'lastservicedate' =>$fieldValues[29],
						'dateofsale' =>$fieldValues[30]);
					
					/*Start code for uotlet master */
					$outletqry = $db->query("select cf_775 as 'outlet_id' from vtiger_users where id = ".$current_user->id."");
					$outletdata =  $db->fetch_array($outletqry);
					$outlet_id = $outletdata['outlet_id'];
					/*End code for uotlet master*/
		
					
					/*Start code for update campaign related data in lead module*/
						$campdata_update_qry = $db->query("select * from vtiger_campaign inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid 
						inner join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_campaign.campaignid where vtiger_crmentity.deleted = 0 
						and vtiger_campaign.campaignid = ".$campaignid." ");
						
						$campdetaildata = $db->fetch_array($campdata_update_qry);
						$campaign_type =  $campdetaildata['campaigntype'];
						$campaign_location =  $campdetaildata['location'];
						/*End*/	
					
					/* Non-blank Update in old lead and no campaign id will be changed when camp priority is less(If Regi no is blank) *****By jitendra singh on 11 Jan 2014*****/
					if($mobileexist == 1 && $fieldValues[24] == '' && $new_campaign_priority <= $old_campaign_priority && $temp_mobile_exist = 0){
							
							foreach($myarray as $key=>$row) {
								  if($row != "" && $key != 'mobile' || $key != 'registrationno'){
									  $lead_update_qry = $db->query("UPDATE vtiger_leaddetails 
									  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
									  INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
									  SET modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
									  where vtiger_leaddetails.leadid = ".$oldlead_id." ");
									}
							}
							if($lead_update_qry){
							 		 $this->updated_records++;	
							}
								
					}
					/* Non-blank Update in old lead and campaign id will be changed when camp priority is less(If Regi no is blank) *****By jitendra singh on 11 Jan 2014*****/
					if($mobileexist == 1 && $fieldValues[24] == '' && $new_campaign_priority > $old_campaign_priority && $temp_mobile_exist = 0){
							
							foreach($myarray as $key=>$row) {
								  if($row != "" && $key != 'mobile' || $key != 'registrationno'){
									  $lead_update_qry = $db->query("UPDATE vtiger_leaddetails 
									  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
									  INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
									  SET modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
									  where vtiger_leaddetails.leadid = ".$oldlead_id." ");
									}
							}
							if($lead_update_qry){
							 $campaign_details_update_qry = $db->query("UPDATE vtiger_leadaddress 
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leadaddress.leadaddressid 
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leadsubdetails.leadsubscriptionid
									  inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
									  SET vtiger_leadaddress.campaignid = ".$campaignid.",vtiger_leadaddress.campaigntype = '".$campaign_type."'
									  ,vtiger_leadsubdetails.campaignlocation = '".$campaign_location."'
									  where vtiger_leadaddress.leadaddressid = ".$oldlead_id." ");	
									  $this->updated_records++;	
									  unset($_SESSION['importid']);
							}
								
					}
					
					/* Non-blank Update in old leads(Lead with same mobile no) and campaign id will be updated when camp priority is heigher (If Regi no is blank) *****By jitendra singh on 14 Jan 2014*****/
					if($fieldValues[24] == '' && $check_lead_exist == 0 && $temp_mobile_exist = 0){
							
							$duplicateqry_lead = $db->query("select mobile,priority,vtiger_leadscf.leadid as 'oldlead_id' from vtiger_leadscf 
							inner join vtiger_crmentity on  vtiger_crmentity.crmid = vtiger_leadscf.leadid 
							inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid
							inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
							inner join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_campaign.campaigntype 
							where vtiger_crmentity.deleted = 0 and mobile = '".trim($fieldValues[0])."'");
						foreach($duplicateqry_lead as $lead_data){
							
							$existing_lead_riority = $lead_data['priority'];
							$oldlead_id = $lead_data['oldlead_id'];
							if($new_campaign_priority > $existing_lead_riority){
								
								foreach($myarray as $key=>$row) {
									  if($row != "" && $key != 'mobile' || $key != 'registrationno'){
										  $lead_update_qry = $db->query("UPDATE vtiger_leaddetails 
										  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
										  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
										  INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
										  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
										  SET modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
										  where vtiger_leaddetails.leadid = ".$oldlead_id." ");
										}
								}
								if($lead_update_qry){
								 $campaign_details_update_qry = $db->query("UPDATE vtiger_leadaddress 
										  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leadaddress.leadaddressid 
										  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leadsubdetails.leadsubscriptionid
										  inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
										  SET vtiger_leadaddress.campaignid = ".$campaignid.",vtiger_leadaddress.campaigntype = '".$campaign_type."'
										  ,vtiger_leadsubdetails.campaignlocation = '".$campaign_location."'
										  where vtiger_leadaddress.leadaddressid = ".$oldlead_id." ");	
										  $this->updated_records++;	
										  unset($_SESSION['importid']);
								}
						}
					}
								
				}
					
					
					
					/* Non-blank Update in old lead and assign to heiger camp and Campaign, Vehicle and Outlet is also updated*****By jitendra singh on 11 Jan 2014******/
					elseif($mobileexist == 1 && $fieldValues[24] != '' && $new_campaign_priority > $old_campaign_priority && $temp_mobile_exist = 0){
						
						
						foreach($myarray as $key=>$row) {
							
								  if($row != "" && $key != 'mobile'){
									  $lead_update_qry = $db->query("UPDATE vtiger_leaddetails 
									  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
									  INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
									  inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
									  SET  modifiedby = ".$current_user->id.",modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
									  where vtiger_leaddetails.leadid = ".$oldlead_id." ");
									}
							}
							if($lead_update_qry){
								 	$campaign_details_update_qry = $db->query("UPDATE vtiger_leadaddress 
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leadaddress.leadaddressid 
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leadsubdetails.leadsubscriptionid
									  inner join vtiger_leaddetails on vtiger_leaddetails.leadid = vtiger_leadaddress.leadaddressid
									  inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
									  SET smownerid = ".$current_user->id.",vtiger_leaddetails.outlet = ".$outlet_id.",vtiger_leadaddress.campaignid = ".$campaignid.",vtiger_leadaddress.campaigntype = '".$campaign_type."'
									  ,vtiger_leadsubdetails.campaignlocation = '".$campaign_location."'
									  where vtiger_leadaddress.leadaddressid = ".$oldlead_id." ");
									
									  $this->updated_records++;
									  unset($_SESSION['importid']);
							}
					}
					
					
					/*(When its first time with registration)Non-blank Update in old lead and assign to heiger camp and Campaign, Vehicle and Outlet is also updated*****By jitendra singh on 11 Jan 2014******/
					elseif($firsttime_mobile == 1 && $fieldValues[24] != '' && $firsttime_mobile == 1 && $temp_mobile_exist = 0){
					
						
						foreach($myarray as $key=>$row) {
							
								  if($row != "" && $key != 'mobile'){
									  $lead_update_qry = $db->query("UPDATE vtiger_leaddetails 
									  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
									  INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
									  inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
									  SET  modifiedby = ".$current_user->id.",modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
									  where vtiger_leaddetails.leadid = ".$oldlead_id." ");
									}
							}
							if($lead_update_qry){
								 	$campaign_details_update_qry = $db->query("UPDATE vtiger_leadaddress 
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leadaddress.leadaddressid 
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leadsubdetails.leadsubscriptionid
									  inner join vtiger_leaddetails on vtiger_leaddetails.leadid = vtiger_leadaddress.leadaddressid
									  inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_leadaddress.campaignid
									  SET smownerid = ".$current_user->id.",vtiger_leaddetails.outlet = ".$outlet_id.",vtiger_leadaddress.campaignid = ".$campaignid.",vtiger_leadaddress.campaigntype = '".$campaign_type."'
									  ,vtiger_leadsubdetails.campaignlocation = '".$campaign_location."'
									  where vtiger_leadaddress.leadaddressid = ".$oldlead_id." ");
									  $this->updated_records++;	
									  unset($_SESSION['importid']);
							}
					}
					
					
					
					/* Non-blank Update in old lead  *****By jitendra singh on 11 Jan 2014******/
					elseif($mobileexist == 1 && $fieldValues[24] != '' && $new_campaign_priority <= $old_campaign_priority && $temp_mobile_exist = 0){
						
						foreach($myarray as $key=>$row) {
								  if($row != "" && $key != 'mobile' || $key != 'registrationno'){
									  $lead_update_qry = $db->query("UPDATE vtiger_leaddetails 
									  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid 
									  INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
									  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
									  SET modifiedtime = '".date("Y-m-d H:i:s",time())."', ".$key." = '".$row."'
									  where vtiger_leaddetails.leadid = ".$oldlead_id." ");
									}
							}
							if($lead_update_qry){
								$this->updated_records++;
							}
						}
				}
					
				$logerror = rtrim($logerror, "/");
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