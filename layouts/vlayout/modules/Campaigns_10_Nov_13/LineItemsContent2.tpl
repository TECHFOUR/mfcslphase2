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
    
    {assign var="campaigntype" value="campaigntype"|cat:$row_no}
    {assign var="sponsor" value="sponsor"|cat:$row_no}
    {assign var="targetsize" value="targetsize"|cat:$row_no}
    {assign var="expectedsalescount" value="expectedsalescount"|cat:$row_no}
    {assign var="actualsalescount" value="actualsalescount"|cat:$row_no}
    {assign var="expectedresponsecount" value="expectedresponsecount"|cat:$row_no}
    {assign var="extra" value="extra"|cat:$row_no}
    
    {assign var="row_1_campaigntype" value="row_1_campaigntype"|cat:$row_no}
    {assign var="row_2_sponsor" value="row_2_sponsor"|cat:$row_no}
    {assign var="row_3_targetsize" value="row_3_targetsize"|cat:$row_no}
    {assign var="row_4_actualsalescount" value="row_4_actualsalescount"|cat:$row_no}
    {assign var="row_5_expectedsalescount" value="row_5_expectedsalescount"|cat:$row_no}
    {assign var="row_6_expectedresponsecount" value="row_6_expectedresponsecount"|cat:$row_no}
    {assign var="row_13_extra" value="row_13_extra"|cat:$row_no}
    
   
    {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
	<td id="{$row_13_extra}">  </td> 
    <td id="{$row_1_campaigntype}" style="visibility:visible;">
		<strong>Type of Activity</strong><select  id="{$campaigntype}"  name="{$campaigntype}" style="width:100px;" >
		<option value='Email'>Email</option>
        <option value='Telemarketing'>Telemarketing</option>
		<option value='Advertisement'>Advertisement</option>
        </select>
	</td>

    <td id="{$row_2_sponsor}" style="visibility:visible;"><strong>Potential&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
		<input   id="{$sponsor}" name="{$sponsor}" type="text" class="qty smallInputBox"  />
	</td>

    <td id="{$row_3_targetsize}" style="visibility:visible;"> <strong>Budget&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
  <input type="text" id="{$targetsize}" name="{$targetsize}"  class="qty smallInputBox"  />
	</td>
	 
    <td id="{$row_4_actualsalescount}" style="visibility:visible;"><strong>Vehicle Send to Hub</strong>
		<input type="text" id="{$actualsalescount}"  name="{$actualsalescount}"  class="qty smallInputBox"  />
	</td>
	
    <td id="{$row_5_expectedsalescount}" style="visibility:visible;"> <strong>Target for Tagging</strong>
		<input  type="text" id="{$expectedsalescount}" name="{$expectedsalescount}"  class="qty smallInputBox"  />
	</td>
	
    <td id="{$row_6_expectedresponsecount}" style="visibility:visible;"><strong>Target for Check Up</strong>
		<input id="{$expectedresponsecount}" name="{$expectedresponsecount}"  type="text" class="qty smallInputBox"  />
	</td>
     
    