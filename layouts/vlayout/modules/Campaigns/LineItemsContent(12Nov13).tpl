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
    {assign var="start_time" value="start_time"|cat:$row_no}
    {assign var="end_time" value="end_time"|cat:$row_no}
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
		
		 <input type="Text" id="{$start_date}" name="{$start_date}" maxlength="25" style="width:128px" size="25"  onclick="javascript:NewCssCal(this.id)" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" placeholder="Start Date" title="Start Date"/>
	</td>
	<td>
		
         <input type="Text" id="{$end_date}" name="{$end_date}" maxlength="25" style="width:128px" size="25" onclick="javascript:NewCssCal(this.id)" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" placeholder="End Date" title="End Date" />
		
	</td>
	<td>
		
        <select id="{$start_time}" name="{$start_time}"  style="width:138px;" title="Start Time">
        		<option value='01:00'>Start Time</option>
                <option value='01:00'>01:00 AM</option>
                <option value='01:30'>01:30 AM</option>
                <option value='02:00'>02:00 AM</option>
                <option value='02:30'>02:30 AM</option>
                <option value='03:00'>03:00 AM</option>
                <option value='03:30'>03:30 AM</option>
                <option value='04:00'>04:00 AM</option>
                <option value='04:30'>04:30 AM</option>
                <option value='05:00'>05:00 AM</option>
                <option value='05:30'>05:30 AM</option>
                <option value='06:00'>06:00 AM</option>
                <option value='06:30'>06:30 AM</option>
                <option value='07:00'>07:00 AM</option>
                <option value='07:30'>07:30 AM</option>
                <option value='08:00'>08:00 AM</option>
                <option value='08:30'>08:30 AM</option>
                <option value='09:00'>09:00 AM</option>
                <option value='09:30'>09:30 AM</option>
                <option value='10:00'>10:00 AM</option>
                <option value='10:30'>10:30 AM</option>
                <option value='11:00'>11:00 AM</option>
                <option value='11:30'>11:30 AM</option>
                <option value='12:00'>12:00 AM</option>
                <option value='12:30'>12:30 AM</option>
                <option value='01:00'>01:00 PM</option>
                <option value='01:30'>01:30 PM</option>
                <option value='02:00'>02:00 PM</option>
                <option value='02:30'>02:30 PM</option>
                <option value='03:00'>03:00 PM</option>
                <option value='03:30'>03:30 PM</option>
                <option value='04:00'>04:00 PM</option>
                <option value='04:30'>04:30 PM</option>
                <option value='05:00'>05:00 PM</option>
                <option value='05:30'>05:30 PM</option>
                <option value='06:00'>06:00 PM</option>
                <option value='06:30'>06:30 PM</option>
                <option value='07:00'>07:00 PM</option>
                <option value='07:30'>07:30 PM</option>
                <option value='08:00'>08:00 PM</option>
                <option value='08:30'>08:30 PM</option>
                <option value='09:00'>09:00 PM</option>
                <option value='09:30'>09:30 PM</option>
                <option value='10:00'>10:00 PM</option>
                <option value='10:30'>10:30 PM</option>
                <option value='11:00'>11:00 PM</option>
                <option value='11:30'>11:30 PM</option>
                <option value='12:00'>12:00 PM</option>
                <option value='12:30'>12:30 PM</option>
        </select>
        
		
	</td>
	<td>
		
        <select id="{$end_time}" name="{$end_time}"  style="width:138px;" placeholder="Start Date" title="End Time" >
        		<option value='01:00'>End Time</option>
                <option value='01:00'>01:00 AM</option>
                <option value='01:30'>01:30 AM</option>
                <option value='02:00'>02:00 AM</option>
                <option value='02:30'>02:30 AM</option>
                <option value='03:00'>03:00 AM</option>
                <option value='03:30'>03:30 AM</option>
                <option value='04:00'>04:00 AM</option>
                <option value='04:30'>04:30 AM</option>
                <option value='05:00'>05:00 AM</option>
                <option value='05:30'>05:30 AM</option>
                <option value='06:00'>06:00 AM</option>
                <option value='06:30'>06:30 AM</option>
                <option value='07:00'>07:00 AM</option>
                <option value='07:30'>07:30 AM</option>
                <option value='08:00'>08:00 AM</option>
                <option value='08:30'>08:30 AM</option>
                <option value='09:00'>09:00 AM</option>
                <option value='09:30'>09:30 AM</option>
                <option value='10:00'>10:00 AM</option>
                <option value='10:30'>10:30 AM</option>
                <option value='11:00'>11:00 AM</option>
                <option value='11:30'>11:30 AM</option>
                <option value='12:00'>12:00 AM</option>
                <option value='12:30'>12:30 AM</option>
                <option value='01:00'>01:00 PM</option>
                <option value='01:30'>01:30 PM</option>
                <option value='02:00'>02:00 PM</option>
                <option value='02:30'>02:30 PM</option>
                <option value='03:00'>03:00 PM</option>
                <option value='03:30'>03:30 PM</option>
                <option value='04:00'>04:00 PM</option>
                <option value='04:30'>04:30 PM</option>
                <option value='05:00'>05:00 PM</option>
                <option value='05:30'>05:30 PM</option>
                <option value='06:00'>06:00 PM</option>
                <option value='06:30'>06:30 PM</option>
                <option value='07:00'>07:00 PM</option>
                <option value='07:30'>07:30 PM</option>
                <option value='08:00'>08:00 PM</option>
                <option value='08:30'>08:30 PM</option>
                <option value='09:00'>09:00 PM</option>
                <option value='09:30'>09:30 PM</option>
                <option value='10:00'>10:00 PM</option>
                <option value='10:30'>10:30 PM</option>
                <option value='11:00'>11:00 PM</option>
                <option value='11:30'>11:30 PM</option>
                <option value='12:00'>12:00 PM</option>
                <option value='12:30'>12:30 PM</option>
            </select>
        
	</td>
    
     <td>
	<input id="{$location}" name="{$location}" type="text" class="qty smallInputBox" placeholder="Location" title="Location" style="width:128px; height:18px;" />
    </td>
	
    
   
   
    