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
   
    {assign var="row_16_targetaudience" value="row_16_targetaudience"|cat:$row_no}
    {assign var="row_1_campaigntype" value="row_1_campaigntype"|cat:$row_no}
    {assign var="row_2_sponsor" value="row_2_sponsor"|cat:$row_no}
    {assign var="row_3_targetsize" value="row_3_targetsize"|cat:$row_no}
    {assign var="row_4_actualsalescount" value="row_4_actualsalescount"|cat:$row_no}
    
    
    {assign var="row_13_extra" value="row_13_extra"|cat:$row_no}
    
   
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
	<td id="{$row_13_extra}">  </td> 
    
   	<td  id="{$row_16_targetaudience}" style="visibility:visible;">
		<input id="{$targetaudience}" name="{$targetaudience}" type="text" class="qty smallInputBox" placeholder="Distance from Outlet" title="Distance from Outlet" style="width:128px; height:18px;"/>
		
	</td >
    
    <td id="{$row_1_campaigntype}" style="visibility:visible;" >
		<select  id="{$campaigntype}"  name="{$campaigntype}" style="width:138px;" title="Type of Activity" >
        <option value='Email'>Type of Activity</option>
		<option value='Email'>Email</option>
        <option value='Telemarketing'>Telemarketing</option>
		<option value='Advertisement'>Advertisement</option>
        </select>
	</td>

    <td id="{$row_2_sponsor}" style="visibility:visible;">
		<input   id="{$sponsor}" name="{$sponsor}" type="text" class="qty smallInputBox" placeholder="Potential" title="Potential" style="width:128px; height:18px;"/>
	</td>

	<td id="{$row_3_targetsize}" style="visibility:visible;"> 
  <input type="text" id="{$targetsize}" name="{$targetsize}"  class="qty smallInputBox"  placeholder="Budget" title="Budget" style="width:128px; height:18px;" />
	</td>
	 
    <td id="{$row_4_actualsalescount}" style="visibility:visible;">
		<input type="text" id="{$actualsalescount}"  name="{$actualsalescount}"  class="qty smallInputBox" placeholder="Vehicle Send to Hub"  title="Vehicle Send to Hub" style="width:128px; height:18px;" />
	</td>
   
     
    