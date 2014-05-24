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

	public function addRecordToDB($fieldNames, $fieldValues,$i,$row,$filename,$handle) {
		$db = PearDatabase::getInstance();

		$tableName = Import_Utils_Helper::getDbTableName($this->user);
	if($_REQUEST['module'] == 'Leads'){
		/*$importid = $_SESSION['importid'];
			unset($_SESSION['importid']); 
		echo $importid; die;*/
		
		$mobile = str_replace('', '-', $fieldValues[0]); // Replaces all spaces with hyphens.
		$fieldValues[0] = substr(preg_replace('/[^0-9\-]/', '', $mobile),-10);
		
		$registrationno = str_replace('', '-', $fieldValues[24]); // Replaces all spaces with hyphens.
		$fieldValues[24] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9\']/', '', $registrationno),-11)); // Removes special chars.
			
			
		if($fieldValues[1] == "") {
			$fieldValues[1] = $fieldValues[2];
			$fieldValues[2] = ".";
		}
				
		$duplicateqry = mysql_query("select mobile from vtiger_leadscf inner join vtiger_crmentity 
		on  vtiger_crmentity.crmid = vtiger_leadscf.leadid inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid 
		where vtiger_crmentity.deleted = 0 and (mobile = '".trim($fieldValues[0])."' and registrationno = '".trim($fieldValues[24])."')");
		
		
		if($fieldValues[0] != '' && mysql_num_rows($duplicateqry) == 0 && strlen($fieldValues[0]) == 10 && ($fieldValues[1] != "" &&  $fieldValues[2] != "")){
				
				$db->pquery('INSERT INTO '.$tableName.' ('. implode(',', $columnNames).') VALUES ('. generateQuestionMarks($fieldValues) .')', $fieldValues);
		}else{
				$logerror = "";
				if($fieldValues[0] == '')
				$logerror .= "Mobile is Empty/";
				if(mysql_num_rows($duplicateqry) > 0)
				$logerror .= "Mobile already Exist/";
				if(strlen($fieldValues[0]) != 10 && $fieldValues[0] != '')
				$logerror .= "Mobile number is not Valid/";
				if($fieldValues[1] == "" && $fieldValues[2] == ".")
				$logerror .= "First Name and Last Name is Empty/";
				$logerror = rtrim($logerror, "/");
				fputcsv($handle, array($fieldValues[0],$fieldValues[1],$fieldValues[2],$fieldValues[3],$fieldValues[4],$fieldValues[5],$fieldValues[6],$fieldValues[7],$fieldValues[8],$fieldValues[9],
				$fieldValues[10],$fieldValues[11],$fieldValues[12],$fieldValues[13],$fieldValues[14],$fieldValues[15],$fieldValues[16],$fieldValues[17],$fieldValues[18],$fieldValues[19],$fieldValues[20],
				$fieldValues[21],$fieldValues[22],$fieldValues[23],$fieldValues[24],$fieldValues[25],$fieldValues[26],$fieldValues[27],$fieldValues[28],$fieldValues[29],$fieldValues[30],$fieldValues[31],$logerror));
				
			
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