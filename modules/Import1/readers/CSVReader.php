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

class Import_CSVReader_Reader extends Import_FileReader_Reader {
	

	public function getFirstRowData($hasHeader=true) {
		global $default_charset;

		$fileHandler = $this->getFileHandler();

		$headers = array();
		$firstRowData = array();
		$currentRow = 0;
		while($data = fgetcsv($fileHandler, 0, $this->request->get('delimiter'))) {
			if($currentRow == 0 || ($currentRow == 1 && $hasHeader)) {
				if($hasHeader && $currentRow == 0) {
					foreach($data as $key => $value) {
						$headers[$key] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $default_charset);
					}
				} else {
					foreach($data as $key => $value) {
						$firstRowData[$key] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $default_charset);
					}
					break;
				}
			}
			$currentRow++;
		}

		if($hasHeader) {
			$noOfHeaders = count($headers);
			$noOfFirstRowData = count($firstRowData);
			// Adjust first row data to get in sync with the number of headers
			if($noOfHeaders > $noOfFirstRowData) {
				$firstRowData = array_merge($firstRowData, array_fill($noOfFirstRowData, $noOfHeaders-$noOfFirstRowData, ''));
			} elseif($noOfHeaders < $noOfFirstRowData) {
				$firstRowData = array_slice($firstRowData, 0, count($headers), true);
			}
			$rowData = array_combine($headers, $firstRowData);
		} else {
			$rowData = $firstRowData;
		}

		unset($fileHandler);
		return $rowData;
	}

	public function read() {
		
		global $default_charset;

		$fileHandler = $this->getFileHandler();
		$status = $this->createTable();
		if(!$status) {
			return false;
		}

		$fieldMapping = $this->request->get('field_mapping');

		$i=-1;
		$total_records = 0; // Added by jitendra on 24 Dec2013
		
		
		
		// Code Added by jitendra on 28 Dec13
		$row = 1;
		$filename = "log_errors/Log_Errors_".date('d_m_Y_h_i_s').".csv";
		$_SESSION['log_error_path'] = $filename;
		$handle = fopen($filename, 'w+');
		fputcsv($handle, array('Errors','Mobile (+91-)','First Name','Last Name','Home Address1','Society Name','Home Address2','Home Address3','Home State','Home City','Home Pin Code','Company Name','Office Address2',
'Office Address3','Office State','Office City','Office Pin Code','Personal Email ID','Date Of Birth','Occupation','Organization Name','Designation','Official Email ID','Make',
'Model','Registration No','Campaign Id','Insurance Date','Insurance Company','Odometer Reading','Last Service date','Date Of Sale','Outlet'));
		//End
		
		$InArray=array();
		while($data = fgetcsv($fileHandler, 0, $this->request->get('delimiter'))) {
			$total_records++; // Added by jitendra on 24 Dec2013
			$i++;
			if($this->request->get('has_header') && $i == 0) continue;
			$mappedData = array();
			$allValuesEmpty = true;
			foreach($fieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$mappedData[$fieldName] = $fieldValue;
				if($this->request->get('file_encoding') != $default_charset) {
					$mappedData[$fieldName] = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $default_charset);
				}
				if(!empty($fieldValue)) $allValuesEmpty = false;
			}
			if($allValuesEmpty) continue;
			$fieldNames = array_keys($mappedData);
			$fieldValues = array_values($mappedData);
			$this->addRecordToDB($fieldNames, $fieldValues,$i,$row,$filename,$handle);
		}
		$_SESSION['updated_records'] = $this->updated_records;  // Added by jitendra on 14 jan14
		fclose($handle);
		if($_REQUEST['module'] == 'Leads'){// Added by jitendra on 24 Dec2013
		$_SESSION['total_records'] = $total_records;
		//$_SESSION['total_records'] = $this->updated_records;
		}
		unset($fileHandler);
	}
}
?>
