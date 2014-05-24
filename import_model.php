<?php
require_once('include/utils/utils.php');
include('config.php');
global $adb;


$row = 1;
if (($handle = fopen("make.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
        for ($c=0; $c < 1; $c++) {
			
			$model =  $data[0];
			$make_id = $data[1];

			
if($row > 2 && $model != '' && $make_id != '')	{	

			
			$currentdatetime = date('Y-m-d H:i:s');         										
        	$crmid = $adb->getUniqueID("vtiger_crmentity"); 
			
			
				
			$adb->query("INSERT into vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,modifiedtime,viewedtime,status,version,presence,deleted)
        values('".$crmid."',1,'11','0','Services','','".$currentdatetime."','".$currentdatetime."','NULL','NULL','0','1','0')");
		
		$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='Services' and active = 1 ");
		  $resultnum = $adb->fetch_array($querynum);
		  $prefix = $resultnum['prefix'];
		  $cur_id = $resultnum['cur_id'];
		  $service_no = $prefix.$cur_id; 
		  $next_curr_id = $cur_id + 1;
		 $adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='Services' and active = 1 ");
		
		$adb->query("INSERT into vtiger_service (serviceid,service_no,model,make) values(".$crmid.",'".$service_no."','".$model."',".$make_id.")");
		$adb->query("INSERT into vtiger_servicecf (serviceid) values(".$crmid.")");
				
} // end if condition $row count
		}
			
    } 
    fclose($handle);
	echo "Updated Successfully.....";
}
?> 