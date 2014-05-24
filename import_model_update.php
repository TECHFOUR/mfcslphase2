<?php
require_once('include/utils/utils.php');
include('config.php');
global $adb;


$row = 1;
if (($handle = fopen("model_update.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
        for ($c=0; $c < 1; $c++) {
			
			$model =  $data[0];
			$model_id = $data[1];

			
if($row > 2 && $model != '' && $model_id != '')	{	
			$updated = $adb->query("UPDATE vtiger_crmentity set label = '".$model."' where crmid = ".$model_id."");
			if($updated)
			$updated++;
	
} // end if condition $row count
		}
			
    } 
    fclose($handle);
	echo $updated." rows Updated Successfully.....";
}
?> 