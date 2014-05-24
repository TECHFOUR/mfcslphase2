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
{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
{assign var="currentDate" value=Vtiger_Date_UIType::getDisplayDateValue('')}
{assign var="time" value=Vtiger_Time_UIType::getDisplayTimeValue(null)}
{assign var="currentTimeInVtigerFormat" value=Vtiger_Time_UIType::getTimeValueInAMorPM($time)}
{if $COUNTER eq 2}

</tr><tr class="{if $FIELD_MODEL->get('fieldvalue') eq ''} hide  {/if} followUpContainer massEditActiveField">
	{assign var=COUNTER value=1}
{else}
	{assign var=COUNTER value=$COUNTER+1}
	
<td></td></tr><tr class="{if $FIELD_MODEL->get('fieldvalue') eq ''} hide  {/if} followUpContainer massEditActiveField">
	
	
{/if}

<td class="fieldLabel">
	<label class="muted pull-right marginRight10px" >
		<input style="display:none;" id="followup" name="followup" type="checkbox" class="alignTop" />
        {if ($MODULE eq 'Events' || $MODULE eq 'Calendar') && $customertype eq 'Individual'}
		 	<span style="color:#096">{vtranslate('LBL_HOLD_FOLLOWUP_ON',$MODULE)}</span>
        {else}
        	{vtranslate('LBL_HOLD_FOLLOWUP_ON',$MODULE)}
        {/if}
	</label>	
</td>
<td class="fieldValue">
	<div>
		<div class="input-append row-fluid">
			<div class="span10 row-fluid date">
				<input name="followup_date_start" type="text" class="span9 dateField" data-date-format="{$dateFormat}" type="text" id = "followup_date_start"  value="{$currentDate}" onchange="validateFollowupdate(this.value,'{$dateFormat}',this.id)"/>
				<span class="add-on"><i class="icon-calendar"></i></span>
			</div>	
		</div>		
	</div>
	<div>
		<div class="input-append time">
			<input type="text" name="followup_time_start" class="timepicker-default input-small" value="{$currentTimeInVtigerFormat}" />
			<span class="add-on cursorPointer">
				<i class="icon-time"></i>
			</span>
		</div>	
	</div>
</td>
<td></td><td></td>