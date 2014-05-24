{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
    {include file='DetailViewBlockView.tpl'|@vtemplate_path:'Vtiger' RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}

    {assign var="IS_HIDDEN" value=false}
	
		{if $MAIN_MODULE eq 'Leads'}
        <!--Code added for popup Ishwar 03-JAN-14--->	
		<link type="text/css" href="popupdragable/jquery_002.css" rel="stylesheet">
		<script type="text/javascript" src="popupdragable/index.js"></script>

		<script type="text/javascript" src="popupdragable/jquery_004.js"></script>

		<div id="example8" class="example_block">
	
   		<div class="demo">
				<div id="window_block8" style="display:none;">
					<div style="padding:10px;">
						
						<div  style="font-size:12px;">
						<div style="color:#C99F58"><b><u>1- Lead Information <a href="index.php?module=Leads&view=Detail&record={$lead_id}&mode=showDetailViewByMode&requestMode=full" target="_blank">[{$lead_no}]</a></u></b></div>{$mytest}
                      
                        <table>
                        <tr><td style="color:#999999;font-size: 12px;">First Name</td><td>{if $firstname eq ''}- -{else}{$firstname}{/if}</td></tr>
                        <tr><td style="color:#999999">Last Name</td><td>{if $lastname eq ''}- -{else}{$lastname}{/if}</td></tr>
                        <tr><td style="color:#999999">Mobile No</td><td>{if $mobile eq ''}- -{else}{$mobile}{/if}</td></tr>
                        <tr><td style="color:#999999">Outlet</td><td>{if $outletname eq ''}- -{else}{$outletname}{/if}</td></tr>
                        <tr><td style="color:#999999">Regi No</td><td>{if $registrationno eq ''}- -{else}{$registrationno}{/if}</td></tr>
                        <tr><td style="color:#999999">Make</td><td>{if $make1 eq ''}- -{else}{$make1}{/if}</td></tr>
                        <tr><td style="color:#999999">Model</td><td>{if $model1 eq ''}- -{else}{$model1}{/if}</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        </table>
                        
                        <div style="color:#C99F58"><b><u>2- Lead Source</u></b></div>{$mytest}
                        
                      
                        <table>
                        <tr><td style="color:#999999;font-size: 12px;">Camp Type</td><td>{if $camp_type eq ''}- -{else}{$camp_type}{/if}</td></tr>
						<tr><td style="color:#999999">Camp Location</td><td>{if $campaign_location eq ''}- -{else}{$campaign_location}{/if}</td></tr>
                        <tr><td style="color:#999999">Camp Start Date</td><td>{if $camp_start_date eq ''}- -{else}{$camp_start_date}{/if}</td></tr>
                        <tr><td style="color:#999999">Camp End Date</td><td>{if $camp_end_date eq ''}- -{else}{$camp_end_date}{/if}</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        </table>
                      
                        <div style="color:#C99F58"><b><u>3- Related Leads</u></b></div>
                         <table>
                        <tr><td style="color:#999999">Total No of Leads:</td><td>{$no_of_rellead}</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        {if $no_of_rellead eq 'No Related Leads'}
                        
                        {else}
                            <tr><td style="color:#999999">Registraion No</td><td style="color:#999999">Outlet</td></tr>
                            {if $no_of_rellead eq '1'}
                            	<tr><td>{if $registrationno1 eq ''}- -{else}{$registrationno1}{/if}</td><td>{if $outletname1 eq ''}- -{else}{$outletname1}{/if}</td></tr>
                            {else}
                                <tr><td>{if $registrationno1 eq ''}- -{else}{$registrationno1}{/if}</td><td>{if $outletname1 eq ''}- -{else}{$outletname1}{/if}</td></tr>
                                <tr><td>{if $registrationno2 eq ''}- -{else}{$registrationno2}{/if}</td><td>{if $outletname2 eq ''}- -{else}{$outletname2}{/if}</td></tr>
                            {/if}
                            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        {/if}
                        </table>
                        <div style="color:#C99F58"><b><u>4- Activity History</u></b></div>
                        <table>
                        <tr><td style="color:#999999">Activity Type</td><td style="color:#999999">Modified Time</td><td style="color:#999999">Activity Status</td></tr>
                        <tr><td>{$activitytype1}</td><td>{$modifiedtime1}</td><td>{$eventstatus1}</td></tr>
                        <tr><td>{$activitytype2}</td><td>{$modifiedtime2}</td><td>{$eventstatus2}</td></tr>
                       
                        </table>
                      </div>
					</div>
				</div>
				<!--<input value="Click here to know about MFCS Lead related information" onClick="createCustWindow();" type="button">-->
			</div>
		</div>
	{/if}
	<!--End code addded by Ishwar 03-JAN-14-->
	
	
	
	
    <!--<table class="table table-bordered equalSplit detailview-table">
		<thead>
		<tr>
				<th class="blockHeader" colspan="4">
						<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if} "  src="{vimage_path('arrowRight.png')}" data-mode="hide" data-id='INVITE_USER_BLOCK_ID'>
						<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}"  src="{vimage_path('arrowDown.png')}" data-mode="show" data-id='INVITE_USER_BLOCK_ID'>
						&nbsp;&nbsp;{vtranslate('LBL_INVITE_USER_BLOCK',{$MODULE_NAME})}
				</th>
		</tr>
		</thead>
        <tr>
            <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_INVITE_USERS',$MODULE_NAME)}</label></td>
            <td class="fieldValue">
                 {foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
					{if in_array($USER_ID,$INVITIES_SELECTED)}
                        {$USER_NAME}
                        <br>
                    {/if}
                {/foreach}
            </td>
        </tr>
   </table>-->
{/strip}