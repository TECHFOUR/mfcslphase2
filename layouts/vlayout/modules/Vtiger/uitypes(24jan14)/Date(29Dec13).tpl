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
{assign var="dateFormat" value=$USER_MODEL->get('date_format')}


<div class="input-append row-fluid">
	<div class="row-fluid date">
		{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
        
        {*<!-- Code modified by jitendra singh[TECHFOUR] -->*}
        
        {if $MODULE eq 'Campaigns' && ($FIELD_MODEL->get("name") eq "closingdate" || $FIELD_MODEL->get("name") eq "end_date") && $RECORD_ID neq '' && $DRAFT_CAMPAIGNS eq '0' && $DEPTH eq '6'}
		<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>
        <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="hidden" class="span9 dateField" name="{$FIELD_MODEL->getFieldName()}" data-date-format="{$dateFormat}"
			type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}'
             {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} />
			 
		   {elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && ($FIELD_MODEL->get("name") eq "app_book_date") }
	 		
			  <div class="{if $FIELD_MODEL->get('fieldvalue') eq ''} hide  {/if} AppbookingDate massEditActiveField">
             
			<input onchange="ValidateDateForAppBook(this.value,this.id,'{$dateFormat}');ValidateHideDriverPickup(this.value,'{$MODULE}_editView_fieldName_driver_pickup','{$dateFormat}');" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" style="width:190px;" class="span9 dateField" name="{$FIELD_MODEL->getFieldName()}" data-date-format="{$dateFormat}"
			type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}'
             {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} />
             <span class="add-on"><i class="icon-calendar"></i></span>
   </div>
		
		
		
		
	
		
		   {elseif $MODULE eq 'Driver'  && ($FIELD_MODEL->get("name") eq "cf_881") } 
		   <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" onchange="validateDriverPickupdate(this.value,'{$dateFormat}',this.id)" type="text" class="span9 dateField" name="{$FIELD_MODEL->getFieldName()}" data-date-format="{$dateFormat}"
			type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}'
             {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} />
             <span class="add-on"><i class="icon-calendar"></i></span>

		{else}
		
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="span9 dateField" name="{$FIELD_MODEL->getFieldName()}" data-date-format="{$dateFormat}"
			type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}'
             {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} />
             <span class="add-on"><i class="icon-calendar"></i></span>
		{/if}
        
	</div>
</div>

{/strip}