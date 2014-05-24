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
    <!--
    All final details are stored in the first element in the array with the index name as final_details
    so we will get that array, parse that array and fill the details
    -->
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

    {assign var="IS_INDIVIDUAL_TAX_TYPE" value=false}
    {assign var="IS_GROUP_TAX_TYPE" value=true}

    {if $FINAL.taxtype eq 'individual'}
        {assign var="IS_GROUP_TAX_TYPE" value=false}
        {assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
    {/if}
 {*<!-- Code modified by jitendra singh [TECHFOUR] -->*}
    <table class="table table-bordered blockContainer lineItemTable" id="lineItemTab">
        <tr>
            <th colspan="3"><span class="inventoryLineItemHeader">{vtranslate('More Documents', $MODULE)}</span></th>
            
        </tr>
       
       
         
       
         {if count($RELATED_PRODUCTS) eq 0}
                   
            <tr id="row1" class="lineItemRow">
                {include file="LineItemsContent.tpl"|@vtemplate_path:'Documents' row_no=1 data=[]}
            </tr>
            <tr id="row1" class="lineItemRow">
                {include file="LineItemsContent.tpl"|@vtemplate_path:'Documents' row_no=2 data=[]}
            </tr>
           
                         
        {/if}
                               
        
    </table>
 

   
{/strip}