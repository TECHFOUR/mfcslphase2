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
	

<div class="filterContainer">		
	<div class="allConditionContainer conditionGroup contentsBackground well"> 
         {if $FILTER_PERMISSION eq 1 || $FILTER_PERMISSION eq 2 || $FILTER_PERMISSION eq 3}      
		<div class="contents">
			<div class="conditionList">	
            	<div style="width:100%">
                	<table width="100%">
                    	<tr>
							                
                    		<th width="20%"><span><strong>Start Date</strong></span> </th>
                      	<td width="30%">                                                           
                      		<input id="start_date" type="text" class="dateField" name="start_date" data-date-format="{$dateFormat}" />               
                 			<span class="add-on"> <i class="icon-calendar"></i> </span>
                       </td>
                       
                       <th width="20%"><span><strong>End Date</strong></span> </th>
                      	<td>                                                           
                      		<input id="end_date" type="text" class="dateField" name="end_date" data-date-format="{$dateFormat}" />               
                 			<span class="add-on"> <i class="icon-calendar"></i> </span>
                       </td>
                     </tr>
                   </table>
				</div>                                                                  		
			</div>               	                                            			            		
		</div> 
        {/if}
       {if $FILTER_PERMISSION ne 1}
       	{if $FILTER_PERMISSION ne 2} 
         {if $FILTER_PERMISSION ne 3}        
        <div class="contents">
			<div class="conditionList">	
            	<div style="width:100%">
                	<table width="100%">
                    	<tr>
							
                        	<th width="20%"><span><strong>Region</strong></span> </th>                                                                                
                      		<td width="30%">
                            	<select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="region_name" id="region_name" data-fieldinfo='{$FIELD_INFO|escape}' >
                        			<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                            		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$FieldReqionValues}
                        			<option value="{$PICKLIST_NAME}" >{$PICKLIST_VALUE}</option>
                            		{/foreach}
                    			</select>
                            </td>                                                                 		                      
                       <th width="20%"><span><strong>Start Date</strong></span> </th>
                      	<td>                                                           
                      		<input id="start_date" type="text" class="dateField" name="start_date" data-date-format="{$dateFormat}" />               
                 			<span class="add-on"> <i class="icon-calendar"></i> </span>
                       </td>
                      
                     </tr>
                   </table>
				</div>                                                                  		
			</div>               	                                            			            		
		</div> 
         {/if}
        <div class="contents">
			<div class="conditionList">	
            	<div style="width:100%">
                	<table width="100%">
                    	<tr>
                        
							<th width="20%"><span><strong>Outlet</strong></span> </th>                                                                                
                      		<td width="30%">
                            	<select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="outlet_id" id="outlet_id" data-fieldinfo='{$FIELD_INFO|escape}' >
                        			<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                                    <option value="0">All</option>
                            		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$FieldOutLetValues}
                        			<option value="{$PICKLIST_NAME}" >{$PICKLIST_VALUE}</option>
                            		{/foreach}
                    			</select>
                            </td> 
                           {if $FILTER_PERMISSION ne 3}  
                            <th width="20%"><span><strong>End Date</strong></span> </th>
                      	<td width="30%">                                                           
                      		<input id="end_date" type="text" class="dateField" name="end_date" data-date-format="{$dateFormat}" />               
                 			<span class="add-on"> <i class="icon-calendar"></i> </span>
                       </td> 
                       {else}
                       	<th width="20%"><span><strong>Summary</strong></span> </th>
                      	<td width="30%">                                                           
                      		<span class="span10">                                                           
                      		<input type="checkbox" id="summary"   name="summary" /> 
                            </span>                                 			
                       </td>
                       {/if}                                                             		
                     </tr>
                   </table>
				</div>                                                                  		
			</div>               	                                            			            		
		</div> 
         {/if}
          <div class="contents">
			<div class="conditionList">	
            	<div style="width:100%">
                	<table width="100%">
                    	<tr>
							<th width="20%"><span><strong>Agent</strong></span> </th>                                                                                
                                <td width="30%">
                                    {assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
                        {assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->getAccessibleUsersForModule('Services')}
                           <select class="chzn-select {$ASSIGNED_USER_ID}"  data-name="{$CURRENT_USER_ID}" name="agent_id" id="agent_id" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>                   
                            <optgroup>
                                <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>                    
                                {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                        <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $FIELD_VALUE eq $OWNER_ID} selected {/if}
                                            {if array_key_exists($OWNER_ID, $ACCESSIBLE_USER_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if}
                                            data-userId="{$CURRENT_USER_ID}">
                                        {$OWNER_NAME}
                                        </option>
                                {/foreach}
                            </optgroup>		
                        </select>
                            </td>
                           {if $FILTER_PERMISSION ne 3} 
                            <th width="20%"><span><strong>Summary</strong></span> </th>
                      	<td width="30%">
                        	<span class="span10">                                                           
                      		<input type="checkbox" id="summary"   name="summary" /> 
                            </span>                               			
                       </td>                      
                       {else}
                       <td>&nbsp;</td>
                       <td>&nbsp;</td>
                       {/if}
                     </tr>
                   </table>
				</div>                                                                  		
			</div>               	                                            			            		
		</div> 
       {/if}                                          
	</div>     	
</div>
{/strip}