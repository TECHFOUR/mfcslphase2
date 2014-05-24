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
    {assign var="manpower" value="manpower"|cat:$row_no}
    {assign var="leaflet" value="leaflet"|cat:$row_no}
    {assign var="poster" value="poster"|cat:$row_no}
    {assign var="tag" value="tag"|cat:$row_no}
    {assign var="other1" value="other1"|cat:$row_no}
    {assign var="other2" value="other2"|cat:$row_no}
    {assign var="extra2" value="extra2"|cat:$row_no}
    
    {assign var="row_7_manpower" value="row_7_manpower"|cat:$row_no}
    {assign var="row_8_leaflet" value="row_8_leaflet"|cat:$row_no}
    {assign var="row_9_poster" value="row_9_poster"|cat:$row_no}
    {assign var="row_10_tag" value="row_10_tag"|cat:$row_no}
    {assign var="row_11_other1" value="row_11_other1"|cat:$row_no}
    {assign var="row_12_other2" value="row_12_other2"|cat:$row_no}
    {assign var="row_14_extra2" value="row_14_extra2"|cat:$row_no}
    
   
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
    
	<td id="{$row_14_extra2}">  </td>
   
    <td id="{$row_7_manpower}" style="visibility:visible;"><strong>Manpower&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input type="text" id="{$manpower}" name="{$manpower}"  class="qty smallInputBox"  />
	</td>
     
    <td id="{$row_8_leaflet}" style="visibility:visible;"><strong>Leaflet&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input type="text"   id="{$leaflet}"  name="{$leaflet}"class="qty smallInputBox"  />
	</td>
    
    <td id="{$row_9_poster}" style="visibility:visible;"> <strong>Poster&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input type="text"   id="{$poster}" name="{$poster}" class="qty smallInputBox" />
	</td>
	
    <td id="{$row_10_tag}" style="visibility:visible;"><strong>Tag&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input type="text" id="{$tag}" name="{$tag}"  class="qty smallInputBox"  />
	</td>
     
    <td id="{$row_11_other1}" style="visibility:visible;"><strong>Other1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input type="text"  id="{$other1}" name="{$other1}"  class="qty smallInputBox" />
	</td>
     
    <td id="{$row_12_other2}" style="visibility:visible;"><strong>Other2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input type="text" id="{$other2}"  name="{$other2}"  class="qty smallInputBox"  />
	</td>
	 
  
	
