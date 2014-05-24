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

    

    {assign var="targetaudience" value="targetaudience"|cat:$row_no}

    {assign var="campaigntype" value="campaigntype"|cat:$row_no}

    {assign var="sponsor" value="sponsor"|cat:$row_no}

    {assign var="targetsize" value="targetsize"|cat:$row_no}

    {assign var="actualsalescount" value="actualsalescount"|cat:$row_no}

   	{assign var="campaigntype_display" value="campaigntype"|cat:$row_no|cat:"_display"}
    {assign var="Campaigns_editView_fieldName_campaigntype_select" value="Campaigns_editView_fieldName_campaigntype"|cat:$row_no|cat:"_select"}


    {assign var="row_16_targetaudience" value="row_16_targetaudience"|cat:$row_no}

    {assign var="row_1_campaigntype" value="row_1_campaigntype"|cat:$row_no}

    {assign var="row_2_sponsor" value="row_2_sponsor"|cat:$row_no}

    {assign var="row_3_targetsize" value="row_3_targetsize"|cat:$row_no}

    {assign var="row_4_actualsalescount" value="row_4_actualsalescount"|cat:$row_no}

    

    

    {assign var="row_13_extra" value="row_13_extra"|cat:$row_no}

    

   

    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}

	<td id="{$row_13_extra}">  </td> 

    

   	<td  id="{$row_16_targetaudience}" style="visibility:visible;">

		 <span class="redColor">&nbsp;&nbsp;</span><input id="{$targetaudience}" name="{$targetaudience}" type="text" class="qty smallInputBox" placeholder="Distance from Outlet" title="Distance from Outlet" style="width:128px; height:18px;"/>

		

	</td >

    

    <td id="{$row_1_campaigntype}" style="visibility:visible;" >

		 <span class="redColor">*</span>
   <input type="hidden" value="Vendors" name="popupReferenceModule">
    
   <input type="hidden" data-displayvalue="" class="sourceField" value="" name="{$campaigntype}" id="{$campaigntype}">
    
   <input type="text"  placeholder="Type of Activity" title="Type of Activity"  data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" value="" class=" marginLeftZero autoComplete ui-autocomplete-input" name="{$campaigntype_display}" id="{$campaigntype_display}" style="width:128px; height:18px;" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
   <span class="add-on relatedPopup cursorPointer">
   <i title="Select" class="icon-search relatedPopup" id="{$Campaigns_editView_fieldName_campaigntype_select}"></i></span>

	</td>



    <td id="{$row_2_sponsor}" style="visibility:visible;">

		 <span class="redColor">&nbsp;&nbsp;</span><input   id="{$sponsor}" name="{$sponsor}" type="text" class="qty smallInputBox" placeholder="Potential" title="Potential" style="width:128px; height:18px;"/>

	</td>



	<td id="{$row_3_targetsize}" style="visibility:visible;"> 

   <span class="redColor">*</span><input type="text" id="{$targetsize}" name="{$targetsize}"  class="qty smallInputBox" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"  placeholder="Budget Cost" title="Budget Cost" style="width:128px; height:18px;" />

	</td>

	 

    <td id="{$row_4_actualsalescount}" style="visibility:visible;">

		 <span class="redColor">&nbsp;&nbsp;</span><input type="text" id="{$actualsalescount}"  name="{$actualsalescount}"  class="qty smallInputBox" placeholder="Vehicle Send to Hub"  title="Vehicle Send to Hub" style="width:128px; height:18px;" />

	</td>

   

     

    