<?php

require_once('modules/Emails/mail.php');
include('config.php');

global $adb,$log, $current_user;
//echo $_SERVER['HTTP_REFERER'];
$leadid = $_REQUEST['id'];
$driverdate = $_REQUEST['driverdate'];
$dateformat = $_REQUEST['dateformat'];

//echo $leadid.'__'.$driverdate.'__'.$dateformat;die;

if($dateformat != "dd-mm-yyyy")
   $driverdate = str_replace("-","/",$driverdate);
   $driverdate = strtotime($driverdate);
   
 $Qry = $adb->query("select app_book_date from vtiger_leadscf where leadid = $leadid");		
  if($adb->num_rows($Qry) > 0) {
		$row = $adb->fetch_array($Qry);
		$app_book_date = strtotime($row['app_book_date']);
	}
	
	

$new_app_book_date = strtotime('-1 days',$app_book_date);

if($app_book_date == $driverdate || $new_app_book_date == $driverdate)
print 1;
else 
print $row['app_book_date'];