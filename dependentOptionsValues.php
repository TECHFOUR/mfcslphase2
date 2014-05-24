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

$fetchdata = $_REQUEST['fetchdata'];
$typevalue = $_REQUEST['type'];
$outletname0 = "Select an Option";

$outletname1 = "All";
if($fetchdata == "outletname") {
	
	$fieldname1 = "fullname";
	$region = "";					
	$query_outlet = "SELECT vtiger_users.id AS useris, CASE WHEN(vtiger_users.last_name NOT LIKE '' AND vtiger_crmentity.crmid!='' ) 
					THEN CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) ELSE vtiger_groups.groupname END AS $fieldname1 FROM vtiger_users 
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_users.id
					INNER JOIN vtiger_outletmaster ON vtiger_outletmaster.outletmasterid = vtiger_users.cf_775 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_outletmaster.outletmasterid
					INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_outletmaster.outletmasterid
					WHERE vtiger_crmentity.deleted = 0 AND vtiger_users.status = 'Active' AND vtiger_outletmastercf.outlet = '$typevalue' ORDER BY $fieldname1";
					
	$outletusers = getAllQueryResult($query_outlet, $fieldname1, $region);	
	$index = 2;
	$li = '';
	$indexid0 = "outlet_id_chzn_g_0";
	$indexid1 = "outlet_id_chzn_o_1";
	$indexid2 = "outlet_id_chzn_o_2";
	$li .= "<li  class='active-result group-option' style='display: list-item;' id=\"" . $indexid0 . "\"></li>";
	$li .= "<li  class='active-result group-option result-selected' style='' id=\"" . $indexid1 . "\">" . $outletname0 . "</li>";
	$li .= "<li  class='active-result group-option' style='' id=\"" . $indexid2 . "\">" . $outletname1 . "</li>";	
	foreach($outletusers as $key=>$row) {
		list($username, $regionname) = explode("#",$row);
			$indexid = "outlet_id_chzn_o_".$index;																			
			$li .= "<li  class='active-result group-option' style=''  id=\"" . $indexid . "\">" . $username . "</li>";			
	$index++;	
	}
	echo $li;
}

if($fetchdata == "regionname") {
	$fieldname1 = "outlet";
	$region = "cf_781";					
	$query_outlet = "SELECT $fieldname1, $region FROM vtiger_outletmaster 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_outletmaster.outletmasterid
			INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_outletmaster.outletmasterid
			WHERE vtiger_crmentity.deleted = 0 ";
			
	$sub_query = "AND outlet NOT IN('Regional Manager', 'Tele Caller', 'BD Corporate', 'RBDM', 'Business Manager', 'Corporate') ORDER BY $fieldname1";
	
	$query = $query_outlet.' '.$sub_query;		
	$outletvalues = getAllQueryResult($query, $fieldname1, $region);
	
	$index = 2;
	$li = '';
	$indexid0 = "outlet_id_chzn_o_0";
	$indexid1 = "outlet_id_chzn_o_1";
	$li .= "<li  class='active-result result-selected' style='' id=\"" . $indexid0 . "\">" . $outletname0 . "</li>";
	$li .= "<li  class='active-result' style='' id=\"" . $indexid1 . "\">" . $outletname1 . "</li>";
	if($typevalue == "All")
		$typevalue = "";
	foreach($outletvalues as $key=>$row) {
		list($outletname, $regionname) = explode("#",$row);
			$indexid = "outlet_id_chzn_o_".$index;
		if($regionname === $typevalue) {																	
			$li .= "<li  class='active-result' style=''  id=\"" . $indexid . "\">" . $outletname . "</li>";	
		}
		elseif($regionname != $typevalue && $typevalue == "")
			$li .= "<li  class='active-result'  id=\"" . $indexid . "\">" . $outletname . "</li>";	
	$index++;	
	}
	echo $li;
}

function getAllQueryResult($sSQL, $field, $region) {		
	global $adb;
	$sSQL = $adb->query($sSQL);
	if($adb->num_rows($sSQL) > 0) {
		$outlet_value = array();
		while($row = $adb->fetch_array($sSQL)) {
			$option = $row[$field].'#'.$row[$region];
			$outlet_value[$row[$field]] = $option;
		}											
	}
	return $outlet_value;	
}							
?>