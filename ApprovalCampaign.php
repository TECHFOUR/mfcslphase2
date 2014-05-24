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

if(isset($_REQUEST['urldataapprove']) && $_REQUEST['urldataapprove'] != "")
	$ulrdata = base64_decode($_REQUEST['urldataapprove']);

if(isset($_REQUEST['urldatareject']) && $_REQUEST['urldatareject'] != "")
	$ulrdata = base64_decode($_REQUEST['urldatareject']);

$ulrdata = explode("%*@",$ulrdata);
list($x,$sourceRecord) = explode("__",$ulrdata[0]);
list($x,$userid) = explode("__",$ulrdata[1]);
list($x,$approvalstatus) = explode("__",$ulrdata[2]);
//echo $sourceRecord.'____'.$userid.'____'.$approvalstatus ;die;
if($sourceRecord != "" && $userid != "" && $approvalstatus != "") {
	
		global $adb,$log, $current_user;
				
// Start for Approval Campaign
 
/**   Function used to send email
  *   $module 		-- current module
  *   $to_email 	-- to email address
  *   $from_name	-- currently loggedin user name
  *   $from_email	-- currently loggedin vtiger_users's email id. you can give as '' if you are not in HelpDesk module
  *   $subject		-- subject of the email you want to send
  *   $contents		-- body of the email you want to send
  *   $cc		-- add email ids with comma seperated. - optional
  *   $bcc		-- add email ids with comma seperated. - optional.
  *   $attachment	-- whether we want to attach the currently selected file or all vtiger_files.[values = current,all] - optional
  *   $emailid		-- id of the email object which will be used to get the vtiger_attachments
  */
   					
    
  //if($currentid != "" && $sourceRecord != "") {
		$sub_query1 = " AND vtiger_users.id = ".$userid." ";		
		$userDetails1 = getUserDetails($sub_query1);
		$email1 = $userDetails1['email'];
		$user_name1 = $userDetails1['user_name'];
		$depth1 = $userDetails1['depth'];
		$zone1 = $userDetails1['zone'];
		$rolename1 = $userDetails1['rolename'];
		$fullname1 = $userDetails1['fullname'];	
		$emailstatus1 = "emailstatus".$depth1;	
	  
	  	$campaign_qry = $adb->pquery("select smownerid,location, end_date, closingdate,  vtiger_campaign.campaignid as campid, campaignname, targetsize, 
		email3, email5, email8, ".$emailstatus1." from vtiger_campaign 
		INNER JOIN vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
		INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid 
		where vtiger_campaign.campaignid = ?", array($sourceRecord));		
  		if($adb->num_rows($campaign_qry) > 0) {
			$campaignid = $adb->query_result($campaign_qry,0,'campid');
			$campaign_no = $adb->query_result($campaign_qry,0,'campaignname');
			$budgetcost = $adb->query_result($campaign_qry,0,'targetsize');
			$location = $adb->query_result($campaign_qry,0,'location');
			$start_date = $adb->query_result($campaign_qry,0,'closingdate');
			$end_date = $adb->query_result($campaign_qry,0,'end_date');
			$report_email3 = $adb->query_result($campaign_qry,0,'email3');
			$report_email5 = $adb->query_result($campaign_qry,0,'email5');
			$report_email8 = $adb->query_result($campaign_qry,0,'email8');
			$smownerid = $adb->query_result($campaign_qry,0,'smownerid');
			$emailstatus_current = $adb->query_result($campaign_qry,0,$emailstatus1);
			
			$cmatrix_qry = $adb->query("select app_authority from vtiger_camatrixcf inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_camatrixcf.camatrixid where min_budget <= ".$budgetcost." and max_budget >= ".$budgetcost." and vtiger_crmentity.deleted = 0 ");		
			if($adb->num_rows($cmatrix_qry) > 0) {
				$cmatrix_res = $adb->fetch_array($cmatrix_qry);
				$app_authority = $cmatrix_res['app_authority'];
			}
			
		}		
	
	if($emailstatus_current == '0') {
					$crmid = $adb->getUniqueID("vtiger_crmentity");					
					$createrid = $userid;
					$currentdatetime = date("Y-m-d H:i:s");												
															
					$querynum = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='CampaignApproval' and active = 1");
					$resultnum = $adb->fetch_array($querynum);
					$prefix = $resultnum['prefix'];
					$cur_id = $resultnum['cur_id'];
					$CampaignApprovalNum = $prefix.$cur_id; 
					$next_curr_id = $cur_id + 1;
					$adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='CampaignApproval' and active = 1");					  					
					$callerName = $CampaignApprovalNum;
		 			$all_Values = array('campaignapproval'=>$CampaignApprovalNum,
									'cf_839'=> $approvalstatus,																
									'assigned_user_id'=>$createrid, 
									'createdtime'=>$currentdatetime, 
									'modifiedby'=>$createrid, 
									'draft_campaign'=>1,
									'record_id'=>$crmid,									
									'record_module'=>'CampaignApproval'
								);
								
					$query = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,
					modifiedtime,viewedtime,status,version,presence,deleted,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
					$adb->pquery($query, array($crmid, $createrid, $createrid, 0, "CampaignApproval", "", $currentdatetime, $currentdatetime, NULL, NULL, 0, 1, 0, $callerName));
					
					$adb->query("INSERT INTO vtiger_campaignapproval (campaignapprovalid, campaignapproval, cf_839) VALUES(".$crmid.",'".$CampaignApprovalNum."', '".$approvalstatus."')"); 
					
					$adb->query("INSERT INTO vtiger_campaignapprovalcf (campaignapprovalid) VALUES(".$crmid.")");
					
					$sql = "insert into vtiger_crmentityrel values (?,?,?,?)";
					$adb->pquery($sql, array($sourceRecord,'Campaigns',$crmid,'CampaignApproval'));
					
					// Start Save in Modtracker table ******************
			$thisid = $adb->getUniqueId('vtiger_modtracker_basic');					
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
					VALUES(?,?,?,?,?,?)', Array($thisid, $crmid, 'CampaignApproval',$current_user->id, date('Y-m-d H:i:s',time()), 2));
			foreach($all_Values as $key=>$row) {
				if($row != "")	{									
					$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,postvalue) VALUES(?,?,?)',
						Array($thisid, $key, $row));
				}
			}
														
// End Save in Modtracker table ******************
	
	$remarksdata = '';
	$remarks_qry = $adb->query("select concat(vtiger_users.first_name,' ',vtiger_users.last_name) as 'approvefullname', remark, cf_839 from vtiger_campaignapproval 
inner join vtiger_crmentityrel on vtiger_crmentityrel.relcrmid = vtiger_campaignapproval.campaignapprovalid 
inner join vtiger_campaign on vtiger_campaign.campaignid = vtiger_crmentityrel.crmid 
inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaignapproval.campaignapprovalid 
inner join vtiger_users  on vtiger_users.id = vtiger_crmentity.smownerid	
where vtiger_crmentity.deleted = 0 and vtiger_campaign.campaignid = ".$sourceRecord." ");	
	if($adb->num_rows($remarks_qry) > 0) {			
		while($row = $adb->fetch_array($remarks_qry)) {
			$fullname = $row['approvefullname'];
			$remark = $row['remark'];
			$approvestatus = $row['cf_839'];			
			$remarksdata .= '<tr><td>'.$fullname.'</td><td>'.$approvestatus.'</td><td>'.$remark.'</td></tr>';
		}		
	}	
	
	//echo 	$currentid.'___'.$sourceRecord.'___'.$approvalstatus.'___'.$app_authority;die;	
		//$remarksdata .= '<tr><td>'.$fullname1.'</td><td>'.$approvalstatus.'</td><td>'.$remark.'</td></tr>';
		$role_Manager = "";	
		switch($app_authority) {
			
			case "Head BD":
				$depth_start = 2;						
			break;
												
			case "BD Corporate Office":
				$depth_start = 3;							
			break;
			
			case "RBDM":
				$depth_start = 5;							
			break;			
		}
			
		$allemails = "";			
		switch($depth1) {			
			case 2:
			$depth_next = 0;			
			$allemails =  $report_email8.', '.$report_email5.', '.$report_email3;					
			break;
												
			case 3:
			$depth_next = 2;
			$role_Manager = "Head Business Development";									
			$allemails =  $report_email8.', '.$report_email5;
			break;
			
			case 5:
			$depth_next = 3;			
			$allemails =  $report_email8;
			$role_Manager = "BD Corporate";
			break;
			
			default:			
			$depth_next = 5;			
			$role_Manager = "RBDM ".$zone1;
		}
						
		$sub_query2 = " AND vtiger_role.rolename = '".$role_Manager."' AND vtiger_role.depth = ".$depth_next." ";
		
		$permission_up_report = 0;
		if($depth1 == $depth_start)
			$permission_up_report = 1;			
		
		
		$userDetails2 = getUserDetails($sub_query2);
		$id2 = $userDetails2['id'];
		$email2 = $userDetails2['email'];
		$user_name2 = $userDetails2['user_name'];
		$depth2 = $userDetails2['depth'];
		$zone2 = $userDetails2['zone'];
		$rolename2 = $userDetails2['rolename'];
		$fullname2 = $userDetails2['fullname'];
		
		$emailstatus1 = "emailstatus".$depth1;
		$email_field1 = "email".$depth1;
		
		$emailstatus2 = "emailstatus".$depth_next;									
		$email_field2 = "email".$depth_next;
		
				
$urldataapprove = 'campaignid__'.$sourceRecord.'%*@userid__'.$id2.'%*@approvalstatus__Approve';
$urldatareject = 'campaignid__'.$sourceRecord.'%*@userid__'.$id2.'%*@approvalstatus__Reject';
$urldataapprove = base64_encode($urldataapprove);
$urldatareject = base64_encode($urldatareject);

$descriptionsalesapproval = '<table width="80%" cellpadding="5" cellspacing="1" border="0" align="left">
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left" width="50%">Dear Manager , </td></tr>
						<tr><td align="left" colspan="2">I Here by request you to verify and approve/Reject the same. </td></tr>
						<tr><td align="left" colspan="2">The Campaign Details are as follows: </td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><th align="left">Campaign number : </th><td>'.$campaign_no.'</td></tr>
						<tr><th align="left">Budget Cost : </th><td>'.$budgetcost.'</td></tr>
						<tr><th align="left">Location : </th><td>'.$location.'</td></tr>
						<tr><th align="left">Start Date : </th><td>'.$start_date.'</td></tr>
						<tr><th align="left">End Date : </th><td>'.$end_date.'</td></tr>
						
						<tr><th align="left">Approved By</th><th align="left">Approval Status</th>
						
						<th width="36%" align="left">Remarks</th></tr>
						[RemarksData]
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left" colspan="2">Please approve this campaign by clicking the below link <br><a href="[SITEURL]index.php?module=CampaignApproval&view=Edit&sourceModule=Campaigns&sourceRecord='.$sourceRecord.'&relationOperation=true&picklistDependency=[]&cf_839=&remark=&sourceModule=Campaigns&sourceRecord='.$sourceRecord.'&relationOperation=true">Click Here To Login</a><br></td></tr>
						
						<tr><th align="left">Approval URL : </th><td>Approve this campaign by clicking the below link <br><a href="[SITEURL]ApprovalCampaign.php?urldataapprove='.$urldataapprove.'">Click Here To Approve</a><br></td></tr>
						
						<tr><th align="left">Reject URL : </th><td>Reject this campaign by clicking the below link <br><a href="[SITEURL]ApprovalCampaign.php?urldatareject='.$urldatareject.'">Click Here To Reject</a><br></td></tr>
						<tr><td align="left">Thanks and Regards </td></tr>
						<tr><td align="left">'.$fullname1.'</td></tr>
						</table>';
						
$descriptionsalesapproved = '<table width="80%" cellpadding="5" cellspacing="1" border="0" align="left">
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left" width="50%">Dear Manager ('.$fullname1.')</td></tr>
						<tr><td align="left" colspan="2">Your below Campaign is approved. </td></tr>						
						<tr><td>&nbsp;</td></tr>
						<tr><th align="left">Campaign number : </th><td>'.$campaign_no.'</td></tr>
						<tr><th align="left">Budget Cost : </th><td>'.$budgetcost.'</td></tr>
						<tr><th align="left">Location : </th><td>'.$location.'</td></tr>
						<tr><th align="left">Start Date : </th><td>'.$start_date.'</td></tr>
						<tr><th align="left">End Date : </th><td>'.$end_date.'</td></tr>
						<tr><th align="left">Approved By</th><th align="left">Approval Status</th>
						
						<th width="36%" align="left">Remarks</th></tr>

						[RemarksData]
						<tr><td>&nbsp;</td></tr>						
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="left">Thanks and Regards </td></tr>
						<tr><td align="left">'.$fullname1.'</td></tr>
						</table>';
						
			
	
//$site_URL = "http://mfcscrm.innov.co.in/";
		
$site_URL = "http://mfcslcrm.com/";									
$descriptionsalesapproval = str_replace("[SITEURL]",$site_URL,$descriptionsalesapproval);
$descriptionsalesapproval = str_replace("[RemarksData]",$remarksdata,$descriptionsalesapproval);

$descriptionsalesapproved = str_replace("[RemarksData]",$remarksdata,$descriptionsalesapproved);
		
		
		
		
		$cc = "";		
		if($allemails != "") {
			$allemails_alert = str_replace(',','\n',$allemails);											
		}				
		if($allemails != "")
			$cc  = $allemails;
		
		if($approvalstatus == "Approve") {
			$descriptions = $descriptionsalesapproval;
			$subject = "Campaign Approval Request for ".$fullname2;								
			$adb->query("UPDATE vtiger_campaign
			INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid 
			SET approval_stage = ".$id2.", ".$emailstatus1." = '1', ".$emailstatus2." = '0', ".$email_field1." = '".$email1."' 
			where vtiger_campaign.campaignid = ".$sourceRecord." ");
			$approvalmailalert = "Approval Campaign mail has been sent to this email id  $email2";
			if($permission_up_report == 1) {
				$descriptions = $descriptionsalesapproved;
				$subject = "Campaign Approved Request for ".$fullname1;
				$email2 = $email1;
				
				$approveddate = date('Y-m-d');				
				$adb->query("UPDATE vtiger_campaign 
				INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
				SET approveddate = '".$approveddate."', campaignstatus = 'Approved', approval_stage = '' , emailstatus8 = '1', emailstatus5 = '1', emailstatus3 = '1', emailstatus2 = '1' where vtiger_campaign.campaignid = ".$sourceRecord." ");																
				$approvalmailalert = "Approved Campaign mail has been sent to this email id  $allemails_alert";				
			}
		}
		else {
			$approvalmailalert = "Approval Campaign Canceled mail has been sent to this email id  $allemails_alert";
			$adb->query("UPDATE vtiger_campaign 
			INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid 
			SET emailstatus8 = '0', emailstatus5 = '1',approval_stage = ".$smownerid.", campaignstatus = 'Rejected' , emailstatus3 = '1', emailstatus2 = '1',  ".$email_field1." = '".$email1."' 
			where vtiger_campaign.campaignid = ".$sourceRecord." ");
			$descriptions = $descriptionsalesapproved;
			if($permission_up_report == 1) {
				$email2 = $email1;											
				$approvalmailalert = "Approval Campaign Canceled mail has been sent to this email id  $allemails_alert";				
			}			
			$subject = "Approval Campaign Canceled By ".$fullname1;
		}
		

		//$email2 = 'ajayk@techfoursolutions.com';
		//echo $email2.'___'.$user_name1.'___'.$subject.'___'.$cc; echo "<br>";
		//echo $approvalmailalert;die;	
		//$cc = 'ajayk@techfoursolutions.com ,ajay.kumar.iimt@gmail.com';				
		send_mail('',$email2,$user_name1,'',$subject,$descriptions,$cc);
 // }
		
// End for Approval Campaign 		

	
	}// End emailstatus check
	else {
		$approvalmailalert = "You are already perform this action.";
	}
} 
?>

<script>
	var approvalalert = "<?php echo $approvalmailalert; ?>";
	alert(approvalalert);
	window.close();
</script>