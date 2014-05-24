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
    
    
   
    {assign var="extra3" value="extra3"|cat:$row_no}
    {assign var="other1" value="other1"|cat:$row_no}
    {assign var="other2" value="other2"|cat:$row_no}
    
    {assign var="row_11_other1" value="row_11_other1"|cat:$row_no}
    {assign var="row_12_other2" value="row_12_other2"|cat:$row_no}
       
   
    {assign var="row_17_extra3" value="row_17_extra3"|cat:$row_no}
     {assign var="row_19_extra5" value="row_19_extra5"|cat:$row_no}
    {assign var="row_20_extra6" value="row_20_extra6"|cat:$row_no}
    
   
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
    
    <td id="{$row_17_extra3}">  </td>
    
	
    
    
    <td id="{$row_11_other1}" style="visibility:visible;">
		<textarea  id="{$other1}" name="{$other1}"  class="qty smallInputBox" placeholder="Other1" title="Other1" style="width:128px; height:40px;" /></textarea>
	</td>
     
    <td id="{$row_12_other2}" style="visibility:visible;">
		<textarea id="{$other2}"  name="{$other2}"  class="qty smallInputBox" placeholder="Other2" title="Other2" style="width:128px; height:40px;" /></textarea>
	</td>
    
    <td id="{$row_19_extra5}">  </td>
     <td id="{$row_20_extra6}">  </td>
     
   
  
	
