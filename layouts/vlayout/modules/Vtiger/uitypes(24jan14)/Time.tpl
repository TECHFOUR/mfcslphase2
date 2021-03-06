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
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}


<div class="input-append time">
	{*<!-- Code modified by jitendra singh[TECHFOUR] -->*}
    {if $MODULE eq 'Campaigns' && ($FIELD_MODEL->get("name") eq "start_time" || $FIELD_MODEL->get("name") eq "end_time") && $RECORD_ID neq '' && $DRAFT_CAMPAIGNS eq '0' && $DEPTH eq '6'}
	<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>
     <input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="hidden" {if $MODULE eq 'Calendar' || $MODULE eq 'Events'}data-format="{$TIME_FORMAT}"{/if} class="timepicker-default input-small" value="{$FIELD_VALUE}" name="{$FIELD_MODEL->getFieldName()}"
	data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}' />
	
	{*<!--Code added by Ishwar-->*}
	{elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && ($FIELD_MODEL->get("name") eq "app_book_time") }
	  <div class="{if $FIELD_MODEL->get('fieldvalue') eq ''} hide  {/if} ActivityStatusEventdiv massEditActiveField"><input  id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="text" {if $MODULE eq 'Calendar' || $MODULE eq 'Events'}data-format="{$TIME_FORMAT}"{/if} class="timepicker-default input-small" value="{if $FIELD_VALUE eq ''}09:00 AM{else} {$FIELD_VALUE} {/if}" name="{$FIELD_MODEL->getFieldName()}"
	data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}' />
	<span class="add-on cursorPointer">
        <i class="icon-time"></i>
    </span>
	</div>
    
    
     {*<!-- Add code by jtiendra singh to make field readonly when Customer type will be Individual -->*}
     {elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && $customertype eq "Individual" && ($FIELD_MODEL->get("name") eq "time_start" || $FIELD_MODEL->get("name") eq "time_end")}
     <input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="text" {if $MODULE eq 'Calendar' || $MODULE eq 'Events'}data-format="{$TIME_FORMAT}"{/if} class="timepicker-default input-small" value="{$FIELD_VALUE}" name="{$FIELD_MODEL->getFieldName()}"
	data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}' readonly="readonly" />
    <span class="add-on cursorPointer">
        <i class="icon-time"></i>
    </span>   
    {*<!-- End code by jtiendra singh to make field readonly when Customer type will be Individual -->*}
	
    
    
    {else}
    <input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="text" {if $MODULE eq 'Calendar' || $MODULE eq 'Events'}data-format="{$TIME_FORMAT}"{/if} class="timepicker-default input-small" value="{$FIELD_VALUE}" name="{$FIELD_MODEL->getFieldName()}"
	data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}' />
    <span class="add-on cursorPointer">
        <i class="icon-time"></i>
    </span>
    {/if}

</div>
{/strip}