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
    {assign var="notes_title" value="notes_title"|cat:$row_no}
    {assign var="filename" value="filename"|cat:$row_no}    
    {assign var="notecontent" value="notecontent"|cat:$row_no}
    
    
  
    
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}    
	
    <td>	
    <label class="muted pull-left marginRight10px"> <span class="redColor">*</span>Title</label>
    <input id="{$notes_title}" name="{$notes_title}" type="text" class="qty smallInputBox" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
	</td>
    
     <td>
    <label class="muted pull-left marginRight10px"> <span class="redColor">*</span>File Name</label>
    <input type="file"   data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" value=""  name="{$filename}" id="{$filename}" >
	</td>
    
 	<td>				
          <label class="muted pull-left marginRight10px"> Note</label>
          <textarea id="{$notecontent}" name="{$notecontent}" maxlength="25" style="width:200px; height:50px;" > </textarea>
	</td>
	    