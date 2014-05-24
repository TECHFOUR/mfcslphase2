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
<div class='editViewContainer'>
	<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
		{/if}
		{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
		{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
		{if $IS_PARENT_EXISTS}
			{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
			<input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}" />
			<input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}" />
		{else}
			<input type="hidden" name="module" value="{$MODULE}" />
		{/if}
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="{$RECORD_ID}" />
		<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
		<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
		{if $IS_RELATION_OPERATION }
			<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
			<input type="hidden" id="sourceRecord" name="sourceRecord" value="{$SOURCE_RECORD}" />
			<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
		{/if}
		<div class="contentHeader row-fluid">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		{if $RECORD_ID neq ''}
			<span class="span8 font-x-x-large textOverflowEllipsis" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}</span>
		{else}
			<span class="span8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</span>
		{/if}
			<span class="pull-right">
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</span>
		</div>
	<!--Code added for popup Ishwar 02-JAN-14--->	

	{if $MODE eq 'edit' && $MAIN_MODULE eq 'Leads'} 
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
	<!--End code addded by Ishwar 02-JAN-14-->

		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			
		{*<!--Start code to hide related fields in acitvity form by jitendra singh -->*}	
		{if $MODULE eq 'Events' && $BLOCK_LABEL eq 'LBL_RELATED_TO'}
        
        {else}	
		<table class="table table-bordered blockContainer showInlineTable">
			<tr>
				<th class="blockHeader" colspan="4">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
			</tr>
			<tr>
			{assign var=COUNTER value=0}
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}

				{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
				{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19"}
					{if $COUNTER eq '1'}
						<td></td><td></td></tr><tr>
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				{if $COUNTER eq 2}
					</tr><tr>
					{assign var=COUNTER value=1}
				{else}
					{assign var=COUNTER value=$COUNTER+1}
				{/if}
				<td class="fieldLabel">
					{if $isReferenceField neq "reference"}<label class="muted pull-right marginRight10px">{/if}
						{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
						{if $isReferenceField eq "reference"}
							{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
							{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
							{if $REFERENCE_LIST_COUNT > 1}
								{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
								{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
								{if !empty($REFERENCED_MODULE_STRUCT)}
									{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
								{/if}
								<span class="pull-right">
									{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
								
					<!--Code added by Ishwar 2-JAN-2014-->			
					{if $MODE eq 'edit'} <input type="hidden" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" value="{$REFERENCED_MODULE_NAME}"> {/if}
					<!--End code added by Ishwar-->
					
								<select  {if $MODE eq 'edit'} disabled="disabled" {/if}  id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="chzn-select referenceModulesList streched" style="width:140px;">
										<optgroup> 
											{foreach key=index item=value from=$REFERENCE_LIST}
												<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $MODULE)}</option>
											{/foreach}
										</optgroup>
									</select>
								</span>
							{else}
								<label class="muted pull-right marginRight10px">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
							{/if}
						{else if $FIELD_MODEL->get('uitype') eq "83"}
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
						
                        {else if ($MODULE eq 'Events' || $MODULE eq 'Calendar') && ($FIELD_MODEL->get('label') eq 'Activity Status' || $FIELD_MODEL->get('label') eq 'Appointment Booking Date' || $FIELD_MODEL->get('label') eq 'Appointment Booking Time' || $FIELD_MODEL->get('label') eq 'Driver Pickup' || $FIELD_MODEL->get('label') eq 'Sub Dispositions' || $FIELD_MODEL->get('label') eq 'Followup on') && $customertype eq 'Individual'}
                        <span style="color:#096">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</span>
                        
                        {else}
							{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
						{/if}
					{if $isReferenceField neq "reference"}</label>{/if}
				</td>
				{if $FIELD_MODEL->get('uitype') neq "83"}
					<td class="fieldValue" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
						<div class="row-fluid">
							<span class="span10">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
							</span>
						</div>
					</td>
				{/if}
				{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype"}<td></td><td></td>{/if}
				{if $MODULE eq 'Events' && $BLOCK_LABEL eq 'LBL_EVENT_INFORMATION' && $smarty.foreach.blockfields.last }	
					{include file=vtemplate_path('uitypes/FollowUp.tpl',$MODULE) COUNTER=$COUNTER}
				{/if}	
			{/foreach}
			</tr>
			</table>
          {/if}  
		{/foreach}
{/strip}