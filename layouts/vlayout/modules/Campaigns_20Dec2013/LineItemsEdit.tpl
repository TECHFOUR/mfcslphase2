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

   

                        <button type="button" class="btn addButton" id="addProduct">

                            <i class="icon-plus icon-white"></i><strong>{vtranslate('Add More',$MODULE)}</strong>

                        </button>

               

    </tr>

        <tr>

            <th colspan="3"><span class="">{vtranslate('More Campaigns', $MODULE)}</span></th>

            <th colspan="1" >          

            </th>

            <th colspan="1" >

            </th>

            <th colspan="1" >

            </th>

        </tr>


        <tr id="row0" class="hide lineItemCloneCopy">

            {include file="LineItemsContent.tpl"|@vtemplate_path:'Campaigns' row_no=0 data=[]}

        </tr>

        {if count($RELATED_PRODUCTS) eq 0}

            <tr id="row1" class="lineItemRow">

                {include file="LineItemsContent.tpl"|@vtemplate_path:'Campaigns' row_no=1 data=[]}

            </tr>
        {/if}

    </table>


 {*<!--   

    <input type="hidden" name="totalProductCount" id="totalProductCount" value="{$row_no}" />

    <input type="hidden" name="subtotal" id="subtotal" value="" />

    <input type="hidden" name="total" id="total" value="" />

    

  -->*} 

{/strip}