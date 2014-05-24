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
       
    {assign var="target_check" value="target_check"|cat:$row_no}
    {assign var="outlet" value="outlet"|cat:$row_no}
    {assign var="leaflet" value="leaflet"|cat:$row_no}
    {assign var="poster" value="poster"|cat:$row_no}
    {assign var="tag" value="tag"|cat:$row_no}
    {assign var="total_uploaded_lead" value="total_uploaded_lead"|cat:$row_no}
    {assign var="total_rejected_lead" value="total_rejected_lead"|cat:$row_no}
    
    
    {assign var="row_1_target_check" value="row_1_target_check"|cat:$row_no}
    {assign var="row_2_outlet" value="row_2_outlet"|cat:$row_no}
    {assign var="row_3_leaflet" value="row_3_leaflet"|cat:$row_no}
    {assign var="row_4_poster" value="row_4_poster"|cat:$row_no}
    {assign var="row_5_tag" value="row_5_tag"|cat:$row_no}
    {assign var="row_6_total_uploaded_lead" value="row_6_total_uploaded_lead"|cat:$row_no}
    {assign var="row_7_total_rejected_lead" value="row_7_total_rejected_lead"|cat:$row_no}
    
    
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
	
	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
    
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
	
    
	<td id="{$row_1_target_check}" style="visibility:visible;" >
		vhfghfghfgh<input id="{$target_check}" name="{$target_check}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}{/if}"/>
	</td>
	<td id="{$row_2_outlet}" style="visibility:visible;">
   fghfghfgh <input id="{$outlet}" name="{$outlet}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}{/if}"/>
		
	</td>
	<td id="{$row_3_leaflet}" style="visibility:visible;">
		gfhfghfgh<input id="{$leaflet}" name="{$leaflet}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}{/if}"/>
		
	</td>
	<td id="{$row_4_poster}" style="visibility:visible;">
		<input id="{$poster}" name="{$poster}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
	<td id="{$row_5_tag}" style="visibility:visible;">
		<input id="{$tag}" name="{$tag}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
    <td id="{$row_6_total_uploaded_lead}" style="visibility:visible;">
		<input id="{$total_uploaded_lead}" name="{$total_uploaded_lead}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
    <td id="{$row_7_total_rejected_lead}" style="visibility:visible;">
		<input id="{$total_rejected_lead}" name="{$total_rejected_lead}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>