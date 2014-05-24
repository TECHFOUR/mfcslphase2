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
      
    {assign var="startdate" value="startdate"|cat:$row_no}
    {assign var="enddate" value="enddate"|cat:$row_no}
    {assign var="day" value="day"|cat:$row_no}
    {assign var="location" value="location"|cat:$row_no}
    {assign var="distance" value="distance"|cat:$row_no}
    {assign var="potential" value="potential"|cat:$row_no}
    {assign var="budgetcost" value="budgetcost"|cat:$row_no}
    
    {assign var="deleteditemvalue_" value="deleteditemvalue_"|cat:$row_no}
    
    
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
	
	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
    
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
     <div style="display:block;" id="row_item_{$row_no}"> 
	<td>
		<i class="icon-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}" 
        onclick="getDeletedCurrentid(this.id)" id="{$deleteditemvalue_}"></i>
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
	</td>
	<td>
		
		 <input type="Text" id="{$startdate}" maxlength="25" style="width:77px" size="25" onclick="javascript:NewCssCal(this.id)" />
	</td>
	<td>
		 <input type="Text" id="{$enddate}" maxlength="25" style="width:77px" size="25" onclick="javascript:NewCssCal(this.id)" />
		
	</td>
	<td>
		<input id="{$day}" name="{$day}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}{/if}"/>
		
	</td>
	<td>
		<input id="{$location}" name="{$location}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
	<td>
		<input id="{$distance}" name="{$distance}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
    <td>
		<input id="{$potential}" name="{$potential}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
    <td>
		<input id="{$budgetcost}" name="{$budgetcost}" type="text" class="qty smallInputBox"  value="{if !empty($data.$qty)}{$data.$qty}{else}0{/if}"/>
		
	</td>
    </div>