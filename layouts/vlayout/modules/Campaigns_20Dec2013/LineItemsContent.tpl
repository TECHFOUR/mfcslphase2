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

    {assign var="targetsize" value="targetsize"|cat:$row_no}

    {assign var="campaigntype" value="campaigntype"|cat:$row_no}
    
    {assign var="campaigntype_display" value="campaigntype"|cat:$row_no|cat:"_display"}
    {assign var="Campaigns_editView_fieldName_campaigntype_select" value="Campaigns_editView_fieldName_campaigntype"|cat:$row_no|cat:"_select"}

    {assign var="location" value="location"|cat:$row_no}


    {assign var="deleted" value="deleted"|cat:$row_no}


    {assign var="deleteditemvalue_" value="deleteditemvalue_"|cat:$row_no}

    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

	{assign var="productDeleted" value="productDeleted"|cat:$row_no}

    

    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}    

	<td>

		<i class="icon-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}" 

        onclick="getDeletedCurrentid(this.id)" id="{$deleteditemvalue_}"></i>

		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>

		<input type="hidden" name="{$deleted}" id="{$deleted}" class="rowNumber" value="{$row_no}" />

	</td>
    
 	<td>

		 <span class="redColor">*</span><input type="Text" id="{$start_date}" name="{$start_date}" maxlength="25" style="width:128px" size="25"  onclick="javascript:NewCssCal(this.id)" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" placeholder="Start Date" title="Start Date"/>

	</td>

	<td>

         <span class="redColor">*</span><input type="Text" id="{$end_date}" name="{$end_date}" maxlength="25" style="width:128px" size="25" onclick="javascript:NewCssCal(this.id)"  placeholder="End Date" title="End Date" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>

	</td>

	<td>
    
    <span class="redColor">*</span><input id="{$location}" name="{$location}" type="text" class="qty smallInputBox" placeholder="Location" title="Location" style="width:128px; height:18px;" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
	
    </td>

	<td>

	<span class="redColor">*</span><input type="text" id="{$targetsize}" name="{$targetsize}"  class="qty smallInputBox"  placeholder="Budget Cost" title="Budget Cost" style="width:128px; height:18px;" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />       

	</td>


    
       <td>
    <label class="muted pull-left marginRight5px"> <span class="redColor">*</span></label>
    <input type="hidden" value="Vendors" name="popupReferenceModule">
    
    <input type="hidden" data-displayvalue="" class="sourceField" value="" name="{$campaigntype}" id="{$campaigntype}">
    
    <!--<span class="add-on clearReferenceSelection cursorPointer"><i title="Clear" class="icon-remove-sign" id="Targets_editView_fieldName_{$salesperson}_clear"></i></span>-->
    
   <input type="text"  placeholder="Type of Activity" title="Type of Activity"  data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" value="" class=" marginLeftZero autoComplete ui-autocomplete-input" name="{$campaigntype_display}" id="{$campaigntype_display}" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
   <span class="add-on relatedPopup cursorPointer">
   <i title="Select" class="icon-search relatedPopup" id="{$Campaigns_editView_fieldName_campaigntype_select}"></i></span>
		
	</td>   