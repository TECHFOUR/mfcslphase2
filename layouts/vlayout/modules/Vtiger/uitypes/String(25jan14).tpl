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
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}


{*<!--Start Make field Non-Editable in Detail View by jitendra singh on 22 Jan 2014 -->*}
{if $MODULE eq 'Leads' && ($FIELD_MODEL->get("name") eq "state" || $FIELD_MODEL->get("name") eq "code")}
<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" 
type="text" 
	   class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if}" 
	   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	   name="{$FIELD_MODEL->getFieldName()}" 
	   value="{$FIELD_MODEL->get('fieldvalue')}"
		onkeyup="ValidatePriceField(this.id);"	
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} 

maxlength="6" />

{*<!--End Make field Non-Editable in Detail View by jitendra singh on 22 Jan 2014 -->*}



{*<!-- Code modified by jitendra singh[TECHFOUR] -->*}
{else if $MODULE eq 'Campaigns' && ($FIELD_MODEL->get("name") eq "location" || $FIELD_MODEL->get("name") eq "targetaudience" || $FIELD_MODEL->get("name") eq "sponsor" || $FIELD_MODEL->get("name") eq "leaflet" || $FIELD_MODEL->get("name") eq "tag" || $FIELD_MODEL->get("name") eq "poster") && $RECORD_ID neq '' && $DRAFT_CAMPAIGNS eq '0' && $DEPTH eq '6'}
<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>

<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" 

  {if $MODULE eq "Leads" && $FIELD_MODEL->get("name") eq "registrationno"} 
  onkeyup="ValidateCustomFields();"
maxlength = "11"
{/if}
    

type="hidden" 
	   class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if}" 
	   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	   name="{$FIELD_MODEL->getFieldName()}" 
	   value="{$FIELD_MODEL->get('fieldvalue')}"
		{if ($FIELD_MODEL->get('uitype') eq '106' && $MODE neq '') || $FIELD_MODEL->get('uitype') eq '3' 
				|| $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} 
				readonly 
		{/if} 
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />

{*<!-- End -->*}


{*<!-- Add code by jtiendra singh to make field readonly when Customer type will be Individual -->*}
{elseif ($MODULE eq 'Events' || $MODULE eq 'Calendar' ) && $FIELD_MODEL->get("name") eq "subject" && $customertype eq "Individual"}

<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" 

  {if $MODULE eq "Leads" && $FIELD_MODEL->get("name") eq "registrationno"} 
  onkeyup="ValidateCustomFields();"
maxlength = "11"
{elseif $MODULE eq 'Events' && $FIELD_MODEL->get("name") eq "location"}
    style = "display:none;"
{/if}
type="text" 
	   class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if}" 
	   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	   name="{$FIELD_MODEL->getFieldName()}" 
	   value="{$FIELD_MODEL->get('fieldvalue')}"
		{if ($FIELD_MODEL->get('uitype') eq '106' && $MODE neq '') || $FIELD_MODEL->get('uitype') eq '3' 
				|| $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} 
				readonly 
		{/if} 
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} readonly />
{*<!-- End code by jtiendra singh to make field readonly when Customer type will be Individual -->*}



{*<!-- Add code for target module to enter only number -->*}
{elseif ($MODULE eq 'Targets' && $FIELD_MODEL->get("name") eq "target") || ($MODULE eq 'Campaigns' && ($FIELD_MODEL->get("name") eq "targetaudience" || $FIELD_MODEL->get("name") eq "sponsor" || $FIELD_MODEL->get("name") eq "leaflet" || $FIELD_MODEL->get("name") eq "tag" || $FIELD_MODEL->get("name") eq "poster"))}

<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" 

  {if $MODULE eq "Leads" && $FIELD_MODEL->get("name") eq "registrationno"} 
  onkeyup="ValidateCustomFields();"
maxlength = "11"
{/if}
onkeyup="ValidatePriceField(this.id);"
type="text" 
	   class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if}" 
	   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	   name="{$FIELD_MODEL->getFieldName()}" 
	   value="{$FIELD_MODEL->get('fieldvalue')}"
		{if ($FIELD_MODEL->get('uitype') eq '106' && $MODE neq '') || $FIELD_MODEL->get('uitype') eq '3' 
				|| $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} 
				readonly 
		{/if} 
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />

{*<!-- End code for target module to enter only number -->*}



{else}

<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" 

  {if $MODULE eq "Leads" && $FIELD_MODEL->get("name") eq "registrationno"} 
  onkeyup="ValidateCustomFields();"
maxlength = "11"
{elseif $MODULE eq 'Events' && $FIELD_MODEL->get("name") eq "location"}
    style = "display:none;"
{/if}
type="text" 
	   class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if}" 
	   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	   name="{$FIELD_MODEL->getFieldName()}" 
	   value="{$FIELD_MODEL->get('fieldvalue')}"
		{if ($FIELD_MODEL->get('uitype') eq '106' && $MODE neq '') || $FIELD_MODEL->get('uitype') eq '3' 
				|| $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} 
				readonly 
		{/if} 
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />

{/if}


{* TODO - Handler Ticker Symbol field  ($FIELD_MODEL->get('uitype') eq '106' && $MODE eq 'edit') ||*}
{/strip}