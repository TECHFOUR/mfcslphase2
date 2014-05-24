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
    
    {assign var="expectedsalescount" value="expectedsalescount"|cat:$row_no}
    {assign var="expectedresponsecount" value="expectedresponsecount"|cat:$row_no}
    {assign var="leaflet" value="leaflet"|cat:$row_no}
    {assign var="poster" value="poster"|cat:$row_no}
     {assign var="tag" value="tag"|cat:$row_no}
    
   
    
    {assign var="row_5_expectedsalescount" value="row_5_expectedsalescount"|cat:$row_no}
    {assign var="row_6_expectedresponsecount" value="row_6_expectedresponsecount"|cat:$row_no}
    {assign var="row_8_leaflet" value="row_8_leaflet"|cat:$row_no}
    {assign var="row_9_poster" value="row_9_poster"|cat:$row_no}
     {assign var="row_10_tag" value="row_10_tag"|cat:$row_no}
    
    {assign var="row_14_extra2" value="row_14_extra2"|cat:$row_no}
    
   
   
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
    
	<td id="{$row_14_extra2}">  </td>
    
     	
    <td id="{$row_5_expectedsalescount}" style="visibility:visible;">
		<input  type="text" id="{$expectedsalescount}" name="{$expectedsalescount}"  class="qty smallInputBox"  placeholder="Target for Tagging" title="Target for Tagging" style="width:128px; height:18px;"/>
	</td>
	
    <td id="{$row_6_expectedresponsecount}" style="visibility:visible;">
		<input id="{$expectedresponsecount}" name="{$expectedresponsecount}"  type="text" class="qty smallInputBox" placeholder="Target for Check Up" title="Target for Check Up" style="width:128px; height:18px;" />
	</td>
    
    <td id="{$row_8_leaflet}" style="visibility:visible;">
		<input type="text"   id="{$leaflet}"  name="{$leaflet}"class="qty smallInputBox" placeholder="Leaflet" title="Leaflet" style="width:128px; height:18px;"/>
	</td>
    
    <td id="{$row_9_poster}" style="visibility:visible;">
		<input type="text"   id="{$poster}" name="{$poster}" class="qty smallInputBox" placeholder="Poster" title="Poster" style="width:128px; height:18px;"/>
	</td>
    
    <td id="{$row_10_tag}" style="visibility:visible;">
		<input type="text" id="{$tag}" name="{$tag}"  class="qty smallInputBox"  placeholder="Tag"  title="Tag" style="width:128px; height:18px;"/>
	</td>
	
   
   
	 
  
	
