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
{if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'}

	{if $FIELD_MODEL->get('name') eq 'notecontent' && $SOURCE_MODULE eq 'Campaigns'}
  
   <textarea id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
    
    {elseif $MODULE eq 'Events' && $FIELD_MODEL->get("name") eq "description"}
    <textarea id="{$FIELD_NAME}" class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} style=" height:150px;">
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
    
    {else}
    
     <textarea id="{$FIELD_NAME}" class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
    
    {/if}
    
{else}
	{*<!-- Code modified by jitendra singh[TECHFOUR] -->*}
	{if $MODULE eq 'Campaigns' && ($FIELD_MODEL->get("name") eq "other1" || $FIELD_MODEL->get("name") eq "other2") && $RECORD_ID neq '' && $APPROVAL_STATUS eq 'Approved'}
	<table><tr><td>{$FIELD_MODEL->get('fieldvalue')}</td></tr></table>
    <textarea id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" style="display:none;" class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
	
    {else}
    <textarea id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
    {/if}
{/if}
{/strip}