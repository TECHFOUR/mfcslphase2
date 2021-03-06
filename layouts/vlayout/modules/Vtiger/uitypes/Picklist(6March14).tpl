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
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}


{*<!-- Code modified by jitendra singh[TECHFOUR] -->*}
{if $MODULE eq 'Campaigns' && $FIELD_MODEL->get("name") eq "campaigntype" && $RECORD_ID neq '' && $DRAFT_CAMPAIGNS eq '0' }
	<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>
    <div style="display:none">
    <select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
    </div>
   
 {*<!-- Add new code from jitendra singh to change the default value of picklist 2 Dec 2013 -->*}
{elseif $MODULE eq 'Users' && ($FIELD_MODEL->get("name") eq "time_zone" || $FIELD_MODEL->get("name") eq "language") }
	<select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->get("name") eq "time_zone"}
        <option value="Asia/Kolkata">(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
    	{/if}
        {if $FIELD_MODEL->get("name") eq "language"}
        <option value="en_us">US English</option>
    	{/if}
	</select>
  {*<!-- End -->*}
  
 

  
  
   {elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && ($FIELD_MODEL->get("name") eq "activitytype") }
 
 
 	{*<!-- Add code by jitendra singh to make field readonly when Customer type will be Individual -->*}
     {if ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && $customertype eq "Individual" && $FIELD_MODEL->get("name")}
  
    <select disabled class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
    
     <div style="display:none">
    <select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
    </div>
   
    {*<!-- End code by jitendra singh to make field readonly when Customer type will be Individual -->*}
 
  {else}
  <select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
   {/if}
  
  
     {elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && ($FIELD_MODEL->get("name") eq "cf_903") }
     <!--For sub disposition --> 
   
  <div class="{if $FIELD_MODEL->get('fieldvalue') eq ''} hide  {/if} SubDispositions massEditActiveField">
  <select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
  </div>
  
  
  {elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && ($FIELD_MODEL->get("name") eq "cf_895") }
     <!--  Activity Status -->
  <div class="ActivityStatusR massEditActiveField">
  <select  class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
 
  </div>
  
  
  {*<!-- Add code by jitendra singh to make field readonly when Customer type will be Individual -->*}
     {elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && $customertype eq "Individual" && $FIELD_MODEL->get("name") eq "eventstatus"}
  
    <select disabled class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
    
     <div style="display:none">
    <select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
    </div>
   
    {*<!-- End code by jitendra singh to make field readonly when Customer type will be Individual -->*}
  
  
  
  {*<!--Start Code to hide actual field in campaing module when Campaign status will be "Held" and Approval Status will be "Approved" by jitendra singh[TECHFOUR] -->*}

{elseif $MODULE eq 'Campaigns' &&  $FIELD_MODEL->get('name') eq 'expectedresponse' && $CAMPAIGN_STATUS eq 'Held' && $APPROVAL_STATUS eq 'Approved'}
<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>

<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="hidden" class="input-large" name="{$FIELD_MODEL->getFieldName()}"
value="{$FIELD_MODEL->get('fieldvalue')}"/>
{*<!--End Code to hide actual field in campaing module when Campaign status will be "Held" and Approval Status will be "Approved" by jitendra singh[TECHFOUR] -->*}

 
 {*<!-- Start code to make non-editable fields in user module for all user expect Admin -->*}

{elseif $MODULE eq 'Users' && $CURRENT_USER neq '1' && $FIELD_MODEL->get("name") eq 'lead_view'}
<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>

<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="hidden" class="input-large" name="{$FIELD_MODEL->getFieldName()}"
value="{$FIELD_MODEL->get('fieldvalue')}"/>

{*<!-- End code to make non-editable fields in user module for all user expect Admin -->*}
 
 
 {else} 
 
<select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	</select>
{/if}
{/strip}