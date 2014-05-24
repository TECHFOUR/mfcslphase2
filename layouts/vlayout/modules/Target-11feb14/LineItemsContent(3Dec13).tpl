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
	{assign var="deleted" value="deleted"|cat:$row_no}
      
    {assign var="start_date" value="start_date"|cat:$row_no}
    {assign var="end_date" value="end_date"|cat:$row_no}    
    {assign var="target" value="target"|cat:$row_no}
    {assign var="salesperson" value="salesperson"|cat:$row_no}
    {assign var="salesperson_display" value="salesperson"|cat:$row_no|cat:"_display"}
    {assign var="Targets_editView_fieldName_salesperson_select" value="Targets_editView_fieldName_salesperson"|cat:$row_no|cat:"_select"}
    {assign var="deleted" value="deleted"|cat:$row_no}
    {assign var="revenue" value="revenue"|cat:$row_no}
    
    
  
    
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}    
	<td>
		<i class="icon-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}" ></i>
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
		<input type="hidden" name="{$deleted}" id="{$deleted}" class="rowNumber" value="{$row_no}" />
	</td>
    
    <td>				
          <label class="muted pull-left marginRight5px"> <span class="redColor">*</span></label>
          <input type="Text" id="{$start_date}" placeholder="Start Date" title="Start Date"  name="{$start_date}" maxlength="25" style="width:128px" size="25"  onclick="javascript:NewCssCal(this.id)" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>
	</td>
	<td>		
         <label class="muted pull-left marginRight5px"><span class="redColor">*</span></label> 
         <input type="Text" id="{$end_date}" placeholder="End Date" title="End Date" name="{$end_date}" maxlength="25" style="width:128px" size="25" onclick="javascript:NewCssCal(this.id)" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
		
	</td>	
           
     <td>
    <label class="muted pull-left marginRight5px"> <span class="redColor">*</span></label>
    <input type="hidden" value="Users" name="popupReferenceModule">
    
    <input type="hidden" data-displayvalue="" class="sourceField" value="" name="{$salesperson}" id="{$salesperson}">
    
    <!--<span class="add-on clearReferenceSelection cursorPointer"><i title="Clear" class="icon-remove-sign" id="Targets_editView_fieldName_{$salesperson}_clear"></i></span>-->
    
   <input type="text"  placeholder="Sales Person" title="Sales Person"  data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" value="" class=" marginLeftZero autoComplete ui-autocomplete-input" name="{$salesperson_display}" id="{$salesperson_display}" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
   <span class="add-on relatedPopup cursorPointer">
   <i title="Select" class="icon-search relatedPopup" id="{$Targets_editView_fieldName_salesperson_select}"></i></span>
		
	</td>   
     <td>	
    <label class="muted pull-left marginRight5px"> <span class="redColor">*</span></label>
    <input id="{$target}" name="{$target}" type="text" placeholder="No of ROs" style="width:128px" title="No of ROs" class="qty smallInputBox" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
		
	</td>
     <td>	
    <label class="muted pull-left marginRight5px"> <span class="redColor">*</span></label>
    <input id="{$revenue}" name="{$revenue}" type="text" placeholder="Revenue" style="width:128px" maxlength="10" title="Revenue" class="qty smallInputBox" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
		
	</td>  		    